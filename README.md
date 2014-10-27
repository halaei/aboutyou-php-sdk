## ABOUT YOU App SDK

# Documentation 

See [ABOUT YOU Developer Center](https://developer.aboutyou.de/) for more Information.

# Installation

The recommended way to install the ShopAPI is through [Composer](http://getcomposer.org).

First install composer

    curl -sS https://getcomposer.org/installer | php

then add the App SDK as a dependency. 
The SDK is available via [Packagist](https://packagist.org/) under the [aboutyou/app-sdk](https://packagist.org/packages/aboutyou/app-sdk) package.

    php composer.phar require aboutyou/app-sdk

at least, after the installing was successful, you need to require the Composer's autoloader:

    require 'vendor/autoload.php';

## Usage

For more detailed information see [ABOUT YOU Developer Center Documentation](https://developer.aboutyou.de/doc).

### Caching

Example how to use the App SDK with the apc cache.

    $cache = new \Aboutyou\Common\Cache\ApcCache();
    $ay    = new \AY($appId, $appPassword, $apiHost, null, null, $cache);

This is an example, how to pre cache facets and categories per cron (hourly pre caching is preferred). 
We use APC for simplicity, but you can also use memcached, redis or other supported cache systems. 
First you need a php script which initialize the app sdk, fetch and cache the data to your preferred cache.

    #!/usr/bin/env php
    <?php
    // filename precache-cron.php
    require 'myconfig.php';
    require 'vendor/autoload.php';
    
    $cache = new \Aboutyou\Common\Cache\ApcCache();
    $ay = new \AY($appId, $appPassword, $aboutYouHost, null, null, $cache);
    
    $ay->preCache();


Then add the script to your crontab, 
to edit the cron jobs call `crontab -e` on your shell

    # Edit this file to introduce tasks to be run by cron.
    # [snip]
    # For more information see the manual pages of crontab(5) and cron(8)
    # 
    # m h  dom mon dow   command
    0 * * * * * <path to your project>/precache-cron.php

## Testing

To test the SDK, just copy the dist file:

    cp phpunit.dist.xml phpunit.xml

and run the test:

    vendor/bin/phpunit

