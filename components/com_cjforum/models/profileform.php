<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

// Base this model on the backend version.
require_once JPATH_ADMINISTRATOR . '/components/com_cjforum/models/profile.php';

class CjForumModelProfileForm extends CjForumModelProfile
{
	public $typeAlias = 'com_cjforum.profile';

	protected function populateState ()
	{
		$app = JFactory::getApplication();
		
		// Load state from the request.
		$pk = $app->input->getInt('p_id');
		$this->setState('profile.id', $pk);
		
		$return = $app->input->get('return', null, 'base64');
		if(empty($return) && $pk)
		{
			$return = base64_encode(CjForumHelperRoute::getProfileRoute($pk));
		}
		
		$this->setState('return_page', base64_decode($return));
		
		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);
		
		$this->setState('layout', $app->input->getString('layout'));
	}

	public function getItem ($itemId = null)
	{
		$itemId = (int) (! empty($itemId)) ? $itemId : $this->getState('profile.id');
		
		// Get a row instance.
		$table = $this->getTable();
		
		// Attempt to load the row.
		$return = $table->load($itemId);
		
		// Check for a table object error.
		if ($return === false && $table->getError())
		{
			$this->setError($table->getError());
			
			return false;
		}
		
		$properties = $table->getProperties(1);
		$value = JArrayHelper::toObject($properties, 'JObject');
		
		// Convert attrib field to Registry.
		$value->params = new JRegistry();
		$value->params->loadString($value->attribs);
		
		// Compute selected asset permissions.
		$user = JFactory::getUser();
		$userId = $user->get('id');
		$asset = 'com_cjforum';
		
		// Check general edit permission first.
		if ($user->authorise('core.edit', $asset))
		{
			$value->params->set('access-edit', true);
		}
		
		// Now check if edit.own is available.
		elseif (! empty($userId) && $user->authorise('core.edit.own', $asset))
		{
			// Check for a valid user and that they are the owner.
			if ($userId == $value->id)
			{
				$value->params->set('access-edit', true);
			}
		}
		
		// Check edit state permission.
		if ($itemId)
		{
			// Existing item
			$value->params->set('access-change', $user->authorise('core.edit.state', $asset));
		}
		else
		{
			$value->params->set('access-change', $user->authorise('core.edit.state', 'com_cjforum'));
		}
		
		$value->params->set('access-admin', $user->authorise('core.admin', 'com_cjforum'));

		// Convert the params field to an array.
		$registry = new JRegistry();
		$registry->loadString($value->attribs);
		$value->attribs = $registry->toArray();
		
		// Convert the metadata field to an array.
		$registry = new JRegistry();
		$registry->loadString($value->metadata);
		$value->metadata = $registry->toArray();
		
		return $value;
	}

	public function getReturnPage ()
	{
		return base64_encode($this->getState('return_page'));
	}
}
