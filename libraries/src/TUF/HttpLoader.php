<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\TUF;

use GuzzleHttp\Promise\Create;
use GuzzleHttp\Promise\PromiseInterface;
use Joomla\Http\Http;
use Tuf\Exception\RepoFileNotFound;
use Tuf\Loader\LoaderInterface;

/**
 * @since  5.1.0
 *
 * @internal Currently this class is only used for Joomla! updates and will be extended in the future to support 3rd party updates
 *           Don't extend this class in your own code, it is subject to change without notice.
 */
class HttpLoader implements LoaderInterface
{
    public function __construct(private readonly string $repositoryPath, private readonly Http $http)
    {
    }

    public function load(string $locator, int $maxBytes): PromiseInterface
    {
        try {
            /** @var Http $client */
            $response = $this->http->get($this->repositoryPath . $locator);
        } catch (\Exception $e) {
            // We convert the generic exception thrown in the Http library into a TufException
            throw new HttpLoaderException($e->getMessage(), $e->getCode(), $e);
        }

        if ($response->code !== 200) {
            throw new RepoFileNotFound();
        }

        // Rewind to start
        $response->getBody()->rewind();

        // Return response
        return Create::promiseFor($response->getBody());
    }
}
