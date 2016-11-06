<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_postinstall
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Model class to display postinstall messages
 *
 * @since  3.2
 */
class PostinstallViewMessages extends FOFViewHtml
{
	/**
	 * Executes before rendering the page for the Browse task.
	 *
	 * @param   string  $tpl  Subtemplate to use
	 *
	 * @return  boolean  Return true to allow rendering of the page
	 *
	 * @since   3.2
	 */
	protected function onBrowse($tpl = null)
	{
		/** @var PostinstallModelMessages $model */
		$model = $this->getModel();

		$this->eid = (int) $model->getState('eid', '700', 'int');

		if (empty($this->eid))
		{
			$this->eid = 700;
		}

		$this->token = JFactory::getSession()->getFormToken();
		$this->extension_options = $model->getComponentOptions();

		JToolBarHelper::title(JText::sprintf('COM_POSTINSTALL_MESSAGES_TITLE', $model->getExtensionName($this->eid)));

		return parent::onBrowse($tpl);
	}
}
