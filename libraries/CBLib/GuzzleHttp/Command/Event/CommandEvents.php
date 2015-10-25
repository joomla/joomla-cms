<?php
namespace GuzzleHttp\Command\Event;

use GuzzleHttp\Command\CanceledResponse;
use GuzzleHttp\Command\CommandTransaction;
use GuzzleHttp\Event\ErrorEvent;
use GuzzleHttp\Event\RequestEvents;

/**
 * Wraps HTTP lifecycle events with command lifecycle events.
 */
class CommandEvents
{
    /**
     * Handles the workflow of a command before it is sent.
     *
     * This includes preparing a request for the command, hooking the command
     * event system up to the request's event system, and returning the
     * prepared request.
     *
     * @param CommandTransaction $trans Command execution context
     * @throws \RuntimeException
     */
    public static function prepare(CommandTransaction $trans)
    {
        try {
            $ev = new PrepareEvent($trans);
            $trans->getCommand()->getEmitter()->emit('prepare', $ev);
        } catch (\Exception $e) {
            self::emitError($trans, $e);
            return;
        }

        if ($ev->isPropagationStopped()) {
            // Event was intercepted with a result, so emit process
            self::process($trans);
        } elseif ($trans->getRequest()) {
            self::injectErrorHandler($trans);
        } else {
            throw new \RuntimeException('No request was prepared for the'
                . ' command and no result was added to intercept the event.'
                . ' One of the listeners must set a request in the prepare'
                . ' event.');
        }
    }

    /**
     * Handles the processing workflow of a command after it has been sent.
     *
     * @param CommandTransaction $trans Command execution context
     * @throws \Exception
     */
    public static function process(CommandTransaction $trans)
    {
        // Throw if an exception occurred while sending the request
        if ($e = $trans->getException()) {
            $trans->setException(null);
            throw $e;
        }

        try {
            $trans->getCommand()->getEmitter()->emit(
                'process',
                new ProcessEvent($trans)
            );
        } catch (\Exception $e) {
            self::emitError($trans, $e);
        }
    }

    /**
     * Emits an error event for the command.
     *
     * @param CommandTransaction $trans Command execution context
     * @param \Exception         $e     Exception encountered
     * @throws \Exception
     */
    public static function emitError(
        CommandTransaction $trans,
        \Exception $e
    ) {
        $trans->setException($e);

        // If this exception has already emitted, then throw it now.
        if (isset($e->_emittedError)) {
            throw $e;
        }

        $e->_emittedError = true;
        $event = new CommandErrorEvent($trans);
        $trans->getCommand()->getEmitter()->emit('error', $event);

        if (!$event->isPropagationStopped()) {
            throw $e;
        }
    }

    /**
     * Wrap HTTP level errors with command level errors.
     */
    private static function injectErrorHandler(CommandTransaction $trans)
    {
        $trans->getRequest()->getEmitter()->on(
            'error',
            function (ErrorEvent $re) use ($trans) {
                $re->stopPropagation();
                $trans->setException(CommandEvents::exceptionFromError($trans, $re));
                $cev = new CommandErrorEvent($trans);
                $trans->getCommand()->getEmitter()->emit('error', $cev);

                if ($cev->isPropagationStopped()) {
                    $trans->setException(null);
                }
            },
            RequestEvents::LATE
        );
    }

    /**
     * Create a CommandException from a request error event.
     * @param CommandTransaction $trans
     * @param ErrorEvent         $re
     * @return \Exception
     */
    private static function exceptionFromError(
        CommandTransaction $trans,
        ErrorEvent $re
    ) {
        if ($response = $re->getResponse()) {
            $trans->setResponse($response);
        } else {
            self::stopRequestError($re);
        }

        return $trans->getClient()->createCommandException(
            $trans,
            $re->getException()
        );
    }

    /**
     * Prevent a request from sending and intercept it's complete event.
     *
     * This method is required when a request fails before sending to prevent
     * adapters from still transferring the request over the wire.
     */
    private static function stopRequestError(ErrorEvent $e)
    {
        $fn = function ($ev) {
			/** @noinspection PhpUndefinedMethodInspection */
			$ev->stopPropagation();
		};
        $e->getRequest()->getEmitter()->once('complete', $fn, 'first');
        $e->intercept(new CanceledResponse());
    }
}
