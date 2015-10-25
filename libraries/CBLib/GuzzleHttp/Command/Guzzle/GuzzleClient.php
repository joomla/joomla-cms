<?php

namespace GuzzleHttp\Command\Guzzle;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Command\AbstractClient;
use GuzzleHttp\Event\HasEmitterTrait;
use GuzzleHttp\Command\Guzzle\Subscriber\PrepareRequest;
use GuzzleHttp\Command\Guzzle\Subscriber\ProcessResponse;
use GuzzleHttp\Command\Guzzle\Subscriber\ValidateInput;

/**
 * Default Guzzle web service client implementation.
 */
class GuzzleClient extends AbstractClient implements GuzzleClientInterface
{
    /** @var Description Guzzle service description */
    private $description;

    /** @var callable Factory used for creating commands */
    private $commandFactory;

    /**
     * The client constructor accepts an associative array of configuration
     * options:
     *
     * - defaults: Associative array of default command parameters to add to
     *   each command created by the client.
     * - validate: Specify if command input is validated (defaults to true).
     *   Changing this setting after the client has been created will have no
     *   effect.
     * - process: Specify if HTTP responses are parsed (defaults to true).
     *   Changing this setting after the client has been created will have no
     *   effect.
     * - request_locations: Associative array of location types mapping to
     *   RequestLocationInterface objects.
     * - response_locations: Associative array of location types mapping to
     *   ResponseLocationInterface objects.
     *
     * @param ClientInterface   $client      Client used to send HTTP requests
     * @param Description       $description Guzzle service description
     * @param array             $config      Configuration options
     */
    public function __construct(
        ClientInterface $client,
        Description $description,
        array $config = array()
    ) {
        parent::__construct($client, $config);
        $this->description = $description;
        $this->processConfig($config);
    }

    public function getCommand($name, array $args = array())
    {
        $factory = $this->commandFactory;
        // Merge in default command options
        $args += $this->getConfig('defaults');

        if (!($command = call_user_func_array($factory, array($name, $args, $this)))) {
            throw new \InvalidArgumentException("No operation found named $name");
        }

        return $command;
    }

    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Creates a callable function used to create command objects from a
     * service description.
     *
     * @param Description $description Service description
     *
     * @return callable Returns a command factory
     */
    public static function defaultCommandFactory(Description $description)
    {
        return function (
            $name,
            array $args = array(),
            GuzzleClientInterface $client
        ) use ($description) {

            $operation = null;

            if ($description->hasOperation($name)) {
                $operation = $description->getOperation($name);
            } else {
                $name = ucfirst($name);
                if ($description->hasOperation($name)) {
                    $operation = $description->getOperation($name);
                }
            }

            if (!$operation) {
                return null;
            }

            return new Command($operation, $args, clone $client->getEmitter());
        };
    }

    /**
     * Prepares the client based on the configuration settings of the client.
     *
     * @param array $config Constructor config as an array
     */
    protected function processConfig(array $config)
    {
        // Use the passed in command factory or a custom factory if provided
        $this->commandFactory = isset($config['command_factory'])
            ? $config['command_factory']
            : self::defaultCommandFactory($this->description);

        // Add event listeners based on the configuration option
        $emitter = $this->getEmitter();

        if (!isset($config['validate']) ||
            $config['validate'] === true
        ) {
            $emitter->attach(new ValidateInput());
        }

        $emitter->attach(new PrepareRequest(
            isset($config['request_locations'])
                ? $config['request_locations']
                : array()
        ));

        if (!isset($config['process']) ||
            $config['process'] === true
        ) {
            $emitter->attach(new ProcessResponse(
                isset($config['response_locations'])
                    ? $config['response_locations']
                    : array()
            ));
        }
    }
}
