<?php
require_once(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'Collins.php');
use CollinsAPI\Collins;

$product_id = intval(@$_GET['product_id']);
$productResult = Collins::getProducts(array($product_id));

$product = $productResult->ids[$product_id];

$variant_id = isset($_GET['variant_id']) ? $_GET['variant_id'] : $product['default_variant']['id'];

$variant = $productResult->getVariantById($product['id'], $variant_id);

if(!$variant)
{
	$variant = $product['default_variant'];
}

$images = $productResult->getImageURLsByVariant($product_id, $variant_id);

?>

<a href="index.php?page=results&brand_id=<?php echo $product['brand_id']?>">&laquo; zur&uuml;ck</a>

<h1>Produktdetailseite: <?php echo htmlentities($product['name'])?></h1>

<h2>Bilder dieser Produktvariante (<?php echo $variant_id?>)</h2>
<?php foreach($images as $image):?>
	<img src="<?php echo $image?>" alt="Bild" />
<?php endforeach;?>

<script type="text/javascript">
function addToCart(product_variant_id, quantity, userId)
{
	var data = {
		id: product_variant_id,
		quantity: quantity,
		userId: userId,
		appId: <?php echo CollinsAPI\Config::APP_ID?>
	};
	parent.postMessage(data, 'http://www.mary-paul.de');
}
</script>
<br />
<input type="button" onclick="addToCart(<?php echo $variant['id']?>, 1, 12345)" value="in den Warenkorb"/>

<h2>Weitere Varianten</h2>
<?php foreach($product['variants'] as $v):?>
	<div class="variant <?php echo $v['id'] == $variant['id'] ? 'active' : ''?>">
		<?php $imgs = $productResult->getImageURLsByVariant($product['id'], $v['id'], 100, 100)?>
		<a href="index.php?page=detail&product_id=<?php echo $product['id']?>&variant_id=<?php echo $v['id']?>">
			<?php if(count($imgs)):?>
				<img src="<?php echo $imgs[0]?>" alt="<?php echo $v['id']?>"/>

			<?php else:?>
				kein Bild
			<?php endif;?>
		</a>
		<br />
		<?php foreach($productResult->getFacetsByVariant($product['id'], $v['id']) as $facets):?>
			<?php foreach($facets as $facet):?>
				<?php if(in_array($facet['id'], array(
					CollinsAPI\Constants::FACET_COLOR, CollinsAPI\Constants::FACET_SIZE
				))):?>
					<?php echo htmlentities($facet['name'])?><br />
				<?php endif;?>
			<?php endforeach;?>
		<?php endforeach;?>
	</div>
<?php endforeach; ?>
<div style="clear: both"></div>
