<?php
namespace GuzzleHttp\Subscriber\MessageIntegrity;

use GuzzleHttp\Event\SubscriberInterface;
use GuzzleHttp\Event\BeforeEvent;
use GuzzleHttp\Message\ResponseInterface;

/**
 * Verifies the message integrity of a response after all of the data has been
 * received.
 */
class ResponseIntegrity implements SubscriberInterface
{
    private $full;
    private $streaming;

    /**
     * Creates a new plugin that validates the Content-MD5 of responses
     *
     * @return self
     */
    public static function createForContentMd5()
    {
        return new self(array(
            'hash' => new PhpHash('md5', array('base64' => true)),
            'expected' => function (ResponseInterface $response) {
                return $response->getHeader('Content-MD5');
            }
		));
    }

    /**
     * Validates the options provided to an integrity plugin.
     *
     * @param array $config Associative array of configuration options.
     * @throws \InvalidArgumentException
     */
    public static function validateOptions(array $config)
    {
        if (!isset($config['expected'])) {
            throw new \InvalidArgumentException('expected is required');
        }

        if (!is_callable($config['expected'])) {
            throw new \InvalidArgumentException('expected must be callable');
        }

        if (!isset($config['hash'])) {
            throw new \InvalidArgumentException('hash is required');
        }

        if (!($config['hash']) instanceof HashInterface) {
            throw new \InvalidArgumentException('hash must be an instance of '
                . __NAMESPACE__ . '\\HashInterface');
        }
    }

    /**
     * @param array $config Associative array of configuration options.
     *     - expected: (callable) A function that returns the hash that is
     *       expected for a response. The function accepts a ResponseInterface
     *       objects and returns a string that is compared against the
     *       calculated rolling hash.
     *     - hash: (HashInterface) used to validate the header value
     *     - size_cutoff: (int) Don't validate when size is greater than this
     *       number.
     * @throws \InvalidArgumentException
     */
    public function __construct(array $config)
    {
        $this->full = new CompleteResponse($config);
        $this->streaming = new StreamResponse($config);
    }

    public function getEvents()
    {
        return array('before' => array('onBefore'));
    }

    public function onBefore(BeforeEvent $event)
    {
        $request = $event->getRequest();
        $config = $request->getConfig();

        // So that we do not attach subscribers multiple times.
        if (isset($config['applied_message_integrity'])) {
            return;
        }

        $config['applied_message_integrity'] = true;
        $request->getEmitter()->attach(
            $config['stream']
                ? $this->streaming
                : $this->full
        );
    }
}
