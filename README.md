# Shop API SDK

## Installation

The recommended way to install the ShopAPI is through [Composer](http://getcomposer.org).

```bash
# Install Composer
curl -sS https://getcomposer.org/installer | php

# Add the ShopAPI as a dependency

    ``` json
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

## Testing
$ php vendor/phpunit/phpunit/phpunit.php tests
