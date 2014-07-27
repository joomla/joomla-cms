<?php
/**
 * @package    Joomla.Build
 *
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// Set flag that this is a parent file.
const _JEXEC = 1;

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
 * @package  Joomla.Build
 * @since    3.0
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
			JPATH_ADMINISTRATOR . '/templates/isis/less/template.less' => JPATH_ADMINISTRATOR . '/templates/isis/css/template.css',
			JPATH_ADMINISTRATOR . '/templates/isis/less/template-rtl.less' => JPATH_ADMINISTRATOR . '/templates/isis/css/template-rtl.css',
			JPATH_ADMINISTRATOR . '/templates/hathor/less/template.less' => JPATH_ADMINISTRATOR . '/templates/hathor/css/template.css',
			JPATH_ADMINISTRATOR . '/templates/hathor/less/colour_blue.less' => JPATH_ADMINISTRATOR . '/templates/hathor/css/colour_blue.css',
			JPATH_ADMINISTRATOR . '/templates/hathor/less/colour_brown.less' => JPATH_ADMINISTRATOR . '/templates/hathor/css/colour_brown.css',
			JPATH_ADMINISTRATOR . '/templates/hathor/less/colour_standard.less' => JPATH_ADMINISTRATOR . '/templates/hathor/css/colour_standard.css',
			JPATH_SITE . '/templates/protostar/less/template.less' => JPATH_SITE . '/templates/protostar/css/template.css',
			JPATH_SITE . '/templates/beez3/css/turq.less' => JPATH_SITE . '/templates/beez3/css/turq.css',
			// Below files are to recompile the default Bootstrap CSS files
			__DIR__ . '/less/bootstrap-extended.less' => JPATH_SITE . '/media/jui/css/bootstrap-extended.css',
			__DIR__ . '/less/bootstrap-rtl.less' => JPATH_SITE . '/media/jui/css/bootstrap-rtl.css'
		);

		// Load the RAD layer
		if (!defined('FOF_INCLUDED'))
		{
			require_once JPATH_LIBRARIES . '/fof/include.php';
		}

		$less = new FOFLess;
		$less->setFormatter(new FOFLessFormatterJoomla);

		foreach ($templates as $source => $output)
		{
			try
			{
				$less->compileFile($source, $output);
			}
			catch (Exception $e)
			{
				echo $e->getMessage();
			}
		}
	}
}

JApplicationCli::getInstance('GenerateCss')->execute();
