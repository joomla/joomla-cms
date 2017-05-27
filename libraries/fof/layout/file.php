<?php
/**
 * @package     FrameworkOnFramework
 * @subpackage  layout
 * @copyright   Copyright (C) 2010-2016 Nicholas K. Dionysopoulos / Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('FOF_INCLUDED') or die;

/**
 * Base class for rendering a display layout
 * loaded from from a layout file
 *
 * This class searches for Joomla! version override Layouts. For example,
 * if you have run this under Joomla! 3.0 and you try to load
 * mylayout.default it will automatically search for the
 * layout files default.j30.php, default.j3.php and default.php, in this
 * order.
 *
 * @package  FrameworkOnFramework
 * @since    1.0
 */
class FOFLayoutFile extends JLayoutFile
{
	/**
	 * Method to finds the full real file path, checking possible overrides
	 *
	 * @return  string  The full path to the layout file
	 */
	protected function getPath()
	{
		$filesystem = FOFPlatform::getInstance()->getIntegrationObject('filesystem');

		if (is_null($this->fullPath) && !empty($this->layoutId))
		{
			$parts = explode('.', $this->layoutId);
			$file  = array_pop($parts);

			$filePath = implode('/', $parts);
			$suffixes = FOFPlatform::getInstance()->getTemplateSuffixes();

			foreach ($suffixes as $suffix)
			{
				$files[] = $file . $suffix . '.php';
			}

			$files[] = $file . '.php';

            $platformDirs = FOFPlatform::getInstance()->getPlatformBaseDirs();
            $prefix       = FOFPlatform::getInstance()->isBackend() ? $platformDirs['admin'] : $platformDirs['root'];

			$possiblePaths = array(
				$prefix . '/templates/' . JFactory::getApplication()->getTemplate() . '/html/layouts/' . $filePath,
				$this->basePath . '/' . $filePath
			);

			reset($files);

			while ((list(, $fileName) = each($files)) && is_null($this->fullPath))
			{
				$r = $filesystem->pathFind($possiblePaths, $fileName);
				$this->fullPath = $r === false ? null : $r;
			}
		}

		return $this->fullPath;
	}
}
