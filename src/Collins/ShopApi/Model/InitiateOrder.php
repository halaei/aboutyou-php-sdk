<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Model;


class InitiateOrder
{
    /** @var string */
    protected $url;

    /** @var string */
    protected $userToken;

    /** @var string */
    protected $appToken;

    public function __construct($json)
    {
        $this->url = $json->url;
        $this->userToken = $json->user_token;
        $this->appToken = $json->app_token;
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
    public function getUserToken()
    {
        return $this->userToken;
    }

    /**
     * @return string
     */
    public function getAppToken()
    {
        return $this->appToken;
    }
}