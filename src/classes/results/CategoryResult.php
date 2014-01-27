<?php
namespace CollinsAPI\Results;

/**
 * Contains the result data of a category API request.
 *
 * @author Antevorte GmbH
 */
class CategoryResult extends BaseResult
{
	/**
	 * Category data
	 * @var array 
	 */
	public $categories = null;
	
	/**
	 * Root key of the JSON API result
	 * @var string 
	 */
	protected $resultKey = 'category';
	
	/**
	 * Initializes this result object.
	 * This means, the object attributes will be filled with 
	 * the data given from the API response.
	 * By default all the result attributes will be matches to the
	 * class attributes. This method can be overwritten of custom
	 * data operations need to be done.
	 * 
	 * @param void
	 */
	protected function init(array $result)
	{
		$this->categories = $result;
	}
	
}