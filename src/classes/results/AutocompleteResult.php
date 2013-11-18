<?php
namespace CollinsAPI\Results;

/**
 * Contains the result data of an autocompletion API request.
 *
 * @author Antevorte GmbH
 */
class AutocompleteResult extends BaseResult
{
	/**
	 * Root key of the JSON API result
	 * @var string 
	 */
	protected $resultKey = 'autocompletion';
	
	/**
	 * Products found
	 * @var array
	 */
	public $products = array();
	
	/**
	 * Categories found
	 * @var array
	 */
	public $categories = array();
}