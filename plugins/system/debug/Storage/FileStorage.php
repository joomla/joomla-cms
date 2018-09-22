<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.Debug
 *
 * @copyright   Copyright (C) 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Debug\Storage;

use Joomla\Filesystem\Folder;

/**
 * Stores collected data into files
 *
 * @since  __DEPLOY_VERSION__
 */
class FileStorage extends \DebugBar\Storage\FileStorage
{
	/**
	 * Saves collected data
	 *
	 * @param   string  $id
	 * @param   string  $data
	 *
	 * @return  void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function save($id, $data)
	{
		if (!file_exists($this->dirname))
		{
			Folder::create($this->dirname);
		}

		$dataStr = '<?php die(); ?>#(^-^)#' . json_encode($data);

		file_put_contents($this->makeFilename($id), $dataStr);
	}

	/**
	 * Returns collected data with the specified id
	 *
	 * @param   string   $id
	 *
	 * @return  array
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function get($id)
	{
		$dataStr = file_get_contents($this->makeFilename($id));
		$dataStr = str_replace('<?php die(); ?>#(^-^)#', '', $dataStr);

		return json_decode($dataStr, true) ?: array();
	}

	/**
	 * Returns a metadata about collected data
	 *
	 * @param   array    $filters
	 * @param   integer  $max
	 * @param   integer  $offset
	 *
	 * @return  array
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function find(array $filters = array(), $max = 20, $offset = 0)
	{
		// Loop through all .php files and remember the modified time and id.
		$files = array();
		foreach (new \DirectoryIterator($this->dirname) as $file)
		{
			if ($file->getExtension() == 'php')
			{
				$files[] = array(
					'time' => $file->getMTime(),
					'id' => $file->getBasename('.php')
				);
			}
		}

		// Sort the files, newest first
		usort($files, function ($a, $b) {
			return $a['time'] < $b['time'];
		});

		// Load the metadata and filter the results.
		$results = array();
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

			if (count($results) >= ($max + $offset))
			{
				break;
			}
		}

		return array_slice($results, $offset, $max);
	}


	/**
	 * @param  string $id
	 *
	 * @return string
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function makeFilename($id)
	{
		return $this->dirname . basename($id). '.php';
	}
}
