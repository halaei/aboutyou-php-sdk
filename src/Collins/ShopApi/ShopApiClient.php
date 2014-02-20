<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi;


use Collins\Cache\CacheInterface;
use Collins\Cache\NoCache;
use Collins\ShopApi\Exception\ApiErrorException;
use Collins\ShopApi\Exception\MalformedJsonException;
use Guzzle\Http\Client;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class ShopApiClient
{
    const API_END_POINT_STAGE = 'http://ant-core-staging-s-api1.wavecloud.de/api';
    const API_END_POINT_LIVE  = 'http://ant-shop-api1.wavecloud.de/api';

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

    /** @var CacheInterface */
    protected $cache;

    /** @var string */
    protected $appId = null;
    /** @var string */
    protected $appPassword = null;

    /**
     * @var string
     *
     * current end points are:
     * stage: http://ant-core-staging-s-api1.wavecloud.de/api
     * live:  http://ant-shop-api1.wavecloud.de/api
     */
    protected $apiEndPoint;

    /**
     * @param string $appId
     * @param string $appPassword
     * @param string $apiEndPoint
     * @param CacheInterface $cache
     * @param LoggerInterface $logger
     */
    public function __construct($appId, $appPassword, $apiEndPoint = 'stage', CacheInterface $cache = null, LoggerInterface $logger = null)
    {
        $this->setAppCredentials($appId, $appPassword);
        $this->setApiEndpoint($apiEndPoint);
        $this->setCache($cache ?: new NoCache());
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
            case 'live':
                $this->apiEndPoint = self::API_END_POINT_STAGE;
                break;
            default:
                $this->apiEndPoint = $apiEndPoint;
        }
    }

    /**
     * @param CacheInterface $cache
     */
    public function setCache(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @return CacheInterface
     */
    public function getCache()
    {
        return $this->cache;
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

    public function setClient(Client $guzzleClient)
    {
        $this->guzzleClient = $guzzleClient;
    }

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
     * @param array $data array representing the API request data
     * @param integer $cacheDuration how long to save the response in the cache (if enabled) - 0 = no caching
     *
     * @return \Guzzle\Http\Message\Response response object
     *
     * @throws ApiErrorException will be thrown if response was invalid
     */
    public function request($body, $cacheDuration = 0)
    {
        $apiClient = $this->getClient();

        $cacheKey = md5($body);

        $response = $this->cache->get($cacheKey);
        if ($response) {
            return $response;
        }

        /** @var EntityEnclosingRequestInterface $request */
        $request = $apiClient->post();
        $request->setBody($body);
        $request->setAuth($this->appId, $this->appPassword);

        if ($this->logger) {
            $adapter = new \Guzzle\Log\PsrLogAdapter($this->logger);
            $logPlugin = new \Guzzle\Plugin\Log\LogPlugin($adapter, $this->logTemplate);

            $request->addSubscriber($logPlugin);
        }

        $response = $request->send();

        $this->cache->set($cacheKey, $response, $cacheDuration);

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