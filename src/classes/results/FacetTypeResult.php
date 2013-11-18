<?php
namespace CollinsAPI\Results;

/**
 * Contains the result data of a facet type API request.
 *
 * @author Antevorte GmbH
 */
class FacetTypeResult extends BaseResult
{
	/**
	 * Root key of the JSON API result
	 * @var string 
	 */
	protected $resultKey = 'facet_types';
	
	/**
	 * IDs of facets found
	 * @var array 
	 */
	public $ids = array();
	
	/**
	 * Initializes the FacetTypeResult object
	 * 
	 * @param array $result API result array
	 */
	protected function init($result)
	{
		$this->ids = $result;
	}
}