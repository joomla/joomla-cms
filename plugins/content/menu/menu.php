<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.menu
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

JLoader::import('joomla.application.component.model');

/**
 * Menu Plugin
 *
 * @since  3.6
 */
class PlgContentMenu extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.6
	 */
	protected $autoloadLanguage = true;

	/**
	 * Adds additional fields to the editing form
	 *
	 * @param   JForm  $form  The form to be altered.
	 * @param   mixed  $data  The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @since   3.6
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

		if (!in_array($name, array('com_content.article','com_contact.contact')))
		{
			return true;
		}

		$app = JFactory::getApplication();

		if ($app->isAdmin())
		{
			// Add the fields to the form.
			JForm::addFormPath(__DIR__ . '/forms');
			$form->loadFile('menu', false);
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
	 * @since   3.6
	 */

	public function onContentBeforeSave($context,$article)
	{
		$session =& JFactory::getSession();
		$session->set("formData", JFactory::getApplication()->input->post->get('jform', array(), 'array'));

		return true;
	}

	/**
	 * Adds additional fields to the editing form
	 *
	 * @param   JForm  $context  The form to be altered.
	 * @param   mixed  $article  The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @since   3.6
	 */

	public function onContentAfterSave($context,$article)
	{
		$session =& JFactory::getSession();
		$data = $session->get("formData");

		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_menus/models', 'MenusModel');
		$itemModel = JModelAdmin::getInstance('Item', 'MenusModel');
		$itemModel->addTablePath(JPATH_ADMINISTRATOR . '/components/com_menus/tables');
		$str = (explode(".", $context));

		$menuData = array(
			'id' => $data['menuid'],
			'menutype' => $data['menutype'],
			'title' => $data['menutitle'],
			'alias' => $data['menualias'],
			'link' => 'index.php?option=' . $str[0] . '&view=' . $str[1] . '&id=' . $article->id,
			'type' => 'component',
			'published' => 1,
			'parent_id' => $data['parent_id'],
			'level' => 1,
			'component_id' => JComponentHelper::getComponent($str[0])->id,
			'browserNav' => 0,
			'access' => 1,
			'template_style_id' => 0,
			'home' => 0,
			'language' => $data['language'],
			'client_id' => 0
		);

		$itemModel->save($menuData);

		return true;
	}
}
