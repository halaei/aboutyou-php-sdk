<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\Test\Functional;

use Collins\ShopApi;

class BasketTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var \Collins\ShopApi
	 */
	private $api = null;

	private $sessionId = null;

	/**
	 *
	 */
	public function setUp()
	{
		$this->api = new ShopApi('key', 'token');
		$this->sessionId = 'testing';
	}

	/**
	 *
	 */
	public function testBasket()
	{
		$basket = $this->api->fetchBasket($this->sessionId);
		$this->assertObjectHasAttribute('totalPrice', $basket);
		$this->assertObjectHasAttribute('totalVat', $basket);
		$this->assertObjectHasAttribute('totalNet', $basket);
		$this->assertObjectHasAttribute('items', $basket);

		foreach( $basket->items as $item ) {
			$this->assertObjectHasAttribute('price', $item);
			$this->assertObjectHasAttribute('unitPrice', $item);
			$this->assertObjectHasAttribute('vat', $item);
			$this->assertObjectHasAttribute('tax', $item);
			$this->assertObjectHasAttribute('quantity', $item);
			$this->assertObjectHasAttribute('productVariantId', $item);
		}
	}

	/**
	 *
	 */
	public function testAddToBasket()
	{
		// add one item to basket
		$productVariantId = 123;
		$success = $this->api->addToBasket($this->sessionId, $productVariantId);
		$this->assertTrue($success);

		// add more of one item to basket
		$productVariantId = 123;
		$quantity = 2;
		$success = $this->api->addToBasket($this->sessionId, $productVariantId, $quantity);
		$this->assertTrue($success);
	}

	/**
	 *
	 */
	public function testRemoveFromBasket()
	{
		// remove one item from basket
		$productVariantId = 123;
		$success = $this->api->removeFromBasket($this->sessionId, $productVariantId);
		$this->assertTrue($success);

		// remove more of one item from basket
		$productVariantId = 123;
		$quantity = 2;
		$success = $this->api->removeFromBasket($this->sessionId, $productVariantId, $quantity);
		$this->assertTrue($success);
	}
}
