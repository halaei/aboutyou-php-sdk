## ABOUT YOU App SDK

# Documentation

See [ABOUT YOU Developer Center](https://developer.aboutyou.de/) for more Information.

# Installation

The recommended way to install the ShopAPI is through [Composer](http://getcomposer.org).

```bash
# Install Composer
curl -sS https://getcomposer.org/installer | php
```

# Add the App SDK as a dependency.
The App SDK depends on additional packages that are not on packagist, for these a repository entry is required.

```json
    {
        "repositories": [
            {
                "type": "git",
                "url": "https://github.com/goetas/cache.git"
            },
            {
                "type": "git",
                "url": "https://app-developers-89:98ashUZsujna!isi.asU7@antevorte.codebasehq.com/public-sdks-2/php-jws.git"
            },
            {
                "type": "git",
                "url": "https://app-developers-89:98ashUZsujna!isi.asU7@antevorte.codebasehq.com/public-sdks-2/php-auth-sdk.git"
            }
        ],
        "require": {
            "aboutyou/app-sdk": "*"
        }
    }
```
After installing, you need to require Composer's autoloader:

```php
require 'vendor/autoload.php';
```

## Usage

Example how to use the App SDK with the mono logger, the log detail depends on yii debug configuration.

```php
use Collins\ShopApi;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

    $logger = new Logger('name');
    $logger->pushHandler(new StreamHandler(Yii::app()->getRuntimePath(). '/mono.log', Logger::DEBUG));

    $api = new ShopApi($appId, $appPassword, $apiHost, $logger);
    if (YII_DEBUG) {
        $api->setLogTemplate(\Guzzle\Log\MessageFormatter::DEBUG_FORMAT);
    } else {
        $api->setLogTemplate(\Guzzle\Log\MessageFormatter::SHORT_FORMAT);
    }
```

Example how to use the App SDK with the apc cache.

```php
    $cache = new \Doctrine\Common\Cache\ApcCache();
    $api = new \Collins\ShopApi($appId, $appPassword, $apiHost, null, null, $cache);
```

To precache facets per cron (hourly pre caching is preferred), you can write a new php file to do that

```php
#/usr/bin/env php
<?php
// filename precache-cron.php
require 'myconfig.php';
require 'vendor/autoload.php';

$cache = new \Doctrine\Common\Cache\ApcCache();
$shopApi = new \Collins\ShopApi($appId, $appPassword, $shopApiHost, null, null, $cache);
/** @var DefaultFacetManager $facetManager */
$facetManager = $shopApi->getResultFactory()->getFacetManager();
/** @var DoctrineMultiGetCacheStrategy $doctrineMultiGetCacheStrategy */
$doctrineMultiGetCacheStrategy = $facetManager->getFetchStrategy();

$doctrineMultiGetCacheStrategy->cacheAllFacets($shopApi);
```

## Testing

```bash
vendor/bin/phpunit
```
