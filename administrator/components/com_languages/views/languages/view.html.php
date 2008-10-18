<?php
/**
* @version		$Id: $
* @package		Joomla
* @subpackage	Languages
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
 * HTML View class for the Languages component
 *
 * @static
 * @package		Joomla
 * @subpackage	Languages
 * @since 1.0
 */
class LanguagesViewLanguages extends JView
{
	protected $client;
	protected $ftp;
	protected $filter;
	protected $pagination;
	protected $rows;
	protected $user;
	function display($tpl = null)
	{
		// Set toolbar items for the page
		JToolBarHelper::title( JText::_( 'Language Manager' ), 'langmanager.png' );
		JToolBarHelper::makeDefault( 'publish' );
		JToolBarHelper::help( 'screen.languages' );

		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		$ftp =& JClientHelper::setCredentialsFromRequest('ftp');

		// Get data from the model
		$rows		= & $this->get( 'Data');
		$total		= & $this->get( 'Total');
		$pagination = & $this->get( 'Pagination' );
		$filter		= & $this->get( 'Filter');
		$client		= & $this->get( 'Client');

		if ($client->id == 1) {
			JSubMenuHelper::addEntry(JText::_('Site'),'#" onclick="javascript:document.adminForm.client.value=\'0\';submitbutton(\'\');');
			JSubMenuHelper::addEntry(JText::_('Administrator'), '#" onclick="javascript:document.adminForm.client.value=\'1\';submitbutton(\'\');', true );
		} else {
			JSubMenuHelper::addEntry(JText::_('Site'), '#" onclick="javascript:document.adminForm.client.value=\'0\';submitbutton(\'\');', true );
			JSubMenuHelper::addEntry(JText::_('Administrator'), '#" onclick="javascript:document.adminForm.client.value=\'1\';submitbutton(\'\');');
		}

		$this->assignRef('user',		JFactory::getUser());
		$this->assignRef('rows',		$rows);
		$this->assignRef('pagination',	$pagination);
		$this->assignRef('filter',		$filter);
		$this->assignRef('ftp',			$ftp);
		$this->assignRef('client',		$client);

		parent::display($tpl);
	}
}
