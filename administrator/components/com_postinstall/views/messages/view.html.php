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
 * Model class to display postinstall messages
 *
 * @package     Joomla.Administrator
 * @subpackage  com_postinstall
 * @since       3.2
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
		$this->eid = $this->input->getInt('eid', '700');
		$this->token = JFactory::getSession()->getFormToken();

		return parent::onBrowse($tpl);
	}
}
