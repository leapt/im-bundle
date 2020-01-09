Upgrade from 2.x to 3.x
-----------------------

* Requires Symfony 4.4+
* Requires PHP 7.2+

The only changes that are BC are the following:

* The main path is now the project dir, not the old root dir of Symfony
* The `leapt_im.web_path` has been renamed to `leapt_im.public_path` and now points to `public` by default instead of
`../public`, as path starts from project dir instead of root dir
