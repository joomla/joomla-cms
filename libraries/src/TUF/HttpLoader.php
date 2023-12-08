<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\TUF;

use GuzzleHttp\Promise\Create;
use Joomla\Http\HttpFactory;
use GuzzleHttp\Promise\PromiseInterface;
use Tuf\Exception\RepoFileNotFound;
use Tuf\Loader\LoaderInterface;

class HttpLoader implements LoaderInterface
{
    public function __construct(private readonly string $repositoryPath)
    {
    }

    public function load(string $locator, int $maxBytes): PromiseInterface
    {
        $httpFactory = new HttpFactory();

        // Get client instance
        $client = $httpFactory->getHttp([], 'curl');
        $response = $client->get($this->repositoryPath . $locator);

        if ($response->code === 404) {
            throw new RepoFileNotFound();
        }

        // Rewind to start
        $response->getBody()->rewind();

        // Return response
        return Create::promiseFor($response->getBody());
    }
}
