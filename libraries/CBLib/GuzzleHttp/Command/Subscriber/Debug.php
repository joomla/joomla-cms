<?php
namespace GuzzleHttp\Command\Subscriber;

use GuzzleHttp\Command\CommandInterface;
use GuzzleHttp\Command\Event\CommandErrorEvent;
use GuzzleHttp\Command\Event\PrepareEvent;
use GuzzleHttp\Command\Event\ProcessEvent;
use GuzzleHttp\Command\ServiceClientInterface;
use GuzzleHttp\Event\EventInterface;
use GuzzleHttp\Event\RequestEvents;
use GuzzleHttp\Event\SubscriberInterface;
use GuzzleHttp\Message\AbstractMessage;
use GuzzleHttp\Message\MessageInterface;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Stream\StreamInterface;
use GuzzleHttp\ToArrayInterface;

/**
 * Provides debug information about operations, including HTTP wire traces.
 *
 * This subscriber is useful for debugging the command and request event
 * system and seeing what data was sent and received over the wire.
 */
class Debug implements SubscriberInterface
{
    /** @var \GuzzleHttp\Stream\StreamInterface */
    private $output;
    private $http;
    private $maxStreamSize;
    private $states;
    private $adapterDebug;

    /**
     * The constructor accepts a hash of debug options.
     *
     * - output: Where debug data is written (fopen or StreamInterface)
     * - http: Set to false to not display debug HTTP event data
     * - max_stream_size: Set to an integer to override the default maximum
     *   stream size of 10240 bytes (or 10 KB). This value determines whether
     *   or not stream data is written to the output stream based on the size
     *   of the stream.
     * - adapter_debug: Set to false to disable turning on the debug adapter
     *   setting.
     *
     * @param array $options Hash of debug options
     */
    public function __construct(array $options = array())
    {
        $this->states = new \SplObjectStorage();
        $this->output = isset($options['output'])
            ? $options['output']
            : fopen('php://output', 'w');
        $this->output = Stream::factory($this->output);
        $this->http = isset($options['http']) ? $options['http'] : true;
        $this->adapterDebug = isset($options['adapter_debug'])
            ? $options['adapter_debug']
            : true;
        $this->maxStreamSize = isset($options['max_stream_size'])
            ? $options['max_stream_size']
            : 10240;
    }

    public function getEvents()
    {
        return array(
            'prepare' => array(
				array('beforePrepare', 'first'),
                array('afterPrepare', 'last')
			),
            'process' => array(
				array('beforeProcess', 'first'),
                array('afterProcess', 'last')
			),
            'error' => array(
				array('beforeError', 'first'),
                array('afterError', 'last')
			)
		);
    }

    private function write($text)
    {
        $this->output->write(date('c') . ': ' . $text . PHP_EOL);
    }

    private function hashCommand(
        ServiceClientInterface $client,
        CommandInterface $command,
        EventInterface $event
    ) {
        return get_class($client) . '::' . $command->getName()
            . ' (' . spl_object_hash($event) . ')';
    }

	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function startEvent(
        $name,
        $hash,
        $command,
        $request = null,
        $response = null,
        $result = null,
        $error = null
    ) {
        $last = isset($this->states[$command])
            ? $this->states[$command]
            : null;
        $this->states[$command] = $this->eventState(func_get_args());
        $this->write(sprintf(
            "Starting the %s event for %s: %s",
            $name,
            $hash,
            $this->diffStates($last, $this->states[$command])
        ));
    }

	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function endEvent(
        $name,
        $hash,
        $command,
        $request = null,
        $response = null,
        $result = null,
        $error = null
    ) {
        if (!isset($this->states[$command])) {
            throw new \RuntimeException('Matching start event not found');
        }

        $last = $this->states[$command];
        $this->states[$command] = $this->eventState(func_get_args());
        $this->write(sprintf(
            "Done with the %s event for %s (took %f seconds): %s",
            $name,
            $hash,
            microtime(true) - $last['time'],
            $this->diffStates($last, $this->states[$command])
        ));
    }

    public function beforePrepare(PrepareEvent $e)
    {
        $this->proxyEvent('command:prepare', $e);
    }

    public function afterPrepare(PrepareEvent $e)
    {
        $this->proxyEvent('command:prepare', $e);
        $request = $e->getRequest();

        if (!$this->http || !$request) {
            return;
        }

        if ($this->adapterDebug) {
            $request->getConfig()->set('debug', true);
        }

		$that = $this;

        // Attach listeners to request events
        $before = function ($before) use ($e, $that) {
			$that->proxyReqEvent('startEvent', $e, $before);
        };

        $after = function ($after) use ($e, $that) {
			$that->proxyReqEvent('endEvent', $e, $after);
        };

        foreach (array('before', 'complete', 'error') as $event) {
            $request->getEmitter()->on($event, $before, RequestEvents::EARLY);
            $request->getEmitter()->on($event, $after, RequestEvents::LATE);
        }
    }

    public function beforeProcess(ProcessEvent $e)
    {
        $this->proxyEvent('command:process', $e);
    }

    public function afterProcess(ProcessEvent $e)
    {
        $this->proxyEvent('command:process', $e);
    }

    public function beforeError(CommandErrorEvent $e)
    {
        $this->proxyEvent('command:error', $e);
    }

    public function afterError(CommandErrorEvent $e)
    {
        $this->proxyEvent('command:error', $e);
    }

    /**
     * Proxies the appropriate call to start or end event
     *
     * @param string         $name Name of the event
     * @param EventInterface $e    Event to proxy
     */
    public function proxyEvent($name, EventInterface $e)
    {
		$backtrace = debug_backtrace();
        $meth = substr($backtrace[1]['function'], 0, 6) == 'before'
            ? 'startEvent' : 'endEvent';

        call_user_func_array(
            array($this, $meth),
            array(
                $name,
                $this->hashCommand($e->getClient(), $e->getCommand(), $e),
                $e->getCommand(),
                $e->getRequest(),
                method_exists($e, 'getResponse') ? $e->getResponse() : null,
                method_exists($e, 'getResult') ? $e->getResult() : null,
                method_exists($e, 'getException') ? $e->getException() : null
			)
        );
    }

    /**
     * Proxies a call to start or end event based on a request event.
     *
     * @param string         $meth startEvent or endEvent
     * @param EventInterface $cev  Command event
     * @param EventInterface $rev  Request event
     */
    private function proxyReqEvent(
        $meth,
        EventInterface $cev,
        EventInterface $rev
    ) {
		/** @var \GuzzleHttp\Command\Event\AbstractCommandEvent $cev */
		/** @noinspection PhpUndefinedMethodInspection */
		call_user_func(
			array($this, $meth),
            $this->getEventName($rev),
            $this->hashCommand($cev->getClient(), $cev->getCommand(), $rev),
            $cev->getCommand(),
            $rev->getRequest(),
            method_exists($rev, 'getResponse') ? $rev->getResponse() : null,
            method_exists($cev, 'getResult') ? $cev->getResult() : null,
            method_exists($cev, 'getError') ? $cev->getError() : null
        );
    }

    /**
     * Gets the state of an event as a hash.
     *
     * @param array $args Ordered array of arguments passed to an event fn
     *
     * @return array
     */
    private function eventState($args)
    {
        return array(
            'time'     => microtime(true),
            'command'  => $this->toArrayState($args[2]),
            'request'  => $this->messageState($args[3]),
            'response' => $this->messageState($args[4]),
            'result'   => $this->resultState($args[5]),
            'error'    => $this->errorState($args[6])
		);
    }

    /**
     * Calculates the event name of a request event.
     *
     * @param EventInterface $event
     *
     * @return string
     */
    private function getEventName(EventInterface $event)
    {
        $cl = get_class($event);
        $name = strtolower(substr($cl, strrpos($cl, '\\') + 1));
        return 'request:' . substr($name, 0, -5);
    }

    /**
     * Gets the state of a stream as a hash or null.
     *
     * If the size of the stream is below the max threshold and the stream is
     * seekable, then the contents of the stream is included in the hash.
     *
     * @param StreamInterface $stream
     *
     * @return array|null
     */
    private function streamState(StreamInterface $stream = null)
    {
        if (!$stream) {
            return null;
        }

        $result = array(
            'class' => get_class($stream),
            'size'  => $stream->getSize(),
            'tell'  => $stream->tell()
		);

        if ($stream->getSize() < $this->maxStreamSize &&
            $stream->isSeekable()
        ) {
            $pos = $stream->tell();
            $result['contents'] = (string) $stream;
            $stream->seek($pos);
        }

        return $result;
    }

    /**
     * Gets the state of a message as a hash.
     *
     * @param MessageInterface $msg
     *
     * @return array|null
     */
    private function messageState(MessageInterface $msg = null)
    {
        return !$msg
            ? null : array(
				'start-line' => AbstractMessage::getStartLine($msg),
				'headers'    => AbstractMessage::getHeadersAsString($msg),
				'body'       => $this->streamState($msg->getBody())
			);
    }

    /**
     * Converts a ToArrayInterface object into a hash with streams mapped as
     * needed to hash states.
     *
     * @param ToArrayInterface $data
     *
     * @return array
     */
    private function toArrayState(ToArrayInterface $data)
    {
        $params = $data->toArray();
		$that = $this;
        array_walk_recursive($params, function (&$value) use ($that) {
            if ($value instanceof StreamInterface) {
                $value = $that->streamState($value);
            }
        });

        $result = array(
            'id'    => spl_object_hash($data),
            'class' => get_class($data),
            'keys'  => $params
		);

        if ($data instanceof CommandInterface) {
            $result['name'] = $data->getName();
        }

        return $result;
    }

    /**
     * Returns the most appropriate JSON encodable value for a result state.
     *
     * @param null $result
     *
     * @return array|null|string
     */
    private function resultState($result = null)
    {
        if ($result === null) {
            return null;
        } elseif ($result instanceof ToArrayInterface) {
            return $this->toArrayState($result);
        } elseif ($result instanceof StreamInterface) {
            return $this->streamState($result);
        } else {
            return json_encode($result);
        }
    }

    /**
     * Returns the state of an exception as a hash or null.
     *
     * @param \Exception $e
     *
     * @return array|null
     */
    private function errorState(\Exception $e = null)
    {
        return !$e ? null : array(
            'class'   => get_class($e),
            'message' => $e->getMessage(),
            'line'    => $e->getLine(),
            'file'    => $e->getFile(),
            'code'    => $e->getCode()
		);
    }

    /**
     * Provides a diff between two states as a string
     */
    private function diffStates($a, $b)
    {
        if (!$a) {
            return 'State was changed to ' . print_r($b, true);
        }

        unset($a['time'], $b['time']);
        $diff = $this->diff($a, $b);

        return $diff ? print_r($diff, true) : 'No change';
    }

    private function diff($a, $b) {

        $result = array();

        // Check differences in previous keys
        foreach ($a as $k => $v) {
            if (!array_key_exists($k, $b)) {
                $result[$k . ' was removed, previously'] = $v;
            } elseif (is_array($v)) {
                if ($diff = $this->diff($v, $b[$k])) {
                    $result[$k . ' has a change'] = $diff;
                }
            } elseif ($v !== $b[$k]) {
                $result[$k . ' was changed'] = $b[$k];
            }
        }

        return $result;
    }
}
