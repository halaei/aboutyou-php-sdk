<?php
namespace AboutYou\SDK\Model;

use AboutYou\SDK\Factory\ModelFactoryInterface;

/**
 *
 */
class Autocomplete
{
    const NOT_REQUESTED = null;

    const TYPE_PRODUCTS   = 'products';
    const TYPE_CATEGORIES = 'categories';
    const TYPE_BRANDS     = 'brands';

    /**
     * @var Product[]
     */
    private $products = null;

    /**
     * @var Category[]
     */
    private $categories = null;
    
    /**
     * @var Brand[]
     */
    private $brands = null;

    public function __construct(array $categories = null, array $products = null, array $brands = null)
    {
        $this->categories = $categories;
        $this->products   = $products;
        $this->brands     = $brands;
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
            static::parseProducts($jsonObject, $factory),
            static::parseBrands($jsonObject, $factory)
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
     * Get autocompleted brands.
     *
     * @return Brand[]
     */
    public function getBrands()
    {
        return $this->brands;
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
        if (!property_exists($jsonObject, 'categories')) {
            return self::NOT_REQUESTED;
        }

        if ($jsonObject->categories === null) {
            return array();
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
        if (!property_exists($jsonObject, 'products')) {
            return self::NOT_REQUESTED;
        }

        if ($jsonObject->products === null) {
            return array();
        }

        $products = array();
        foreach ($jsonObject->products as $product) {
            $products[] = $factory->createProduct($product);
        }

        return $products;
    }
    
    /**
     * parse autocompleted brands.
     *
     * @param \stdClass $jsonObject
     * @param ModelFactoryInterface $factory
     *
     * @return Brand[]
     */
    protected static function parseBrands(\stdClass $jsonObject, ModelFactoryInterface $factory)
    {
        if (!property_exists($jsonObject, 'brands')) {
            return self::NOT_REQUESTED;
        }

        if ($jsonObject->brands === null) {
            return array();
        }

        $brands = array();
        foreach ($jsonObject->brands as $brand) {
            $brands[] = $factory->createBrand($brand);
        }

        return $brands;
    }
}