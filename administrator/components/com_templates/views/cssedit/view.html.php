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
class TemplatesViewCssedit extends JView
{
	protected $option = null;
	protected $client = null;
	protected $ftp = null;
	protected $template = null;
	protected $content = null;

	public function display($tpl = null)
	{
		JToolBarHelper::title( JText::_( 'Template Manager' ), 'thememanager' );
		JToolBarHelper::save( 'save_css' );
		JToolBarHelper::apply( 'apply_css');
		JToolBarHelper::cancel('choose_css');
		JToolBarHelper::help( 'screen.templates' );

		// Initialize some variables
		$option		= JRequest::getCmd('option');

		$content	=& $this->get('Data');
		$client		=& $this->get('Client');
		$template	=& $this->get('Template');
		$filename	=& $this->get('Filename');

		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		$ftp =& JClientHelper::setCredentialsFromRequest('ftp');

		$this->assign('option',		$option);
		$this->assignRef('client',		$client);
		$this->assignRef('ftp',			$ftp);
		$this->assignRef('template',	$template);
		$this->assignRef('filename',	$filename);
		$this->assignRef('content',		$content);

		parent::display($tpl);
	}
}
