Installation
============

Requirements
------------

* You need to have the ImageMagick binaries available (convert & mogrify)
* You need to have a cache folder in your web dir, writeable by the webserver
* Symfony >= 2.7

Add the bundle in your project
------------------------------

.. code-block:: json

  {
      "require": {
          "leapt/im-bundle": "~1.0"
      }
  }

Activate the bundle
-------------------

app/AppKernel.php

.. code-block:: php

    new Leapt\ImBundle\LeaptImBundle(),

Add routing
-----------

app/config/routing.yml

.. code-block:: yaml

    leapt_im:
        resource: "@LeaptImBundle/Resources/config/routing.yml"
