<?php
namespace GuzzleHttp\Subscriber\MessageIntegrity;

use GuzzleHttp\Event\RequestEvents;
use GuzzleHttp\Event\SubscriberInterface;
use GuzzleHttp\Event\CompleteEvent;
use GuzzleHttp\Message\ResponseInterface;
use GuzzleHttp\Stream\StreamInterface;

/**
 * Verifies the message integrity of a response after all of the data has been
 * received.
 */
class CompleteResponse implements SubscriberInterface
{
    /** @var HashInterface */
    private $hash;

    /** @var callable */
    private $expectedFn;
    private $sizeCutoff;

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
        $this->sizeCutoff = isset($config['size_cutoff'])
            ? $config['size_cutoff']
            : null;
    }

    public function getEvents()
    {
        // Fire at the same level of normal response verification.
        return array('complete' => array('onComplete', RequestEvents::VERIFY_RESPONSE));
    }

    public function onComplete(CompleteEvent $event)
    {
        $response = $event->getResponse();
        $expected = call_user_func($this->expectedFn, $response);

        if ($expected !== null && $this->canValidate($response)) {
            $this->matchesHash($event, $expected, $response->getBody());
        }
    }

    private function canValidate(ResponseInterface $response)
    {
        if (!($body = $response->getBody())) {
            return false;
        } elseif ($response->hasHeader('Transfer-Encoding') ||
            $response->hasHeader('Content-Encoding')
        ) {
            // Currently does not support un-gzipping or inflating responses
            return false;
        } elseif (!$body->isSeekable()) {
            return false;
        } elseif ($this->sizeCutoff !== null &&
            $body->getSize() > $this->sizeCutoff
        ) {
            return false;
        }

        return true;
    }

    private function matchesHash(
        CompleteEvent $event,
        $hash,
        StreamInterface $body
    ) {
        $body->seek(0);
        while (!$body->eof()) {
            $this->hash->update($body->read(16384));
        }

        $result = $this->hash->complete();

        if ($hash !== $result) {
            throw new MessageIntegrityException(
                sprintf('Message integrity check failure. Expected "%s" but'
                    . ' got "%s"', $hash, $result),
                $event->getRequest(),
                $event->getResponse()
            );
        }
    }
}
