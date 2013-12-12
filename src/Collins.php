<?php //
namespace CollinsAPI;

require_once(__DIR__.DIRECTORY_SEPARATOR.'Config.php');
require_once(__DIR__.DIRECTORY_SEPARATOR.'classes/Constants.php');
require_once(__DIR__.DIRECTORY_SEPARATOR.'classes/CollinsException.php');
require_once(__DIR__.DIRECTORY_SEPARATOR.'vendor/autoload.php');

/**
 * Provides access to the Collins Frontend Platform.
 * This class is abstract because it's not meant to be instanciated.
 * All the public methods cover a single API query.
 *
 * @author Antevorte GmbH
 */
abstract class Collins
{
	/**
	 * Guzzle client that is needed to execute API requests.
	 * Will be initialized before the first request is done.
	 * @var \Guzzle\Http\Client 
	 */
	protected static $client = null;
	
	/**
	 * If Redis Cache is enabled, this predis client will be used
	 * for setting and getting cache date.
	 * Will be initialized before the first request is done.
	 * @var \Predis\Client 
	 */
	protected static $predisClient = null;
	
	
	protected static $appId = Config::APP_ID;
	protected static $appPassword = Config::APP_PASSWORD;
	
	protected static $memorizations = array();
	
	/**
	 * Sets the app id for client authentification.
	 * @param integer $id
	 */
	public static function setAppId($id)
	{
		self::$appId = $id;
	}
	
	/**
	 * Sets the app password for client authentification.
	 * @param string $password
	 */
	public static function setAppPassword($password)
	{
		self::$appPassword = $password;
	}
	
	
	/**
	 * Adds a set of product variants to the basket and returns
	 * the result of a basket API request.
	 * @param int $user_session_id free to choose ID of the current website visitor.
	 * The website visitor is the person the basket belongs to.
	 * @param array $product_variants set of product variants
	 * @return \CollinsAPI\Results\BasketResult
	 */
	public static function addToBasket($user_session_id, $product_variants)
	{
		$data = array(
			'basket_add' => array(
				'session_id' => (string) $user_session_id,
				'product_variant' => $product_variants
			)
		);
		
		return new Results\BasketAddResult(self::getResponse($data));
	}
	
	/**
	 * Adds a product variant to the basket and returns the result of a basket
	 * API request.
	 * @param type $user_session_id
	 * @param type $product_variant_id
	 * @param type $amount
	 * @return type
	 */
	public static function addProductVariantToBasket(
			$user_session_id,
			$product_variant_id,
			$amount)
	{
		$product_variants = array(
			array(
				'id' => $product_variant_id,
				'command' => 'add',
				'amount' => $amount
			)
		);
		
		return self::addToBasket($user_session_id, $product_variants);
	}
	
	/**
	 * Returns the result of an autocompletion API request.
	 * Autocompletion searches for products and categories by
	 * a given prefix ($searchword).
	 * @param string $searchword The prefix search word to search for
	 * @param int $limit Maximum number of results
	 * @param array $types array of types to search for
	 * (Constants::TYPE_PRODUCTS and/or CONSTANTS::TYPE_CATEGORIES)
	 * @return \CollinsAPI\Results\AutocompleteResult
	 */
	public static function getAutocomplete($searchword, $limit = 50, $types = array(
			\CollinsAPI\Constants::TYPE_PRODUCTS,
			\CollinsAPI\Constants::TYPE_CATEGORIES
		)
	)
	{
		$data = array(
			'autocompletion' => array(
				'searchword' => $searchword,
				'types' => $types,
				'limit' => $limit
			)
		);
		
		return new Results\AutocompleteResult(self::getResponse($data));
	}
	
	/**
	 * Returns the result of a basket API request.
	 * This includes all the necessary information of a basket of the user
	 * provided.
	 * @param int $user_session_id free to choose ID of the current website visitor.
	 * The website visitor is the person the basket belongs to.
	 * @param array $product_variants set of product variants
	 * @return \CollinsAPI\Results\BasketResult
	 */
	public static function getBasket($user_session_id)
	{
		$data = array(
			'basket_get' => array(
				'session_id' => (string) $user_session_id
			)
		);
		
		return new Results\BasketGetResult(self::getResponse($data));
	}
	
	/**
	 * Returns the result of a category search API request.
	 * By passing one or several category ids it will return
	 * a result of the categories data.
	 * 
	 * @param mixed $ids either a single category ID as integer or an array of IDs
	 * @return \CollinsAPI\Results\CategoryResult
	 */
	public static function getCategories($ids)
	{
		// we allow to pass a single ID instead of an array
		if(!is_array($ids))
		{
			$ids = array($ids);
		}
		
		
		$data = array(
			'category' => array(
				'ids' => $ids
			)
		);
		
		return new Results\CategoryResult(self::getResponse($data, 60*60));
	}
	
	
	/**
	 * Returns the result of a category tree API request.
	 * It simply returns the whole category tree of your app.
	 * 
	 * @return \CollinsAPI\Results\CategoryTreeResult
	 */
	public static function getCategoryTree()
	{
		$data = array(
			'category_tree' => (object) null
		);
		
		return new Results\CategoryTreeResult(self::getResponse($data, 60*60));
	}
	
	/**
	 * Returns the result of a facet API request.
	 * It simply returns all the facets that are relevant for your app.
	 * 
	 * @param array $group_ids array of group ids
	 * @return \CollinsAPI\Results\FacetResult
	 */
	public static function getFacets($group_ids = array(), $limit = null, $offset = null)
	{
		
		$data = array(
			'facets' => (object) null
		);
		
		if(count($group_ids) || $limit || $offset)
		{
			$data['facets'] = array();
		}
		
		if(!is_array($group_ids))
		{
			$group_ids = array($group_ids);
		}
		
		if(count($group_ids))
		{
			$data['facets']['group_ids'] = $group_ids;
		}
		
		if($limit)
		{
			$data['facets']['limit'] = $limit;
		}
		
		if($offset)
		{
			$data['facets']['offset'] = $offset;
		}
		
		return new Results\FacetResult(self::getResponse($data, 60*60));
	}
	
	/**
	 * Returns the result of a facet type API request.
	 * It simply returns all the ids of facet groups tat are relevant for your app.
	 * 
	 * @return \CollinsAPI\Results\FacetTypeResult
	 */
	public static function getFacetTypes()
	{
		$data = array(
			'facet_types' => (object) null
		);
		
		return new Results\FacetTypeResult(self::getResponse($data, 60*60));
	}
	
	/**
	 * Initiates an order.
	 * 
	 * @param int $user_session_id free to choose ID of the current website visitor.
	 * This is needed here to get the basket of the user.
	 * @param string $success_url URL Collins will redirect to after the order
	 * is finished.
	 * @param string $cancel_url URL Collins will redirect to if the user cancels the order
	 * on purpose.
	 * @param string $error_url URL Collins will redirect to if the order couldn't be finished.
	 * * @return \CollinsAPI\Results\InitiateOrderResult
	 */
	public static function initiateOrder($user_session_id, $success_url, $cancel_url, $error_url)
	{
		$data = array(
			'initiate_order' => array(
				'session_id' => (string) $user_session_id,
				'success_url' => $success_url,
				'cancel_url' => $cancel_url,
				'error_url' => $error_url
			)
		);
		
		return new Results\InitiateOrderResult(self::getResponse($data));
	}
	
	/**
	 * Returns the result of a live query API request.
	 * Use this to check if a product variant is really in stock.
	 * This call skips the internal cache and could return a different
	 * result than the product request because of this. Don't use
	 * this for a lot of products, e.g. on category pages but for
	 * single products e.g. before a product is added to the basket.
	 * 
	 * @param mixed $ids either a single product ID as integer or an array of IDs
	 * @return \CollinsAPI\Results\LiveVariantResult
	 */
	public static function getLiveVariant($ids)
	{
		// we allow to pass a single ID instead of an array
		if(!is_array($ids))
		{
			$ids = array($ids);
		}
		
		$data = array(
			'live_variant' => array(
				'ids' => $ids
			)
		);
		return new Results\LiveVariantResult(self::getResponse($data));
	}
	
	/**
	 * Returns the result of a product search API request.
	 * Use this method to search for products you don't know the ID of.
	 * If you already know the ID, e.g. on a product detail page, use
	 * Collins::getProducts() instead.
	 * 
	 * @param int $user_session_id free to choose ID of the current website visitor.
	 * This field is required for tracking reasons.
	 * @param array $filter contains data to filter products for
	 * @param array $result contains data for reducing the result
	 * @return \CollinsAPI\Results\ProductSearchResult
	 */
	public static function getProductSearch(
			$user_session_id,
			array $filter = array(),
			array $result = array(
				'fields' => array(
					'id',
					'name',
					'active',
					'brand_id',
					'description_long',
					'description_short',
					'default_variant',
					'variants',
					'min_price',
					'max_price',
					'sale',
					'default_image',
					'attributes_merged',
					'categories'
				)
			)
	)
	{
		$data = array(
			'product_search' => array(
				'session_id' => (string) $user_session_id
			)
		);
		
		if(count($filter) > 0)
		{
			$data['product_search']['filter'] = $filter;
		}
		
		if(count($result) > 0)
		{
			$data['product_search']['result'] = $result;
		}
		
		return new Results\ProductSearchResult(self::getResponse($data));
	}
	
	/**
	 * Returns the result of a product get API request.
	 * Use this method to get product data of products you already know
	 * the ID of. E.g. on a product detail page.
	 * 
	 * @param mixed $ids either a single category ID as integer or an array of IDs
	 * @param array $fields fields of product data to be returned
	 * @return \CollinsAPI\Results\ProductResult
	 */
	public static function getProducts($ids, array $fields = array(
		'id',
		'name',
		'active',
		'brand_id',
		'description_long',
		'description_short',
		'default_variant',
		'variants',
		'min_price',
		'max_price',
		'sale',
		'default_image',
		'attributes_merged',
		'categories'
	))
	{
		// we allow to pass a single ID instead of an array
		if(!is_array($ids))
		{
			$ids = array($ids);
		}
		
		$data = array(
			'products' => array(
				'ids' => $ids,
				'fields' => $fields
			)
		);
		
		return new Results\ProductResult(self::getResponse($data, 60*60));
	}
	
	/**
	 * Returns the result of a product get API request.
	 * Use this method to search for products with a given facet
	 *
	 * @param int $user_session_id free to choose ID of the current website visitor.
	 * This field is required for tracking reasons.
	 * @param int $facet_group_id ID of the facet group. You can use the Constants::FACET_* constants for this.
	 * @params mixed $facets facet ID or array of facet IDs you want to filter for
	 * @param array $result contains data for reducing the result
	 */
	public static function getProductSearchByFacet(
			$user_session_id,
			$facet_group_id,
			$facets,
			array $filter = array(),
			array $result = array(
				'fields' => array(
					'id',
					'name',
					'active',
					'brand_id',
					'description_long',
					'description_short',
					'default_variant',
					'variants',
					'min_price',
					'max_price',
					'sale',
					'default_image',
					'attributes_merged',
					'categories'
				)
			))
	{
		if(!is_array($facets))
		{
			$facets = array($facets);
		}
		
		$filter = array(
			'facets' => array(
				$facet_group_id => $facets
			)
		);
		
		return self::getProductSearch($user_session_id,
			$filter,
			$result
		);
	}
	
	/**
	 * Builds a JSON string representing the request data via Guzzle.
	 * Executes the API request.
	 * 
	 * @param array $data array representing the API request data
	 * @param integer $cacheDuration how long to save the response in the cache (if enabled) - 0 = no caching
	 * @return \Guzzle\Http\Message\Response response object
	 * @throws CollinsException will be thrown if response was invalid
	 */
	protected static function getResponse($data, $cacheDuration = 0)
	{
		if(!self::$client)
		{
			self::$client = new \Guzzle\Http\Client(Config::ENTRY_POINT_URL);
		}
		
		$body = json_encode(array($data));
		
		$memorizationKey = md5($body);
		$response = isset(self::$memorizations[$memorizationKey])
						? self::$memorizations[$memorizationKey]
						: null;

		if(!$response)
		{
			if(\CollinsAPI\Config::ENABLE_REDIS_CACHE)
			{
				if(!self::$predisClient)
				{
					self::$predisClient = new \Predis\Client(
						array(
							'scheme' => 'tcp',
							'host'   => '127.0.0.1',
							'port'   => 6379
						)
					);
				}
				
				$response = unserialize(self::$predisClient->get($memorizationKey));
			}
			
			if(!$response)
			{
				$request = self::$client->post();
				$request->setBody($body);
				$request->setAuth(self::$appId, self::$appPassword);

				if(Config::ENABLE_LOGGING)
				{
					$adapter = new \Guzzle\Log\ArrayLogAdapter();
					$logPlugin = new \Guzzle\Plugin\Log\LogPlugin($adapter);

					$request->addSubscriber($logPlugin);
				}

				if(Config::ENABLE_LOGGING)
				{
					$content = '';
					foreach($adapter->getLogs() as $log)
					{
						$message = new \Guzzle\Log\MessageFormatter(Config::LOGGING_TEMPLATE);
						$content .= $message->format($log['extras']['request'], $log['extras']['response']).PHP_EOL;

					}
					$path = Config::LOGGING_PATH
								? Config::LOGGING_PATH
								: __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'logs';

					$operation = array_keys($data);
					$operation = $operation[0];

					$fileName = date('Y-m-d_H_i_s_').$operation.'_'.  uniqid().'.txt';

					file_put_contents(
						$path.DIRECTORY_SEPARATOR.$fileName,
						$content
					);
				}

				$response = $request->send();
				
				self::$memorizations[$memorizationKey] = $response;

				if(\CollinsAPI\Config::ENABLE_REDIS_CACHE && $cacheDuration > 0)
				{
					self::$predisClient->set($memorizationKey, serialize($response));
					self::$predisClient->expire($memorizationKey, $cacheDuration);
				}
			}
		}
		
		if(!$response->isSuccessful() || !is_array($response->json()))
		{
			throw new CollinsException(
					$response->getReasonPhrase(),
					$response->getStatusCode()
			);
		}
		return $response;
	}
	
	/**
	 * Returns the result of a suggest API request.
	 * Suggestions are words that are often searched together
	 * with the searchword you pass (e.g. "stretch" for "jeans").
	 * 
	 * @param string $searchword the search string to search for
	 * @return \CollinsAPI\Results\SuggestResult
	 */
	public static function getSuggest($searchword)
	{
		$data = array(
			'suggest' => array(
				'searchword' => $searchword
			)
		);
		
		return new Results\SuggestResult(self::getResponse($data));
	}
}

spl_autoload_register(function($class)
{
	//use this autoload function only for classes of the
	// the CollinsAPI namespace
	if(preg_match('/^(\\\|)CollinsAPI.+/i', $class) > 0)
	{
		$class = str_replace(array(
			'CollinsAPI',
			'\CollinsAPI'
		), '', $class);


		$pathElements = explode('\\', $class);

		$path = '';
		foreach($pathElements as $i => $pathElement)
		{
			if($i < count($pathElements)-1)
			{
				$pathElement = strtolower($pathElement);
			}

			$path .= DIRECTORY_SEPARATOR.$pathElement;
		}

		require_once('classes'.$path.'.php');
	}
});
