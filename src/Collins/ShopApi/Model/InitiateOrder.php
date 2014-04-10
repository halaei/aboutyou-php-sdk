<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
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

    /**
     * @param $json
     *
     * @return static
     */
    public static function createFRomJson($json)
    {
        $order = new static();

        $order->url = $json->url;
        $order->userToken = $json->user_token;
        $order->appToken = $json->app_token;

        $order->parseErrorResult($json);

        return $order;
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

    /** @var integer */
    protected $errorCode;

    /** @var string */
    protected $errorMessage;

    protected function parseErrorResult(\stdClass $jsonObject)
    {
        $this->errorCode    = isset($jsonObject->error_code) ? $jsonObject->error_code : 0;
        $this->errorMessage = isset($jsonObject->error_message) ? $jsonObject->error_message : null;
    }

    /**
     * @return int
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }
}