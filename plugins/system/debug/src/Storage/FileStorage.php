<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.Debug
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Debug\Storage;

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Stores collected data into files
 *
 * @since  4.0.0
 */
class FileStorage extends \DebugBar\Storage\FileStorage
{
    private const FILE_PREFIX = '<?php die(); ?>#(^-^)#';

    /**
     * Initializes the storage and secures request files created by older versions.
     *
     * @param   string  $dirname  The directory where request data is stored.
     *
     * @since  4.0.0
     */
    public function __construct(string $dirname)
    {
        parent::__construct($dirname);

        $this->secureLegacyFiles();
    }

    /**
     * Saves collected data
     *
     * @param   string  $id    The log id
     * @param   array   $data  The log data
     *
     * @return  void
     *
     * @since  4.0.0
     */
    public function save(string $id, array $data): void
    {
        if (!file_exists($this->dirname)) {
            Folder::create($this->dirname);
        }

        if (!is_dir($this->dirname) || !is_writable($this->dirname)) {
            Log::add(
                \sprintf('Unable to write DebugBar request history; directory is not writable: %s', $this->dirname),
                Log::WARNING,
                'plg_system_debug'
            );

            return;
        }

        $dataStr  = self::FILE_PREFIX . json_encode($data, JSON_THROW_ON_ERROR);
        $fileName = $this->makeFilename($id);

        if (!File::write($fileName, $dataStr)) {
            Log::add(
                \sprintf('Unable to write DebugBar request history file: %s', $fileName),
                Log::WARNING,
                'plg_system_debug'
            );

            return;
        }

        $this->autoPrune();
    }

    /**
     * Returns collected data with the specified id
     *
     * @param   string  $id  The log id
     *
     * @return  array
     *
     * @since  4.0.0
     */
    public function get(string $id): array
    {
        $fileName = $this->makeFilename($id);

        if (!file_exists($fileName)) {
            return [];
        }

        $dataStr = file_get_contents($fileName);

        if ($dataStr === false) {
            throw new \RuntimeException("Unable to read DebugBar data file: $fileName");
        }

        if (!str_starts_with($dataStr, self::FILE_PREFIX)) {
            throw new \UnexpectedValueException("Invalid DebugBar data file: $fileName");
        }

        $data = json_decode(substr($dataStr, \strlen(self::FILE_PREFIX)), true, 512, JSON_THROW_ON_ERROR);

        if (!\is_array($data)) {
            throw new \UnexpectedValueException("Invalid DebugBar data in file: $fileName");
        }

        return $data;
    }

    /**
     * Returns a metadata about collected data
     *
     * @param   array    $filters  Filtering options
     * @param   integer  $max      The limit, items per page
     * @param   integer  $offset   The offset
     *
     * @return  array
     *
     * @since 4.0.0
     */
    public function find(array $filters = [], int $max = 20, int $offset = 0): array
    {
        if (!is_dir($this->dirname)) {
            return [];
        }

        $ids = [];

        foreach (new \FilesystemIterator($this->dirname, \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::CURRENT_AS_PATHNAME) as $path) {
            if (\is_string($path) && str_ends_with($path, '.php')) {
                $ids[] = basename($path, '.php');
            }
        }

        sort($ids, SORT_STRING);
        $ids = array_reverse($ids);

        $filterUtime = null;

        if ($filters['utime'] ?? false) {
            $filterUtime = (float) $filters['utime'];
            unset($filters['utime']);
        }

        // Load the metadata and filter the results.
        $results = [];
        $i       = 0;

        foreach ($ids as $id) {
            // When filter is empty, skip loading the offset
            if ($i++ < $offset && !$filters) {
                $results[] = null;
                continue;
            }

            $data = $this->get($id);

            if (!isset($data['__meta']) || !$this->isSecureToReturnData($data)) {
                continue;
            }

            $meta = $data['__meta'];
            unset($data);

            if ($filterUtime !== null && $meta['utime'] <= $filterUtime) {
                break;
            }

            if ($this->filter($meta, $filters)) {
                $results[] = $meta;
            }

            if (\count($results) >= ($max + $offset)) {
                break;
            }
        }

        return \array_slice($results, $offset, $max);
    }

    /**
     * Get a full path to the file
     *
     * @param   string  $id  The log id
     *
     * @return string
     *
     * @since 4.0.0
     */
    public function makeFilename(string $id): string
    {
        return $this->dirname . basename($id) . '.php';
    }

    /**
     * Remove stored request data.
     *
     * @return void
     *
     * @since  4.0.0
     */
    public function clear(): void
    {
        if (!is_dir($this->dirname)) {
            return;
        }

        foreach (new \FilesystemIterator($this->dirname, \FilesystemIterator::CURRENT_AS_PATHNAME | \FilesystemIterator::SKIP_DOTS) as $path) {
            if (\is_string($path) && is_file($path) && str_ends_with($path, '.php')) {
                unlink($path);
            }
        }
    }

    /**
     * Remove request data older than the given number of hours.
     *
     * @param   int  $hours  The maximum age of stored requests.
     *
     * @return  void
     *
     * @since  4.0.0
     */
    public function prune(int $hours = 24): void
    {
        if (!is_dir($this->dirname)) {
            return;
        }

        $cutoffTime = time() - $hours * 3600;

        foreach (new \FilesystemIterator($this->dirname, \FilesystemIterator::CURRENT_AS_FILEINFO | \FilesystemIterator::SKIP_DOTS) as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), '.php') && $file->getMTime() < $cutoffTime) {
                unlink($file->getPathname());
            }
        }
    }

    /**
     * Migrate unprotected JSON files from previous versions to PHP-guarded files.
     *
     * @return void
     *
     * @since  4.0.0
     */
    private function secureLegacyFiles(): void
    {
        if (!is_dir($this->dirname)) {
            return;
        }

        foreach (new \FilesystemIterator($this->dirname, \FilesystemIterator::CURRENT_AS_FILEINFO | \FilesystemIterator::SKIP_DOTS) as $file) {
            if (!$file->isFile() || !str_ends_with($file->getFilename(), '.json')) {
                continue;
            }

            $legacyFile = $file->getPathname();
            $data       = file_get_contents($legacyFile);

            if ($data === false) {
                throw new \RuntimeException("Unable to read legacy DebugBar data file: $legacyFile");
            }

            $secureFile = $this->makeFilename(basename($file->getFilename(), '.json'));

            if (file_exists($secureFile)) {
                $secureData = file_get_contents($secureFile);

                if ($secureData === false || !str_starts_with($secureData, self::FILE_PREFIX)) {
                    throw new \UnexpectedValueException("Invalid secured DebugBar data file: $secureFile");
                }
            } elseif (!File::write($secureFile, self::FILE_PREFIX . $data)) {
                throw new \RuntimeException("Unable to secure legacy DebugBar data file: $legacyFile");
            }

            if (!unlink($legacyFile)) {
                throw new \RuntimeException("Unable to remove unprotected DebugBar data file: $legacyFile");
            }
        }
    }

    /**
     * Check if the user is allowed to view the request. Users can only see their own requests.
     *
     * @param   array  $data  The data item to process
     *
     * @return boolean
     *
     * @since 4.2.4
     */
    private function isSecureToReturnData($data): bool
    {
        /**
         * We only started this collector in Joomla 4.2.4 - any older files we have to assume are insecure.
         */
        if (!\array_key_exists('juser', $data)) {
            return false;
        }

        $currentUser           = Factory::getApplication()->getIdentity();
        $currentUserId         = $currentUser->id;
        $currentUserSuperAdmin = $currentUser->authorise('core.admin');

        /**
         * Guests aren't allowed to look at other requests because there's no guarantee it's the same guest. Potentially
         * in the future this could be refined to check the session ID to show some requests. But it's unlikely we want
         * guests to be using the debug bar anyhow
         */
        if ($currentUserId === 0) {
            return false;
        }

        /** @var \Joomla\CMS\User\User $user */
        $user = Factory::getContainer()->get(UserFactoryInterface::class)
            ->loadUserById($data['juser']['user_id']);

        // Super users are allowed to look at other users requests. Otherwise users can only see their own requests.
        if ($currentUserSuperAdmin || $user->id === $currentUserId) {
            return true;
        }

        return false;
    }
}
