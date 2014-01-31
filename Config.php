<?php
namespace CollinsAPI;

/**
 * Contains configuration data needed for app development.
 *
 * @author Antevorte GmbH
 */
abstract class Config
{
    const ENTRY_POINT_URL = 'http://ant-shop-api1.wavecloud.de/api';
    const APP_ID = '98';
    const APP_PASSWORD = '6350e1667a67cbb2adb0d790f1f98929';

    const ENABLE_LOGGING = true;
    const LOGGING_PATH = null;
    const LOGGING_TEMPLATE = "Request:\r\n{{request}}\r\n\r\nResponse:\r\n{{response}}";

    const IMAGE_URL = 'http://cdn.mary-paul.de/product_images/{{path}}/{{id}}_{{width}}_{{height}}{{extension}}';

    const ENABLE_REDIS_CACHE = false;
    const REDIS_SCHEME = 'tcp';
    const REDIS_HOST = '127.0.0.1';
    const REDIS_PORT = 6379;
}