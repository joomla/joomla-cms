<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Weblink controller class.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_weblinks
 * @since       1.6
 */
class WeblinksControllerWeblink extends JControllerForm
{
	/**
	 * Method override to check if you can add a new record.
	 *
	 * @param   array  $data  An array of input data.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	protected function allowAdd($data = array())
	{
		$user = JFactory::getUser();
		$categoryId = JArrayHelper::getValue($data, 'catid', $this->input->getInt('filter_category_id'), 'int');
		$allow = null;

		if ($categoryId)
		{
			// If the category has been passed in the URL check it.
			$allow = $user->authorise('core.create', $this->option . '.category.' . $categoryId);
		}

		if ($allow === null)
		{
			// In the absense of better information, revert to the component permissions.
			return parent::allowAdd($data);
		}
		else
		{
			return $allow;
		}
	}

	/**
	 * Method to check if you can add a new record.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key.
	 *
	 * @return  boolean
	 * @since   1.6
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		$recordId = (int) isset($data[$key]) ? $data[$key] : 0;
		$categoryId = 0;

		if ($recordId)
		{
			$categoryId = (int) $this->getModel()->getItem($recordId)->catid;
		}

		if ($categoryId)
		{
			// The category has been set. Check the category permissions.
			return JFactory::getUser()->authorise('core.edit', $this->option . '.category.' . $categoryId);
		}
		else
		{
			// Since there is no asset tracking, revert to the component permissions.
			return parent::allowEdit($data, $key);
		}
	}

	/**
	 * Method to run batch operations.
	 *
	 * @param   object  $model  The model.
	 *
	 * @return  boolean   True if successful, false otherwise and internal error is set.
	 *
	 * @since   1.7
	 */
	public function batch($model = null)
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Set the model
		$model = $this->getModel('Weblink', '', array());

		// Preset the redirect
		$this->setRedirect(JRoute::_('index.php?option=com_weblinks&view=weblinks' . $this->getRedirectToListAppend(), false));

		return parent::batch($model);
	}

	/**
	 * Function that allows child controller access to model data after the data has been saved.
	 *
	 * @param   JModelLegacy  $model      The data model object.
	 * @param   array         $validData  The validated data.
	 *
	 * @return	void
	 * @since	1.6
	 */
	protected function postSaveHook(JModelLegacy $model, $validData = array())
	{
		$task = $this->getTask();

		$item = $model->getItem();

		if (isset($item->params) && is_array($item->params))
		{
			$registry = new JRegistry;
			$registry->loadArray($item->params);
			$item->params = (string) $registry;
		}
		if (isset($item->images) && is_array($item->images))
		{
			$registry = new JRegistry;
			$registry->loadArray($item->images);
			$item->images = (string) $registry;
		}
		if (isset($item->metadata) && is_array($item->metadata))
		{
			$registry = new JRegistry;
			$registry->loadArray($item->metadata);
			$item->metadata = (string) $registry;
		}
		$id =  $item->id;

		$fieldMap = Array(
			'core_title' => "'" . $item->title . "'",
			'core_alias' => "'" . $item->alias . "'",
			'core_body' => "'" . $item->articletext . "'",
			'core_state' => $item->state,
			'core_checked_out_user_id' => $item->checked_out,
			'core_checked_out_time' => "'" . $item->checked_out_time  . "'",
			'core_access' => $item->access,
			'core_params' => "'" . $item->params . "'",
			'core_featured' => $item->featured,
			'core_metadata' => "'" . $item->metadata . "'",
			'core_created_user_id' => $item->created_by,
			'core_created_by_alias' => "'" . $item->created_by_alias . "'" ,
			'core_created_time' => "'" . $item->created  . "'",
			'core_modified_user_id' => $item->modified_by,
			'core_modified_time' => "'" . $item->modified  . "'",
			'core_language' => "'" . $item->language . "'",
			'core_publish_up' => "'" . $item->publish_up . "'",
			'core_publish_down' => "'" . $item->publish_down . "'",
			'core_content_item_id' => $item->id,
			'core_type_alias' => "'" . 'com_weblinks.weblink' . "'",
			'asset_id' => 0,
			'core_images' => "'" . $item->images . "'",
			'core_urls' => "'" . $item->url . "'",
			'core_hits' => "'" . $item->hits . "'",
			'core_version' => "'" . $item->version . "'",
			'core_ordering' => "'" . $item->ordering . "'",
			'core_metakey' => "'" . $item->metakey . "'",
			'core_metadesc' => "'" . $item->metadesc . "'",
			'core_catid' => "'" . $item->catid . "'",
			'core_xreference' => "'" . $item->xreference . "'",
		);

		$tags = $validData['tags'];

		// Store the tag data if the weblink data was saved.
		if ($tags )
		{
			$tagsHelper = new JTags;
			$tagsHelper->tagItem($id, 'com_weblinks.weblink', $tags, $fieldMap, $isNew);
		}

		if ($task == 'save')
		{
			$this->setRedirect(JRoute::_('index.php?option=com_weblinks&view=weblinks', false));
		}
	}
}
