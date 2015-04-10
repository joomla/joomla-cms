<?php
/**
 * @package    Joomla.Build
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// Set flag that this is a parent file.
const _JEXEC = 1;

// Set fixed precision value to avoid round related issues
ini_set('precision', 14);

// Load system defines
if (file_exists(dirname(__DIR__) . '/defines.php'))
{
	require_once dirname(__DIR__) . '/defines.php';
}

if (!defined('_JDEFINES'))
{
	define('JPATH_BASE', dirname(__DIR__));
	require_once JPATH_BASE . '/includes/defines.php';
}

require_once JPATH_LIBRARIES . '/import.php';

require_once JPATH_LIBRARIES . '/cms.php';

/**
 * This script will recompile the CSS files for templates using Less to build their stylesheets.
 *
 * @since  3.0
 */
class GenerateCss extends JApplicationCli
{
	/**
	 * Entry point for the script
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function doExecute()
	{
		$templates = array(
			JPATH_ADMINISTRATOR . '/templates/isis/less/template.less'          => JPATH_ADMINISTRATOR . '/templates/isis/css',
			JPATH_ADMINISTRATOR . '/templates/isis/less/template-rtl.less'      => JPATH_ADMINISTRATOR . '/templates/isis/css',
			JPATH_ADMINISTRATOR . '/templates/hathor/less/template.less'        => JPATH_ADMINISTRATOR . '/templates/hathor/css',
			JPATH_ADMINISTRATOR . '/templates/hathor/less/colour_blue.less'     => JPATH_ADMINISTRATOR . '/templates/hathor/css',
			JPATH_ADMINISTRATOR . '/templates/hathor/less/colour_brown.less'    => JPATH_ADMINISTRATOR . '/templates/hathor/css',
			JPATH_ADMINISTRATOR . '/templates/hathor/less/colour_standard.less' => JPATH_ADMINISTRATOR . '/templates/hathor/css',
			JPATH_SITE . '/templates/beez3/css/turq.less'                       => JPATH_SITE . '/templates/protostar/css',
			JPATH_SITE . '/templates/protostar/less/template.less'              => JPATH_SITE . '/templates/protostar/css',
			// Below files are to recompile the default Bootstrap CSS files
			__DIR__ . '/less/bootstrap-extended.less'                           => JPATH_SITE . '/media/jui/css',
			__DIR__ . '/less/bootstrap-rtl.less'                                => JPATH_SITE . '/media/jui/css'
		);

		foreach ($templates as $source => $destination)
		{
			$this->writeLessToCss($source, $destination);
			$this->writeLessToCss($source, $destination, true);
		}
	}

	public function writeLessToCss($source, $destination, $compress = false)
	{
		$less = new JLess;

		$less->setFormatter(
			$compress
				? new JLessFormatterJoomlaCompressed
				: new JLessFormatterJoomla
		);

		try
		{
			$extension = $compress ? '.min.css' : '.css';
			$filename = str_replace('.less', $extension, basename($source));
			$destination = $destination . '/' . $filename;

			$less->compileFile($source, $destination);

			if (!$compress)
			{
				$css = file_get_contents($destination);
				$this->fixCssSyntax($css, $compress);
				file_put_contents($destination, $css);
			}

			$this->out('Compiled: ' . str_replace(array('\\', '/'), DIRECTORY_SEPARATOR, $destination));
		}
		catch (Exception $e)
		{
			echo $e->getMessage();
		}
	}

	private function fixCssSyntax(&$css)
	{
		// Add spaces to comma seperated stuff
		$css = str_replace(',', ', ', $css);
		$css = str_replace(',  ', ', ', $css);

		// Convert shorthand colors to long: #fff => #fffff
		preg_match_all('#(\#)([0-9a-f])([0-9a-f])([0-9a-f])([;,\)\s])#i', $css, $colors, PREG_SET_ORDER);
		foreach ($colors as $color)
		{
			$css = str_replace(
				$color['0'],
				$color['1'] . strtolower($color['2'] . $color['2'] . $color['3'] . $color['3'] . $color['4'] . $color['4']) . $color['5'],
				$css);
		}

		// Remove trailing spaces
		$css = trim(str_replace(" \n", "\n", $css));
	}
}

JApplicationCli::getInstance('GenerateCss')->execute();
