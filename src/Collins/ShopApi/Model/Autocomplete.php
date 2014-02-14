<?php
namespace Collins\ShopApi\Model;

/**
 *
 */
class Autocomplete
{
    /**
     * @var object
     */
    protected $jsonObject = null;

    /**
     * @var Product[]
     */
    private $products = null;

    /**
     * @var Category[]
     */
    private $categories = null;

    /**
     * Constructor.
     *
     * @param object $jsonObject The autocomplete data.
     */
    public function __construct($jsonObject)
    {
        $this->jsonObject = $jsonObject;
    }

    /**
     * Create product from json object.
     *
     * @param object $jsonProduct
     *
     * @return Product
     */
    protected function createProduct($jsonProduct)
    {
        return new Product($jsonProduct);
    }

    /**
     * Create category from json object.
     *
     * @param object $jsonCategory
     *
     * @return Category
     */
    protected function createCategory($jsonCategory)
    {
        return new Category($jsonCategory);
    }

    /**
     * Get autocompleted products.
     *
     * @return Product[]
     */
    public function getProducts()
    {
        if (!$this->products) {
            $this->products = array();
            if ($this->jsonObject->products) {
                foreach ($this->jsonObject->products as $product) {
                    $this->products[] = $this->createProduct($product);
                }
            }
            unset($this->jsonObject->products); // free memory
        }
        return $this->products;
    }

    /**
     * Get autocompleted categories.
     *
     * @return Category[]
     */
    public function getCategories()
    {
        if (!$this->categories) {
            $this->categories = array();
            if ($this->jsonObject->categories) {
                foreach ($this->jsonObject->categories as $category) {
                    $this->categories[] = $this->createCategory($category);
                }
            }
            unset($this->jsonObject->categories); // free memory
        }
        return $this->categories;
    }
}