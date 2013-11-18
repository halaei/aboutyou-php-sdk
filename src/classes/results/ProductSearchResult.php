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
}
