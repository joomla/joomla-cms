<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * HTML View class for the Contacts component
 *
 * @package     Joomla.Site
 * @subpackage  com_contact
 * @since       1.5
 */
class ContactViewCategory extends JViewCategory
{
	/*
	 * @var string  The name of the extension for the category
	*/
	protected  $extension = 'com_contact';

	/*
	 * @var  string  Default title to use for page title
	*/
	protected  $defaultPageTitle = 'COM_CONTACT_DEFAULT_PAGE_TITLE';

	/*
	 * @var string  The name of the view to link individual items to
	*/
	protected $viewName = 'contact';


	public function display($tpl = null)
	{
		parent::commonCategoryDisplay();

		// Prepare the data.
		// Compute the contact slug.
		foreach ($this->items as $item)
		{
			$item->slug	= $item->alias ? ($item->id.':'.$item->alias) : $item->id;
			$temp		= new JRegistry;
			$temp->loadString($item->params);
			$item->params = clone($this->params);
			$item->params->merge($temp);

			if ($item->params->get('show_email', 0) == 1)
			{
				$item->email_to = trim($item->email_to);

				if (!empty($item->email_to) && JMailHelper::isEmailAddress($item->email_to))
				{
					$item->email_to = JHtml::_('email.cloak', $item->email_to);
				}
				else {
					$item->email_to = '';
				}
			}
		}

		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 */
	protected function _prepareDocument()
	{

		parent::_prepareDocument();

		$menu = $this->menu;
		$id = (int) @$menu->query['id'];
		if ($menu && ($menu->query['option'] != $this->extension || $menu->query['view'] == $this->viewName || $id != $this->category->id))
		{
			$path = array(array('title' => $this->category->title, 'link' => ''));
			$category = $this->category->getParent();

			while (($menu->query['option'] != 'com_contact' || $menu->query['view'] == 'contact' || $id != $category->id) && $category->id > 1)
			{
				$path[] = array('title' => $category->title, 'link' => ContactHelperRoute::getCategoryRoute($category->id));
				$category = $category->getParent();
			}

			$path = array_reverse($path);

			foreach ($path as $item)
			{
				$this->pathway->addItem($item['title'], $item['link']);
			}
		}

		parent::addFeed();
	}
}
