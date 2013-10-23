<?php
/**
 * @package    PatchTester
 *
 * @copyright  Copyright (C) 2011 - 2012 Ian MacLennan, Copyright (C) 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

/**
 * PatchTester Controller
 *
 * @package  PatchTester
 * @since    1.0
 */
class PatchTesterController extends JControllerLegacy
{
	/**
	 * The default view for the display method.
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $default_view = 'pulls';

	/**
	 * Method to purge the cache
	 *
	 * @return  void
	 *
	 * @since   2.0
	 */
	public function purge()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		$failures  = 0;
		$successes = 0;

		$cacheFiles = JFolder::files(JPATH_CACHE);

		foreach ($cacheFiles as $file)
		{
			if (strpos($file, 'patchtester-page-') === 0)
			{
				if (!JFile::delete(JPATH_CACHE . '/' . $file))
				{
					$failures++;
				}
				else
				{
					$successes++;
				}
			}
		}

		if ($failures > 0)
		{
			JFactory::getApplication()->enqueueMessage(JText::plural('COM_PATCHTESTER_PURGE_FAIL', $failures), 'error');
		}

		if ($successes > 0)
		{
			JFactory::getApplication()->enqueueMessage(JText::plural('COM_PATCHTESTER_PURGE_SUCCESS', $successes), 'message');
		}

		if ($failures == 0 && $successes == 0)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_PATCHTESTER_PURGE_NA'), 'message');
		}

		$this->setRedirect('index.php?option=com_patchtester&view=pulls');
	}
}
