<?php
/**
 * @package     FrameworkOnFramework
 * @subpackage  utils
 * @copyright   Copyright (C) 2010-2016 Nicholas K. Dionysopoulos / Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('FOF_INCLUDED') or die;

/**
 * A utility class to check that your extension's files are not missing and have not been tampered with.
 *
 * You need a file called fileslist.php in your component's administrator root directory with the following contents:
 *
 * $phpFileChecker = array(
 *   'version' => 'revCEE2DAB',
 *   'date' => '2014-10-16',
 *   'directories' => array(
 *     'administrator/components/com_foobar',
 *     ....
 *   ),
 *   'files' => array(
 *     'administrator/components/com_foobar/access.xml' => array('705', '09aa0351a316bf011ecc8c1145134761', 'b95f00c7b49a07a60570dc674f2497c45c4e7152'),
 *     ....
 *   )
 * );
 *
 * All directory and file paths are relative to the site's root
 *
 * The directories array is a list of
 */
class FOFUtilsFilescheck
{
	/** @var string The name of the component */
	protected $option = '';

	/** @var string Current component version */
	protected $version = null;

	/** @var string Current component release date */
	protected $date = null;

	/** @var array List of files to check as filepath => (filesize, md5, sha1) */
	protected $fileList = array();

	/** @var array List of directories to check that exist */
	protected $dirList = array();

	/** @var bool Is the reported component version different than the version of the #__extensions table? */
	protected $wrongComponentVersion = false;

	/** @var bool Is the fileslist.php reporting a version different than the reported component version? */
	protected $wrongFilesVersion = false;

	/**
	 * Create and initialise the object
	 *
	 * @param string $option Component name, e.g. com_foobar
	 * @param string $version The current component version, as reported by the component
	 * @param string $date The current component release date, as reported by the component
	 */
	public function __construct($option, $version, $date)
	{
		// Initialise from parameters
		$this->option = $option;
		$this->version = $version;
		$this->date = $date;

		// Retrieve the date and version from the #__extensions table
		$db = FOFPlatform::getInstance()->getDbo();
		$query = $db->getQuery(true)->select('*')->from($db->qn('#__extensions'))
					->where($db->qn('element') . ' = ' . $db->q($this->option))
					->where($db->qn('type') . ' = ' . $db->q('component'));
		$extension = $db->setQuery($query)->loadObject();

		// Check the version and date against those from #__extensions. I hate heavily nested IFs as much as the next
		// guy, but what can you do...
		if (!is_null($extension))
		{
			$manifestCache = $extension->manifest_cache;

			if (!empty($manifestCache))
			{
				$manifestCache = json_decode($manifestCache, true);

				if (is_array($manifestCache) && isset($manifestCache['creationDate']) && isset($manifestCache['version']))
				{
					// Make sure the fileslist.php version and date match the component's version
					if ($this->version != $manifestCache['version'])
					{
						$this->wrongComponentVersion = true;
					}

					if ($this->date != $manifestCache['creationDate'])
					{
						$this->wrongComponentVersion = true;
					}
				}
			}
		}

		// Try to load the fileslist.php file from the component's back-end root
		$filePath = JPATH_ADMINISTRATOR . '/components/' . $this->option . '/fileslist.php';

		if (!file_exists($filePath))
		{
			return;
		}

		include $filePath;

		// Make sure the fileslist.php version and date match the component's version
		if ($this->version != $phpFileChecker['version'])
		{
			$this->wrongFilesVersion = true;
		}

		if ($this->date != $phpFileChecker['date'])
		{
			$this->wrongFilesVersion = true;
		}

		// Initialise the files and directories lists
		$this->fileList = $phpFileChecker['files'];
		$this->dirList = $phpFileChecker['directories'];
	}

	/**
	 * Is the reported component version different than the version of the #__extensions table?
	 *
	 * @return boolean
	 */
	public function isWrongComponentVersion()
	{
		return $this->wrongComponentVersion;
	}

	/**
	 * Is the fileslist.php reporting a version different than the reported component version?
	 *
	 * @return boolean
	 */
	public function isWrongFilesVersion()
	{
		return $this->wrongFilesVersion;
	}

	/**
	 * Performs a fast check of file and folders. If even one of the files/folders doesn't exist, or even one file has
	 * the wrong file size it will return false.
	 *
	 * @return bool False when there are mismatched files and directories
	 */
	public function fastCheck()
	{
		// Check that all directories exist
		foreach ($this->dirList as $directory)
		{
			$directory = JPATH_ROOT . '/' . $directory;

			if (!@is_dir($directory))
			{
				return false;
			}
		}

		// Check that all files exist and have the right size
		foreach ($this->fileList as $filePath => $fileData)
		{
			$filePath = JPATH_ROOT . '/' . $filePath;

			if (!@file_exists($filePath))
			{
				return false;
			}

			$fileSize = @filesize($filePath);

			if ($fileSize != $fileData[0])
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Performs a slow, thorough check of all files and folders (including MD5/SHA1 sum checks)
	 *
	 * @param int $idx The index from where to start
	 *
	 * @return array Progress report
	 */
	public function slowCheck($idx = 0)
	{
		$ret = array(
			'done'	=> false,
			'files'	=> array(),
			'folders'	=> array(),
			'idx'	=> $idx
		);

		$totalFiles = count($this->fileList);
		$totalFolders = count($this->dirList);
		$fileKeys = array_keys($this->fileList);

		$timer = new FOFUtilsTimer(3.0, 75.0);

		while ($timer->getTimeLeft() && (($idx < $totalFiles) || ($idx < $totalFolders)))
		{
			if ($idx < $totalFolders)
			{
				$directory = JPATH_ROOT . '/' . $this->dirList[$idx];

				if (!@is_dir($directory))
				{
					$ret['folders'][] = $directory;
				}
			}

			if ($idx < $totalFiles)
			{
				$fileKey = $fileKeys[$idx];
				$filePath = JPATH_ROOT . '/' . $fileKey;
				$fileData = $this->fileList[$fileKey];

				if (!@file_exists($filePath))
				{
					$ret['files'][] = $fileKey . ' (missing)';
				}
				elseif (@filesize($filePath) != $fileData[0])
				{
					$ret['files'][] = $fileKey . ' (size ' . @filesize($filePath) . ' ≠ ' . $fileData[0] . ')';
				}
				else
				{
					if (function_exists('sha1_file'))
					{
						$fileSha1 = @sha1_file($filePath);

						if ($fileSha1 != $fileData[2])
						{
							$ret['files'][] = $fileKey . ' (SHA1 ' . $fileSha1 . ' ≠ ' . $fileData[2] . ')';
						}
					}
					elseif (function_exists('md5_file'))
					{
						$fileMd5 = @md5_file($filePath);

						if ($fileMd5 != $fileData[1])
						{
							$ret['files'][] = $fileKey . ' (MD5 ' . $fileMd5 . ' ≠ ' . $fileData[1] . ')';
						}
					}
				}
			}

			$idx++;
		}

		if (($idx >= $totalFiles) && ($idx >= $totalFolders))
		{
			$ret['done'] = true;
		}

		$ret['idx'] = $idx;

		return $ret;
	}
}