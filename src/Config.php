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
	const APP_ID = '59';
	const APP_PASSWORD = '46df504885a015333333ff89c7a20458';
	
	const ENABLE_LOGGING = true;
	const LOGGING_PATH = null;
	const LOGGING_TEMPLATE = "Request:\r\n{{request}}\r\n\r\nResponse:\r\n{{response}}";
	
	const IMAGE_URL = 'http://cdn.mary-paul.de/product_images/{{path}}/{{id}}_{{width}}_{{height}}{{extension}}';
}