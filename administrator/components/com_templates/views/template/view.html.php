<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Templates
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the Templates component
 *
 * @static
 * @package		Joomla
 * @subpackage	Templates
 * @since 1.6
 */
class TemplatesViewTemplate extends JView
{
	protected $params = null;
	protected $template = null;
	protected $ftp = null;
	protected $client = null;
	protected $option = null;
	protected $row = null;
	protected $lists = null;
	protected $templatefile = null;

	public function display($tpl = null)
	{

		jimport('joomla.filesystem.path');
		$this->loadHelper('template');

		JToolBarHelper::title( JText::_( 'Template' ) . ': <small><small>[ '. JText::_( 'Edit' ) .' ]</small></small>', 'thememanager' );
		JToolBarHelper::custom('preview', 'preview.png', 'preview_f2.png', 'Preview', false, false);
		JToolBarHelper::custom( 'edit_source', 'html.png', 'html_f2.png', 'Edit HTML', false, false );
		JToolBarHelper::custom( 'choose_css', 'css.png', 'css_f2.png', 'Edit CSS', false, false );
		JToolBarHelper::save( 'save' );
		JToolBarHelper::apply();
		JToolBarHelper::cancel( 'cancel', 'Close' );
		JToolBarHelper::help( 'screen.templates' );

		$row		=& $this->get('Data');
		$params		=& $this->get('Params');
		$client		=& $this->get('Client');
		$template	=& $this->get('Template');

		if (!$template) {
			return JError::raiseWarning( 500, JText::_('Template not specified') );
		}

		if($client->id == '1')  {
			$lists['selections'] =  JText::_('Cannot assign an administrator template');
		} else {
			$lists['selections'] = TemplatesHelper::createMenuList($template);
		}

		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		$ftp =& JClientHelper::setCredentialsFromRequest('ftp');

		$this->assignRef('lists',		$lists);
		$this->assignRef('row',			$row);
		$this->assign('option',		JRequest::getCMD('option'));
		$this->assignRef('client',		$client);
		$this->assignRef('ftp',			$ftp);
		$this->assignRef('template',	$template);
		$this->assignRef('params',		$params);

		parent::display($tpl);
	}
}
