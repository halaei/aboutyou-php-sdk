<?php
namespace CollinsAPI\Results;

/**
 * Contains the result data of an basket (add or get) API request.
 *
 * @author Antevorte GmbH
 */
abstract class BasketResult extends BaseResult
{
	/**
	 * Total brutto price of all items in the basket
	 * @var float
	 */
	public $total_price = 0;
	
	/**
	 * Total netto price of all items in the basket
	 * @var float
	 */
	public $total_net = 0;
	
	/**
	 * Products in the basket
	 * @var array 
	 */
	public $products = array();
	
	/**
	 * Total quantity of all product variants in the basket
	 * @var integer
	 */
	public $amount_variants = 0;
	
	/**
	 * Total number of different product variants in the basket
	 */
	public $total_variants = 0;
}