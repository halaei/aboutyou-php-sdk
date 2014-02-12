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
                "url": "git@codebasehq.com:antevorte/frontend-api-sdks/shop-sdk.git"
            }
        ],
        "require": {
            "collins/shop-sdk": "dev-feature/DEVCENTER-53"
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

    $shopApi = new ShopApi($appId, $appPassword, $shopApiHost, null, $logger);
    if (YII_DEBUG) {
        $shopApi->setLogTemplate(\Guzzle\Log\MessageFormatter::DEBUG_FORMAT);
    } else {
        $shopApi->setLogTemplate(\Guzzle\Log\MessageFormatter::SHORT_FORMAT);
    }
```


## Testing
```bash
php vendor/phpunit/phpunit/phpunit.php
```