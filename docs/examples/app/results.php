<?php
require_once(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'Collins.php');
use CollinsAPI\Collins;

// Produkte mit der übergebenen Marke auslesen
$brand_id = intval(@$_GET['brand_id']);

$productResult = Collins::getProductSearch(12345,array(
	'facets' =>  (object) array( // (object), weil json_encode sonst Array generiert
		CollinsAPI\Constants::FACET_BRAND => array($brand_id)
	)
));

$products = $productResult->products;

?>
<a href="index.php">zur&uuml;ck</a>
<h1>Ergebnisse</h1>

<?php if(!count($products)):?>
	Diese Marke hat keine Produkte :( Beispiel-Marken, die Produkte haben: <br />
	<a href="index.php?page=results&brand_id=2259">ALPHA</a><br />
	<a href="index.php?page=results&brand_id=274">BRUNO BANANI</a><br />
	... und fast alle auf <a href="http://www.mary-paul.de/marken" target="_blank">Mary & Paul</a>
	
<?php endif;?>
<?php foreach($products as $product):?>
	<div class="result">
		<a href="index.php?page=detail&product_id=<?php echo $product['id']?>"><?php echo htmlentities($product['name'])?></a><br />
		<a href="index.php?page=detail&product_id=<?php echo $product['id']?>"><img src="<?php echo $productResult->getDefaultImageURL($product['id'], 100, 100)?>" alt="<?php echo htmlentities($product['name'])?>"/></a>
	</div>
<?php endforeach; ?>
<div style="clear: both"></div>