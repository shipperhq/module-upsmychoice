# module-upsmychoice

## Testing
If you've installed the module into an existing magento store using composer then the dev dependencies will not be installed with the code. You need to run `composer require --dev "fooman/magento2-phpunit-bridge":"^0.9.0"` to install the dependencies.

To run all tests for this module use

```sh
# From the Magento store root
php ./vendor/bin/phpunit -c dev/tests/unit/phpunit.xml.dist vendor/shipperhq/module-upsmychoice/Test/*
```