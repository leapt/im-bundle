# Usage

## Twig tag

```html+twig
{% imresize %}
    <p>some content</p>
    <img src="/some/path" width="100">
    <img src="{{ asset('/some/path') }}" width="100">
{% endimresize %}
```

This will parse all content inside the tag and render image caches regarding their HTML width and/or height attributes.

## Twig function

```html+twig
<img src="{{ imresize('/some/path', 'small') }}">
<img src="{{ imresize('/some/path', '120x') }}">
<img src="{{ imresize('/some/path', 'x120') }}">
<img src="{{ imresize('/some/path', '120x120') }}">
```

The format - the second argument - can be a predefined format in your configuration, or a [width]x[height] syntax.

## Twig filter

```html+twig
<img src="{{ '/some/path'|imresize('small') }}">
<img src="{{ asset('/some/path')|imresize('small') }}">
```

## From a controller or a service

```php
use Leapt\ImBundle\Manager;

// ...

public function __construct(
    private Manager $imManager,
) {
}

// ... 

// To create a cached file
$this->imManager->convert($format, $path);

// To resize the source file
$this->imManager->mogrify($format, $path);
```

## In entities

If you need to alter an uploaded image, you can add attributes on the file public property from your entity:

```php
use Leapt\ImBundle\Doctrine\Mapping as LeaptIm;
// ...

#[Assert\File(maxSize: '6M')]
#[LeaptIm\Mogrify(params: ['thumbnail' => '100x100>'])]
public $file;
```

When the form is submitted, the file will then be "thumbnailed" to 100x100 if bigger. You can then use the `$file->move()`
method like usual.

The `params` parameter can contain:

* an array of ImageMagick key/values (like the example above)
* a format predefined in config

## Clearing the cache

You can clear the cache with the following command-line task

```bash
./bin/console leapt:im:clear [age]
```

Where the age argument - optional - will only clear cache older than the [age] days.
