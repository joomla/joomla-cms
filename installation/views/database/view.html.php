<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * The HTML Joomla Core Database Configuration View
 *
 * @package  Joomla.Installation
 * @since    3.0
 */
class InstallationViewDatabase extends JViewLegacy
{
	/**
	 * @var JForm $form
	 */
	protected $form;

	/**
	 * @var JObject $state
	 */
	protected $state;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function display($tpl = null)
	{
		$setupOptions = JFactory::getSession()->get('setup.options');

		$dbType = (isset($setupOptions['db_type'])) ? $setupOptions['db_type'] : 'mysqli';
		$dbType = ('mysqli' == $dbType) ? 'mysql' : $dbType;

		$this->state = $this->get('State');

		$this->form = $this->getModel()->getForm('database_'.$dbType);

		if (!$this->form)
		{
			// @todo some jformexception
			throw new Exception('Database form failed to load');
		}

		$this->form->setFieldAttribute('db_type', 'onchange', 'Install.updateDbSettings(this);');

		parent::display($tpl);
	}
}
