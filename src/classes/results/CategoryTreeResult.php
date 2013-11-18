<?php
namespace CollinsAPI\Results;

/**
 * Contains the result data of a category tree API request.
 *
 * @author Antevorte GmbH
 */
class CategoryTreeResult extends BaseResult
{
	/**
	 * Root key of the JSON API result
	 * @var string 
	 */
	protected $resultKey = 'category_tree';
	
	public $tree;
	
	/**
	 * Initializes the CategoryTreeResult object
	 * 
	 * @param array $result API result array
	 */
	protected function init(array $result)
	{
		$this->tree = $result;
	}
}