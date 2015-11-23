<?php

/*
 * Output buffer for large output to temporary file on disk (>2MB)
 *
 * @link
 * http://stackoverflow.com/questions/5446647/how-can-i-use-var-dump-output-buffering-without-memory-errors/
 */

/**
 * Iterate over lines of a stream resource.
 *
 * Note: ResourceIterator is a NoRewindIterator on non-seekable streams.
 */
class StreamIterator implements Iterator
{

    private $handle;

    private $current;

    private $index = 0;

    public function __construct ($handle)
    {
        $this->handle = $handle;
    }

    public function current ()
    {
        return $this->current;
    }

    public function next ()
    {
        $this->index ++;
        $this->current = fgets($this->handle);
    }

    public function key ()
    {
        return $this->index;
    }

    public function valid ()
    {
        return $this->current !== FALSE;
    }

    public function rewind ()
    {
        $this->index = 0;
        $meta = stream_get_meta_data($this->handle);
        $meta['seekable'] && fseek($this->handle, 0);
        $this->next();
    }
}

/**
 * Interface of a Store for Buffering
 *
 * (subset of SplFileObject/SplTempFileObject that was originally used)
 */
interface BufferStore
{

    public function fseek ($offset, $whence = SEEK_SET);

    public function ftruncate ($size);

    public function ftell ();

    public function fwrite ($string, $length = null);
}

/**
 * Stream (Stream I/O)
 */
class StreamObject implements BufferStore, IteratorAggregate
{

    private $handle;

    public function __construct ($handle)
    {
        $this->handle = $handle;
    }

    public function fseek ($offset, $whence = SEEK_SET)
    {
        fseek($this->handle, $offset, $whence);
    }

    public function ftruncate ($size)
    {
        ftruncate($this->handle, $size);
    }

    public function ftell ()
    {
        return ftell($this->handle);
    }

    public function fwrite ($string, $length = null)
    {
        if ($length === null) {
            return fwrite($this->handle, $string);
        }
        
        return fwrite($this->handle, $string, $length);
    }

    public function getHandle ()
    {
        return $this->handle;
    }

    public function getUri ()
    {
        $meta = stream_get_meta_data($this->handle);
        return $meta['uri'];
    }

    public function getIterator ()
    {
        return new StreamIterator($this->handle);
    }
}

/**
 * Uri (Stream I/O)
 */
class UriObject extends StreamObject
{

    public function __construct ($uri)
    {
        parent::__construct(fopen($uri, 'r+'));
    }
}

/**
 * Buffer to temporary location if larger than 2 MB
 */
class OutputBuffer
{

    /**
     *
     * @var int
     */
    private $chunkSize;

    /**
     *
     * @var bool
     */
    private $started;

    /**
     *
     * @var SplFileObject
     */
    private $store;

    /**
     *
     * @var int
     */
    private $bufferedCounter;

    /**
     *
     * @var bool Set Verbosity to true to output analysis data to stderr
     */
    private $verbose = true;

    public function __construct ($chunkSize = 1024)
    {
        $this->chunkSize = $chunkSize;
        $this->store = new UriObject('php://temp');
    }

    public function start ()
    {
        if ($this->started) {
            throw new BadMethodCallException('Buffering already started, can not start again.');
        }
        
        $this->started = true;
        $this->bufferedCounter = 0;
        $result = ob_start(array(
                $this,
                'bufferCallback'
        ), $this->chunkSize);
        $this->verbose && file_put_contents('php://stderr', 
                sprintf("Starting Buffering: %d; Level %d\n", $result, ob_get_level()));
        return $result;
    }

    public function flush ()
    {
        $this->started && ob_flush();
    }

    public function stop ()
    {
        if ($this->started) {
            ob_flush();
            $result = ob_end_flush();
            $this->started = false;
            $this->verbose && file_put_contents('php://stderr', 
                    sprintf("Buffering stopped: %d; Level %d\n", $result, ob_get_level()));
        }
    }

    private function bufferCallback ($chunk, $flags)
    {
        $chunkSize = strlen($chunk);
        
        if ($this->verbose) {
            $level = ob_get_level();
            $constants = array(
                    'PHP_OUTPUT_HANDLER_START',
                    'PHP_OUTPUT_HANDLER_WRITE',
                    'PHP_OUTPUT_HANDLER_FLUSH',
                    'PHP_OUTPUT_HANDLER_CLEAN',
                    'PHP_OUTPUT_HANDLER_FINAL'
            );
            
            $flagsText = '';
            foreach ($constants as $i => $constant) {
                if ($flags & ($value = constant($constant)) || $value == $flags) {
                    $flagsText .= (strlen($flagsText) ? ' | ' : '') . $constant . "[$value]";
                }
            }
            
            $total = $this->bufferedCounter;
            
            file_put_contents('php://stderr', 
                    sprintf("Buffer Callback: Chunk Size %s; Total %s; Flags %s (%s); Level %d\n", 
                            number_format($chunkSize), number_format($total + $chunkSize), $flags, $flagsText, $level));
        }
        
        if ($flags & PHP_OUTPUT_HANDLER_FINAL) {
            return TRUE;
        }
        
        if ($flags & PHP_OUTPUT_HANDLER_START) {
            $this->store->fseek(0, SEEK_END);
        }
        
        $chunkSize && $this->store->fwrite($chunk);
        $this->bufferedCounter += $chunkSize;
        
        if ($flags & PHP_OUTPUT_HANDLER_FLUSH) {
            // there is nothing to d
        }
        
        if ($flags & PHP_OUTPUT_HANDLER_CLEAN) {
            $this->store->ftruncate(0);
        }
        
        return "";
    }

    public function getSize ()
    {
        $this->store->fseek(0, SEEK_END);
        return $this->store->ftell();
    }

    public function getBufferFile ()
    {
        return $this->store;
    }

    public function getBuffer ()
    {
        $array = iterator_to_array($this->store);
        return implode('', $array);
    }

    public function __toString ()
    {
        return $this->getBuffer();
    }

    public function endClean ()
    {
        return ob_end_clean();
    }
}