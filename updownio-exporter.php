<?php
if (php_sapi_name() != 'cli-server') {
    die("The script is designed to be run with PHP inbuilt webserver \n
        UPDOWN_TOKEN={token} php -S localhost:8000 updownio-exporter.php \n");
}

require 'vendor/autoload.php';
use Foinikas\Updown\Updown;
use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;

$adapter = new Prometheus\Storage\InMemory();
$registry = new CollectorRegistry($adapter);
$renderer = new RenderTextFormat();

$service = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
/*
 * Simple health check
 * curl -X GET http://localhost:8000/health
 */
if ($service == '/health') {
    header('Content-type: ' . RenderTextFormat::MIME_TYPE);
    echo "I'm alive! 8)";
    log_to_console("SUCCESS - Health check OK");
} elseif ($service == '/metrics') {
    /*
     * Get Metrics
     * curl -X GET http://localhost:8000/metrics/?target={url}
     */
    $target = false;
    if (isset($_GET['target'])) {
        $target = $_GET['target'];
        //We always assume the full URL is provided, prometheus will always send HTTPS
        $target = rtrim(filter_var($target, FILTER_SANITIZE_URL), '/');
    }
    if (! filter_var($target, FILTER_VALIDATE_URL)) {
        header('HTTP/1.1 400 Bad Request');
        if (isset($target) && $target !== false) {
            render_error('Invalid target', 400, 'ERROR - Invalid target: '.$target);
        } else {
            render_error('No target specified', 400, 'ERROR - No target');
        }
    }

    $metrics = get_metrics($target);

    if (!$metrics) {
        render_error('No metrics found', 404);
    }
    $gauge = $registry->GetOrregisterGauge(
        'updownio',
        'info',
        'it sets',
        ['url','alias','token', 'last_check_at', 'next_check_at']
    );
    $gauge->set(
        1,
        [$metrics['url'],
                $metrics['alias'],
                $metrics['token'],
                $metrics['last_check_at'],
                $metrics['next_check_at']
        ]
    );

    //Bit of a head twist we are telling Prometheus its up by checking if its down
    $down = $registry->GetOrregisterGauge('updownio', 'status', 'current state');
    if ($metrics['down'] == true) {
        $down->set(1);
    } else {
        $down->set(0);
    }
    $state = $registry->GetOrregisterGauge('updownio', 'http_status_code', 'HTTP Code');
    if (!$metrics['last_status']) {
        $state->set(0);
    } else {
        $state->set($metrics['last_status']);
    }
    $uptime = $registry->GetOrregisterGauge('updownio', 'uptime', '% uptime over 28 days according to updown.io');
    $uptime->set($metrics['uptime']);

    $apdex = $registry->GetOrregisterGauge('updownio', 'apdex', 'Relative Apdex score according to updown.io');
    $apdex->set($metrics['apdex']);


    $result = $renderer->render($registry->getMetricFamilySamples());
    header('Content-type: ' . RenderTextFormat::MIME_TYPE);
    echo $result;
} else {
    // Routes to default 404
    return false;
}

// Helper functions

/*
 * Get Metrics
 * Connects and returns metrics from updown.io
 * @param string $target
 * @return array
 */
function get_metrics($site = false)
{
    //API Token can be got https://updown.io/api use your ro_ token
    $updown = new Updown(getenv('UPDOWN_TOKEN'));
    $checks = json_decode($updown->checks());
    if (isset($checks->error)) {
        log_to_console("ERROR - Updown API returned: $checks->error");
        return false;
    }
    $metrics = [];
    foreach ($checks as $check) {
        $metrics[$check->url] = [
            'url' => $check->url,
            'token' => $check->token,
            'alias' => $check->alias,
            'last_status' => $check->last_status,
            'uptime' => $check->uptime,
            'down' => $check->down,
            'apdex' => $check->apdex_t,
            'last_check_at' => $check->last_check_at,
            'next_check_at' => $check->next_check_at,
        ];
    }

    if (isset($site) && isset($metrics[$site])) {
        return $metrics[$site];
    }
    return false;
}
/*
 * Log to stdout and therefore docker container logs
 * @param string $message
 */
function log_to_console($message = false)
{
    if ($message) {
        $out = fopen('php://stdout', 'w');
        $time = date('D M d H:i:s Y');
        fputs($out, "[$time] $message\n");
        fclose($out);
    }
}

/*
 * Render error output and optionally log
 * @param string $message
 * @param int $code
 * @param string $log_message
 *
 */
function render_error($message, $code = 400, $log_message = false)
{
    $codes = [
        400 => 'Bad Request',
        404 => 'Not Found',
    ];
    header('HTTP/1.1 '.$code.' '.$codes[$code]);
    echo $message;
    if ($log_message) {
        log_to_console($log_message);
    }
    exit;
}
