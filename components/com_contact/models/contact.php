<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_contact
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');
jimport('joomla.event.dispatcher');

/**
 * @package		Joomla.Site
 * @subpackage	com_contact
 * @since 1.5
 */
class ContactModelContact extends JModelForm
{
	/**
	 * @since	1.6
	 */
	protected $view_item = 'contact';

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
	 * @since	1.6
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication('site');

		// Load state from the request.
		$pk = JRequest::getInt('id');
		$this->setState('contact.id', $pk);

		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);

		$user = JFactory::getUser();
		if ((!$user->authorise('core.edit.state', 'com_contact')) &&  (!$user->authorise('core.edit', 'com_contact'))){
			$this->setState('filter.published', 1);
			$this->setState('filter.archived', 2);
		}
	}

	/**
	 * Method to get the contact form.
	 *
	 * The base form is loaded from XML and then an event is fired
	 *
	 *
	 * @param	array	$data		An optional array of data for the form to interrogate.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	JForm	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_contact.contact', 'contact', array('control' => 'jform', 'load_data' => true));
		if (empty($form)) {
			return false;
		}

		$id = $this->getState('contact.id');
		$params = $this->getState('params');
		$contact = $this->_item[$id];
		$params->merge($contact->params);

		if(!$params->get('show_email_copy', 0)){
			$form->removeField('contact_email_copy');
		}

		return $form;
	}

	protected function loadFormData()
	{
		$data = (array)JFactory::getApplication()->getUserState('com_contact.contact.data', array());
		return $data;
	}

	/**
	 * Gets a list of contacts
	 * @param array
	 * @return mixed Object or null
	 */
	public function &getItem($pk = null)
	{
		// Initialise variables.
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('contact.id');

		if ($this->_item === null) {
			$this->_item = array();
		}

		if (!isset($this->_item[$pk])) {
			try
			{
				$db = $this->getDbo();
				$query = $db->getQuery(true);

				//sqlsrv changes
				$case_when = ' CASE WHEN ';
				$case_when .= $query->charLength('a.alias');
				$case_when .= ' THEN ';
				$a_id = $query->castAsChar('a.id');
				$case_when .= $query->concatenate(array($a_id, 'a.alias'), ':');
				$case_when .= ' ELSE ';
				$case_when .= $a_id.' END as slug';

				$case_when1 = ' CASE WHEN ';
				$case_when1 .= $query->charLength('c.alias');
				$case_when1 .= ' THEN ';
				$c_id = $query->castAsChar('c.id');
				$case_when1 .= $query->concatenate(array($c_id, 'c.alias'), ':');
				$case_when1 .= ' ELSE ';
				$case_when1 .= $c_id.' END as catslug';

				$query->select($this->getState('item.select', 'a.*') . ','.$case_when.','.$case_when1);
				$query->from('#__contact_details AS a');

				// Join on category table.
				$query->select('c.title AS category_title, c.alias AS category_alias, c.access AS category_access');
				$query->join('LEFT', '#__categories AS c on c.id = a.catid');


				// Join over the categories to get parent category titles
				$query->select('parent.title as parent_title, parent.id as parent_id, parent.path as parent_route, parent.alias as parent_alias');
				$query->join('LEFT', '#__categories as parent ON parent.id = c.parent_id');

				$query->where('a.id = ' . (int) $pk);

				// Filter by start and end dates.
				$nullDate = $db->Quote($db->getNullDate());
				$nowDate = $db->Quote(JFactory::getDate()->toSql());

				// Filter by published state.
				$published = $this->getState('filter.published');
				$archived = $this->getState('filter.archived');
				if (is_numeric($published)) {
					$query->where('(a.published = ' . (int) $published . ' OR a.published =' . (int) $archived . ')');
					$query->where('(a.publish_up = ' . $nullDate . ' OR a.publish_up <= ' . $nowDate . ')');
					$query->where('(a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')');
				}

				$db->setQuery($query);

				$data = $db->loadObject();

				if ($error = $db->getErrorMsg()) {
					throw new JException($error);
				}

				if (empty($data)) {
					throw new JException(JText::_('COM_CONTACT_ERROR_CONTACT_NOT_FOUND'), 404);
				}

				// Check for published state if filter set.
				if (((is_numeric($published)) || (is_numeric($archived))) && (($data->published != $published) && ($data->published != $archived)))
				{
					JError::raiseError(404, JText::_('COM_CONTACT_ERROR_CONTACT_NOT_FOUND'));
				}

				// Convert parameter fields to objects.
				$registry = new JRegistry;
				$registry->loadString($data->params);
				$data->params = clone $this->getState('params');
				$data->params->merge($registry);

				$registry = new JRegistry;
				$registry->loadString($data->metadata);
				$data->metadata = $registry;

				// Compute access permissions.
				if ($access = $this->getState('filter.access')) {
					// If the access filter has been set, we already know this user can view.
					$data->params->set('access-view', true);
				}
				else {
					// If no access filter is set, the layout takes some responsibility for display of limited information.
					$user = JFactory::getUser();
					$groups = $user->getAuthorisedViewLevels();

					if ($data->catid == 0 || $data->category_access === null) {
						$data->params->set('access-view', in_array($data->access, $groups));
					}
					else {
						$data->params->set('access-view', in_array($data->access, $groups) && in_array($data->category_access, $groups));
					}
				}

				$this->_item[$pk] = $data;
			}
			catch (JException $e)
			{
				$this->setError($e);
				$this->_item[$pk] = false;
			}

		}

		if ($this->_item[$pk])
		{
			if ($extendedData = $this->getContactQuery($pk)) {
				$this->_item[$pk]->articles = $extendedData->articles;
				$this->_item[$pk]->profile = $extendedData->profile;
			}
		}
  		return $this->_item[$pk];

	}

	protected function getContactQuery($pk = null)
	{
		// TODO: Cache on the fingerprint of the arguments
		$db		= $this->getDbo();
		$user	= JFactory::getUser();
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('contact.id');

		$query	= $db->getQuery(true);
		if ($pk) {
			//sqlsrv changes
			$case_when = ' CASE WHEN ';
			$case_when .= $query->charLength('a.alias');
			$case_when .= ' THEN ';
			$a_id = $query->castAsChar('a.id');
			$case_when .= $query->concatenate(array($a_id, 'a.alias'), ':');
			$case_when .= ' ELSE ';
			$case_when .= $a_id.' END as slug';

			$case_when1 = ' CASE WHEN ';
			$case_when1 .= $query->charLength('cc.alias');
			$case_when1 .= ' THEN ';
			$c_id = $query->castAsChar('cc.id');
			$case_when1 .= $query->concatenate(array($c_id, 'cc.alias'), ':');
			$case_when1 .= ' ELSE ';
			$case_when1 .= $c_id.' END as catslug';
			$query->select('a.*, cc.access as category_access, cc.title as category_name, '
			.$case_when.','.$case_when1);

			$query->from('#__contact_details AS a');

			$query->join('INNER', '#__categories AS cc on cc.id = a.catid');

			$query->where('a.id = ' . (int) $pk);
			$published = $this->getState('filter.published');
			$archived = $this->getState('filter.archived');
			if (is_numeric($published)) {
				$query->where('a.published IN (1,2)');
				$query->where('cc.published IN (1,2)');
			}
			$groups = implode(',', $user->getAuthorisedViewLevels());
			$query->where('a.access IN ('.$groups.')');

			try {
				$db->setQuery($query);
				$result = $db->loadObject();

				if ($error = $db->getErrorMsg()) {
					throw new Exception($error);
				}

				if (empty($result)) {
						throw new JException(JText::_('COM_CONTACT_ERROR_CONTACT_NOT_FOUND'), 404);
				}

			// If we are showing a contact list, then the contact parameters take priority
			// So merge the contact parameters with the merged parameters
				if ($this->getState('params')->get('show_contact_list')) {
					$registry = new JRegistry;
					$registry->loadString($result->params);
					$this->getState('params')->merge($registry);
				}
			} catch (Exception $e) {
				$this->setError($e);
				return false;
			}

			if ($result) {
				$user	= JFactory::getUser();
				$groups	= implode(',', $user->getAuthorisedViewLevels());

				//get the content by the linked user
				$query	= $db->getQuery(true);
				$query->select('a.id');
				$query->select('a.title');
				$query->select('a.state');
				$query->select('a.access');
				$query->select('a.created');

				// SQL Server changes
				$case_when = ' CASE WHEN ';
				$case_when .= $query->charLength('a.alias');
				$case_when .= ' THEN ';
				$a_id = $query->castAsChar('a.id');
				$case_when .= $query->concatenate(array($a_id, 'a.alias'), ':');
				$case_when .= ' ELSE ';
				$case_when .= $a_id.' END as slug';
				$case_when1 = ' CASE WHEN ';
				$case_when1 .= $query->charLength('c.alias');
				$case_when1 .= ' THEN ';
				$c_id = $query->castAsChar('c.id');
				$case_when1 .= $query->concatenate(array($c_id, 'c.alias'), ':');
				$case_when1 .= ' ELSE ';
				$case_when1 .= $c_id.' END as catslug';
				$query->select($case_when1 . ',' . $case_when);

				$query->from('#__content as a');
				$query->leftJoin('#__categories as c on a.catid=c.id');
				$query->where('a.created_by = '.(int)$result->user_id);
				$query->where('a.access IN ('. $groups.')');
				$query->order('a.state DESC, a.created DESC');
				// filter per language if plugin published
				if (JFactory::getApplication()->getLanguageFilter()) {
					$query->where('a.language='.$db->quote(JFactory::getLanguage()->getTag()).' OR a.language='.$db->quote('*'));
				}
				if (is_numeric($published)) {
					$query->where('a.state IN (1,2)');
				}
				$db->setQuery($query, 0, 10);
				$articles = $db->loadObjectList();
				$result->articles = $articles;

				//get the profile information for the linked user
				require_once JPATH_ADMINISTRATOR.'/components/com_users/models/user.php';
				$userModel = JModelLegacy::getInstance('User', 'UsersModel', array('ignore_request' => true));
					$data = $userModel->getItem((int)$result->user_id);

				JPluginHelper::importPlugin('user');
				$form = new JForm('com_users.profile');
				// Get the dispatcher.
				$dispatcher	= JDispatcher::getInstance();

				// Trigger the form preparation event.
				$dispatcher->trigger('onContentPrepareForm', array($form, $data));
				// Trigger the data preparation event.
				$dispatcher->trigger('onContentPrepareData', array('com_users.profile', $data));

				// Load the data into the form after the plugins have operated.
				$form->bind($data);
				$result->profile = $form;

				$this->contact = $result;
				return $result;
			}
		}
	}
}
