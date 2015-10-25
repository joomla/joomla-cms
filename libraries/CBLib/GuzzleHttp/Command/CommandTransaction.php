<?php
namespace GuzzleHttp\Command;

use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Message\ResponseInterface;
use GuzzleHttp\Collection;

/**
 * Represents a command transaction as it is sent over the wire and inspected
 * by event listeners.
 */
class CommandTransaction
{
    /** @var ServiceClientInterface */
    private $client;

    /** @var RequestInterface */
    private $request;

    /** @var ResponseInterface */
    private $response;

    /** @var mixed */
    private $result;

    /** @var CommandInterface */
    private $command;

    /** @var \Exception */
    private $commandException;

    /** @var Collection */
    private $context;

    /**
     * @param ServiceClientInterface $client  Client that executes commands
     * @param CommandInterface       $command Command being executed
     * @param array                  $context Command context array of data
     */
    public function __construct(
        ServiceClientInterface $client,
        CommandInterface $command,
        array $context = array()
    ) {
        $this->client = $client;
        $this->command = $command;
        $this->context = new Collection($context);
    }

    /**
     * Get the service client used to execute the command
     *
     * @return ServiceClientInterface
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Get the command being executed
     *
     * @return CommandInterface
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * Get the HTTP request associated with the transaction (if available)
     *
     * @return RequestInterface|null
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Set the serialized HTTP request associated with the transaction
     *
     * @param RequestInterface $request Request to send
     */
    public function setRequest(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * Get the HTTP response associated with the transaction (if any)
     *
     * @return ResponseInterface|null
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Set the HTTP response associated with the transaction.
     *
     * @param ResponseInterface $response Response to set
     */
    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;
    }

    /**
     * Get the result of the transaction if one has been populated.
     *
     * @return mixed|null Returns null if no result has been populated
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Set the result of the command.
     *
     * Calling the function will automatically
     *
     * @param $result
     */
    public function setResult($result)
    {
        $this->result = $result;
    }

    /**
     * @return \Exception|null
     */
    public function getException()
    {
        return $this->commandException;
    }

    /**
     * Associate an exception with the transaction.
     *
     * @param \Exception $e Exception to associate or pass null to remove any
     *                      previously assigned exception.
     */
    public function setException(\Exception $e = null)
    {
        $this->commandException = $e;
    }

    /**
     * Get contextual information about the transaction.
     *
     * @return Collection
     */
    public function getContext()
    {
        return $this->context;
    }
}
