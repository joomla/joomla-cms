<?php

namespace Algo26\IdnaConvert;

use InvalidArgumentException;

abstract class AbstractIdnaConvert
{
    abstract public function convert(string $host): string;

    /**
     * @param string $emailAddress
     *
     * @return string
     */
    public function convertEmailAddress(string $emailAddress): string
    {
        if (strpos($emailAddress, '@') === false) {
            throw new InvalidArgumentException('The given string does not look like an email address', 206);
        }

        $parts = explode('@', $emailAddress);

        return sprintf(
            '%s@%s',
            $parts[0],
            $this->convert($parts[1])
        );
    }

    /**
     * @param string $url
     *
     * @return string
     */
    public function convertUrl(string $url): string
    {
        $parsed = parse_url($url);
        if ($parsed === false) {
            throw new InvalidArgumentException('The given string does not look like a URL', 206);
        }

        // Nothing to do
        if (!isset($parsed['host']) || $parsed['host'] === null) {
            return $url;
        }
        $parsed['host'] = $this->convert($parsed['host']);

        return http_build_url($parsed);
    }
}
