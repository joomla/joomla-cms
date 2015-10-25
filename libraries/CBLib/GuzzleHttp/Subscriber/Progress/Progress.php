<?php
namespace GuzzleHttp\Subscriber\Progress;

use GuzzleHttp\Event\RequestEvents;
use GuzzleHttp\Event\BeforeEvent;
use GuzzleHttp\Event\HeadersEvent;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Event\SubscriberInterface;

/**
 * Adds upload and download progress callbacks to non-streaming requests.
 */
class Progress implements SubscriberInterface
{
    private $uploadProgress;
    private $downloadProgress;

    /**
     * @param callable $uploadProgress   Invoked as data is uploaded.
     * @param callable $downloadProgress Invoked as data is downloaded.
     */
    public function __construct(
        $uploadProgress = null,
        $downloadProgress = null
    ) {
        $this->uploadProgress = $uploadProgress;
        $this->downloadProgress = $downloadProgress;
    }

    public function getEvents()
    {
        $events = array();
        if ($this->uploadProgress) {
            $events['before'] = array('onBefore', RequestEvents::PREPARE_REQUEST);
        }
        if ($this->downloadProgress) {
            $events['headers'] = array('onHeaders');
        }

        return $events;
    }

    public function onBefore(BeforeEvent $event)
    {
        $body = $event->getRequest()->getBody();

        // Only works when there is a body with a known size.
        if (!$body || !$body->getSize()) {
            return;
        }

        // Wrap the existing request body in an upload decorator.
        $progressBody = new UploadProgressStream(
            $body,
            $this->uploadProgress,
            $event->getClient(),
            $event->getRequest()
        );

        $event->getRequest()->setBody($progressBody);
    }

    public function onHeaders(HeadersEvent $event)
    {
        $response = $event->getResponse();

        // Only works when a Content-Length header is present.
        if (!($size = $response->getHeader('Content-Length'))) {
            return;
        }

        // Wrap the existing body (if present) in a decorator.
        $response->setBody(new DownloadProgressStream(
            $response->getBody() ?: new Stream(fopen('php://temp', 'r+')),
            $this->downloadProgress,
            (int) $size,
            $event->getClient(),
            $event->getRequest(),
            $event->getResponse()
        ));
    }
}
