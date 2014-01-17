<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_tags
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * The Tags List Controller
 *
 * @package     Joomla.Administrator
 * @subpackage  com_tags
 * @since       3.1
 */
class TagsControllerTags extends JControllerAdmin
{
	/**
	 * Proxy for getModel
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 *
	 * @return  JModelLegacy  The model.
	 * @since   3.1
	 */
	public function getModel($name = 'Tag', $prefix = 'TagsModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}

	/**
	 * Rebuild the nested set tree.
	 *
	 * @return  boolean  False on failure or error, true on success.
	 *
	 * @since   3.1
	 */
	public function rebuild()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$this->setRedirect(JRoute::_('index.php?option=com_tags&view=tags', false));

		$model = $this->getModel();

		if ($model->rebuild()) {
			// Rebuild succeeded.
			$this->setMessage(JText::_('COM_TAGS_REBUILD_SUCCESS'));
			return true;
		} else {
			// Rebuild failed.
			$this->setMessage(JText::_('COM_TAGSS_REBUILD_FAILURE'));
			return false;
		}
	}
}
