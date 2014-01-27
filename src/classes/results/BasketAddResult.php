<?php
namespace CollinsAPI\Results;

/**
 * Contains the result data of an add-to-basket API request.
 *
 * @author Antevorte GmbH
 */
class BasketAddResult extends BasketResult
{
    /**
     * Root key of the JSON API result
     * @var string
     */
    protected $resultKey = 'basket_add';
}