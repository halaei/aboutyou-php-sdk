<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Model;

class ResultError
{
    public function __construct(\stdClass $jsonObject)
    {
        $this->parseErrorResult($jsonObject);
    }

    /** @var string */
    protected $errorIdent;

    /** @var integer */
    protected $errorCode;

    /** @var string */
    protected $errorMessage;

    protected function parseErrorResult(\stdClass $jsonObject)
    {
        $this->errorIdent   = isset($jsonObject->error_ident) ? (string)$jsonObject->error_ident : null;
        $this->errorCode    = isset($jsonObject->error_code) ? (int)$jsonObject->error_code : 0;
        $this->errorMessage = isset($jsonObject->error_message) ? $jsonObject->error_message : null;
    }

    /**
     * @return string
     */
    public function getErrorIdent()
    {
        return $this->errorIdent;
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