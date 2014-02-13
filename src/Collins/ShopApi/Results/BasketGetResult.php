<?php
namespace Collins\ShopApi\Results;

/**
 * Contains the result data of an get-basket API request.
 *
 * @author Antevorte GmbH
 */
class BasketGetResult extends BasketResult
{
    /**
     * Root key of the JSON API result
     * @var string
     */
    protected $resultKey = 'basket_get';
}