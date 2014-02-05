<?php
error_reporting(E_ALL);

$baseDir = dirname(dirname(dirname(__DIR__)));
require $baseDir . '/Config.php';

require $baseDir . '/vendor/autoload.php';

$shopApi = new Collins\ShopApi(
    CollinsAPI\Config::APP_ID,
    CollinsAPI\Config::APP_PASSWORD,
    CollinsAPI\Config::ENTRY_POINT_URL
);
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Beispiel-App</title>
<style type="text/css">
	html
	{
		font-family: sans-serif;
	}
	.container
	{
		padding: 10px;
		width: 780px;
		border-width: 1px;
		border-style: solid;
		border-color: green;
		margin: auto;
	}
	
	a
	{
		color: blueviolet;
		text-decoration: none;
	}
	
	a:hover
	{
		text-decoration: underline;
	}
	
	.result
	{
		float: left;
		width: 180px;
		padding: 5px;
		height: 150px;
		border-width: 1px;
		border-style: solid;
		border-color: lightgrey;
	}
	
	.variant
	{
		float: left;
		border-color: lightgrey;
		border-style: solid;
		border-width: 0px;
		padding: 5px;
	}
	
	.variant.active
	{
		border-width: 1px;
	}
</style>

<?php echo $shopApi->getJavaScriptTag(); ?>
</head>

<body>
	<div class="container">
		<?php $page = isset($_GET['page']) && in_array($_GET['page'], array('home', 'detail', 'results')) ? $_GET['page'] : 'home'?>
		<?php require_once($page.'.php')?>
	</div>
</body>

</html>