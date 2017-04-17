<?php
namespace GuzzleHttp\Subscriber\MessageIntegrity;

use GuzzleHttp\Stream\StreamInterface;

/**
 * Stream decorator that calculates and validates a rolling hash of the stream
 * as it is read.
 */
class ReadIntegrityStream extends HashingStream
{
    /** @var callable|null */
    private $mismatchCallback;

    /** @var bool */
    private $expected;

    /**
     * @param StreamInterface $stream     Stream that is validated.
     * @param HashInterface   $hash       Hash used to calculate the hash.
     * @param string          $expected   The expected hash result.
     * @param callable        $onMismatch Optional function to invoke when there
     *     is a mismatch between the calculated hash and the expected hash.
     *     The callback is called with the resulting hash and the expected hash.
     *     This callback can be used to throw specific exceptions.
     */
    public function __construct(
        StreamInterface $stream,
        HashInterface $hash,
        $expected,
        $onMismatch = null
    ) {
        $this->mismatchCallback = $onMismatch;
        $this->expected = $expected;

		$that = $this;

        parent::__construct($stream, $hash, function ($result) use ($that) {
            if ($that->expected !== $result) {
                $that->mismatch($result);
            }
        });
    }

    private function mismatch($result)
    {
        if ($this->mismatchCallback) {
            call_user_func($this->mismatchCallback, $result, $this->expected);
        }

        throw new \UnexpectedValueException(
            sprintf('Message integrity check failure. Expected %s '
                . 'but got %s', $this->expected, $result)
        );
    }
}
