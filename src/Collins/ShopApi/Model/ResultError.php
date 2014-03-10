<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Model;

trait ResultErrorTrait
{
    /** @var string */
    protected $errorIdent;

    /** @var integer */
    protected $errorCode;

    /** @var string */
    protected $errorMessage;

    protected function parseErrorResult(\stdClass $jsonObject)
    {
        $this->error    = isset($jsonObject->error_code) ? $jsonObject->error_code : 0;
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