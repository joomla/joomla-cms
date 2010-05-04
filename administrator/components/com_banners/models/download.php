<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');

/**
 * Download model.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_banners
 * @since		1.5
 */
class BannersModelDownload extends JModelForm
{
	protected $_context = 'com_banners.tracks';

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	protected function populateState()
	{
		jimport('joomla.utilities.utility');
		$basename = JRequest::getString(JUtility::getHash($this->_context.'.basename'),'__SITE__','cookie');
		$this->setState('basename',$basename);

		$compressed = JRequest::getInt(JUtility::getHash($this->_context.'.compressed'),1,'cookie');
		$this->setState('compressed',$compressed);
	}

	/**
	 * Method to get the record form.
	 *
	 * @return	mixed	JForm object on success, false on failure.
	 * @since	1.6
	 */
	public function getForm()
	{
		// Get the form.
		$form = parent::getForm('com_banners.download', 'download', array('control' => 'jform'));
		if (empty($form)) {
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */
	protected function getFormData()
	{
		$form->setValue('basename',		$this->getState('basename'));
		$form->setValue('compressed',	$this->getState('compressed'));
	}
}
