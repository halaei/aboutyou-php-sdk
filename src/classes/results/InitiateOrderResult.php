<?php
namespace CollinsAPI\Results;

/**
 * Contains the result data of an add-to-basket API request.
 *
 * @author Antevorte GmbH
 */
class InitiateOrderResult extends BasketResult
{
	/**
	 * Root key of the JSON API result
	 * @var string 
	 */
	protected $resultKey = 'initiate_order';
	
	public $user_token = null;
	public $app_token = null;
}