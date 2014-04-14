<?php
namespace Collins\ShopApi\Model;

use Collins\ShopApi\Factory\ModelFactoryInterface;

/**
 *
 */
class Autocomplete
{
    const NOT_REQUESTED = null;

    /**
     * @var Product[]
     */
    private $products = null;

    /**
     * @var Category[]
     */
    private $categories = null;

    public function __construct(array $categories = null, array $products = null)
    {
        $this->categories = $categories;
        $this->products   = $products;
    }

    /**
     * @param \stdClass $jsonObject The autocomplete data.
     * @param ModelFactoryInterface $factory
     *
     * @return static
     */
    public static function createFromJson(\stdClass $jsonObject, ModelFactoryInterface $factory)
    {
        $autocomplete = new static(
            static::parseCategories($jsonObject, $factory),
            static::parseProducts($jsonObject, $factory)
        );

        return $autocomplete;
    }

    /**
     * Get autocompleted categories.
     *
     * @return Category[]
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Get autocompleted products.
     *
     * @return Product[]
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * parse autocompleted categories.
     *
     *
     * @param \stdClass $jsonObject
     * @param ModelFactoryInterface $factory
     *
     * @return Category[]|null
     */
    protected static function parseCategories(\stdClass $jsonObject, ModelFactoryInterface $factory)
    {
        if ($jsonObject->categories === null) {
            return array();
        }

        if (!isset($jsonObject->categories)) {
            return self::NOT_REQUESTED;
        }

        $categories = array();
        foreach ($jsonObject->categories as $category) {
            $categories[] = $factory->createCategory($category);
        }


        return $categories;
    }

    /**
     * parse autocompleted products.
     *
     * @param \stdClass $jsonObject
     * @param ModelFactoryInterface $factory
     *
     * @return Products[]
     */
    protected static function parseProducts(\stdClass $jsonObject, ModelFactoryInterface $factory)
    {
        if ($jsonObject->products === null) {
            return array();
        }

        if (!isset($jsonObject->products)) {
            return self::NOT_REQUESTED;
        }

        $products = array();
        foreach ($jsonObject->products as $product) {
            $products[] = $factory->createProduct($product);
        }

        return $products;
    }
}