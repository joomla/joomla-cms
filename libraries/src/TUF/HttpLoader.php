<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\TUF;

use Joomla\Http\HttpFactory;
use Joomla\Http\Http;
use Psr\Http\Message\StreamInterface;
use Tuf\Loader\LoaderInterface;

class HttpLoader implements LoaderInterface
{
    public function load(string $locator, int $maxBytes): StreamInterface
    {
        $httpFactory = new HttpFactory();

        /** @var Http $client */
        $client = $httpFactory->getHttp([], 'curl');
        $response = $client->get($locator);

        // Rewind to start
        $response->getBody()->rewind();

        return $response->getBody();
    }
}
