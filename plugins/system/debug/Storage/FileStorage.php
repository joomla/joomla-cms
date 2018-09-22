<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.Debug
 *
 * @copyright   Copyright (C) 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Debug\Storage;

/**
 * Stores collected data into files
 *
 * @since __DEPLOY_VERSION__
 */
class FileStorage extends \DebugBar\Storage\FileStorage
{
	/**
	 * {@inheritdoc}
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function save($id, $data)
	{
		if (!file_exists($this->dirname)) {
			mkdir($this->dirname, 0777, true);
		}

		$dataStr = '<?php die(); ?>#x#' . json_encode($data);

		file_put_contents($this->makeFilename($id), $dataStr);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function get($id)
	{
		$dataStr = file_get_contents($this->makeFilename($id));
		$dataStr = str_replace('<?php die("Access Denied"); ?>#x#', '', $dataStr);

		return json_decode($dataStr, true);
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
