<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Engine\Filter;

// Protection against direct access
defined('AKEEBAENGINE') || die();

use Akeeba\Engine\Factory;
use Akeeba\Engine\Platform;

/**
 * Joomla! 1.6 libraries off-site relocation workaround
 *
 * After the application of patch 23377
 * (http://joomlacode.org/gf/project/joomla/tracker/?action=TrackerItemEdit&tracker_item_id=23377)
 * it is possible for the webmaster to move the libraries directory of his Joomla!
 * site to an arbitrary location in the folder tree. This filter works around this
 * new feature by creating a new extra directory inclusion filter.
 */
class Libraries extends Base
{
	public function __construct()
	{
		$this->object      = 'dir';
		$this->subtype     = 'inclusion';
		$this->method      = 'direct';
		$this->filter_name = 'Libraries';

		// FIXME This filter doesn't work very well on many live hosts. Disabled for now.
		parent::__construct();

		return;


		if (empty($this->filter_name))
		{
			$this->filter_name = strtolower(basename(__FILE__, '.php'));
		}

		// Get the saved library path and compare it to the default
		$jlibdir = Platform::getInstance()->get_platform_configuration_option('jlibrariesdir', '');
		if (empty($jlibdir))
		{
			if (defined('JPATH_LIBRARIES'))
			{
				$jlibdir = JPATH_LIBRARIES;
			}
			elseif (defined('JPATH_PLATFORM'))
			{
				$jlibdir = JPATH_PLATFORM;
			}
			else
			{
				$jlibdir = false;
			}
		}

		if ($jlibdir !== false)
		{
			$jlibdir          = Factory::getFilesystemTools()->TranslateWinPath($jlibdir);
			$defaultLibraries = Factory::getFilesystemTools()->TranslateWinPath(JPATH_SITE . '/libraries');

			if ($defaultLibraries != $jlibdir)
			{
				// The path differs, add it here
				$this->filter_data['JPATH_LIBRARIES'] = $jlibdir;
			}
		}
		else
		{
			$this->filter_data = array();
		}
		parent::__construct();
	}
}
