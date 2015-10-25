<?php
namespace GuzzleHttp\Command;

use GuzzleHttp\Event\Emitter;
use GuzzleHttp\Event\EmitterInterface;
use GuzzleHttp\Command\Event\CommandEvents;

/**
 * Default command implementation.
 */
class Command implements CommandInterface
{
    //BB use HasDataTrait;
	/** @var array */
	protected $data = array();

	public function getIterator()
	{
		return new \ArrayIterator($this->data);
	}

	public function offsetGet($offset)
	{
		return isset($this->data[$offset]) ? $this->data[$offset] : null;
	}

	public function offsetSet($offset, $value)
	{
		$this->data[$offset] = $value;
	}

	public function offsetExists($offset)
	{
		return isset($this->data[$offset]);
	}

	public function offsetUnset($offset)
	{
		unset($this->data[$offset]);
	}

	public function toArray()
	{
		return $this->data;
	}

	public function count()
	{
		return count($this->data);
	}

	/**
	 * Get a value from the collection using a path syntax to retrieve nested
	 * data.
	 *
	 * @param string $path Path to traverse and retrieve a value from
	 *
	 * @return mixed|null
	 */
	public function getPath($path)
	{
		return \GuzzleHttp\get_path($this->data, $path);
	}

	/**
	 * Set a value into a nested array key. Keys will be created as needed to
	 * set the value.
	 *
	 * @param string $path  Path to set
	 * @param mixed  $value Value to set at the key
	 *
	 * @throws \RuntimeException when trying to setPath using a nested path
	 *     that travels through a scalar value
	 */
	public function setPath($path, $value)
	{
		\GuzzleHttp\set_path($this->data, $path, $value);
	}
	//BB end HasDataTrait;
    //BB use HasEmitterTrait;
	/** @var EmitterInterface */
	private $emitter;

	public function getEmitter()
	{
		if (!$this->emitter) {
			$this->emitter = new Emitter();
		}

		return $this->emitter;
	}
    //BB end HasEmitterTrait;

    /** @var string */
    private $name;

    /**
     * @param string           $name    Name of the command
     * @param array            $args    Arguments to pass to the command
     * @param EmitterInterface $emitter Emitter used by the command
     */
    public function __construct(
        $name,
        array $args = array(),
        EmitterInterface $emitter = null
    ) {
        $this->name = $name;
        $this->data = $args;
        $this->emitter = $emitter;
    }

    /**
     * Ensure that the emitter is cloned.
     */
    public function __clone()
    {
        if ($this->emitter) {
            $this->emitter = clone $this->emitter;
        }
    }

    /**
     * Creates and prepares an HTTP request for a command but does not execute
     * the command.
     *
     * When the request is created, it is no longer associated with the command
     * and the event system of the command should no longer be depended upon.
     *
     * @param ServiceClientInterface $client  Client used to create requests
     * @param CommandInterface       $command Command to convert into a request
     *
     * @return \GuzzleHttp\Message\RequestInterface
     */
    public static function createRequest(
        ServiceClientInterface $client,
        CommandInterface $command
    ) {
        $trans = new CommandTransaction($client, $command);
        CommandEvents::prepare($trans);

        return $trans->getRequest();
    }

    public function getName()
    {
        return $this->name;
    }

    public function hasParam($name)
    {
        return array_key_exists($name, $this->data);
    }
}
