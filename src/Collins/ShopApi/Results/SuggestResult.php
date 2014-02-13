<?php
namespace Collins\ShopApi\Results;

/**
 * Contains the result data of a suggestion API request.
 *
 * @author Antevorte GmbH
 */
class SuggestResult extends BaseResult
{
    /**
     * Root key of the JSON API result
     * @var string
     */
    protected $resultKey = 'suggest';

    /**
     * Suggestionwords found
     * @var array
     */
    public $suggestions = array();

    /**
     * Initializes the FacetTypeResult object
     *
     * @param array $result API result array
     */
    protected function init(array $result)
    {
        $this->suggestions = $result;
    }
}