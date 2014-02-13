<?php
namespace Collins\ShopApi\Results;

/**
 * Contains the result data of a category API request.
 *
 * @author Antevorte GmbH
 */
class CategoryResult extends BaseResult
{
    /**
     * Root key of the JSON API result
     * @var string
     */
    protected $resultKey = 'category';
    
    /**
     * Stores the category data from the API
     * @var array
     */
    public $categories = null;
    
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
        $this->categories = $result;
    }
}