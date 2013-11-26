<?php
require_once(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'Collins.php');
use CollinsAPI\Collins;


// Finde alle Produkte, die eine weiße Produktvariante haben
$productSearchResult = Collins::getProductSearch(12345, array(
	'facets' => array(
		1 => array(570) // 1 = Facet Group ID für die Farbe und 570 = weiß
	)),
	array(
		'fields' => array('name', 'variants'), // wir benötigen nicht alle Felder und reduzieren hiermit das Abfrageergebnis,
		'limit' => 200 // wir möchten 200 Produkte mit einem Schlag... 200 ist übrigens auch das Maximum - mehr geht nicht.
	)

);

$products = array(); // Speichert Produktnamen und Bild-URL

// Durchlaufe die gefunden Produkte...
foreach($productSearchResult->products as $product)
{
	// ... und durchlaufe die Varianten der Produkte ...
	foreach($product['variants'] as $variant)
	{
		// Überprüfe, ob die jeweilige Variante eine Farbfacet hat und 
		// dort weiß (= 570) enthalten ist.
		if(isset($variant['attributes']['attributes_1'])) // Farbfacet?
		{
			if(in_array(570, $variant['attributes']['attributes_1'])) // weiß?
			{
				// Baue nun die Bild-URL mit Hilfe des Mary&Paul-CDN zusammen... 
				$image = array_shift($variant['images']);
				
				$id = $image['id'];
				$directory = substr($image['id'], 0, 3); // Bild-Verzeichnis sind immer die ersten 3 Ziffern der Produkt-ID
				$ext = $image['extension'];
				
				// Höhe und Breite kann frei gewählt werden, das CDN passt die Bildgröße automatisch an.
				$height = 220;
				$width = 280;
				
				$url = 'http://cdn.mary-paul.de/product_images/'.$directory.'/'.$id.'_'.$width.'_'.$height.$ext;
				
				$products[] = array(
					'name' => $product['name'],
					'img' => $url
				);
				
				break 1; // keine weiteren Varianten durchlaufen, zum nächsten Produkt springen
			}
		}
	}
}
?>

<ul>
<?php foreach($products as $product):?>
	<li>
		<?php echo htmlentities($product['name']) ?><br />
		<img src="<?php echo $product['img']?>" alt ="<?php echo htmlentities($product['name'])?>" />
	</li>
<?php endforeach; ?>
</ul>

