<?php
namespace AboutYou\SDK\Test\Functional;

use \AY;

class ChildAppsTest extends AbstractAYTest
{
    /**
     *
     */
    public function testChildApps()
    {
        $ay = $this->getAYWithResultFile('child-apps.json');

        $apps = $ay->fetchChildApps();
        $this->assertInternalType('array', $apps);

        foreach ($apps as $app) {
            $this->assertInstanceOf('\\AboutYou\\SDK\\Model\\App', $app);
            $this->assertInternalType('int', $app->getId());
            $this->assertInternalType('string', $app->getLogoUrl());
            $this->assertInternalType('string', $app->getName());
            $this->assertInternalType('string', $app->getUrl());
            $this->assertInternalType('string', $app->getPrivacyStatementUrl());
            $this->assertInternalType('string', $app->getTosUrl());
        }
    }
}
