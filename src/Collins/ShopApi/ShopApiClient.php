<?php
/**
 * @author nils.droege@project-collins.com
 * (c) ABOUT YOU GmbH
 */

namespace Collins\ShopApi;

use Collins\ShopApi\Exception\ApiErrorException;
use Collins\ShopApi\Exception\MalformedJsonException;
use Guzzle\Http\Client;
use Guzzle\Http\Message\EntityEnclosingRequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class ShopApiClient
{
    const API_END_POINT_STAGE   = 'http://shop-api.staging.aboutyou.de/api';
    const API_END_POINT_SANDBOX = 'http://shop-api.sandbox.aboutyou.de/api';
    const API_END_POINT_LIVE    = 'https://shop-api.aboutyou.de/api';

    /**
     * Guzzle client that is needed to execute API requests.
     * Will be initialized before the first request is done.
     * @var \Guzzle\Http\Client
     */
    protected $guzzleClient = null;

    /** @var LoggerInterface */
    protected $logger;

    /** @var string */
    protected $logTemplate;

    /** @var string */
    protected $appId = null;

    /** @var string */
    protected $appPassword = null;

    /** @var string */
    protected $pageId = null;

    /**
     * @var string
     *
     * current end points are:
     * stage: http://shop-api.staging.aboutyou.de/api
     * live:  https://shop-api.aboutyou.de/api
     */
    protected $apiEndPoint;

    /**
     * @param string $appId
     * @param string $appPassword
     * @param string $apiEndPoint
     * @param LoggerInterface $logger
     */
    public function __construct($appId, $appPassword, $apiEndPoint = 'stage', LoggerInterface $logger = null)
    {
        $this->setAppCredentials($appId, $appPassword);
        $this->setApiEndpoint($apiEndPoint);
        $this->setLogger($logger ?: new NullLogger());
    }

    /**
     * @param string $appId        the app id for client authentication
     * @param string $appPassword  the app password/token for client authentication.
     */
    public function setAppCredentials($appId, $appPassword)
    {
        $this->appId = $appId;
        $this->appPassword = $appPassword;
    }

    /**
     * @return string
     */
    public function getApiEndPoint()
    {
        return $this->apiEndPoint;
    }

    /**
     * @param string $apiEndPoint the endpoint can be the string 'stage' or 'live',
     *                            then the default endpoints will be used or
     *                            an absolute url
     */
    public function setApiEndpoint($apiEndPoint)
    {
        switch ($apiEndPoint) {
            case 'stage':
                $this->apiEndPoint = self::API_END_POINT_STAGE;
                break;
            case 'sandbox':
                $this->apiEndPoint = self::API_END_POINT_SANDBOX;
                break;
            case 'live':
                $this->apiEndPoint = self::API_END_POINT_LIVE;
                break;
            default:
                $this->apiEndPoint = $apiEndPoint;
        }
    }

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param string $logTemplate
     *
     * @see http://api.guzzlephp.org/class-Guzzle.Log.MessageFormatter.html
     */
    public function setLogTemplate($logTemplate)
    {
        $this->logTemplate = $logTemplate;
    }

    /**
     * @return string
     */
    public function getLogTemplate()
    {
        return $this->logTemplate;
    }

    /**
     * @return string
     */
    public function getPageId()
    {
        return $this->pageId;
    }

    /**
     * @param string $pageId
     */
    public function setPageId($pageId)
    {
        $this->pageId = $pageId;
    }

    /**
     * @param Client $guzzleClient
     */
    public function setClient(Client $guzzleClient)
    {
        $this->guzzleClient = $guzzleClient;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        if ($this->guzzleClient) {
            return $this->guzzleClient;
        }

        $this->guzzleClient = new Client($this->getApiEndPoint());

        return $this->guzzleClient;
    }

    /**
     * Builds a JSON string representing the request data via Guzzle.
     * Executes the API request.
     *
     * @param string $body the queries as json string
     *
     * @return \Guzzle\Http\Message\Response response object
     *
     * @throws ApiErrorException will be thrown if response was invalid
     */
    public function request($body)
    {
        $apiClient = $this->getClient();

        /** @var EntityEnclosingRequestInterface $request */
        $request = $apiClient->post();
        $request->setBody($body);
        $request->setAuth($this->appId, $this->appPassword);
        $request->setHeader('Accept-Encoding', 'gzip,deflate');
        if ($this->pageId) {
            $request->setHeader('X-Page-ID', $this->pageId);
        }

        if ($this->logger) {
            $adapter = new \Guzzle\Log\PsrLogAdapter($this->logger);
            $logPlugin = new \Guzzle\Plugin\Log\LogPlugin($adapter, $this->logTemplate);

            $request->addSubscriber($logPlugin);
        }

        $response = $request->send();

        try {
            if (!$response->isSuccessful()) {
                throw new ApiErrorException(
                    $response->getReasonPhrase(),
                    $response->getStatusCode()
                );
            }
            try {
                if (!is_array($response->json())) {
                    throw new MalformedJsonException(
                        'result is not array'
                    );
                }
            } catch (\Exception $e) {
                throw new MalformedJsonException(
                    'unknown error occurred', 0, $e
                );
            }
        } catch (\Exception $e) {
            throw new ApiErrorException(
                'unknown error occurred', 0, $e
            );
        }

        return $response;
    }
}
