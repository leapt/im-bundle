6.2.0
-----

* Serve already cached images without regenerating them (and without re-downloading remote images) on each request
* Serve cached images using a `BinaryFileResponse`, with `ETag`, `Last-Modified` & long-lived `Cache-Control` headers (and `304 Not Modified` support)
* Return a 404 response for image paths or formats containing `..` segments
* Add `Manager::getCacheFilePath()`
* Do not keep invalid or partially downloaded remote images in the cache

6.1.0
-----

* Fix usage with AssetMapper
* Fix a shell command injection vulnerability in `Wrapper` (image format/paths were not escaped before being passed to the shell)
* Fix a shell command injection vulnerability in `leapt:im:clear` (cache directory path was not escaped before being passed to the shell)
* Fix a regex injection in `ImExtension::convert()` that could break image resizing for `src` attributes containing regex-special characters

6.0.0
-----

* Drop support for PHP < 8.4
* Drop support for Symfony < 7.4 & Twig < 3.23
* Require leapt/core-bundle 6
