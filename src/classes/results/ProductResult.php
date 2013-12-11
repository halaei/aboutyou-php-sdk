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
					
					foreach($facets as $facetGroupId => $facetId)
					{
						$attributeKey = 'attributes_'.$facetGroupId;
						
						if(isset($variant['attributes']) &&
							isset($variant['attributes'][$attributeKey]))
						{
							if(!in_array($facetId, $variant['attributes'][$attributeKey]))
							{
								$insert = false;
								break;
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
}