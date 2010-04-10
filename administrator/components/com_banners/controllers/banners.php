<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');

/**
 * Banners list controller class.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_banners
 * @since		1.6
 */
class BannersControllerBanners extends JControllerAdmin
{
	protected $_context = 'com_banners.banners';
	/**
	 * Constructor.
	 *
	 * @param	array An optional associative array of configuration settings.
	 * @see		JController
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->registerTask('unpublish',		'publish');
		$this->registerTask('archive',			'publish');
		$this->registerTask('trash',			'publish');
		$this->registerTask('orderup',			'reorder');
		$this->registerTask('orderdown',		'reorder');
		$this->registerTask('sticky_unpublish',	'sticky_publish');
		$this->setURL('index.php?option=com_banners&view=banners');
	}
	
	/**
	 * Proxy for getModel.
	 */
	public function &getModel($name = 'Banner', $prefix = 'BannersModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
	
	public function sticky_publish()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$user	= JFactory::getUser();
		$ids	= JRequest::getVar('cid', array(), '', 'array');
		$values	= array('sticky_publish' => 1, 'sticky_unpublish' => 0);
		$task	= $this->getTask();
		$value	= JArrayHelper::getValue($values, $task, 0, 'int');

		if (empty($ids)) {
			JError::raiseWarning(500, JText::_('COM_BANNERS_NO_BANNERS_SELECTED'));
		}
		else
		{
			// Get the model.
			$model	= $this->getModel();

			// Change the state of the records.
			if (!$model->stick($ids, $value)) {
				JError::raiseWarning(500, $model->getError());
			}
			else
			{
				if ($value == 1) {
					$text = 'COM_BANNERS_BANNER_STUCK';
					$ntext = 'COM_BANNERS_N_BANNERS_STUCK';
				}
				else {
					$text = 'COM_BANNERS_BANNER_UNSTUCK';
					$ntext = 'COM_BANNERS_N_BANNERS_UNSTUCK';
				}
				$this->setMessage(JText::sprintf((count($ids) == 1) ? $text : $ntext, count($ids)));
			}
		}

		$this->setRedirect('index.php?option=com_banners&view=banners');
	}
}