<?php
namespace GuzzleHttp\Command\Event;

use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Command\CommandTransaction;

/**
 * Event emitted when a command is being prepared.
 *
 * Event listeners can inject a {@see GuzzleHttp\Message\RequestInterface}
 * object onto the event to be used as the request sent over the wire.
 */
class PrepareEvent extends AbstractCommandEvent
{
    /**
     * @param CommandTransaction $trans Contextual transfer information
     */
    public function __construct(CommandTransaction $trans)
    {
        $this->trans = $trans;
    }

    /**
     * Set the HTTP request that will be sent for the command.
     *
     * @param RequestInterface $request Request to send for the command
     */
    public function setRequest(RequestInterface $request)
    {
        $this->trans->setRequest($request);
    }

    /**
     * Intercept the prepare event and inject a response.
     *
     * @param mixed $result Result to associate with the command
     */
    public function setResult($result)
    {
        $this->trans->setResult($result);
        $this->stopPropagation();
    }
}
