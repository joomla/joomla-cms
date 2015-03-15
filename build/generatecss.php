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
			JPATH_SITE . '/templates/protostar/less/template.less'              => JPATH_SITE . '/templates/protostar/css',
			JPATH_SITE . '/templates/beez3/css/turq.less'                       => JPATH_SITE . '/templates/beez3/css',
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
		jimport('joomla.filesystem.file');
		
		require_once __DIR__ . '/libraries/less/Less.php';
		$less = new Less_Parser(array('compress' => $compress));

		try
		{
			$extension = $compress ? '.min.css' : '.css';
			$filename = str_replace('.less', $extension, basename($source));
			$destination = $destination . '/' . $filename;

			$less->parseFile($source);
			$css = $less->getCss();
			JFile::write($destination, $css);

		}
		catch (Exception $e)
		{
			echo $e->getMessage();
		}
	}
}

JApplicationCli::getInstance('GenerateCss')->execute();
