<?php
namespace CollinsAPI\Results;

/**
 * Contains the result data of a product search API request.
 *
 * @author Antevorte GmbH
 */
class ProductSearchResult extends BaseResult
{
	/**
	 * Root key of the JSON API result
	 * @var string 
	 */
	protected $resultKey = 'product_search';
	
	/**
	 * Number of products found
	 * @var int
	 */
	public $product_count = 0;
	
	/**
	 * Array of categories found
	 * @var array 
	 */
	public $categories = array();
	
	/**
	 * Array of products found
	 * @var array 
	 */
	public $products = array();
	
	/**
	 * Array of facets found
	 * @var array 
	 */
	public $facets = array();
	
	/**
	 * Returns an array of IDs of all the products found
	 * @return array array of product IDs
	 */
	public function getProductIds()
	{
		return array_map(function($product) {
			return $product['id'];
		}, $this->products);
	}
	
	/**
	 * Returns the URLs for images of the default variant of 
	 * passed products. If no products are passed, URLs for
	 * all products will be returneed
	 * @param int $width width of the images
	 * @param int $height height of the images
	 * @see CollinsAPI\\Config::IMAGE_URL
	 * @return array product urls for default image of each product
	 */
	public function getDefaultImageURLs($width = 200, $height = 280)
	{
		$urls = array();
		
		foreach($this->products as $product)
		{
			if(isset($product['default_image']))
			{
				$image = $product['default_image'];
				$id = $image['id'];
				$path = substr($id, 0, 3);
				$extension = $image['extension'];

				$url = str_replace(array(
					'{{path}}', '{{id}}', '{{extension}}', '{{width}}', '{{height}}'
				), array(
					$path, $id, $extension, $width, $height
				), \CollinsAPI\Config::IMAGE_URL);

				if(!isset($urls[$product['id']]))
				{
					$urls[$product['id']] = array();
				}

				$urls[$product['id']] = $url;
			}
		}
		
		return $urls;
	}
	
	/**
	 * Returns the default image URL for a single product
	 * @param integer $productId ID of the product
	 * @return string URL of the default image or null if no default image exists
	 */
	public function getDefaultImageURL($productId, $width = 200, $height = 280)
	{
		$url = null;
		
		$urls = $this->getDefaultImageURLs($width, $height);
		if(isset($urls[$productId]))
		{
			$url = $urls[$productId];
		}
		
		return $url;
	}
	
	/**
	 * Returns an array of variants that have the given facet
	 * @param integer $productId ID of the product
	 * @param integer $facetGroup ID of the facet group
	 * @param mixed $facetIds single ID of a facet or array of facet IDs
	 * @return array variants with passed faces
	 */
	public function getVariantsByFacet($productId, $facetGroupId, $facetIds)
	{
		$arr = array();
		
		if(!is_array($facetIds))
		{
			$facetIds = array($facetIds);
		}
		
		foreach($this->products as $product)
		{
			if($product['id'] == $productId &&isset($product['variants']))
			{
				foreach($product['variants'] as $variant)
				{
					$attributeKey = 'attributes_'.$facetGroupId;
					
					if(isset($variant['attributes']) &&
						isset($variant['attributes'][$attributeKey]))
					{
						foreach($facetIds as $facetId)
						{
							if(in_array($facetId, $variant['attributes'][$attributeKey]))
							{
								$arr[] = $variant; 
								break 2;
							}
						}
					}
				}
			}
		}
		
		return $arr;
	}
	
	/**
	 * Returns all the image URLs for a passed variant
	 */
	public function getImageURLsByVariant($productId, $productVariantId, $width = 200, $height = 280)
	{
		$urls = array();

		foreach($this->products as $product)
		{
			if($product['id'] == $productId)
			{
				foreach($product['variants'] as $variant)
				{
					if($variant['id'] == $productVariantId)
					{
						if(isset($variant['images']))
						{
							foreach($variant['images'] as $image)
							{
								$id = $image['id'];
								$path = substr($id, 0, 3);
								$extension = $image['extension'];

								$url = str_replace(array(
									'{{path}}', '{{id}}', '{{extension}}', '{{width}}', '{{height}}'
								), array(
									$path, $id, $extension, $width, $height
								), \CollinsAPI\Config::IMAGE_URL);

								$urls[] = $url;
							}
						}
					}
				}
			}
		}
		
		return $urls;
	}
}
