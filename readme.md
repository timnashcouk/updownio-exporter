<!-- TABLE OF CONTENTS -->
<details>
  <summary>Table of Contents</summary>
  <ol>
    <li>
      <a href="#about-the-project">About The Project</a>
    </li>
    <li>
      <a href="#getting-started">Getting Started</a>
      <ul>
        <li><a href="#prerequisites">Prerequisites</a></li>
        <li><a href="#installation">Installation</a></li>
      </ul>
    </li>
    <li><a href="#usage">Usage</a></li>
    <li><a href="#security">Security</a></li>
    <li><a href="#contributing">Contributing</a></li>
    <li><a href="#license">License</a></li>
  </ol>
</details>



<!-- ABOUT THE PROJECT -->
## About Updown.io Exporter

Simple Exporter for Prometheus written in PHP that acts as a proxy between Prometheus and [updown.io](https://updown.io/r/vRtA2)(*aff*) to allow out of network uptime monitoring.

Designed to be run in a docker container, using the built in PHP Webserver.

Updown.io is a simple web app that does uptime monitoring, it has a simple UI but has several nice features and a good easy to use API. This project is not associated with updown.io though some links marked with *aff* are referral links for @timnashcouk. 

<p align="right">(<a href="#top">back to top</a>)</p>



<!-- GETTING STARTED -->
## Getting Started

Updown.io Exporter is designed to be run in a docker container, but install instructions are provided for standalone as well.

### Prerequisites
- Docker 

or

- PHP > 8.0

In addition

- You will need a API key from updown.io which can be got from https://updown.io/api


### Installation

### Standalone
The script uses composer so before running you will need to do:

```composer install ```

Then to launch from the CLI:

```UPDOWN_TOKEN={token} php -q -S localhost:9128 updownio-exporter.php```
Using `-q` parameter will silence the connection notices to stdout.

<p align="right">(<a href="#top">back to top</a>)</p>

### Docker file
There is a provided docker file, its designed to be standalone with no need to mount volumes etc

```docker build -t "updownio-exporter:Dockerfile" .```

To run:

```
docker run \
  -p 9124:9124 \
  -e UPDOWN_TOKEN=YOURAPIKEY \
  updownio-exporter
```


<!-- USAGE EXAMPLES -->
## Usage
Assuming you have kept the port the same confirm it's working by visiting:
```http://localhost/9124:health```
You should be greated with a confirmation message.

To access metrics use:
```http://localhost:9124/metrics/?target={url}```
the URL should match the URL within updown.io, EXCLUDING the protocol that is checked (it assumes https:// )

Within Prometheus scrape configs need to be set up, they match Prometheus Blackbox Exporter

```
  - job_name: "updown.io"
    params:
      module: [http_prometheus]
    static_configs:
      - targets:
        - example.com
    relabel_configs:
      - source_labels: [__address__]
        target_label: __param_target
      - source_labels: [__param_target]
        target_label: instance
      - target_label: __address__
        replacement: yourserver:9124
```
If you recieve "is not valid hostname" error make sure your target is a hostname and not the full URL with https://.

<p align="right">(<a href="#top">back to top</a>)</p>

<!-- SECURITY -->
## Security

The script has pretty limited scope but it also doesn't have any authentication so should be ran on an internal network, or restricted to be accessed only by your prometheus server.

When adding your updown.io API key, you can choose and recommend to use your read only API key. The script does not require write access.

If you do find a security issue, please do contact me security AT timnash.co.uk but beaware this is a side project and I cannot provide financial renumeration for security issues raised.

<!-- CONTRIBUTING -->
## Contributing

Contributions are what make the open source community such an amazing place to learn, inspire, and create. Any contributions you make are **greatly appreciated**.

If you have a suggestion that would make this better, please fork the repo and create a pull request. You can also simply open an issue with the tag "enhancement".
Don't forget to give the project a star! Thanks again!

1. Fork the Project
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the Branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

<p align="right">(<a href="#top">back to top</a>)</p>



<!-- LICENSE -->
## License

Distributed under the MIT License. See `LICENSE.txt` for more information.

<p align="right">(<a href="#top">back to top</a>)</p>



<!-- ACKNOWLEDGMENTS -->
## Acknowledgments

very much inspired by https://github.com/eze-kiel/uptimerobot-exporter project which does the same thing but for uptimerobot and in Go.

<p align="right">(<a href="#top">back to top</a>)</p>


