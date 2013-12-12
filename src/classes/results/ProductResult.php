<?php
namespace CollinsAPI\Results;

/**
 * Contains the result data of a product API request.
 *
 * @author Antevorte GmbH
 */
class ProductResult extends BaseResult
{
	/**
	 * Root key of the JSON API result
	 * @var string 
	 */
	protected $resultKey = 'products';
	
	/**
	 * Products and their product data fields found
	 * @var array 
	 */
	public $ids = array();
	
	/**
	 * Returns an array of variants that have the given facets. A variant
	 * needs to have all of the passed facets to be in the result (AND search)
	 * @param integer $productId ID of the product
	 * @param mixed $facets array of facet groups (key) and facet ids (value) to filter for
	 * @return array variants with passed faces
	 */
	public function getVariantsByFacets($productId, $facets)
	{
		$arr = array();
		
		foreach($this->ids as $product)
		{
			if($product['id'] == $productId && isset($product['variants']))
			{
				foreach($product['variants'] as $variant)
				{
					$insert = true;
					
					foreach($facets as $facetGroupId => $facetIds)
					{
						if(!is_array($facetIds))
						{
							$facetIds = array($facetIds);
						}
					
						$attributeKey = 'attributes_'.$facetGroupId;
						
						if(isset($variant['attributes']) &&
							isset($variant['attributes'][$attributeKey]))
						{
							foreach($facetIds as $facetId)
							{
								if(!in_array($facetId, $variant['attributes'][$attributeKey]))
								{
									$insert = false;
									break;
								}
							}
						}
					}
					
					if($insert)
					{
						$arr[] = $variant;
					}
					
				}
			}
		}
		
		return $arr;
	}
	
	/**
	 * Returns an array of facets that are relevant for the product detail page.
	 * Relevant means that the user must be able to choose between these facets.
	 * Some facets are not relevant - for example the brand - because it's the same
	 * for every variant
	 * @param type $productId
	 */
	public function getRelevantFacetsByProduct($productId, $facets = null)
	{
		$facets = array();
		if(isset($this->ids[$productId]))
		{
			$product = $this->ids[$productId];
			
			if(isset($product['variants']))
			{
				foreach($product['variants'] as $variant)
				{
					// Check if this variants has one of the filter facets.
					// If not, skip it.
					if($facets)
					{
						foreach($facets as $facetGroupId => $facetIds)
						{
							if(!is_array($facetIds))
							{
								$facetIds = array($facetIds);
							}
							
							if(!isset($variant['attributes']['attribute_'.$facetGroupId]))
							{
								break;
							}
							
							foreach($facetIds as $facetId)
							{
								if(!in_array($facetId, $variant['attributes']['attribute_'.$facetGroupId]))
								{
									break 2;
								}
							}
						}
					}
					
					foreach($variant['attributes'] as $groupId => $facetIds)
					{
						$groupId = intval(str_replace('attributes_', '', $groupId));
						
						if(!isset($facets[$groupId]))
						{
							$facets[$groupId] = array();
						}
						
						$exists = false;
						foreach($facets[$groupId] as $facetIdsExisting)
						{
							if(serialize($facetIds) == serialize($facetIdsExisting))
							{
								$exists = true;
								break;
							}
						}
						
						if(!$exists)
						{
							$facets[$groupId][] = $facetIds;
						}
					}
				}
			}
			
			foreach($facets as $groupId => $facetIds)
			{
				if(count($facetIds) < 2)
				{
					unset($facets[$groupId]);
				}
			}
			
			// replace IDs with actual facet data
			$groupIds = array_keys($facets);
			$facetResult = \CollinsAPI\Collins::getFacets($groupIds);

			foreach($facets as $groupId => $f)
			{
				foreach($f as $i => $facetIds)
				{
					$facets[$groupId][$i] = $facetResult->getFacetByIds($groupId, $facetIds);
				}
			}
		}
		
		return $facets;
	}
	
	/**
	 * Returns the variant array of a specific variant of a specific product.
	 * @param int $productId ID of the product
	 * @param int $variantId ID of the variant
	 * @return null
	 */
	public function getVariantById($productId, $variantId)
	{
		if(isset($this->ids[$productId]))
		{
			$product = $this->ids[$productId];
			
			if(isset($product['variants']))
			{
				foreach($product['variants'] as $variant)
				{
					if($variant['id'] == $variantId)
					{
						return $variant;
					}
				}
			}
		}
		
		return null;
	}
	
	/**
	 * Returns an array of facets for the given variant.
	 * 
	 * @param int $productId ID of the product
	 * @param int $variantId ID of the product's variant
	 * @return array array of facets
	 */
	public function getFacetsByVariant($productId, $variantId)
	{
		$result = array();
		
		if(isset($this->ids[$productId]))
		{
			$product = $this->ids[$productId];
			
			if(isset($product['variants']))
			{
				foreach($product['variants'] as $variant)
				{
					if($variant['id'] == $variantId)
					{
						if(isset($variant['attributes']))
						{
							foreach($variant['attributes'] as $groupId => $facetIds)
							{
								$groupId = intval(str_replace('attributes_', '', $groupId));
								
								$facets = \CollinsAPI\Collins::getFacets($groupId);
								
								if(!isset($result[$groupId]))
								{
									$result[$groupId] = array();
								}
								
								$result[$groupId] = array_merge($result[$groupId], $facets->getFacetByIds($groupId, $facetIds));
							}
						}
						break;
					}
				}
			}
		}
		
		ksort($result);
		return $result;
	}
	
	/**
	 * Returns an array of merged facets of all variants of the given product.
	 * 
	 * @param int $productId ID of the product
	 * @return array array of facets
	 */
	public function getFacetsByProduct($productId)
	{
		$result = array();
		if(isset($this->ids[$productId]))
		{
			$product = $this->ids[$productId];

			if(isset($product['attributes_merged']))
			{
				foreach($product['attributes_merged'] as $groupId => $facetIds)
				{
					$groupId = intval(str_replace('attributes_', '', $groupId));

					$facets = \CollinsAPI\Collins::getFacets($groupId);
					$result = array_merge($result, $facets->getFacetByIds($groupId, $facetIds));
				}
			}
		}
		
		return $result;
	}
	
	/**
	 * Returns all the image URLs for a passed variant
	 */
	public function getImageURLsByVariant($productId, $productVariantId, $width = 200, $height = 280)
	{
		$urls = array();

		foreach($this->ids as $product)
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