<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

require_once JPATH_COMPONENT_ADMINISTRATOR.'/models/weblink.php';

/**
 * Weblinks model.
 *
 * @package     Joomla.Site
 * @subpackage  com_weblinks
 * @since       1.6
 */
class WeblinksModelForm extends WeblinksModelWeblink
{
	/**
	 * Get the return URL.
	 *
	 * @return  string	The return URL.
	 * @since   1.6
	 */
	public function getReturnPage()
	{
		return base64_encode($this->getState('return_page'));
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since   1.6
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication();

		// Load state from the request.
		$pk = $app->input->getInt('w_id');
		$this->setState('weblink.id', $pk);
		// Add compatibility variable for default naming conventions.
		$this->setState('form.id', $pk);

		$categoryId	= $app->input->getInt('catid');
		$this->setState('weblink.catid', $categoryId);

		$return = $app->input->get('return', null, 'base64');

		if (!JUri::isInternal(base64_decode($return)))
		{
			$return = null;
		}

		$this->setState('return_page', base64_decode($return));

		// Load the parameters.
		$params	= $app->getParams();
		$this->setState('params', $params);

		$this->setState('layout', $app->input->get('layout'));
	}

	/**
	 * Method to get a single record.
	 *
	 * @param	integer	The id of the primary key.
	 *
	 * @return	mixed	Object on success, false on failure.
	 * @since	1.6
	 */
	public function getItem($pk = null)
	{

		if ($item = parent::getItem($pk))
		{
			// Convert the params field to an array.
			$registry = new JRegistry;
			$registry->loadString($item->metadata);
			$item->metadata = $registry->toArray();

			// Convert the images field to an array.
			$registry = new JRegistry;
			$registry->loadString($item->images);
			$item->images = $registry->toArray();

			if (!empty($item->id))
			{
				$item->tags = new JTags;
				$item->tags->getTagIds($item->id, 'com_weblinks.weblink');
			}
		}

		return $item;
	}
}
