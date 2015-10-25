<?php
/*
 * This file is deprecated and only included so that backwards compatibility
 * is maintained for downstream packages.
 *
 * Use the functions available in the Utils class instead of the the below
 * namespaced functions.
 */
namespace GuzzleHttp\Stream;

if (!defined('GUZZLE_STREAMS_FUNCTIONS')) {

    define('GUZZLE_STREAMS_FUNCTIONS', true);

    /**
     * @deprecated Moved to Stream::factory
     */
    function create($resource = '', $size = null)
    {
        return Stream::factory($resource, $size);
    }

    /**
     * @deprecated Moved to Utils::copyToString
     */
    function copy_to_string(StreamInterface $stream, $maxLen = -1)
    {
        return Utils::copyToString($stream, $maxLen);
    }

    /**
     * @deprecated Moved to Utils::copyToStream
     */
    function copy_to_stream(
        StreamInterface $source,
        StreamInterface $dest,
        $maxLen = -1
    ) {
        Utils::copyToStream($source, $dest, $maxLen);
    }

    /**
     * @deprecated Moved to Utils::hash
     */
    function hash(
        StreamInterface $stream,
        $algo,
        $rawOutput = false
    ) {
        return Utils::hash($stream, $algo, $rawOutput);
    }

    /**
     * @deprecated Moced to Utils::readline
     */
    function read_line(StreamInterface $stream, $maxLength = null)
    {
        return Utils::readline($stream, $maxLength);
    }

    /**
     * @deprecated Moved to Utils::open()
     */
    function safe_open($filename, $mode)
    {
        return Utils::open($filename, $mode);
    }
}
