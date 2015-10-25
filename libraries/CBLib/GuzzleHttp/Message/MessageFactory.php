<?php
namespace GuzzleHttp\Message;

use GuzzleHttp\Event\HasEmitterInterface;
use GuzzleHttp\Post\PostFileInterface;
use GuzzleHttp\Subscriber\Cookie;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\CookieJarInterface;
use GuzzleHttp\Subscriber\HttpError;
use GuzzleHttp\Post\PostBody;
use GuzzleHttp\Post\PostFile;
use GuzzleHttp\Subscriber\Redirect;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Query;
use GuzzleHttp\Url;

/**
 * Default HTTP request factory used to create Request and Response objects.
 */
class MessageFactory implements MessageFactoryInterface
{
    //BB use ListenerAttacherTrait;

	/**
	 * Attaches event listeners and properly sets their priorities and whether
	 * or not they are are only executed once.
	 *
	 * @param HasEmitterInterface $object    Object that has the event emitter.
	 * @param array               $listeners Array of hashes representing event
	 *                                       event listeners. Each item contains
	 *                                       "name", "fn", "priority", & "once".
	 */
	private function attachListeners(HasEmitterInterface $object, array $listeners)
	{
		$emitter = $object->getEmitter();
		foreach ($listeners as $el) {
			if ($el['once']) {
				$emitter->once($el['name'], $el['fn'], $el['priority']);
			} else {
				$emitter->on($el['name'], $el['fn'], $el['priority']);
			}
		}
	}

	/**
	 * Extracts the allowed events from the provided array, and ignores anything
	 * else in the array. The event listener must be specified as a callable or
	 * as an array of event listener data ("name", "fn", "priority", "once").
	 *
	 * @param array $source Array containing callables or hashes of data to be
	 *                      prepared as event listeners.
	 * @param array $events Names of events to look for in the provided $source
	 *                      array. Other keys are ignored.
	 * @return array
	 */
	private function prepareListeners(array $source, array $events)
	{
		$listeners = array();
		foreach ($events as $name) {
			if (isset($source[$name])) {
				$this->buildListener($name, $source[$name], $listeners);
			}
		}

		return $listeners;
	}

	/**
	 * Creates a complete event listener definition from the provided array of
	 * listener data. Also works recursively if more than one listeners are
	 * contained in the provided array.
	 *
	 * @param string         $name      Name of the event the listener is for.
	 * @param array|callable $data      Event listener data to prepare.
	 * @param array          $listeners Array of listeners, passed by reference.
	 *
	 * @throws \InvalidArgumentException if the event data is malformed.
	 */
	private function buildListener($name, $data, &$listeners)
	{
		static $defaults = array('priority' => 0, 'once' => false);

		// If a callable is provided, normalize it to the array format.
		if (is_callable($data)) {
			$data = array('fn' => $data);
		}

		// Prepare the listener and add it to the array, recursively.
		if (isset($data['fn'])) {
			$data['name'] = $name;
			$listeners[] = $data + $defaults;
		} elseif (is_array($data)) {
			foreach ($data as $listenerData) {
				$this->buildListener($name, $listenerData, $listeners);
			}
		} else {
			throw new \InvalidArgumentException('Each event listener must be a '
				. 'callable or an associative array containing a "fn" key.');
		}
	}

	//BB end of ListenerAttacherTrait

    /** @var HttpError */
    private $errorPlugin;

    /** @var Redirect */
    private $redirectPlugin;

    /** @var array */
    protected static $classMethods = array();

    public function __construct()
    {
        $this->errorPlugin = new HttpError();
        $this->redirectPlugin = new Redirect();
    }

    public function createResponse(
        $statusCode,
        array $headers = array(),
        $body = null,
        array $options = array()
    ) {
        if (null !== $body) {
            $body = Stream::factory($body);
        }

        return new Response($statusCode, $headers, $body, $options);
    }

    public function createRequest($method, $url, array $options = array())
    {
        // Handle the request protocol version option that needs to be
        // specified in the request constructor.
        if (isset($options['version'])) {
            $options['config']['protocol_version'] = $options['version'];
            unset($options['version']);
        }

        $request = new Request($method, $url, array(), null,
            isset($options['config']) ? $options['config'] : array());

        unset($options['config']);

        // Use a POST body by default
        if ($method == 'POST' &&
            !isset($options['body']) &&
            !isset($options['json'])
        ) {
            $options['body'] = array();
        }

        if ($options) {
            $this->applyOptions($request, $options);
        }

        return $request;
    }

    /**
     * Create a request or response object from an HTTP message string
     *
     * @param string $message Message to parse
     *
     * @return RequestInterface|ResponseInterface
     * @throws \InvalidArgumentException if unable to parse a message
     */
    public function fromMessage($message)
    {
        static $parser;
        if (!$parser) {
            $parser = new MessageParser();
        }

        // Parse a response
        if (strtoupper(substr($message, 0, 4)) == 'HTTP') {
            $data = $parser->parseResponse($message);
            return $this->createResponse(
                $data['code'],
                $data['headers'],
                $data['body'] === '' ? null : $data['body'],
                $data
            );
        }

        // Parse a request
        if (!($data = ($parser->parseRequest($message)))) {
            throw new \InvalidArgumentException('Unable to parse request');
        }

        return $this->createRequest(
            $data['method'],
            Url::buildUrl($data['request_url']),
			array(
                'headers' => $data['headers'],
                'body' => $data['body'] === '' ? null : $data['body'],
                'config' => array(
                    'protocol_version' => $data['protocol_version']
				)
			)
        );
    }

    /**
     * Apply POST fields and files to a request to attempt to give an accurate
     * representation.
     *
     * @param RequestInterface $request Request to update
     * @param array            $body    Body to apply
     */
    protected function addPostData(RequestInterface $request, array $body)
    {
        static $fields = array('string' => true, 'array' => true, 'NULL' => true,
            'boolean' => true, 'double' => true, 'integer' => true);

        $post = new PostBody();
        foreach ($body as $key => $value) {
            if (isset($fields[gettype($value)])) {
                $post->setField($key, $value);
            } elseif ($value instanceof PostFileInterface) {
                $post->addFile($value);
            } else {
                $post->addFile(new PostFile($key, $value));
            }
        }

        if ($request->getHeader('Content-Type') == 'multipart/form-data') {
            $post->forceMultipartUpload(true);
        }

        $request->setBody($post);
    }

    protected function applyOptions(
        RequestInterface $request,
        array $options = array()
    ) {
        // Values specified in the config map are passed to request options
        static $configMap = array('connect_timeout' => 1, 'timeout' => 1,
            'verify' => 1, 'ssl_key' => 1, 'cert' => 1, 'proxy' => 1,
            'debug' => 1, 'save_to' => 1, 'stream' => 1, 'expect' => 1);

        // Take the class of the instance, not the parent
        $selfClass = get_class($this);

        // Check if we already took it's class methods and had them saved
        if (!isset(self::$classMethods[$selfClass])) {
            self::$classMethods[$selfClass] = array_flip(get_class_methods($this));
        }

        // Take class methods of this particular instance
        $methods = self::$classMethods[$selfClass];

        // Iterate over each key value pair and attempt to apply a config using
        // double dispatch.
        $config = $request->getConfig();
        foreach ($options as $key => $value) {
            $method = "add_{$key}";
            if (isset($methods[$method])) {
                $this->{$method}($request, $value);
            } elseif (isset($configMap[$key])) {
                $config[$key] = $value;
            } else {
                throw new \InvalidArgumentException("No method is configured "
                    . "to handle the {$key} config key");
            }
        }
    }

	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function add_body(RequestInterface $request, $value)
    {
        if ($value !== null) {
            if (is_array($value)) {
                $this->addPostData($request, $value);
            } else {
                $request->setBody(Stream::factory($value));
            }
        }
    }

	/** @noinspection PhpUnusedPrivateMethodInspection */
    private function add_allow_redirects(RequestInterface $request, $value)
    {
        static $defaultRedirect = array(
            'max'     => 5,
            'strict'  => false,
            'referer' => false
		);

        if ($value === false) {
            return;
        }

        if ($value === true) {
            $value = $defaultRedirect;
        } elseif (!isset($value['max'])) {
            throw new \InvalidArgumentException('allow_redirects must be '
                . 'true, false, or an array that contains the \'max\' key');
        } else {
            // Merge the default settings with the provided settings
            $value += $defaultRedirect;
        }

        $config = $request->getConfig();
        $config['redirect'] = $value;
        $request->getEmitter()->attach($this->redirectPlugin);
    }

	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function add_exceptions(RequestInterface $request, $value)
    {
        if ($value === true) {
            $request->getEmitter()->attach($this->errorPlugin);
        }
    }

	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function add_auth(RequestInterface $request, $value)
    {
        if (!$value) {
            return;
        } elseif (is_array($value)) {
            $authType = isset($value[2]) ? strtolower($value[2]) : 'basic';
        } else {
            $authType = strtolower($value);
        }

        $request->getConfig()->set('auth', $value);

        if ($authType == 'basic') {
            $request->setHeader(
                'Authorization',
                'Basic ' . base64_encode("$value[0]:$value[1]")
            );
        } elseif ($authType == 'digest') {
            // Currently only implemented by the cURL adapter.
            // @todo: Need an event listener solution that does not rely on cURL
            $config = $request->getConfig();
            $config->setPath('curl/' . CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
            $config->setPath('curl/' . CURLOPT_USERPWD, "$value[0]:$value[1]");
        }
    }

	/** @noinspection PhpUnusedPrivateMethodInspection */
    private function add_query(RequestInterface $request, $value)
    {
        if ($value instanceof Query) {
            $original = $request->getQuery();
            // Do not overwrite existing query string variables by overwriting
            // the object with the query string data passed in the URL
            $request->setQuery($value->overwriteWith($original->toArray()));
        } elseif (is_array($value)) {
            // Do not overwrite existing query string variables
            $query = $request->getQuery();
            foreach ($value as $k => $v) {
                if (!isset($query[$k])) {
                    $query[$k] = $v;
                }
            }
        } else {
            throw new \InvalidArgumentException('query value must be an array '
                . 'or Query object');
        }
    }

	/** @noinspection PhpUnusedPrivateMethodInspection */
    private function add_headers(RequestInterface $request, $value)
    {
        if (!is_array($value)) {
            throw new \InvalidArgumentException('header value must be an array');
        }

        // Do not overwrite existing headers
        foreach ($value as $k => $v) {
            if (!$request->hasHeader($k)) {
                $request->setHeader($k, $v);
            }
        }
    }

	/** @noinspection PhpUnusedPrivateMethodInspection */
    private function add_cookies(RequestInterface $request, $value)
    {
        if ($value === true) {
            static $cookie = null;
            if (!$cookie) {
                $cookie = new Cookie();
            }
            $request->getEmitter()->attach($cookie);
        } elseif (is_array($value)) {
            $request->getEmitter()->attach(
                new Cookie(CookieJar::fromArray($value, $request->getHost()))
            );
        } elseif ($value instanceof CookieJarInterface) {
            $request->getEmitter()->attach(new Cookie($value));
        } elseif ($value !== false) {
            throw new \InvalidArgumentException('cookies must be an array, '
                . 'true, or a CookieJarInterface object');
        }
    }

	/** @noinspection PhpUnusedPrivateMethodInspection */
    private function add_events(RequestInterface $request, $value)
    {
        if (!is_array($value)) {
            throw new \InvalidArgumentException('events value must be an array');
        }

        $this->attachListeners($request, $this->prepareListeners($value,
			array('before', 'complete', 'error', 'headers')
        ));
    }

	/** @noinspection PhpUnusedPrivateMethodInspection */
    private function add_subscribers(RequestInterface $request, $value)
    {
        if (!is_array($value)) {
            throw new \InvalidArgumentException('subscribers must be an array');
        }

        $emitter = $request->getEmitter();
        foreach ($value as $subscribers) {
            $emitter->attach($subscribers);
        }
    }

	/** @noinspection PhpUnusedPrivateMethodInspection */
    private function add_json(RequestInterface $request, $value)
    {
        if (!$request->hasHeader('Content-Type')) {
            $request->setHeader('Content-Type', 'application/json');
        }

        $request->setBody(Stream::factory(json_encode($value)));
    }

	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function add_decode_content(RequestInterface $request, $value)
    {
        if ($value === false) {
            return;
        }

        if ($value !== true) {
            $request->setHeader('Accept-Encoding', $value);
        }

        $requestConfig = $request->getConfig();
        $requestConfig['decode_content'] = true;
    }
}
