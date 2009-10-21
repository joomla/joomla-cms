<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * @package		Joomla.Administrator
 * @subpackage	com_content
 */
class ContentViewArticle extends JView
{
	protected $state;
	protected $item;
	protected $form;

	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$app	= JFactory::getApplication();
		$state	= $this->get('State');
		$item	= $this->get('Item');
		$form 	= $this->get('Form');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		// Convert dates from UTC
		$offset	= $app->getCfg('offset');
		if (intval($item->created)) {
			$item->created = JHtml::date($item->created, '%Y-%m-%d %H-%M-%S', $offset);
		}
		if (intval($item->publish_up)) {
			$item->publish_up = JHtml::date($item->publish_up, '%Y-%m-%d %H-%M-%S', $offset);
		}
		if (intval($item->publish_down)) {
			$item->publish_down = JHtml::date($item->publish_down, '%Y-%m-%d %H-%M-%S', $offset);
		}

		$form->bind($item);

		$this->assignRef('state',	$state);
		$this->assignRef('item',	$item);
		$this->assignRef('form',	$form);

		$this->_setToolbar();
		parent::display($tpl);
	}

	/**
	 * Display the toolbar
	 *
	 * @access	private
	 */
	protected function _setToolbar()
	{
		JRequest::setVar('hidemainmenu', true);

		$user		= &JFactory::getUser();
		$isNew		= ($this->item->id == 0);
		$checkedOut	= !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		$canDo		= ContentHelper::getActions($this->state->get('filter.category_id'), $this->item->id);

		JToolBarHelper::title(JText::_('Content_Page_'.($checkedOut ? 'View_Article' : ($isNew ? 'Add_Article' : 'Edit_Article'))), 'article-add.png');

		// If an existing item, can save to a copy.
		if (!$isNew && $canDo->get('core.create')) {
			JToolBarHelper::custom('article.save2copy', 'copy.png', 'copy_f2.png', 'JToolbar_Save_as_Copy', false);
		}

		// If not checked out, can save the item.
		if (!$checkedOut && $canDo->get('core.edit'))
		{
			JToolBarHelper::save('article.save');
			JToolBarHelper::apply('article.apply');
			JToolBarHelper::custom('article.save2new', 'new.png', 'new_f2.png', 'JToolbar_Save_and_new', false);
		}
		if (empty($this->item->id))  {
			JToolBarHelper::cancel('article.cancel');
		}
		else {
			JToolBarHelper::cancel('article.cancel', 'JToolbar_Close');
		}

		JToolBarHelper::divider();
		JToolBarHelper::help('screen.content.article');
	}
}