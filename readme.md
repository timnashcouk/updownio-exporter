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

Simple Exporter for Prometheus written in PHP that acts as a proxy between Prometheus and Updown.io to allow out of network uptime monitoring.
Designed to be run in a docker container, using the built in PHP Webserver.

<p align="right">(<a href="#top">back to top</a>)</p>



<!-- GETTING STARTED -->
## Getting Started

Set the PHP Server up and running
```UPDOWN_TOKEN={token} php -S localhost:9128 updownio-exporter.php```
Then scrape it from prometheus.

### Prerequisites

- PHP > 8.0
- You will need a API key from updown.io which can be got from https://updown.io/api


### Installation

### Standalone
The script uses composer so before running you will need to do:
```composer update ```

<p align="right">(<a href="#top">back to top</a>)</p>

### Docker file


<!-- USAGE EXAMPLES -->
## Usage
Assuming you have kept the port the same confirm it's working by visiting:
```http://localhost/9128/health```
You should be greated with a confirmation message.

To access metrics use:
```http://localhost:9124/metrics/?target={url}```
the URL should match the URL within updown.io, including the protocol that is checked.


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

Use this space to list resources you find helpful and would like to give credit to. I've included a few of my favorites to kick things off!

* [Choose an Open Source License](https://choosealicense.com)
* [GitHub Emoji Cheat Sheet](https://www.webpagefx.com/tools/emoji-cheat-sheet)
* [Malven's Flexbox Cheatsheet](https://flexbox.malven.co/)
* [Malven's Grid Cheatsheet](https://grid.malven.co/)
* [Img Shields](https://shields.io)
* [GitHub Pages](https://pages.github.com)
* [Font Awesome](https://fontawesome.com)
* [React Icons](https://react-icons.github.io/react-icons/search)

<p align="right">(<a href="#top">back to top</a>)</p>


