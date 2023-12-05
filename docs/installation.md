# Installation

## Requirements

* You need to have the ImageMagick binaries available (convert & mogrify)
* You need to have a cache folder in your web dir, writeable by the webserver
* Symfony 6.4/7.0+
* PHP 8.2+

## Install steps

If Symfony Flex is installed, the only thing you have to do is requiring the package with composer:

```bash
composer require leapt/im-bundle
```

The bundle will automatically be registered in the `bundles.php` file.

### If Flex is not installed

#### Activate the bundle

config/bundles.php (if not automatically added by Symfony Flex)

In `config/bundles.php` add the following line:

```php
Leapt\ImBundle\LeaptImBundle::class => ['all' => true],
```

#### Add routing

Create a `config/routes/leapt_im.yaml` and add the following configuration:

```yaml
leapt_im:
    resource: "@LeaptImBundle/Resources/config/routing.php"
```
