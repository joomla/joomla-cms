<?php

namespace GuzzleHttp\Command\Guzzle;

use GuzzleHttp\Command\Command as DefaultCommand;
use GuzzleHttp\Event\EmitterInterface;
use GuzzleHttp\HasDataTrait;
use GuzzleHttp\Event\HasEmitterTrait;

/**
 * Default Guzzle command implementation.
 */
class Command extends DefaultCommand implements GuzzleCommandInterface
{
    /** @var Operation */
    private $operation;

    /**
     * @param Operation        $operation Operation associated with the command
     * @param array            $args      Arguments to pass to the command
     * @param EmitterInterface $emitter   Emitter used by the command
     */
    public function __construct(
        Operation $operation,
        array $args,
        EmitterInterface $emitter = null
    ) {
        $this->operation = $operation;
        parent::__construct('', $args, $emitter);
    }

    public function getName()
    {
        return $this->operation->getName();
    }

    public function getOperation()
    {
        return $this->operation;
    }
}
