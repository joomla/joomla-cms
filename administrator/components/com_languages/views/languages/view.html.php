<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * HTML Languages View class for the Languages component
 *
 * @package		Joomla.Administrator
 * @subpackage	com_languages
 * @since		1.6
 */
class LanguagesViewLanguages extends JView
{
	/**
	 * @var array languages information
	 */
	protected $rows=null;

	/**
	 * @var object pagination information
	 */
	protected $pagination=null;

	/**
	 * @var boolean|JExeption True, if FTP settings should be shown, or an exeption
	 */
	protected $ftp = null;

	/**
	 * @var object client object
	 */
	protected $client = null;

	/**
	 * @var object user object
	 */
	protected $user = null;

	/**
	 * @var string option name
	 */
	protected $option = null;


	/**
	 * Display the view
	 */
	function display($tpl = null)
	{
		$state		= $this->get('State');
		$items		= $this->get('Items');
		$pagination	= $this->get('Pagination');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->assignRef('state',			$state);
		$this->assignRef('items',			$items);
		$this->assignRef('pagination',		$pagination);

		parent::display($tpl);
		$this->_setToolbar();
	}

	/**
	 * Setup the Toolbar
	 *
	 * @since	1.6
	 */
	protected function _setToolBar()
	{
		JToolBarHelper::title(JText::_('Langs_View_Languages_Title'), 'generic.png');
		JToolBarHelper::addNew('language.add');
				JToolBarHelper::editList('language.edit');
				JToolBarHelper::divider();
		JToolBarHelper::publishList('languages.publish');
		JToolBarHelper::unpublishList('languages.unpublish');
		if ($this->state->get('filter.published') == -2) {
			JToolBarHelper::deleteList('', 'languages.delete', 'JToolbar_Empty_trash');
		}
		else {
			JToolBarHelper::trash('languages.trash');
		}
		JToolBarHelper::divider();
		JToolBarHelper::preferences('com_languagaes');
		JToolBarHelper::divider();
		JToolBarHelper::help('screen.languages');
	}
}
