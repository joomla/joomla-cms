<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Single item model for a contact
 *
 * @package     Joomla.Site
 * @subpackage  com_contact
 * @since       1.5
 */
class ContactModelContact extends JModelForm
{
	/**
	 * The name of the view for a single item
	 *
	 * @since   1.6
	 */
	protected $view_item = 'contact';

	/**
	 * A loaded item
	 *
	 * @since   1.6
	 */
	protected $_item = null;

	/**
	 * Model context string.
	 *
	 * @var		string
	 */
	protected $_context = 'com_contact.contact';

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication('site');

		// Load state from the request.
		$pk = $app->input->getInt('id');
		$this->setState('contact.id', $pk);

		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);

		$user = JFactory::getUser();

		if ((!$user->authorise('core.edit.state', 'com_contact')) &&  (!$user->authorise('core.edit', 'com_contact')))
		{
			$this->setState('filter.published', 1);
			$this->setState('filter.archived', 2);
		}
	}

	/**
	 * Method to get the contact form.
	 * The base form is loaded from XML and then an event is fired
	 *
	 * @param   array    $data      An optional array of data for the form to interrogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  JForm  A JForm object on success, false on failure
	 *
	 * @since   1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_contact.contact', 'contact', array('control' => 'jform', 'load_data' => true));

		if (empty($form))
		{
			return false;
		}

		$id = $this->getState('contact.id');
		$params = $this->getState('params');
		$contact = $this->_item[$id];
		$params->merge($contact->params);

		if (!$params->get('show_email_copy', 0))
		{
			$form->removeField('contact_email_copy');
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  array    The default data is an empty array.
	 *
	 * @since   1.6.2
	 */
	protected function loadFormData()
	{
		$data = (array) JFactory::getApplication()->getUserState('com_contact.contact.data', array());

		$this->preprocessData('com_contact.contact', $data);

		return $data;
	}

	/**
	 * Gets a contact
	 *
	 * @param   integer  $pk  Id for the contact
	 *
	 * @return  mixed Object or null
	 *
	 * @since   1.6.0
	 */
	public function getItem($pk = null)
	{
		$pk = $pk ?: (int) $this->getState('contact.id');

		if ($this->_item === null)
		{
			$this->_item = array();
		}

		if (!isset($this->_item[$pk]))
		{
			try
			{
				$db    = $this->getDbo();
				$query = $db->getQuery(true);

				$query->select($this->getState('item.select', 'a.*'))
					->select($this->getSlugColumn($query, 'a.id', 'a.alias') . ' AS slug')
					->select($this->getSlugColumn($query, 'c.id', 'c.alias') . ' AS catslug')
					->from($db->quoteName('#__contact_details', 'a'))

					// Join on category table.
					->select('c.title AS category_title, c.alias AS category_alias, c.access AS category_access')
					->leftJoin($db->quoteName('#__categories', 'c') . ' ON c.id = a.catid')


					// Join over the categories to get parent category titles
					->select('parent.title AS parent_title, parent.id AS parent_id, parent.path AS parent_route, parent.alias AS parent_alias')
					->leftJoin($db->quoteName('#__categories', 'parent') . ' ON parent.id = c.parent_id')

					->where('a.id = ' . (int) $pk);

				// Filter by start and end dates.
				$nullDate = $db->quote($db->getNullDate());
				$nowDate = $db->quote(JFactory::getDate()->toSql());

				// Filter by published state.
				$published = $this->getState('filter.published');
				$archived = $this->getState('filter.archived');
				if (is_numeric($published))
				{
					$query->where('(a.published = ' . (int) $published . ' OR a.published =' . (int) $archived . ')')
						->where('(a.publish_up = ' . $nullDate . ' OR a.publish_up <= ' . $nowDate . ')')
						->where('(a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')');
				}

				$db->setQuery($query);
				$data = $db->loadObject();

				if (empty($data))
				{
					JError::raiseError(404, JText::_('COM_CONTACT_ERROR_CONTACT_NOT_FOUND'));
				}

				// Check for published state if filter set.
				if ((is_numeric($published) || is_numeric($archived)) && (($data->published != $published) && ($data->published != $archived)))
				{
					JError::raiseError(404, JText::_('COM_CONTACT_ERROR_CONTACT_NOT_FOUND'));
				}

				// Convert parameter fields to objects.
				$registry = new Registry($data->params);
				$data->params = clone $this->getState('params');
				$data->params->merge($registry);

				$registry = new Registry($data->metadata);
				$data->metadata = $registry;

				$data->tags = new JHelperTags;
				$data->tags->getItemTags('com_contact.contact', $data->id);

				// Compute access permissions.
				if ($access = $this->getState('filter.access'))
				{

					// If the access filter has been set, we already know this user can view.
					$data->params->set('access-view', true);
				}
				else
				{
					// If no access filter is set, the layout takes some responsibility for display of limited information.
					$user = JFactory::getUser();
					$groups = $user->getAuthorisedViewLevels();

					if ($data->catid == 0 || $data->category_access === null)
					{
						$data->params->set('access-view', in_array($data->access, $groups));
					}
					else
					{
						$data->params->set('access-view', in_array($data->access, $groups) && in_array($data->category_access, $groups));
					}
				}

				$this->_item[$pk] = $data;
			}
			catch (Exception $e)
			{
				$this->setError($e);
				$this->_item[$pk] = false;
			}
		}

		if ($this->_item[$pk])
		{
			$this->buildContactExtendedData($this->_item[$pk]);
		}

		return $this->_item[$pk];
	}

	/**
	 * Load extended data (profile, articles) for a contact
	 *
	 * @param   object  $contact  The contact object
	 *
	 * @return  void
	 */
	protected function buildContactExtendedData($contact)
	{
		$db        = $this->getDbo();
		$nullDate  = $db->quote($db->getNullDate());
		$nowDate   = $db->quote(JFactory::getDate()->toSql());
		$user      = JFactory::getUser();
		$groups    = implode(',', $user->getAuthorisedViewLevels());
		$published = $this->getState('filter.published');
		$query     = $db->getQuery(true);

		// If we are showing a contact list, then the contact parameters take priority
		// So merge the contact parameters with the merged parameters
		if ($this->getState('params')->get('show_contact_list'))
		{
			$this->getState('params')->merge($contact->params);
		}

		// Get the com_content articles by the linked user
		if ((int) $contact->user_id && $this->getState('params')->get('show_articles'))
		{

			$query->select('a.id')
				->select('a.title')
				->select('a.state')
				->select('a.access')
				->select('a.catid')
				->select('a.created')
				->select('a.language')
				->select($this->getSlugColumn($query, 'a.id', 'a.alias') . ' AS slug')
				->select($this->getSlugColumn($query, 'c.id', 'c.alias') . ' AS catslug')
				->from($db->quoteName('#__content', 'a'))
				->leftJoin($db->quoteName('#__categories', 'c') . ' ON a.catid = c.id')
				->where('a.created_by = ' . (int) $contact->user_id)
				->where('a.access IN (' . $groups . ')')
				->order('a.state DESC, a.created DESC');

			// Filter per language if plugin published
			if (JLanguageMultilang::isEnabled())
			{
				$query->where('a.language IN (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
			}

			if (is_numeric($published))
			{
				$query->where('a.state IN (1,2)')
					->where('(a.publish_up = ' . $nullDate . ' OR a.publish_up <= ' . $nowDate . ')')
					->where('(a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')');
			}

			// Number of articles to display from config/menu params
			$articles_display_num = $this->getState('params')->get('articles_display_num', 10);

			// Use contact setting?
			if ($articles_display_num === 'use_contact')
			{
				$articles_display_num = $contact->params->get('articles_display_num', 10);

				// Use global?
				if ((string) $articles_display_num === '')
				{
					$articles_display_num = JComponentHelper::getParams('com_contact')->get('articles_display_num', 10);
				}
			}

			$db->setQuery($query, 0, (int) $articles_display_num);
			$contact->articles = $db->loadObjectList();
		}
		else
		{
			$contact->articles = null;
		}

		// Get the profile information for the linked user
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_users/models', 'UsersModel');
		$userModel = JModelLegacy::getInstance('User', 'UsersModel', array('ignore_request' => true));
		$data = $userModel->getItem((int) $contact->user_id);

		JPluginHelper::importPlugin('user');
		$form = new JForm('com_users.profile');

		// Trigger the form preparation event.
		JFactory::getApplication()->triggerEvent('onContentPrepareForm', array($form, $data));

		// Trigger the data preparation event.
		JFactory::getApplication()->triggerEvent('onContentPrepareData', array('com_users.profile', $data));

		// Load the data into the form after the plugins have operated.
		$form->bind($data);
		$contact->profile = $form;
	}

	/**
	* Generate column expression for slug or catslug.
	*
	* @param   JDatabaseQuery  $query  Current query instance.
	* @param   string          $id     Column id name.
	* @param   string          $alias  Column alias name.
	*
	* @return  string
	*
	* @since   __DEPLOY_VERSION__
	*/
	private function getSlugColumn($query, $id, $alias)
	{
		return 'CASE WHEN '
			. $query->charLength($alias, '!=', '0')
			. ' THEN '
			. $query->concatenate(array($query->castAsChar($id), $alias), ':')
			. ' ELSE '
			. $id . ' END';
	}

	/**
	 * Gets the query to load a contact item
	 *
	 * @param   integer  $pk  The item to be loaded
	 *
	 * @return  mixed    The contact object on success, false on failure
	 *
	 * @throws  Exception  On database failure
	 */
	protected function getContactQuery($pk = null)
	{
		// @todo Cache on the fingerprint of the arguments
		$db       = $this->getDbo();
		$nullDate = $db->quote($db->getNullDate());
		$nowDate  = $db->quote(JFactory::getDate()->toSql());
		$user     = JFactory::getUser();
		$pk       = $pk ?: (int) $this->getState('contact.id');
		$query    = $db->getQuery(true);

		if ($pk)
		{
			$query->select('a.*')
				->select('cc.access AS category_access, cc.title AS category_name')
				->select($this->getSlugColumn($query, 'a.id', 'a.alias') . ' AS slug')
				->select($this->getSlugColumn($query, 'cc.id', 'cc.alias') . ' AS catslug')
				->from($db->quoteName('#__contact_details', 'a'))
				->innerJoin($db->quoteName('#__categories', 'cc') . ' ON cc.id = a.catid')
				->where('a.id = ' . (int) $pk);

			$published = $this->getState('filter.published');

			if (is_numeric($published))
			{
				$query->where('a.published IN (1,2)')
					->where('cc.published IN (1,2)');
			}

			$groups = implode(',', $user->getAuthorisedViewLevels());
			$query->where('a.access IN (' . $groups . ')');

			try
			{
				$db->setQuery($query);
				$result = $db->loadObject();

				if (empty($result))
				{
					return false;
				}
			}
			catch (Exception $e)
			{
				$this->setError($e->getMessage());

				return false;
			}

			if ($result)
			{

				$contactParams = new Registry($result->params);

				// If we are showing a contact list, then the contact parameters take priority
				// So merge the contact parameters with the merged parameters
				if ($this->getState('params')->get('show_contact_list'))
				{
					$this->getState('params')->merge($contactParams);
				}

				// Get the com_content articles by the linked user
				if ((int) $result->user_id && $this->getState('params')->get('show_articles'))
				{
					$query->clear()
						->select('a.id')
						->select('a.title')
						->select('a.state')
						->select('a.access')
						->select('a.catid')
						->select('a.created')
						->select('a.language')
						->select($this->getSlugColumn($query, 'a.id', 'a.alias') . ' AS slug')
						->select($this->getSlugColumn($query, 'c.id', 'c.alias') . ' AS catslug')
						->from($db->quoteName('#__content', 'a'))
						->leftJoin($db->quoteName('#__categories', 'c') . ' ON a.catid = c.id')
						->where('a.created_by = ' . (int) $result->user_id)
						->where('a.access IN (' . $groups . ')')
						->order('a.state DESC, a.created DESC');

					// Filter per language if plugin published
					if (JLanguageMultilang::isEnabled())
					{
						$query->where('a.language IN (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
					}

					if (is_numeric($published))
					{
						$query->where('a.state IN (1,2)')
							->where('(a.publish_up = ' . $nullDate . ' OR a.publish_up <= ' . $nowDate . ')')
							->where('(a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')');
					}

					// Number of articles to display from config/menu params
					$articles_display_num = $this->getState('params')->get('articles_display_num', 10);

					// Use contact setting?
					if ($articles_display_num === 'use_contact')
					{
						$articles_display_num = $contactParams->get('articles_display_num', 10);

						// Use global?
						if ((string) $articles_display_num === '')
						{
							$articles_display_num = JComponentHelper::getParams('com_contact')->get('articles_display_num', 10);
						}
					}

					$db->setQuery($query, 0, (int) $articles_display_num);
					$result->articles = $db->loadObjectList();
				}
				else
				{
					$result->articles = null;
				}

				// Get the profile information for the linked user
				JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_users/models', 'UsersModel');
				$userModel = JModelLegacy::getInstance('User', 'UsersModel', array('ignore_request' => true));
				$data = $userModel->getItem((int) $result->user_id);

				JPluginHelper::importPlugin('user');
				$form = new JForm('com_users.profile');

				// Trigger the form preparation event.
				JFactory::getApplication()->triggerEvent('onContentPrepareForm', array($form, $data));

				// Trigger the data preparation event.
				JFactory::getApplication()->triggerEvent('onContentPrepareData', array('com_users.profile', $data));

				// Load the data into the form after the plugins have operated.
				$form->bind($data);
				$result->profile = $form;
				$this->contact = $result;

				return $result;
			}
		}

		return false;
	}

	/**
	 * Increment the hit counter for the contact.
	 *
	 * @param   integer  $pk  Optional primary key of the contact to increment.
	 *
	 * @return  boolean  True if successful; false otherwise and internal error set.
	 *
	 * @since   3.0
	 */
	public function hit($pk = 0)
	{
		$input = JFactory::getApplication()->input;
		$hitcount = $input->getInt('hitcount', 1);

		if ($hitcount)
		{
			$pk = $pk ?: (int) $this->getState('contact.id');

			$table = JTable::getInstance('Contact', 'ContactTable');
			$table->load($pk);
			$table->hit($pk);
		}

		return true;
	}
}
