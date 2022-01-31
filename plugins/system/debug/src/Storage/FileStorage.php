<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.Debug
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Debug\Storage;

use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;

/**
 * Stores collected data into files
 *
 * @since  4.0.0
 */
class FileStorage extends \DebugBar\Storage\FileStorage
{
	/**
	 * Saves collected data
	 *
	 * @param   string  $id    The log id
	 * @param   string  $data  The log data
	 *
	 * @return  void
	 *
	 * @since  4.0.0
	 */
	public function save($id, $data)
	{
		if (!file_exists($this->dirname))
		{
			Folder::create($this->dirname);
		}

		$dataStr = '<?php die(); ?>#(^-^)#' . json_encode($data);

		File::write($this->makeFilename($id), $dataStr);
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
	public function get($id)
	{
		$dataStr = file_get_contents($this->makeFilename($id));
		$dataStr = str_replace('<?php die(); ?>#(^-^)#', '', $dataStr);

		return json_decode($dataStr, true) ?: [];
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
	public function find(array $filters = [], $max = 20, $offset = 0)
	{
		// Loop through all .php files and remember the modified time and id.
		$files = [];

		foreach (new \DirectoryIterator($this->dirname) as $file)
		{
			if ($file->getExtension() == 'php')
			{
				$files[] = [
					'time' => $file->getMTime(),
					'id' => $file->getBasename('.php'),
				];
			}
		}

		// Sort the files, newest first
		usort(
			$files,
			function ($a, $b) {
				if ($a['time'] === $b['time'])
				{
					return 0;
				}

				return $a['time'] < $b['time'] ? 1 : -1;
			}
		);

		// Load the metadata and filter the results.
		$results = [];
		$i = 0;

		foreach ($files as $file)
		{
			// When filter is empty, skip loading the offset
			if ($i++ < $offset && empty($filters))
			{
				$results[] = null;
				continue;
			}

			$data = $this->get($file['id']);
			$meta = $data['__meta'];
			unset($data);

			if ($this->filter($meta, $filters))
			{
				$results[] = $meta;
			}

			if (\count($results) >= ($max + $offset))
			{
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
	public function makeFilename($id)
	{
		return $this->dirname . basename($id) . '.php';
	}
}
