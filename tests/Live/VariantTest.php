<?php

namespace Collins\ShopApi\Test\Live;

/**
 * @group live
 */
class VariantTest extends \Collins\ShopApi\Test\Live\AbstractShopApiLiveTest
{
    public function testGetVariantById()
    {
        $shopApi = $this->getShopApi();
        $id = $this->getVariantId(1);

        $result = $shopApi->fetchVariantsByIds(array($id, $id * 1000));

        $this->assertInstanceOf('Collins\ShopApi\Model\VariantsResult', $result);
        $this->assertTrue($result->hasVariantsNotFound());

        $errors = $result->getVariantsNotFound();

        $this->assertEquals($id * 1000, $errors[0]);

        $this->assertCount(1, $result->getVariantsFound());

        $variant = $result->getVariantById($id);
        $this->assertInstanceOf('Collins\ShopApi\Model\Variant', $variant);

        if ($variant->getAboutNumber() !== null) {
            $this->assertInternalType('string', $variant->getAboutNumber());
        }

        $this->assertEquals($id, $variant->getId());
        $this->assertInstanceOf('Collins\ShopApi\Model\Product', $variant->getProduct());
    }

    public function testGetVariantByIdWithSameProduct()
    {
        $shopApi = $this->getShopApi();

        $result = $shopApi->fetchVariantsByIds(array('4683343', '4683349'));

        $this->assertInstanceOf('Collins\ShopApi\Model\VariantsResult', $result);
        $this->assertFalse($result->hasVariantsNotFound());

        $this->assertCount(2, $result->getVariantsFound());

        foreach ($result->getVariantsFound() as $variant) {
            $this->assertInstanceOf('Collins\ShopApi\Model\Variant', $variant);
            $product = $variant->getProduct();
            $this->assertInstanceOf('Collins\ShopApi\Model\Product', $product);

            $this->assertEquals(215114, $product->getId());
        }
    }

    public function testGetVariantByIdWithWrongIds()
    {
        $shopApi = $this->getShopApi();
        $ids = array('583336000', '58333600');

        $result = $shopApi->fetchVariantsByIds($ids);

        $this->assertInstanceOf('Collins\ShopApi\Model\VariantsResult', $result);
        $this->assertTrue($result->hasVariantsNotFound());

        $errors = $result->getVariantsNotFound();

        $this->assertCount(2, $errors);

        foreach ($ids as $id) {
            $this->assertTrue(in_array($id, $errors));
        }
    }
}
