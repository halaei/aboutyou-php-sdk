<?php
error_reporting(E_ALL);
require_once(__DIR__.'/../../src/Collins.php');
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
</head>

<body>
	<div class="container">
		<?php $page = isset($_GET['page']) && in_array($_GET['page'], array('home', 'detail', 'results')) ? $_GET['page'] : 'home'?>
		<?php require_once($page.'.php')?>
	</div>

    <script type="text/javascript" src="http://map.dev/apps/js/api.js"></script>
</body>

</html>