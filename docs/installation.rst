Installation
============

Requirements
------------

* You need to have the ImageMagick binaries available (convert & mogrify)
* You need to have a cache folder in your web dir, writeable by the webserver
* Symfony >= 4.4

Add the bundle in your project
------------------------------

.. code-block:: bash

  composer require leapt/im-bundle

Activate the bundle
-------------------

config/bundles.php (if not automatically added by Symfony Flex)

.. code-block:: php

    Leapt\ImBundle\LeaptImBundle::class => ['all' => true],

Add routing
-----------

config/routes/leapt_im.yaml (if not automatically added by Symfony Flex)

.. code-block:: yaml

    leapt_im:
        resource: "@LeaptImBundle/Resources/config/routing.yml"
