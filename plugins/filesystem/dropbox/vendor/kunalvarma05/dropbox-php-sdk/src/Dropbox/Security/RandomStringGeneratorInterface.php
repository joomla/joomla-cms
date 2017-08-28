<?php
namespace Kunnu\Dropbox\Security;

/**
 * Thanks to Facebook
 *
 * @link https://developers.facebook.com/docs/php/PseudoRandomStringGeneratorInterface
 */
interface RandomStringGeneratorInterface
{
    /**
     * Get a randomly generated secure token
     *
     * @param  int $length Length of the string to return
     *
     * @return string
     */
    public function generateString($length);
}
