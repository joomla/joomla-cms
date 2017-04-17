<?php
namespace GuzzleHttp\Command\Event;

use GuzzleHttp\Command\CommandTransaction;
use GuzzleHttp\Message\ResponseInterface;

/**
 * Event emitted when an error occurs while transferring a request for a
 * command.
 *
 * Event listeners can inject a result onto the event to intercept the
 * exception with a successful result.
 */
class CommandErrorEvent extends AbstractCommandEvent
{
    /**
     * @param CommandTransaction $trans Command transfer context
     */
    public function __construct(CommandTransaction $trans)
    {
        $this->trans = $trans;
    }

    /**
     * Returns the exception that was encountered.
     *
     * @return \Exception
     */
    public function getException()
    {
        return $this->trans->getException();
    }

    /**
     * Retrieves the HTTP response that was received for the command
     * (if available).
     *
     * @return ResponseInterface|null
     */
    public function getResponse()
    {
        return $this->trans->getResponse();
    }

    /**
     * Intercept the error and inject a result
     *
     * @param mixed $result Result to associate with the command
     */
    public function setResult($result)
    {
        $this->trans->setResult($result);
        $this->stopPropagation();
    }
}
