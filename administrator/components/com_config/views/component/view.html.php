<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Config
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * @package		Joomla.Administrator
 * @subpackage	Config
 */
class ConfigViewComponent extends JView
{
	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$params		= $this->get('Params');
		$component  = $this->get('Component');

		$this->document->setTitle(JText::_('Edit Preferences'));
		JRequest::setVar('tmpl', 'component');

		JHtml::_('behavior.tooltip');
		JHtml::_('behavior.switcher');

		$this->assignRef('params',		$params);
		$this->assignRef('component',	$component);

		parent::display($tpl);
	}
}