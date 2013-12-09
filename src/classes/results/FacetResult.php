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
	
	/**
	 * Returns the part of the result for the facet with the passed ID
	 * 
	 * @param integer $id ID of the facet
	 * @return array facet data or null if facet not found
	 */
	public function getFacetById($id)
	{
		foreach($this->facet as $facet)
		{
			if($facet['facet_id'] == $id)
			{
				return $facet;
			}
		}
		
		return null;
	}
}