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
		$this->checkProduct($product);
	}

	/**
	 * @depends testFetchProduct
	 */
	public function testFetchProducts()
	{
		// fetch all available products
		$products = $this->api->fetchProducts();
		$this->checkProductList($products);

		// fetch products by filter
		$filter = array(
			'categoryId' => 123
		);
		$products = $this->api->fetchProducts($filter);
		$this->checkProductList($products);

		// fetch products and sort
		$sorting = array('name', ShopApi::SORT_ASC);
		$products = $this->api->fetchProducts(null, $sorting);
		$this->checkProductList($products);

		// fetch limited products
		$limit = 20;
		$page = 2;
		$pagination = [$limit, $page];
		$products = $this->api->fetchProducts(null, null, $pagination);
		$this->checkProductList($products);
	}

	/**
	 *
	 */
	public function testFetchCategoryTree()
	{
		$depth = 1;
		$tree = $this->api->fetchCategoryTree($depth);
		$this->checkCategory($tree->category);

		foreach( $tree->childs as $subTree ) {
			$this->checkCategory($subTree->category);
			$this->assertEmpty($subTree->childs);
		}
	}

	/**
	 *
	 */
	public function testFetchParentCategories()
	{
		$categoryId = 123;
		$categories = $this->api->fetchParentCategories($categoryId);

		$this->assertTrue(is_array($categories));
		foreach( $categories as $category ) {
			$this->checkCategory($category);
		}
	}

	/**
	 *
	 */
	private function checkProduct($product)
	{
		$this->assertObjectHasAttribute('id', $product);
		$this->assertObjectHasAttribute('name', $product);
		//TODO: check if this is a product
	}

	/**
	 *
	 */
	private function checkProductList($products)
	{
		$this->assertTrue(is_array($products));
		foreach( $products as $product ) {
			$this->checkProduct($product);
		}
	}


	/**
	 *
	 */
	private function checkCategory($category)
	{
		$this->assertObjectHasAttribute('id', $category);
		$this->assertObjectHasAttribute('name', $category);
		$this->assertObjectHasAttribute('active', $category);
		//TODO: check if this is a category
	}
}
