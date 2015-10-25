<?php

namespace GuzzleHttp\Adapter;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Event\HasEmitterInterface;
use GuzzleHttp\Message\RequestInterface;

/**
 * Converts a sequence of request objects into a transaction.
 * @internal
 */
class TransactionIterator implements \Iterator
{
    //BB use ListenerAttacherTrait;

	/**
	 * Attaches event listeners and properly sets their priorities and whether
	 * or not they are are only executed once.
	 *
	 * @param HasEmitterInterface $object    Object that has the event emitter.
	 * @param array               $listeners Array of hashes representing event
	 *                                       event listeners. Each item contains
	 *                                       "name", "fn", "priority", & "once".
	 */
	private function attachListeners(HasEmitterInterface $object, array $listeners)
	{
		$emitter = $object->getEmitter();
		foreach ($listeners as $el) {
			if ($el['once']) {
				$emitter->once($el['name'], $el['fn'], $el['priority']);
			} else {
				$emitter->on($el['name'], $el['fn'], $el['priority']);
			}
		}
	}

	/**
	 * Extracts the allowed events from the provided array, and ignores anything
	 * else in the array. The event listener must be specified as a callable or
	 * as an array of event listener data ("name", "fn", "priority", "once").
	 *
	 * @param array $source Array containing callables or hashes of data to be
	 *                      prepared as event listeners.
	 * @param array $events Names of events to look for in the provided $source
	 *                      array. Other keys are ignored.
	 * @return array
	 */
	private function prepareListeners(array $source, array $events)
	{
		$listeners = array();
		foreach ($events as $name) {
			if (isset($source[$name])) {
				$this->buildListener($name, $source[$name], $listeners);
			}
		}

		return $listeners;
	}

	/**
	 * Creates a complete event listener definition from the provided array of
	 * listener data. Also works recursively if more than one listeners are
	 * contained in the provided array.
	 *
	 * @param string         $name      Name of the event the listener is for.
	 * @param array|callable $data      Event listener data to prepare.
	 * @param array          $listeners Array of listeners, passed by reference.
	 *
	 * @throws \InvalidArgumentException if the event data is malformed.
	 */
	private function buildListener($name, $data, &$listeners)
	{
		static $defaults = array('priority' => 0, 'once' => false);

		// If a callable is provided, normalize it to the array format.
		if (is_callable($data)) {
			$data = array('fn' => $data);
		}

		// Prepare the listener and add it to the array, recursively.
		if (isset($data['fn'])) {
			$data['name'] = $name;
			$listeners[] = $data + $defaults;
		} elseif (is_array($data)) {
			foreach ($data as $listenerData) {
				$this->buildListener($name, $listenerData, $listeners);
			}
		} else {
			throw new \InvalidArgumentException('Each event listener must be a '
				. 'callable or an associative array containing a "fn" key.');
		}
	}

	//BB end of use ListenerAttacherTrait

    /** @var \Iterator */
    private $source;

    /** @var ClientInterface */
    private $client;

    /** @var array Listeners to attach to each request */
    private $eventListeners = array();

    public function __construct(
        $source,
        ClientInterface $client,
        array $options
    ) {
        $this->client = $client;
        $this->eventListeners = $this->prepareListeners(
            $options,
            array('before', 'complete', 'error')
        );
        if ($source instanceof \Iterator) {
            $this->source = $source;
        } elseif (is_array($source)) {
            $this->source = new \ArrayIterator($source);
        } else {
            throw new \InvalidArgumentException('Expected an Iterator or array');
        }
    }

    public function current()
    {
        $request = $this->source->current();
        if (!$request instanceof RequestInterface) {
            throw new \RuntimeException('All must implement RequestInterface');
        }

        $this->attachListeners($request, $this->eventListeners);

        return new Transaction($this->client, $request);
    }

    public function next()
    {
        $this->source->next();
    }

    public function key()
    {
        return $this->source->key();
    }

    public function valid()
    {
        return $this->source->valid();
    }

    public function rewind()
    {
        if (!($this->source instanceof \Generator)) {
            $this->source->rewind();
        }
    }
}
