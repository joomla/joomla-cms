<?php

namespace GuzzleHttp\Command\Guzzle\Subscriber;

use GuzzleHttp\Event\SubscriberInterface;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Command\Guzzle\GuzzleClientInterface;
use GuzzleHttp\Command\Guzzle\GuzzleCommandInterface;
use GuzzleHttp\Command\Guzzle\RequestLocation\BodyLocation;
use GuzzleHttp\Command\Guzzle\RequestLocation\HeaderLocation;
use GuzzleHttp\Command\Guzzle\RequestLocation\JsonLocation;
use GuzzleHttp\Command\Guzzle\RequestLocation\PostFieldLocation;
use GuzzleHttp\Command\Guzzle\RequestLocation\PostFileLocation;
use GuzzleHttp\Command\Guzzle\RequestLocation\QueryLocation;
use GuzzleHttp\Command\Guzzle\RequestLocation\XmlLocation;
use GuzzleHttp\Command\Event\PrepareEvent;
use GuzzleHttp\Command\Guzzle\Parameter;
use GuzzleHttp\Command\Guzzle\RequestLocation\RequestLocationInterface;

/**
 * Subscriber used to create HTTP requests for commands based on a service
 * description.
 */
class PrepareRequest implements SubscriberInterface
{
    /** @var RequestLocationInterface[] */
    private $requestLocations;

    /**
     * @param RequestLocationInterface[] $requestLocations Extra request locations
     */
    public function __construct(array $requestLocations = array())
    {
        static $defaultRequestLocations;
        if (!$defaultRequestLocations) {
            $defaultRequestLocations = array(
                'body'      => new BodyLocation('body'),
                'query'     => new QueryLocation('query'),
                'header'    => new HeaderLocation('header'),
                'json'      => new JsonLocation('json'),
                'xml'       => new XmlLocation('xml'),
                'postField' => new PostFieldLocation('postField'),
                'postFile'  => new PostFileLocation('postFile')
			);
        }

        $this->requestLocations = $requestLocations + $defaultRequestLocations;
    }

    public function getEvents()
    {
        return array('prepare' => array('onPrepare'));
    }

    public function onPrepare(PrepareEvent $event)
    {
        // Don't modify the request if one is already present
        if ($event->getRequest()) {
            return;
        }

        /* @var GuzzleCommandInterface $command */
        $command = $event->getCommand();
        /* @var GuzzleClientInterface $client */
        $client = $event->getClient();
        $request = $this->createRequest($command, $client);
        $this->prepareRequest($command, $client, $request);
        $event->setRequest($request);
    }

    /**
     * Prepares a request for sending using location visitors
     *
     * @param GuzzleCommandInterface $command Command to prepare
     * @param GuzzleClientInterface  $client  Client that owns the command
     * @param RequestInterface       $request Request being created
     * @throws \RuntimeException If a location cannot be handled
     */
    protected function prepareRequest(
        GuzzleCommandInterface $command,
        GuzzleClientInterface $client,
        RequestInterface $request
    ) {
        $visitedLocations = array();
        $context = array('client' => $client, 'command' => $command);
        $operation = $command->getOperation();

        // Visit each actual parameter
        foreach ($operation->getParams() as $name => $param) {
            /* @var Parameter $param */
            $location = $param->getLocation();
            // Skip parameters that have not been set or are URI location
            if ($location == 'uri' || !$command->hasParam($name)) {
                continue;
            }
            if (!isset($this->requestLocations[$location])) {
                throw new \RuntimeException("No location registered for $location");
            }
            $visitedLocations[$location] = true;
            $this->requestLocations[$location]->visit(
                $command,
                $request,
                $param,
                $context
            );
        }

        // Ensure that the after() method is invoked for additionalParameters
        if ($additional = $operation->getAdditionalParameters()) {
            $visitedLocations[$additional->getLocation()] = true;
        }

        // Call the after() method for each visited location
        foreach (array_keys($visitedLocations) as $location) {
            $this->requestLocations[$location]->after(
                $command,
                $request,
                $operation,
                $context
            );
        }
    }

    /**
     * Create a request for the command and operation
     *
     * @param GuzzleCommandInterface $command Command being executed
     * @param GuzzleClientInterface  $client  Client used to execute the command
     *
     * @return RequestInterface
     * @throws \RuntimeException
     */
    protected function createRequest(
        GuzzleCommandInterface $command,
        GuzzleClientInterface $client
    ) {
        $operation = $command->getOperation();

        // If the command does not specify a template, then assume the base URL
        // of the client
        if (null === ($uri = $operation->getUri())) {
            return $client->getHttpClient()->createRequest(
                $operation->getHttpMethod(),
                $client->getDescription()->getBaseUrl(),
                $command['request_options'] ?: array()
            );
        }

        return $this->createCommandWithUri($command, $client);
    }

    /**
     * Create a request for an operation with a uri merged onto a base URI
     */
    private function createCommandWithUri(
        GuzzleCommandInterface $command,
        GuzzleClientInterface $client
    ) {
        // Get the path values and use the client config settings
        $variables = array();
        $operation = $command->getOperation();
        foreach ($operation->getParams() as $name => $arg) {
            /* @var Parameter $arg */
            if ($arg->getLocation() == 'uri') {
                if (isset($command[$name])) {
                    $variables[$name] = $arg->filter($command[$name]);
                    if (!is_array($variables[$name])) {
                        $variables[$name] = (string) $variables[$name];
                    }
                }
            }
        }

        return $client->getHttpClient()->createRequest(
            $operation->getHttpMethod(),
			array($client->getDescription()->getBaseUrl()->combine($operation->getUri()), $variables),
            $command['request_options'] ?: array()
        );
    }
}
