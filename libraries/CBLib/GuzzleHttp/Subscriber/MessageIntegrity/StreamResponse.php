<?php
namespace GuzzleHttp\Subscriber\MessageIntegrity;

use GuzzleHttp\Event\CompleteEvent;
use GuzzleHttp\Event\RequestEvents;
use GuzzleHttp\Event\SubscriberInterface;
use GuzzleHttp\Message\ResponseInterface;

/**
 * Verifies the message integrity of a response only after the entire response
 * body has been read.
 */
class StreamResponse implements SubscriberInterface
{
    private $hash;
    private $expectedFn;

    /**
     * @param array $config Associative array of configuration options.
     * @see GuzzleHttp\Subscriber\ResponseIntegrity::__construct for a
     *     list of available configuration options.
     */
    public function __construct(array $config)
    {
        ResponseIntegrity::validateOptions($config);
        $this->expectedFn = $config['expected'];
        $this->hash = $config['hash'];
    }

    public function getEvents()
    {
        // Fire this event near the end of the event chain.
        return array('headers' => array('onComplete', RequestEvents::LATE));
    }

    public function onComplete(CompleteEvent $event)
    {
        $response = $event->getResponse();
        if (!($expected = $this->getExpected($response))) {
            return;
        }

        $request = $event->getRequest();
        $response->setBody(new ReadIntegrityStream(
            $response->getBody(),
            $this->hash,
            $expected,
            function ($result, $expected) use ($request, $response) {
                throw new MessageIntegrityException(
                    sprintf('Message integrity check failure. Expected '
                        . '"%s" but got "%s"', $expected, $result),
                    $request,
                    $response
                );
            }
        ));
    }

    private function getExpected(ResponseInterface $response)
    {
        if (!($body = $response->getBody())) {
            return false;
        } elseif ($response->hasHeader('Transfer-Encoding') ||
            $response->hasHeader('Content-Encoding')
        ) {
            // Currently does not support un-gzipping or inflating responses
            return false;
        }

        return call_user_func($this->expectedFn, $response);
    }
}
