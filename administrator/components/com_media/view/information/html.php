<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * View to edit an article.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_content
 * @since       1.6
 */
class MediaViewInformationHtml extends JViewHtml
{
	protected $form;

	protected $item;

	/**
	 * Display the view
	 *
	 * @return view
	 *
	 * @since 3.2
	 */
	public function render()
	{
		$this->form = $this->model->getForm();
		$this->item = $this->model->getItem();
		$this->state = $this->model->getState();
		$this->canDo = MediaHelper::getActions($this->state->get('filter.category_id'));
		$this->addToolbar();

		return parent::render();
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since   3.2
	 */
	protected function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);
		$user = JFactory::getUser();
		$userId = $user->get('id');
		$isNew = ($this->item->content_id == 0);
		$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $userId);
		$canDo = MediaHelper::getActions($this->state->get('filter.category_id'), $this->item->content_id);
		JToolbarHelper::title(JText::_('COM_MEDIA_EDIT_INFORMATION'));
		$bar = JToolBar::getInstance('toolbar');

		// Built the actions for new and existing records.

		// Can't save the record if it's checked out.
		if (!$checkedOut)
		{
			// Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
			if ($canDo->get('core.edit'))
			{
				$title = JText::_('JTOOLBAR_APPLY');
				$dhtml = "<button id='apply' class=\"btn btn-small btn-success\">
						<i class=\"icon-apply icon-white\" title=\"$title\"></i>
						$title</button>";
				$bar->appendButton('Custom', $dhtml, 'save');
				JToolbarHelper::divider();
				$title = JText::_('JTOOLBAR_SAVE');
				$dhtml = "<button id='save' class=\"btn btn-small\">
						<i class=\"icon-save\" title=\"$title\"></i>
						$title</button>";
				$bar->appendButton('Custom', $dhtml, 'save');
				JToolbarHelper::divider();
			}
		}

		$title = JText::_('JTOOLBAR_CLOSE');
		$dhtml = "<button id='close' class=\"btn btn-small\">
						<i class=\"icon-cancel\" title=\"$title\"></i>
						$title</button>";
		$bar->appendButton('Custom', $dhtml, 'close');
		JToolbarHelper::divider();
	}

	/**
	 * Return the JForm
	 *
	 * @return JForm
	 *
	 * @since   3.2
	 */
	public function getForm()
	{
		return $this->form;
	}
}
