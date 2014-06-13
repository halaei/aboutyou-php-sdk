# Shop API SDK

## Installation

The recommended way to install the ShopAPI is through [Composer](http://getcomposer.org).

```bash
# Install Composer
curl -sS https://getcomposer.org/installer | php
```

# Add the ShopAPI as a dependency

```json
    {
        "repositories": [
             {
                "type": "git",
                "url": "https://github.com/goetas/cache.git"
            },
            {
                "type": "git",
                "url": "https://app-developers-89:98ashUZsujna!isi.asU7@antevorte.codebasehq.com/public-sdks-2/php-auth-sdk.git"
            },
            {
                "type": "git",
                "url": "https://app-developers-89:98ashUZsujna!isi.asU7@antevorte.codebasehq.com/public-sdks-2/php-jws.git"
            },
            {
                "type": "git",
                "url": "https://github.com/aboutyou/PHP-SDK.git"
            }

        ],
        "require": {
            "collins/shop-sdk": "0.0.*",
            "collins/php-auth-sdk": "0.*",
            "doctrine/cache": "dev-multiget"
        }
    }

```
After installing, you need to require Composer's autoloader:

```php
require 'vendor/autoload.php';
```

## Usage

Example how to use the shop-api-sdk with the mono logger, the log detail depends on yii debug configuration.

```php
use Collins\ShopApi;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

    $logger = new Logger('name');
    $logger->pushHandler(new StreamHandler(Yii::app()->getRuntimePath(). '/mono.log', Logger::DEBUG));

    $shopApi = new ShopApi($appId, $appPassword, $shopApiHost, $logger);
    if (YII_DEBUG) {
        $shopApi->setLogTemplate(\Guzzle\Log\MessageFormatter::DEBUG_FORMAT);
    } else {
        $shopApi->setLogTemplate(\Guzzle\Log\MessageFormatter::SHORT_FORMAT);
    }
```

Example how to use the shop-api-sdk with apc cache.

```php
    $cache = new \Doctrine\Common\Cache\ApcCache();
    $shopApi = new \Collins\ShopApi($appId, $appPassword, $shopApiHost, null, null, $cache);
```

To precache facets per cron (hourly pre caching is preferred), write a new php file with

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
