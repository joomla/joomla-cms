<?php
/**
 * @package    PatchTester
 *
 * @copyright  Copyright (C) 2011 - 2012 Ian MacLennan, Copyright (C) 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

/**
 * Pull controller class
 *
 * @package  PatchTester
 * @since    1.0
 */
class PatchtesterControllerPull extends JControllerLegacy
{
	/**
	 * Method to apply a patch
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function apply()
	{
		try
		{
			$this->getModel('pull')->apply(JFactory::getApplication()->input->getInt('pull_id'));

			$msg  = JText::_('COM_PATCHTESTER_APPLY_OK');
			$type = 'message';
		}
		catch (Exception $e)
		{
			$msg  = $e->getMessage();
			$type = 'error';
		}

		$this->setRedirect(JRoute::_('index.php?option=com_patchtester&view=pulls', false), $msg, $type);
	}

	/**
	 * Method to revert a patch
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function revert()
	{
		try
		{
			$this->getModel('pull')->revert(JFactory::getApplication()->input->getInt('pull_id'));

			$msg  = JText::_('COM_PATCHTESTER_REVERT_OK');
			$type = 'message';
		}
		catch (Exception $e)
		{
			$msg  = $e->getMessage();
			$type = 'error';
		}

		$this->setRedirect(JRoute::_('index.php?option=com_patchtester&view=pulls', false), $msg, $type);
	}
}
