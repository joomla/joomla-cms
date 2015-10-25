<?php
namespace GuzzleHttp\Command;

use GuzzleHttp\Event\HasEmitterInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;

/**
 * Web service client interface.
 *
 * Any event listener or subscriber added to the client is added to each
 * command created by the client when the command is created.
 */
interface ServiceClientInterface extends HasEmitterInterface
{
    /**
     * Creates and executes a command for an operation by name.
     *
     * @param string $name      Name of the command to execute.
     * @param array  $arguments Arguments to pass to the command.
     * @throws \Exception
     */
    public function __call($name, array $arguments);

    /**
     * Create a command for an operation name.
     *
     * @param string $name Name of the operation to use in the command
     * @param array  $args Arguments to pass to the command
     *
     * @return CommandInterface
     * @throws \InvalidArgumentException if no command can be found by name
     */
    public function getCommand($name, array $args = array());

    /**
     * Execute a single command.
     *
     * @param CommandInterface $command Command to execute
     *
     * @return mixed Returns the result of the executed command
     * @throws \Exception
     */
    public function execute(CommandInterface $command);

    /**
     * Execute multiple commands in parallel.
     *
     * @param array|\Iterator $commands Array or iterator that contains
     *     CommandInterface objects to execute.
     * @param array $options Associative array of options.
     *     - parallel: (int) Max number of commands to send in parallel
     *     - prepare: (callable) Receives a CommandPrepareEvent Concrete
     *       implementations MAY choose to implement this setting.
     *     - process: (callable) Receives a CommandProcessEvent. Concrete
     *       implementations MAY choose to implement this setting.
     *     - error: (callable) Receives a CommandErrorEvent. Concrete
     *       implementations MAY choose to implement this setting.
     */
    public function executeAll($commands, array $options = array());

    /**
     * Sends multiple commands in parallel and returns a hash map of commands
     * mapped to their corresponding result or exception.
     *
     * Note: This method keeps every command and command and result in memory,
     * and as such is NOT recommended when sending a large number or an
     * indeterminable number of commands in parallel. Instead, you should use
     * executeAll() and utilize the event system to work with results.
     *
     * @param array|\Iterator $commands Commands to send in parallel
     * @param array           $options  Passes through the options available
     *                                  in {@see ClientInterface::executeAll()}
     *
     * @return \SplObjectStorage Commands are the key and each value is the
     *     result of the command on success or an instance of
     *     {@see GuzzleHttp\Command\Exception\CommandException} if a failure
     *     occurs while executing the command.
     * @throws \InvalidArgumentException if the event format is incorrect.
     */
    public function batch($commands, array $options = array());

    /**
     * Get the HTTP client used to send requests for the web service client
     *
     * @return ClientInterface
     */
    public function getHttpClient();

    /**
     * Get a client configuration value.
     *
     * @param string|int|null $keyOrPath The Path to a particular configuration
     *     value. The syntax uses a path notation that allows you to retrieve
     *     nested array values without throwing warnings.
     *
     * @return mixed
     */
    public function getConfig($keyOrPath = null);

    /**
     * Set a client configuration value at the specified configuration path.
     *
     * @param string|int $keyOrPath Path at which to change a configuration
     *     value. This path syntax follows the same path syntax specified in
     *     {@see getConfig}.
     *
     * @param mixed $value Value to set
     */
    public function setConfig($keyOrPath, $value);

    /**
     * Create an exception for a command based on a request exception.
     *
     * This method is invoked when an exception occurs while transferring an
     * HTTP request for a specific command. This method MUST return an instance
     * of \Exception that will be thrown for the given command.
     *
     * @param CommandTransaction $transaction Command transaction context
     * @param RequestException   $previous    Request exception encountered
     *
     * @return \Exception
     */
    public function createCommandException(
        CommandTransaction $transaction,
        RequestException $previous
    );
}
