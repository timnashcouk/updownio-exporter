<?php
if (php_sapi_name() != 'cli-server') {
    die("The script is designed to be run with PHP inbuilt webserver \nUPDOWN_TOKEN={token} php -S localhost:8000 updownio-exporter.php \n");
 }

require 'vendor/autoload.php';
use Foinikas\Updown\Updown;
use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;

$adapter = new Prometheus\Storage\InMemory();
$registry = new CollectorRegistry($adapter);
$renderer = new RenderTextFormat();

$service = rtrim(parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH),'/');
if( $service == '/health' ) {
    header('Content-type: ' . RenderTextFormat::MIME_TYPE);
    echo "I'm alive! 8)";
}
elseif( $service == '/metrics' ) {

    $target = false;
    if(isset($_GET['target'])){
        $target = $_GET['target'];
        //Always assuming it' over https
        $target = rtrim(filter_var('https://'.$target, FILTER_SANITIZE_URL),'/');
    }  
    if(! filter_var( $target, FILTER_VALIDATE_URL ) ) {
        header('HTTP/1.1 400 Bad Request');
        echo "Invalid target";
        exit;
    }

    $metrics = get_metrics($target);

    if(!$metrics) {
        header('HTTP/1.1 404 Not Found');
        echo "No metrics found";
        exit;
    }
    $gauge = $registry->GetOrregisterGauge('updownio', 'info', 'it sets', ['url','alias','token', 'last_check_at', 'next_check_at']);
    $gauge->set(1, [$metrics['url'],$metrics['alias'],$metrics['token'], $metrics['last_check_at'], $metrics['next_check_at']]);

    //Bit of a head twist we are telling Prometheus its up by checking if its down
    $down = $registry->GetOrregisterGauge('updownio', 'status', 'current state');
    if( $metrics['down'] == true ) {
        $down->set(1);
    }
    else {
        $down->set(0);
    }
    $state = $registry->GetOrregisterGauge('updownio', 'http_status_code', 'HTTP Code');
    if(!$metrics['last_status']){
        $state->set(0);
    }else{
        $state->set($metrics['last_status']);
    }
    $uptime = $registry->GetOrregisterGauge('updownio', 'uptime', '% uptime over 28 days according to updown.io');
    $uptime->set($metrics['uptime']);

    $apdex = $registry->GetOrregisterGauge('updownio', 'apdex', 'Relative Apdex score according to updown.io');
    $apdex->set($metrics['apdex']);


    $result = $renderer->render($registry->getMetricFamilySamples());
    header('Content-type: ' . RenderTextFormat::MIME_TYPE);
    echo $result;
}
else{
    return false;
}

function get_metrics( $site = false ){
    //API Token can be got https://updown.io/api use your ro_ token
    $updown = new Updown(getenv('UPDOWN_TOKEN'));
    $checks = json_decode($updown->checks());
    if( isset($checks->error)){
        error_log($checks->error, 0);
        return false;
    }
    $metrics = [];
    foreach( $checks as $check ){

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

    if( isset($site) && isset($metrics[$site]) ) {
        return $metrics[$site];
    }
    return false;
}