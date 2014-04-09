<?php
namespace Collins\ShopApi\Test\Functional;

use Collins\ShopApi;

class ChildAppsTest extends AbstractShopApiTest
{
    /**
     *
     */
    public function testChildApps()
    {
        $shopApi = $this->getShopApiWithResultFile('child-apps.json');

        $apps = $shopApi->fetchChildApps();
        $this->assertInternalType('array', $apps);

        foreach ($apps as $app) {
            $this->assertInstanceOf('Collins\\ShopApi\\Model\\App', $app);
            $this->assertInternalType('int', $app->getId());
            $this->assertInternalType('string', $app->getLogoUrl());
            $this->assertInternalType('string', $app->getName());
            $this->assertInternalType('string', $app->getUrl());
            $this->assertInternalType('string', $app->getPrivacyStatementUrl());
            $this->assertInternalType('string', $app->getTosUrl());
        }
    }
}
