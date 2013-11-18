<?php
error_reporting(-1);
ini_set('display_errors', 1);
require_once('src/Collins.php');


$result = CollinsAPI\Collins::getProductSearch(12345);
print_r($result->products);