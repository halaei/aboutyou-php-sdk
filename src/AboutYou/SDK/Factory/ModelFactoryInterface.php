<?php
/**
 * @author nils.droege@aboutyou.de
 * (c) ABOUT YOU GmbH
 */

namespace AboutYou\SDK\Factory;

use AboutYou\SDK\Model;

interface ModelFactoryInterface extends ResultFactoryInterface
{
    /**
     * @param \stdClass $jsonObject
     * @param Model\Product[] $products
     *
     * @return \AboutYou\SDK\Model\Basket\BasketItem
     */
    public function createBasketItem(\stdClass $jsonObject, array $products);

    /**
     * @param \stdClass $jsonObject
     * @param Model\Product[] $products
     *
     * @return \AboutYou\SDK\Model\Basket\BasketSet
     */
    public function createBasketSet(\stdClass $jsonObject, array $products);

    /**
     * @param \stdClass $jsonObject
     * @param Model\Product[] $products
     *
     * @return \AboutYou\SDK\Model\Basket\BasketSetItem
     */
    public function createBasketSetItem(\stdClass $jsonObject, array $products);

    /**
     * @param \stdClass $jsonObject
     *
     * @return Model\Category
     */
    public function createCategory(\stdClass $jsonObject);


    /**
     * @param \stdClass $jsonObject
     *
     * @return Model\Composition
     */
    public function createCompositionList(\stdClass $jsonObject);

    /**
     * @param \stdClass $jsonObject
     *
     * @return Model\Facet
     */
    public function createFacet(\stdClass $jsonObject);

    /**
     * @param \stdClass $jsonObject
     *
     * @return Model\Image
     */
    public function createImage(\stdClass $jsonObject);

    /**
     * @param \stdClass $jsonObject
     *
     * @return Model\Material
     */
    public function createMaterial(\stdClass $jsonObject);

    /**
     * @param \stdClass[] $jsonArray
     *
     * @return Model\Material[]
     */
    public function createMaterialList(array $jsonArray);

    /**
     * @param \stdClass $jsonObject
     *
     * @return Model\Product
     */
    public function createProduct(\stdClass $jsonObject);

    /**
     * @param \stdClass $jsonObject
     * @param Model\Product $product
     *
     * @return \AboutYou\SDK\Model\Variant
     */
    public function createVariant(\stdClass $jsonObject, Model\Product $product);

    /***************************************+
     * ProductSearchResult Facets
     +++++++++++++++++++++++++++++++++++++++++*/

    /**
     * @param \stdClass $jsonObject
     *
     * @return Model\ProductSearchResult\PriceRange[]
     */
    public function createPriceRanges(\stdClass $jsonObject);

    /**
     * @param \stdClass $jsonObject
     *
     * @return Model\ProductSearchResult\FacetCounts[]
     */
    public function createFacetsCounts(\stdClass $jsonObject);

    /**
     * @param \stdClass $jsonObject
     *
     * @return Model\ProductSearchResult\SaleCounts
     */
    public function createSaleFacet(\stdClass $jsonObject);

    /**
     * @param \stdClass[] $jsonArray
     *
     * @return Model\Category
     */
    public function createCategoriesFacets(array $jsonArray);
}
