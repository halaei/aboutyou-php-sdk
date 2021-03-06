<?php
/**
 * @author nils.droege@aboutyou.de
 * (c) ABOUT YOU GmbH
 */

namespace AboutYou\SDK\Test\Unit\Model;

use AboutYou\SDK\Model\Image;

class ImageTest extends AbstractModelTest
{
    public function testFromJson()
    {
        $jsonObject = $this->getJsonObject('image.json');

        $image = Image::createFromJson($jsonObject);
        $this->assertNotNull($image->getBaseUrl());

        $this->assertEquals('hash1', $image->getHash());
        $this->assertEquals('.jpg', $image->getExt());
        $this->assertEquals('image/jpeg', $image->getMimetype());
        $this->assertEquals(12345678, $image->getFilesize());
        $this->assertEquals(array('tag1', 'tag2'), $image->getTags());

        $imageSize = $image->getImageSize();
        $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\ImageSize', $imageSize);
        $this->assertEquals(1400, $imageSize->getWidth());
        $this->assertEquals(2000, $imageSize->getHeight());

        $image->setBaseUrl();
        $this->assertStringStartsWith('/hash1', $image->getUrl());
        $image->setBaseUrl(false);
        $this->assertStringStartsWith('/hash1', $image->getUrl());
        $image->setBaseUrl(null);
        $this->assertStringStartsWith('/hash1', $image->getUrl());
        $image->setBaseUrl('');
        $this->assertStringStartsWith('/hash1', $image->getUrl());
        $this->assertStringStartsWith('/hash1?width=123&height=456', $image->getUrl(123, 456));

        $ay = new \AY('appid', 'pw');
        $ay->getResultFactory();
        $this->assertStringStartsWith(\AY::IMAGE_URL_LIVE . '/hash1', $image->getUrl());
        $ay->setBaseImageUrl('http://domain.tld');
        $this->assertStringStartsWith('http://domain.tld/hash1', $image->getUrl());
        $ay->setBaseImageUrl(false);
        $this->assertStringStartsWith('/hash1', $image->getUrl());
        $ay->setBaseImageUrl(null);
        $this->assertStringStartsWith(\AY::IMAGE_URL_LIVE . '/hash1', $image->getUrl());
        $ay->getResultFactory()->setBaseImageUrl('http://domain2.tld');
        $this->assertStringStartsWith('http://domain2.tld/hash1', $image->getUrl());
        Image::setBaseUrl('http://domain3.tld');
        $this->assertStringStartsWith('http://domain3.tld/hash1', $image->getUrl());

        $this->assertNull($image->getAdditionalItems());
        $this->assertNull($image->getAngle());
        $this->assertNull($image->getBackground());
        $this->assertNull($image->getColor());
        $this->assertNull($image->getFocus());
        $this->assertNull($image->getGender());
        $this->assertNull($image->getModelData());
        $this->assertNull($image->getNextDetailLevel());
        $this->assertNull($image->getPreparation());
        $this->assertNull($image->getView());
        $this->assertNull($image->getType());
    }
}
 