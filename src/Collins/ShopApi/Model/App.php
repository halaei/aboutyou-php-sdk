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

    public function __construct($json)
    {
        $this->id     = $json->id;
        $this->logoUrl = $json->logo_url;
        $this->name     = $json->name;
        $this->url     = $json->url;
        $this->privacyStatementUrl     = $json->privacy_statement_url;
        $this->tosUrl     = $json->tos_url;
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