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
}