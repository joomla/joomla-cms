<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.createmenuitem
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Createmenuitem Plugin
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgContentCreateMenuitem extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = true;

	/**
	 * Runs on content preparation
	 *
	 * @param   string  $context  The context for the data
	 * @param   object  $data     An object containing the data for the form.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onContentPrepareData($context, $data)
	{
		$checkView = preg_split("/\\r\\n|\\r|\\n/", $this->params->get('allowedContexts'));

		if (!in_array($context, $checkView))
		{
			return true;
		}

		if (!(JFactory::getApplication()->isAdmin()))
		{
			return true;
		}

		if (is_array($data))
		{
			$data = Joomla\Utilities\ArrayHelper::toObject($data, 'JObject');
		}

		$jinput    = JFactory::getApplication()->input;
		$component = $jinput->getCmd('option');
		$view      = $jinput->getCmd('view');

		$menu      = JFactory::getApplication()->getMenu('site');
		$menuItems = $menu->getItems(
			'link',
			'index.php?option=' . $component . '&view=' . $view . '&id=' . $data->id
		);

		JHtml::_('jquery.framework', false);

		$session = JFactory::getSession();
		$session->set('componentHiddenView', $jinput->get('view'));

		if (!empty($menuItems))
		{
			$data->menuid        = $menuItems[0]->id;
			$data->menutitle     = $menuItems[0]->title;
			$data->menualias     = $menuItems[0]->alias;
			$data->menutype      = $menuItems[0]->menutype;
			$data->parent_id     = $menuItems[0]->parent_id;
			$data->menuordering  = $menuItems[0]->id;
			$data->menuempty     = 0;
			$data->retrievedmenu = $menuItems[0]->title;

			JHtml::_('script', 'plg_content_createmenuitem/parentitem.js', array('version' => 'auto', 'relative' => true));
		}

		return true;
	}

	/**
	 * Adds additional fields to the editing form
	 *
	 * @param   JForm  $form  The form to be altered.
	 * @param   mixed  $data  The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onContentPrepareForm($form, $data)
	{
		if (!($form instanceof JForm))
		{
			$this->_subject->setError('JERROR_NOT_A_FORM');

			return false;
		}

		// Check we are manipulating a valid form.
		$name = $form->getName();

		$checkView = preg_split("/\\r\\n|\\r|\\n/", $this->params->get('allowedContexts'));

		if (!in_array($name, $checkView))
		{
			return true;
		}

		if (JFactory::getApplication()->isAdmin())
		{
			// Add the fields to the form.
			JForm::addFormPath(__DIR__ . '/forms');
			$form->loadFile('createmenuitem', false);
		}

		return true;
	}

	/**
	 * Sets the fields value from edit form
	 *
	 * @param   JForm  $context  The form to be altered.
	 * @param   mixed  $article  The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onContentBeforeSave($context, $article)
	{
		$session = JFactory::getSession();
		$session->set('formData', JFactory::getApplication()->input->post->get('jform', array(), 'array'));
		$table = JTable::getInstance('Menu');
		$formData = $session->get('formData');

		if ($table->load(array('title' => $formData['menutitle'])) && $formData['menuempty'] == 1)
		{
			JFactory::getApplication()->enqueueMessage(
				JText::sprintf('JLIB_DATABASE_ERROR_MENU_UNIQUE_ALIAS', $table->getErrors()),
				'error');

			return false;
		}

		if ($formData['menutitle'] != $formData['retrievedmenu'])
		{
			if ($table->load(array('title' => $formData['menutitle'])))
			{
				JFactory::getApplication()->enqueueMessage(
					JText::sprintf('JLIB_DATABASE_ERROR_MENU_UNIQUE_ALIAS', $table->getErrors()),
					'error');

				return false;
			}
		}

		return true;
	}

	/**
	 * Save the menu item in the menus table
	 *
	 * @param   JForm  $context  The form to be altered.
	 * @param   mixed  $article  The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onContentAfterSave($context, $article)
	{
		if (in_array($context, array('com_menus.item')))
		{
			return true;
		}

		$checkView = preg_split("/\\r\\n|\\r|\\n/", $this->params->get('allowedContexts'));

		if (!in_array($context, $checkView))
		{
			return true;
		}

		$session = JFactory::getSession();
		$data    = $session->get('formData');
		$session->clear('formData');

		$jinput    = JFactory::getApplication()->input;
		$component = $jinput->getCmd('option');
		$view      = $session->get('componentHiddenView');
		$session->clear('componentHiddenView');

		$menuData = array(
			'id'                => $data['menuid'],
			'menutype'          => $data['menutype'],
			'title'             => $data['menutitle'],
			'alias'             => $data['menualias'],
			'link'              => 'index.php?option=' . $component . '&view=' . $view . '&id=' . $article->id,
			'type'              => 'component',
			'published'         => 1,
			'parent_id'         => $data['parent_id'],
			'level'             => 1,
			'component_id'      => JComponentHelper::getComponent($component)->id,
			'browserNav'        => 0,
			'access'            => 1,
			'template_style_id' => 0,
			'home'              => 0,
			'language'          => $data['language'],
			'client_id'         => 0,
			'menuordering'      => $data['menuordering'],
		);

		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_menus/models', 'MenusModel');
		$itemModel = JModelAdmin::getInstance('Item', 'MenusModel');
		$itemModel->addTablePath(JPATH_ADMINISTRATOR . '/components/com_menus/tables');

		// Attempt to save the data.
		if (!empty($menuData['title']))
		{
			if (!$itemModel->save($menuData))
			{
				JFactory::getApplication()->enqueueMessage(
					JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $itemModel->getError()),
					'error'
				);

				return false;
			}
		}

		return true;
	}
}
