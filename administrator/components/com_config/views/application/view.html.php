<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Config
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
  */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
* @package	Joomla.Administrator
* @subpackage	Config
*/
class ConfigViewApplication extends JView
{
	public $state;
	public $config;
	public $form;
	public $fieldsets;
	public $ftp;

	public $group;
	public $label;
	public $fields;

	/**
	 * Display the view
	 *
	 * @param	string	Optional sub-template
	 */
	function display($tpl = null)
	{
		$state		= $this->get('State');
		$config		= $this->get('Config');
		$form		= $this->get('Form');
		$fieldsets	= $form->getFieldsets();

		// Build the component's submenu
		$submenu = $this->loadTemplate('navigation');
		$document = &JFactory::getDocument();
		$document->setBuffer($submenu, 'modules', 'submenu');

		// Load settings for the FTP layer
		jimport('joomla.client.helper');
		$ftp = &JClientHelper::setCredentialsFromRequest('ftp');

		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$form->bind($config);

		$this->assignRef('state',		$state);
		$this->assignRef('config',		$config);
		$this->assignRef('form',		$form);
		$this->assignRef('fieldsets',	$fieldsets);
		$this->assignRef('ftp',			$ftp);

		$this->_setToolbar();
		parent::display($tpl);
	}

	/**
	 * Setup the Toolbar
	 */
	protected function _setToolbar()
	{
		JToolBarHelper::title(JText::_('Global Configuration'), 'config.png');
		JToolBarHelper::save('application.save');
		JToolBarHelper::apply('application.apply');
		JToolBarHelper::cancel('application.cancel', 'Close');
		JToolBarHelper::help('screen.config');
	}

	public function renderGroup($group)
	{
		$return = '';
		if (!isset($this->fieldsets[$group]['label'])) {
			return $return;
		}

		$this->fields = $this->form->getFields($group);
		$this->label = $this->fieldsets[$group]['label'];
		$this->group = $group;

		return $this->loadTemplate('common');
	}


	public function getWarningIcon($text)
	{
		$return = '<span class="hasTip" title="'.JText::_('Warning').'::'.JText::_($text).'">'
			. '<img src="'.JURI::base(true).'/includes/js/ThemeOffice/warning.png" border="0"  alt="" />'
			. '</span>';

		return $return;
	}
}
