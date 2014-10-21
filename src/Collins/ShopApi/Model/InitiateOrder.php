<?php
/**
 * @author nils.droege@project-collins.com
 * (c) ABOUT YOU GmbH
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
     * @param string $url
     * @param string $userToken
     * @param string $appToken
     */
    public function __construct($url, $userToken, $appToken)
    {
        $this->url       = $url;
        $this->userToken = $userToken;
        $this->appToken  = $appToken;
    }

    /**
     * @param \stdClass $json
     *
     * @return static
     */
    public static function createFromJson(\stdClass $jsonObject)
    {
        $order = new static(
            $jsonObject->url,
            $jsonObject->user_token,
            $jsonObject->app_token
        );

        $order->parseErrorResult($jsonObject);

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
    protected $errorCode = 0;

    /** @var string */
    protected $errorMessage = null;

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