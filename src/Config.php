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
	const APP_ID = '95';
	const APP_PASSWORD = 'f7f8d79e075ca86da08b1a4ecc061977';
	
	const ENABLE_LOGGING = true;
	const LOGGING_PATH = null;
	const LOGGING_TEMPLATE = "Request:\r\n{{request}}\r\n\r\nResponse:\r\n{{response}}";
	
	const IMAGE_URL = 'http://cdn.mary-paul.de/product_images/{{path}}/{{id}}_{{width}}_{{height}}{{extension}}';
}