<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\Test\Functional;

use Collins\ShopApi;
use Collins\ShopApi\Pagination;

class ShopApiTest extends \PHPUnit_Framework_TestCase
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
	public function testFetchProduct()
	{
		$categoryId = 123;
		$product = $this->api->fetchProductById($categoryId);
	}

	/**
	 * @depends testFetchProduct
	 */
	public function testFetchProducts()
	{
		// fetch all available products
		$products = $this->api->fetchProducts();

		// fetch products by filter
		$filter = array(
			'categoryId' => 123
		);
		$products = $this->api->fetchProducts($filter);

		// fetch products and sort
		$sorting = array('name', ShopApi::SORT_ASC);
		$products = $this->api->fetchProducts(null, $sorting);

		// fetch limited products
		$pagination = new Pagination();
		$pagination->limit = 20;
		$pagination->page = 2;
		$products = $this->api->fetchProducts(null, null, $pagination);
	}

	/**
	 *
	 */
	public function testFetchCategoryTree()
	{
		$depth = 2;
		$rootCategories = $this->api->fetchCategoryTree($depth);

		foreach( $rootCategories as $category ) {
			foreach( $category->childs as $childCategory ) {
				$this->assertNull($childCategory->childs);
			}
		}
	}

	/**
	 *
	 */
	public function testFetchParentCategories()
	{
		$categoryId = 123;
		$categories = $this->api->fetchParentCategories($categoryId);
	}
}
