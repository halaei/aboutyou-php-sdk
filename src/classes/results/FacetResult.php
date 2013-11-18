<?php
namespace CollinsAPI\Results;

/**
 * Contains the result data of a facet API request.
 *
 * @author Antevorte GmbH
 */
class FacetResult extends BaseResult
{
	/**
	 * Root key of the JSON API result
	 * @var string 
	 */
	protected $resultKey = 'facets';
	
	public $facet = array();
	public $hits = null;
}