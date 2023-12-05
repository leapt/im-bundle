Upgrade from 4.x to 5.x
-----------------------

* Drop support for PHP < 8.1
* Drop support for Symfony < 6.4 & Twig 2
* Add support for Symfony 7
* Require leapt/core-bundle 5
* Update directory structure
* Remove YAML routing file

The path of the routing file has changed, you have to update them manually or sync the recipe:

```yaml
# config/routes/leapt_im.yaml
leapt_im:
  resource: '@LeaptImBundle/config/routing.php'
```
