<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Test\Unit\Model;

use Collins\ShopApi\Model\Image;

class ImageTest extends AbstractModelTest
{
    public function testFromJson()
    {
        $jsonObject = $this->getJsonObject('image.json');

        $image = new Image($jsonObject);
        $this->assertNull($image->getShopApi());

        $this->assertEquals('hash1', $image->getHash());
        $this->assertEquals('.jpg', $image->getExt());
        $this->assertEquals('image/jpeg', $image->getMimetype());
        $this->assertEquals(12345678, $image->getFilesize());
        $this->assertEquals(array('tag1', 'tag2'), $image->getTags());

        $imageSize = $image->getImageSize();
        $this->assertInstanceOf('Collins\\ShopApi\\Model\\ImageSize', $imageSize);
        $this->assertEquals(1400, $imageSize->getWidth());
        $this->assertEquals(2000, $imageSize->getHeight());

        $this->assertStringStartsWith('/hash1', $image->getUrl());
        $this->assertStringStartsWith('/hash1?width=123&height=456', $image->getUrl(123, 456));
    }
}
 