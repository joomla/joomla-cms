<?php
namespace GuzzleHttp\Command\Exception;

use GuzzleHttp\Command\CommandTransaction;

/**
 * Exception encountered while transferring a command.
 */
class CommandException extends \RuntimeException
{
    /** @var CommandTransaction */
    private $trans;

    /**
     * @param string             $message  Exception message
     * @param CommandTransaction $trans    Contextual transfer information
     * @param \Exception         $previous Previous exception (if any)
     */
    public function __construct(
        $message,
        CommandTransaction $trans,
        \Exception $previous = null
    ) {
        $this->trans = $trans;
        parent::__construct($message, 0, $previous);
    }

    /**
     * Gets the transaction associated with the exception
     *
     * @return CommandTransaction
     */
    public function getTransaction()
    {
        return $this->trans;
    }
}
