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
	const APP_ID = '64';
	const APP_PASSWORD = '3276afa37fd6bf72e7dfbc025b72f14b';
	
	const ENABLE_LOGGING = true;
	const LOGGING_PATH = null;
	const LOGGING_TEMPLATE = "Request:\r\n{{request}}\r\n\r\nResponse:\r\n{{response}}";
}