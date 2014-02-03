<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\Test\Functional;

use Collins\ShopApi;

class ShopApiTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var \Collins\ShopApi
	 */
	private $api = null;

	/**
	 *
	 */
	public function setUp()
	{
		$this->api = new ShopApi('key', 'token');
	}

	/**
	 *
	 */
	public function testFetchProducts()
	{
		$productIds = array(123, 456);
		$products = $this->api->fetchProductsById($productIds);
		$this->checkProduct($products[123]);
		$this->checkProduct($products[456]);
	}

	/**
	 *
	 */
	public function testSearchProducts()
	{
		// get all available products
		$products = $this->api->searchProducts();
		$this->checkProductList($products);

		// search products by filter
		$filter = array(
			'categoryId' => 123
		);
		$products = $this->api->searchProducts($filter);
		$this->checkProductList($products);

		// search products and sort
		$sorting = array('name', ShopApi::SORT_ASC);
		$products = $this->api->searchProducts(null, $sorting);
		$this->checkProductList($products);

		// search products with limit
		$pagination = array(
			'pageSize' => 20,
			'page' => 1,
		);
		// or:
		$pagination = array(
			'limit' => 20,
			'offset' => 21,
		);
		$products = $this->api->searchProducts(null, null, $pagination);
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
