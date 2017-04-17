<?php

namespace GuzzleHttp\Subscriber\Retry;

use GuzzleHttp\Event\RequestEvents;
use GuzzleHttp\Event\SubscriberInterface;
use GuzzleHttp\Event\AbstractTransferEvent;
use GuzzleHttp\Event\ErrorEvent;
use GuzzleHttp\Subscriber\Log\Formatter;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Plugin to automatically retry failed HTTP requests using filters a delay
 * strategy.
 */
class RetrySubscriber implements SubscriberInterface
{
    const RETRY = true;
    const DEFER = false;
    const BREAK_CHAIN = -1;
    const MSG_FORMAT = '[{ts}] {method} {url} - {code} {phrase} - Retries: {retries}, Delay: {delay}, Time: {connect_time}, {total_time}, Error: {error}';

    /** @var callable */
    private $filter;

    /** @var callable */
    private $delayFn;

    /** @var int */
    private $maxRetries;

    /** @var callable */
    private $sleepFn;

    /**
     * @param array $config Associative array of configuration options.
     *     - filter: (callable) (Required) Filter used to determine whether or
     *       not to retry a request. The filter must be a callable that accepts
     *       the current number of retries and an AbstractTransferEvent object.
     *       The filter must return true or false to denote if the request must
     *       be retried.
     *     - delay: (callable) Accepts the number of retries and an
     *       AbstractTransferEvent and returns the amount of of time in
     *       milliseconds to delay. If no value is provided, a default
     *       exponential backoff implementation.
     *     - max: (int) Maximum number of retries to allow before giving up.
     *       Defaults to 5.
     *     - sleep: (callable) Function invoked when the subscriber needs to
     *       sleep. Accepts a float containing the amount of time in
     *       milliseconds to sleep and an AbstractTransferEvent. Defaults to a
     *       usleep().
     *
     * @throws \InvalidArgumentException if a filter is not provided.
     */
    public function __construct(array $config)
    {
        if (!isset($config['filter'])) {
            throw new \InvalidArgumentException('A "filter" is required');
        }

        $this->filter = $config['filter'];
        $this->delayFn = isset($config['delay'])
            ? $config['delay']
            : array(__CLASS__, 'exponentialDelay');
        $this->sleepFn = isset($config['sleep'])
            ? $config['sleep']
            : array(__CLASS__, 'defaultSleep');
        $this->maxRetries = isset($config['max'])
            ? $config['max']
            : 5;
    }

    public function getEvents()
    {
        return array(
            // Fire before responses are verified (e.g., HttpError).
            'complete' => array('onComplete', RequestEvents::VERIFY_RESPONSE + 100),
            // Fire soon after logging, history, and other early events.
            'error'    => array('onComplete', RequestEvents::EARLY - 100)
		);
    }

    public function onComplete(AbstractTransferEvent $event)
    {
        $request = $event->getRequest();
        $retries = (int) $request->getConfig()->get('retries');

        if ($retries >= $this->maxRetries) {
            return;
        }

        $filterFn = $this->filter;
        if (call_user_func_array($filterFn, array($retries, $event))) {
            $delayFn = $this->delayFn;
            $sleepFn = $this->sleepFn;
            call_user_func_array($sleepFn, array(call_user_func_array($delayFn, array($retries, $event)), $event));
            $request->getConfig()->set('retries', ++$retries);
            $event->intercept($event->getClient()->send($request));
        }
    }

    /**
     * Default sleep implementation.
     */
    public static function defaultSleep($time, /** @noinspection PhpUnusedParameterInspection */ AbstractTransferEvent $event)
    {
        usleep($time * 1000);
    }

    /**
     * Returns an exponential delay calculation in milliseconds.
     *
     * @param int                   $retries Number of retries so far
     * @param AbstractTransferEvent $event   Event containing transaction info
     *
     * @return int Returns the number of milliseconds to sleep
     */
    public static function exponentialDelay(
        $retries,
		/** @noinspection PhpUnusedParameterInspection */
		AbstractTransferEvent $event
    ) {
        return (int) pow(2, $retries - 1);
    }

    /**
     * Creates a delay function that logs each retry before proxying to a
     * wrapped delay function.
     *
     * @param callable         $delayFn   Delay function to proxy to
     * @param LoggerInterface  $logger    Logger used to log messages
     * @param string|Formatter $formatter Message formatter to format messages
     *
     * @return callable
     */
    public static function createLoggingDelay(
        $delayFn,
        LoggerInterface $logger,
        $formatter = null
    ) {
        if (!$formatter) {
            $formatter = new Formatter(self::MSG_FORMAT);
        } elseif (!($formatter instanceof Formatter)) {
            $formatter = new Formatter($formatter);
        }

        return function (
            $retries,
            AbstractTransferEvent $event
        ) use ($delayFn, $logger, $formatter) {
            $delay = call_user_func_array($delayFn, array($retries, $event));
            $logger->log(LogLevel::NOTICE, $formatter->format(
                $event->getRequest(),
                $event->getResponse(),
                $event instanceof ErrorEvent ? $event->getException() : null,
				array(
                    'retries' => $retries + 1,
                    'delay'   => $delay
				) + $event->getTransferInfo()
            ));
            return $delay;
        };
    }

    /**
     * Creates a retry filter based on HTTP status codes
     *
     * @param array $failureStatuses Pass an array of status codes to override
     *                               the default of [500, 503].
     * @return callable
     */
    public static function createStatusFilter(
        array $failureStatuses = array(500, 503)
    ) {
        // Convert the array of values into a set for hash lookups
        $failureStatuses = array_fill_keys($failureStatuses, true);

		/** @noinspection PhpUnusedParameterInspection */
		return function (
            $retries,
            AbstractTransferEvent $event
        ) use ($failureStatuses) {
            if (!($response = $event->getResponse())) {
                return false;
            }
            return isset($failureStatuses[$response->getStatusCode()]);
        };
    }

    /**
     * Creates a retry filter based on whether an HTTP method is considered
     * "safe" or "idempotent" based on RFC 7231.
     *
     * If the HTTP request method is a PUT, POST, or PATCH request, then the
     * request will not be retried. Otherwise, the filter will defer to other
     * filters if added to a filter chain via `createFilterChain()`.
     *
     * @return callable
     * @link http://tools.ietf.org/html/rfc7231#section-4.2.2
     */
    public static function createIdempotentFilter()
    {
        static $retry = array('GET' => true, 'HEAD' => true, 'PUT' => true,
            'DELETE' => true, 'OPTIONS' => true, 'TRACE' => true);

		/** @noinspection PhpUnusedParameterInspection */
		return function ($retries, AbstractTransferEvent $e) use ($retry) {
            return isset($retry[$e->getRequest()->getMethod()])
                ? RetrySubscriber::DEFER
                : RetrySubscriber::BREAK_CHAIN;
        };
    }

    /**
     * Creates a retry filter based on cURL error codes.
     *
     * @param array $errorCodes Pass an array of curl error codes to override
     *                          the default list of error codes.
     * @return callable
     */
    public static function createCurlFilter($errorCodes = null)
    {
        $errorCodes = $errorCodes ?: array(CURLE_COULDNT_RESOLVE_HOST,
            CURLE_COULDNT_CONNECT, CURLE_PARTIAL_FILE, CURLE_WRITE_ERROR,
            CURLE_READ_ERROR, CURLE_OPERATION_TIMEOUTED,
            CURLE_SSL_CONNECT_ERROR, CURLE_HTTP_PORT_FAILED, CURLE_GOT_NOTHING,
            CURLE_SEND_ERROR, CURLE_RECV_ERROR);

        $errorCodes = array_fill_keys($errorCodes, 1);

		/** @noinspection PhpUnusedParameterInspection */
		return function (
            $retries,
            AbstractTransferEvent $event
        ) use ($errorCodes) {
            return isset($errorCodes[(int) $event->getTransferInfo('curl_result')]);
        };
    }

    /**
     * Creates a chain of callables that triggers one after the other until a
     * callable returns true (which results in a true return value), or a
     * callable short circuits the chain by returning -1 (resulting in a false
     * return value).
     *
     * @param array $filters Array of callables that accept the number of
     *   retries and an after send event and return true to retry the
     *   transaction, false to not retry and pass to the next filter in the
     *   chain, or -1 to not retry and to immediately break the chain.
     *
     * @return callable Returns a filter that can be used to determine if a
     *   transaction should be retried
     */
    public static function createChainFilter(array $filters)
    {
        return function (
            $retries,
            AbstractTransferEvent $event
        ) use ($filters) {
            foreach ($filters as $filter) {
                $result = call_user_func_array($filter, array($retries, $event));
                if ($result === RetrySubscriber::RETRY) {
                    return true;
                } elseif ($result === RetrySubscriber::BREAK_CHAIN) {
                    return false;
                }
            }

            return false;
        };
    }
}
