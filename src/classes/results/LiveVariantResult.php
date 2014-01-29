<?php
namespace CollinsAPI\Results;

/**
 * Contains the result data of a live variant API request.
 *
 * @author Antevorte GmbH
 */
class LiveVariantResult extends BaseResult
{
    /**
     * Root key of the JSON API result
     * @var string
     */
    protected $resultKey = 'live_variant';
    
    public $result = null;
    
    /**
     * Initializes the CategoryResult object
     * This works a bit different than the default initialization because the API
     * root indices are the IDs of the categories. So we just store the result data
     * in $this->categories.
     *
     * @param void
     */
    protected function init(array $result)
    {
        $this->result = $result;
    }

}