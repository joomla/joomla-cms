<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Layout;

defined('_JEXEC') || die;

use FOF30\Container\Container;
use Joomla\CMS\Layout\FileLayout;

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
 */
class LayoutFile extends FileLayout
{
	/** @var  Container  The component container */
	public $container = null;

	/**
	 * Method to finds the full real file path, checking possible overrides
	 *
	 * @return  string  The full path to the layout file
	 */
	protected function getPath()
	{
		if (is_null($this->container))
		{
			$component       = $this->options->get('component');
			$this->container = Container::getInstance($component);
		}

		$filesystem = $this->container->filesystem;

		if (is_null($this->fullPath) && !empty($this->layoutId))
		{
			$parts = explode('.', $this->layoutId);
			$file  = array_pop($parts);

			$filePath = implode('/', $parts);
			$suffixes = $this->container->platform->getTemplateSuffixes();

			foreach ($suffixes as $suffix)
			{
				$files[] = $file . $suffix . '.php';
			}

			$files[] = $file . '.php';

			$platformDirs = $this->container->platform->getPlatformBaseDirs();
			$prefix       = $this->container->platform->isBackend() ? $platformDirs['admin'] : $platformDirs['root'];

			$possiblePaths = [
				$prefix . '/templates/' . $this->container->platform->getTemplate() . '/html/layouts/' . $filePath,
				$this->basePath . '/' . $filePath,
				$platformDirs['root'] . '/layouts/' . $filePath,
			];

			reset($files);

			foreach ($files as $fileName)
			{
				if (!is_null($this->fullPath))
				{
					break;
				}

				$r              = $filesystem->pathFind($possiblePaths, $fileName);
				$this->fullPath = $r === false ? null : $r;
			}
		}

		return $this->fullPath;
	}
}
