<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_postinstall
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * The Toolbar class renders the component title area and the toolbar.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_postinstall
 * @since       3.2
 */
class PostinstallToolbar extends FOFToolbar
{
	/**
	 * Setup the toolbar and title
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function onMessages()
	{
		$extension_name = JText::_('COM_POSTINSTALL_TITLE_JOOMLA');

		$eid = $this->input->getInt('eid', 700);

		if ($eid != 700)
		{
			$model = FOFModel::getTmpInstance('Messages', 'PostinstallModel');
			$extension_name = $model->getExtensionName($eid);
		}

		JToolBarHelper::title(JText::sprintf('COM_POSTINSTALL_MESSAGES_TITLE', $extension_name));
		JToolBarHelper::preferences($this->config['option'], 550, 875);
		JToolbarHelper::help('JHELP_COMPONENTS_POST_INSTALLATION_MESSAGES');
	}
}
