<?php
use CollinsAPI\Collins;

// Alle Marken auslesen, die im System vorhanden sind
$facetResult= Collins::getFacets(CollinsAPI\Constants::FACET_BRAND);
$brands = $facetResult->facet;
?>

<h1>Produktsuche</h1>

<ul>
<?php foreach($brands as $brand):?>
	<li>
		<a href="index.php?page=results&brand_id=<?php echo $brand['facet_id']?>"><?php echo htmlentities($brand['name'])?></a>
	</li>
<?php endforeach; ?>
</ul>