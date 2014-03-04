<?php
namespace Collins\ShopApi\Model;

/**
 *
 */
class Autocomplete extends AbstractModel
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
     * Get autocompleted products.
     *
     * @return Product[]
     */
    public function getProducts()
    {
        if (!$this->products) {
            $factory = $this->getModelFactory();

            $this->products = array();
            if ($this->jsonObject->products) {
                foreach ($this->jsonObject->products as $product) {
                    $this->products[] = $factory->createProduct($product);
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
            $factory = $this->getModelFactory();

            $this->categories = array();
            if ($this->jsonObject->categories) {
                foreach ($this->jsonObject->categories as $category) {
                    $this->categories[] = $factory->createCategory($category);
                }
            }
            unset($this->jsonObject->categories); // free memory
        }
        return $this->categories;
    }
}