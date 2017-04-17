<?php

namespace GuzzleHttp\Command\Guzzle\Subscriber;

use GuzzleHttp\Event\SubscriberInterface;
use GuzzleHttp\Message\ResponseInterface;
use GuzzleHttp\Command\Guzzle\Parameter;
use GuzzleHttp\Command\Guzzle\GuzzleCommandInterface;
use GuzzleHttp\Command\Guzzle\ResponseLocation\JsonLocation;
use GuzzleHttp\Command\Event\ProcessEvent;
use GuzzleHttp\Command\Guzzle\ResponseLocation\ResponseLocationInterface;
use GuzzleHttp\Command\Guzzle\ResponseLocation\BodyLocation;
use GuzzleHttp\Command\Guzzle\ResponseLocation\StatusCodeLocation;
use GuzzleHttp\Command\Guzzle\ResponseLocation\ReasonPhraseLocation;
use GuzzleHttp\Command\Guzzle\ResponseLocation\HeaderLocation;
use GuzzleHttp\Command\Guzzle\ResponseLocation\XmlLocation;
use GuzzleHttp\Command\Model;

/**
 * Subscriber used to create response models based on an HTTP response and
 * a service description.
 *
 * Response location visitors are registered with this subscriber to handle
 * locations (e.g., 'xml', 'json', 'header'). All of the locations of a response
 * model that will be visited first have their ``before`` method triggered.
 * After the before method is called on every visitor that will be walked, each
 * visitor is triggered using the ``visit()`` method. After all of the visitors
 * are visited, the ``after()`` method is called on each visitor. This is the
 * place in which you should handle things like additionalProperties with
 * custom locations (i.e., this is how it is handled in the JSON visitor).
 */
class ProcessResponse implements SubscriberInterface
{
    /** @var ResponseLocationInterface[] */
    private $responseLocations;

    /**
     * @param ResponseLocationInterface[] $responseLocations Extra response locations
     */
    public function __construct(array $responseLocations = array())
    {
        static $defaultResponseLocations;
        if (!$defaultResponseLocations) {
            $defaultResponseLocations = array(
                'body'         => new BodyLocation('body'),
                'header'       => new HeaderLocation('header'),
                'reasonPhrase' => new ReasonPhraseLocation('reasonPhrase'),
                'statusCode'   => new StatusCodeLocation('statusCode'),
                'xml'          => new XmlLocation('xml'),
                'json'         => new JsonLocation('json')
			);
        }

        $this->responseLocations = $responseLocations + $defaultResponseLocations;
    }

    public function getEvents()
    {
        return array('process' => array('onProcess'));
    }

    public function onProcess(ProcessEvent $event)
    {
        $command = $event->getCommand();
        if (!($command instanceof GuzzleCommandInterface)) {
            throw new \RuntimeException('The command sent to ' . __METHOD__
                . ' is not a GuzzleHttp\\Command\\Guzzle\\GuzzleCommandInterface');
        }

        // Do not overwrite a previous result
        if ($event->getResult()) {
            return;
        }

        $operation = $command->getOperation();

        // Add a default Model as the result if no matching schema was found.
        if (!($modelName = $operation->getResponseModel())) {
            $event->setResult(new Model(array()));
            return;
        }

        $model = $operation->getServiceDescription()->getModel($modelName);
        if (!$model) {
            throw new \RuntimeException("Unknown model: {$modelName}");
        }

        $event->setResult(new Model($this->visit($model, $event)));
    }

    protected function visit(Parameter $model, ProcessEvent $event)
    {
        $result = array();
        $context = array('client' => $event->getClient(), 'visitors' => array());
        $command = $event->getCommand();
        $response = $event->getResponse();

        if ($model->getType() == 'object') {
            $this->visitOuterObject($model, $result, $command, $response, $context);
        } elseif ($model->getType() == 'array') {
            $this->visitOuterArray($model, $result, $command, $response, $context);
        } else {
            throw new \InvalidArgumentException('Invalid response model: ' . $model->getType());
        }

        // Call the after() method of each found visitor
        foreach ($context['visitors'] as $visitor) {
			/** @noinspection PhpUndefinedMethodInspection */
			$visitor->after($command, $response, $model, $result, $context);
        }

        return $result;
    }

    private function triggerBeforeVisitor(
        $location,
        Parameter $model,
        array &$result,
        GuzzleCommandInterface $command,
        ResponseInterface $response,
        array &$context
    ) {
        if (!isset($this->responseLocations[$location])) {
            throw new \RuntimeException("Unknown location: $location");
        }

        $context['visitors'][$location] = $this->responseLocations[$location];

        $this->responseLocations[$location]->before(
            $command,
            $response,
            $model,
            $result,
            $context
        );
    }

    private function visitOuterObject(
        Parameter $model,
        array &$result,
        GuzzleCommandInterface $command,
        ResponseInterface $response,
        array &$context
    ) {
        // If top-level additionalProperties is a schema, then visit it
        $additional = $model->getAdditionalProperties();
        if ($additional instanceof Parameter) {
            $this->triggerBeforeVisitor($additional->getLocation(), $model,
                $result, $command, $response, $context);
        }

        // Use 'location' from all individual defined properties
        $properties = $model->getProperties();
        foreach ($properties as $schema) {
            if ($location = $schema->getLocation()) {
                // Trigger the before method on each unique visitor location
                if (!isset($context['visitors'][$location])) {
                    $this->triggerBeforeVisitor($location, $model, $result,
                        $command, $response, $context);
                }
            }
        }

        // Actually visit each response element
        foreach ($properties as $schema) {
            if ($location = $schema->getLocation()) {
                $this->responseLocations[$location]->visit($command, $response,
                    $schema, $result, $context);
            }
        }
    }

    private function visitOuterArray(
        Parameter $model,
        array &$result,
        GuzzleCommandInterface $command,
        ResponseInterface $response,
        array &$context
    ) {
        // Use 'location' defined on the top of the model
        if (!($location = $model->getLocation())) {
            return;
        }

        if (!isset($foundVisitors[$location])) {
            $this->triggerBeforeVisitor($location, $model, $result,
                $command, $response, $context);
        }

        // Visit each item in the response
        $this->responseLocations[$location]->visit($command, $response,
            $model, $result, $context);
    }
}
