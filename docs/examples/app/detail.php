<?php

$productId = filter_input(INPUT_GET, 'product_id', FILTER_VALIDATE_INT);
$productResult = $shopApi->getProducts(array($productId));

$product = $productResult->ids[$productId];

$variantId = filter_input(INPUT_GET, 'variant_id', FILTER_VALIDATE_INT);
$variantId = $variantId ?: $product['default_variant']['id'];

$variant = $productResult->getVariantById($product['id'], $variantId);

if (!$variant) {
	$variant = $product['default_variant'];
}

$images = $productResult->getImageURLsByVariant($productId, $variantId);

?>

<a href="index.php?page=results&brand_id=<?php echo $product['brand_id']?>">&laquo; zur&uuml;ck</a>

<h1>Produktdetailseite: <?php echo htmlentities($product['name'])?></h1>

<h2>Bilder dieser Produktvariante (<?php echo $variantId?>)</h2>
<?php foreach($images as $image):?>
	<img src="<?php echo $image?>" alt="Bild" />
<?php endforeach;?>

<input type="button" onclick="collins.addToCart(<?php echo $variant['id']?>, 1, 12345, <?php echo CollinsAPI\Config::APP_ID?>)" value="in den Warenkorb"/>

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
					Collins\ShopApi\Constants::FACET_COLOR, Collins\ShopApi\Constants::FACET_SIZE
				))):?>
					<?php echo htmlentities($facet['name'])?><br />
				<?php endif;?>
			<?php endforeach;?>
		<?php endforeach;?>
	</div>
<?php endforeach; ?>
<div style="clear: both"></div>
