<?php

namespace Collins\ShopApi\Model;

class App
{
    /** @var string */
    protected $id;

    /** @var string */
    protected $logoUrl;

    /** @var string */
    protected $name;

    /** @var string */
    protected $url;

    /** @var string */
    protected $privacyStatementUrl;

    /** @var string */
    protected $tosUrl;

    protected function __construct()
    {
    }

    /**
     * @param \stdClass $json
     *
     * @return static
     */
    public static function createFromJson(\stdClass $json)
    {
        $app = new static();

        $app->id                  = $json->id;
        $app->logoUrl             = $json->logo_url;
        $app->name                = $json->name;
        $app->url                 = $json->url;
        $app->privacyStatementUrl = $json->privacy_statement_url;
        $app->tosUrl              = $json->tos_url;

        return $app;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLogoUrl()
    {
        return $this->logoUrl;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getPrivacyStatementUrl()
    {
        return $this->privacyStatementUrl;
    }

    /**
     * @return string
     */
    public function getTosUrl()
    {
        return $this->tosUrl;
    }
}