<?php

namespace GuzzleHttp\Adapter\Curl;

use GuzzleHttp\Adapter\TransactionInterface;
use GuzzleHttp\Message\MessageFactoryInterface;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Stream\LazyOpenStream;
use GuzzleHttp\Exception\AdapterException;

/**
 * Creates curl resources from a request and response object
 */
class CurlFactory
{
    /**
     * Creates a cURL handle based on a transaction.
     *
     * @param TransactionInterface $transaction Holds a request and response
     * @param MessageFactoryInterface $messageFactory Used to create responses
     * @param null|resource $handle Optionally provide a curl handle to modify
     *
     * @return resource Returns a prepared cURL handle
     * @throws AdapterException when an option cannot be applied
     */
    public function __invoke(
        TransactionInterface $transaction,
        MessageFactoryInterface $messageFactory,
        $handle = null
    ) {
        $request = $transaction->getRequest();
        $mediator = new RequestMediator($transaction, $messageFactory);
        $options = $this->getDefaultOptions($request, $mediator);
        $this->applyMethod($request, $options);
        $this->applyTransferOptions($request, $mediator, $options);
        $this->applyHeaders($request, $options);
        unset($options['_headers']);

        // Add adapter options from the request's configuration options
		$requestConfig = $request->getConfig();

        if ($config = $requestConfig['curl']) {
            $options = $this->applyCustomCurlOptions($config, $options);
        }

        if (!$handle) {
            $handle = curl_init();
        }

        curl_setopt_array($handle, $options);

        return $handle;
    }

    protected function getDefaultOptions(
        RequestInterface $request,
        RequestMediator $mediator
    ) {
        $url = $request->getUrl();

        // Strip fragment from URL. See:
        // https://github.com/guzzle/guzzle/issues/453
        if (($pos = strpos($url, '#')) !== false) {
            $url = substr($url, 0, $pos);
        }

        $config = $request->getConfig();
        $options = array(
            CURLOPT_URL            => $url,
            CURLOPT_CONNECTTIMEOUT => $config['connect_timeout'] ?: 150,
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_HEADER         => false,
            CURLOPT_WRITEFUNCTION  => array($mediator, 'writeResponseBody'),
            CURLOPT_HEADERFUNCTION => array($mediator, 'receiveResponseHeader'),
            CURLOPT_READFUNCTION   => array($mediator, 'readRequestBody'),
            CURLOPT_HTTP_VERSION   => $request->getProtocolVersion() === '1.0'
                ? CURL_HTTP_VERSION_1_0 : CURL_HTTP_VERSION_1_1,
            CURLOPT_SSL_VERIFYPEER => 1,
            CURLOPT_SSL_VERIFYHOST => 2,
            '_headers'             => $request->getHeaders()
        );

        if (defined('CURLOPT_PROTOCOLS')) {
            // Allow only HTTP and HTTPS protocols
            $options[CURLOPT_PROTOCOLS] = CURLPROTO_HTTP | CURLPROTO_HTTPS;
        }

        // cURL sometimes adds a content-type by default. Prevent this.
        if (!$request->hasHeader('Content-Type')) {
            $options[CURLOPT_HTTPHEADER][] = 'Content-Type:';
        }

        return $options;
    }

    private function applyMethod(RequestInterface $request, array &$options)
    {
        $method = $request->getMethod();
        if ($method == 'HEAD') {
            $options[CURLOPT_NOBODY] = true;
            unset($options[CURLOPT_WRITEFUNCTION], $options[CURLOPT_READFUNCTION]);
        } else {
            $options[CURLOPT_CUSTOMREQUEST] = $method;
            if (!$request->getBody()) {
                unset($options[CURLOPT_READFUNCTION]);
            } else {
                $this->applyBody($request, $options);
            }
        }
    }

    private function applyBody(RequestInterface $request, array &$options)
    {
        if ($request->hasHeader('Content-Length')) {
            $size = (int) $request->getHeader('Content-Length');
        } else {
            $size = null;
        }

        $request->getBody()->seek(0);

        // You can send the body as a string using curl's CURLOPT_POSTFIELDS
		$requestConfig = $request->getConfig();

		if (($size !== null && $size < 32768) ||
            isset($requestConfig['curl']['body_as_string'])
        ) {
            $options[CURLOPT_POSTFIELDS] = $request->getBody()->getContents();
            // Don't duplicate the Content-Length header
            $this->removeHeader('Content-Length', $options);
            $this->removeHeader('Transfer-Encoding', $options);
        } else {
            $options[CURLOPT_UPLOAD] = true;
            // Let cURL handle setting the Content-Length header
            if ($size !== null) {
                $options[CURLOPT_INFILESIZE] = $size;
                $this->removeHeader('Content-Length', $options);
            }
        }

        // If the Expect header is not present, prevent curl from adding it
        if (!$request->hasHeader('Expect')) {
            $options[CURLOPT_HTTPHEADER][] = 'Expect:';
        }
    }

    private function applyHeaders(RequestInterface $request, array &$options)
    {
        foreach ($options['_headers'] as $name => $values) {
            $options[CURLOPT_HTTPHEADER][] = $name . ': ' . implode(', ', $values);
        }

        // Remove the Expect header if one was not set
        if (!$request->hasHeader('Accept')) {
            $options[CURLOPT_HTTPHEADER][] = 'Accept:';
        }
    }

    private function applyTransferOptions(
        RequestInterface $request,
        RequestMediator $mediator,
        array &$options
    ) {
        static $methods;
        if (!$methods) {
            $methods = array_flip(get_class_methods(__CLASS__));
        }

        foreach ($request->getConfig()->toArray() as $key => $value) {
            $method = "add_{$key}";
            if (isset($methods[$method])) {
                $this->{$method}($request, $mediator, $options, $value);
            }
        }
    }

	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function add_debug(
        /** @noinspection PhpUnusedParameterInspection */
		RequestInterface $request,
        RequestMediator $mediator,
        &$options,
        $value
    ) {
        if ($value) {
            $options[CURLOPT_STDERR] = is_resource($value) ? $value : STDOUT;
            $options[CURLOPT_VERBOSE] = true;
        }
    }

	/** @noinspection PhpUnusedPrivateMethodInspection */
    private function add_proxy(
        RequestInterface $request,
		/** @noinspection PhpUnusedParameterInspection */
		RequestMediator $mediator,
        &$options,
        $value
    ) {
        if (!is_array($value)) {
            $options[CURLOPT_PROXY] = $value;
        } else {
            $scheme = $request->getScheme();
            if (isset($value[$scheme])) {
                $options[CURLOPT_PROXY] = $value[$scheme];
            }
        }
    }

	/** @noinspection PhpUnusedPrivateMethodInspection */
    private function add_timeout(
		/** @noinspection PhpUnusedParameterInspection */
        RequestInterface $request,
        RequestMediator $mediator,
        &$options,
        $value
    ) {
        $options[CURLOPT_TIMEOUT_MS] = $value * 1000;
    }

	/** @noinspection PhpUnusedPrivateMethodInspection */
    private function add_connect_timeout(
		/** @noinspection PhpUnusedParameterInspection */
        RequestInterface $request,
        RequestMediator $mediator,
        &$options,
        $value
    ) {
        $options[CURLOPT_CONNECTTIMEOUT_MS] = $value * 1000;
    }

	/** @noinspection PhpUnusedPrivateMethodInspection */
    private function add_verify(
		/** @noinspection PhpUnusedParameterInspection */
        RequestInterface $request,
        RequestMediator $mediator,
        &$options,
        $value
    ) {
        if ($value === false) {
            unset($options[CURLOPT_CAINFO]);
            $options[CURLOPT_SSL_VERIFYHOST] = 0;
            $options[CURLOPT_SSL_VERIFYPEER] = false;
        } elseif ($value === true || is_string($value)) {
            $options[CURLOPT_SSL_VERIFYHOST] = 2;
            $options[CURLOPT_SSL_VERIFYPEER] = true;
            if ($value !== true) {
                if (!file_exists($value)) {
                    throw new AdapterException('SSL certificate authority file'
                        . " not found: {$value}");
                }
                $options[CURLOPT_CAINFO] = $value;
            }
        }
    }

	/** @noinspection PhpUnusedPrivateMethodInspection */
    private function add_cert(
		/** @noinspection PhpUnusedParameterInspection */
        RequestInterface $request,
        RequestMediator $mediator,
        &$options,
        $value
    ) {
        if (!file_exists($value)) {
            throw new AdapterException("SSL certificate not found: {$value}");
        }

        $options[CURLOPT_SSLCERT] = $value;
    }

	/** @noinspection PhpUnusedPrivateMethodInspection */
    private function add_ssl_key(
		/** @noinspection PhpUnusedParameterInspection */
        RequestInterface $request,
        RequestMediator $mediator,
        &$options,
        $value
    ) {
        if (is_array($value)) {
            $options[CURLOPT_SSLKEYPASSWD] = $value[1];
            $value = $value[0];
        }

        if (!file_exists($value)) {
            throw new AdapterException("SSL private key not found: {$value}");
        }

        $options[CURLOPT_SSLKEY] = $value;
    }

	/** @noinspection PhpUnusedPrivateMethodInspection */
    private function add_stream(
		/** @noinspection PhpUnusedParameterInspection */
        RequestInterface $request,
        RequestMediator $mediator,
        &$options,
        $value
    ) {
        if ($value === false) {
            return;
        }

        throw new AdapterException('cURL adapters do not support the "stream"'
            . ' request option. This error is typically encountered when trying'
            . ' to send requests with the "stream" option set to true in '
            . ' parallel. You will either need to send these one at a time or'
            . ' implement a custom ParallelAdapterInterface that supports'
            . ' sending these types of requests in parallel. This error can'
            . ' also occur if the StreamAdapter is not available on your'
            . ' system (e.g., allow_url_fopen is disabled in your php.ini).');
    }

	/** @noinspection PhpUnusedPrivateMethodInspection */
    private function add_save_to(
		/** @noinspection PhpUnusedParameterInspection */
        RequestInterface $request,
        RequestMediator $mediator,
        &$options,
        $value
    ) {
        $mediator->setResponseBody(is_string($value)
            ? new LazyOpenStream($value, 'w')
            : Stream::factory($value));
    }

	/** @noinspection PhpUnusedPrivateMethodInspection */
    private function add_decode_content(
        RequestInterface $request,
		/** @noinspection PhpUnusedParameterInspection */
        RequestMediator $mediator,
        &$options,
		/** @noinspection PhpUnusedParameterInspection */
		$value
    ) {
        if (!$request->hasHeader('Accept-Encoding')) {
            $options[CURLOPT_ENCODING] = '';
            // Don't let curl send the header over the wire
            $options[CURLOPT_HTTPHEADER][] = 'Accept-Encoding:';
        } else {
            $options[CURLOPT_ENCODING] = $request->getHeader('Accept-Encoding');
        }
    }

    /**
     * Takes an array of curl options specified in the 'curl' option of a
     * request's configuration array and maps them to CURLOPT_* options.
     *
     * This method is only called when a  request has a 'curl' config setting.
     * Array key strings that start with CURL that have a matching constant
     * value will be automatically converted to the matching constant.
     *
     * @param array $config  Configuration array of custom curl option
     * @param array $options Array of existing curl options
     *
     * @return array Returns a new array of curl options
     */
    private function applyCustomCurlOptions(array $config, array $options)
    {
        unset($config['body_as_string']);
        $curlOptions = array();

        // Map curl constant strings to defined values
        foreach ($config as $key => $value) {
            if (defined($key) && substr($key, 0, 4) === 'CURL') {
                $key = constant($key);
            }
            $curlOptions[$key] = $value;
        }

        return $curlOptions + $options;
    }

    /**
     * Remove a header from the options array
     *
     * @param string $name    Case-insensitive header to remove
     * @param array  $options Array of options to modify
     */
    private function removeHeader($name, array &$options)
    {
        foreach (array_keys($options['_headers']) as $key) {
            if (!strcasecmp($key, $name)) {
                unset($options['_headers'][$key]);
                return;
            }
        }
    }
}
