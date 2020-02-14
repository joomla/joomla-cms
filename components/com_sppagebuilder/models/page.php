<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2015 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

jimport('joomla.application.component.modelitem');


class SppagebuilderModelPage extends JModelItem
{

	protected $_context = 'com_sppagebuilder.page';


	protected function populateState()
	{
		$app = JFactory::getApplication('site');

		$pageId = $app->input->getInt('id');
		$this->setState('page.id', $pageId);

		$user = JFactory::getUser();

		if ((!$user->authorise('core.edit.state', 'com_sppagebuilder')) && (!$user->authorise('core.edit', 'com_sppagebuilder')))
		{
			$this->setState('filter.published', 1);
		}

		$this->setState('filter.language', JLanguageMultilang::isEnabled());
	}

	public function getItem( $pageId = null )
	{
		$user = JFactory::getUser();

		$pageId = (!empty($pageId))? $pageId : (int)$this->getState('page.id');

		if ( $this->_item == null )
		{
			$this->_item = array();
		}

		if (!isset($this->_item[$pageId]))
		{
			try
			{
				$db = $this->getDbo();
				$query = $db->getQuery(true)
					->select('a.*')
					->from('#__sppagebuilder as a')
					->where('a.id = ' . (int) $pageId);

				$query->select('l.title AS language_title')
					->leftJoin( $db->quoteName('#__languages') . ' AS l ON l.lang_code = a.language');

				$query->select('ua.name AS author_name')
					->leftJoin('#__users AS ua ON ua.id = a.created_by');

				// Filter by published state.
				$published = $this->getState('filter.published');

				if (is_numeric($published))
				{
					$query->where('a.published = ' . (int) $published);
				}
				elseif ($published === '')
				{
					$query->where('(a.published IN (0, 1))');
				}

				if ($this->getState('filter.language'))
				{
					$query->where('a.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
				}

				$db->setQuery($query);
				$data = $db->loadObject();

				if (empty($data)) {
					return JError::raiseError(404, JText::_('COM_SPPAGEBUILDER_ERROR_PAGE_NOT_FOUND'));
				}

				if ($access = $this->getState('filter.access')) {
					$data->access_view = true;
				}else{
					$user = JFactory::getUser();
					$groups = $user->getAuthorisedViewLevels();

					 $data->access_view = in_array($data->access, $groups);
				}

				$this->_item[$pageId] = $data;
			}
			catch (Exception $e)
			{
				if ($e->getCode() == 404 )
				{
					JError::raiseError(404, $e->getMessage());
				}
				else
				{
					$this->setError($e);
					$this->_item[$pageId] = false;
				}
			}
		}

		return $this->_item[$pageId];
	}

	// Get form
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_users.profile', 'profile', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		// Check for username compliance and parameter set
		$isUsernameCompliant = true;

		if ($this->loadFormData()->username)
		{
			$username = $this->loadFormData()->username;
			$isUsernameCompliant  = !(preg_match('#[<>"\'%;()&\\\\]|\\.\\./#', $username) || strlen(utf8_decode($username)) < 2
				|| trim($username) != $username);
		}

		$this->setState('user.username.compliant', $isUsernameCompliant);

		if (!JComponentHelper::getParams('com_users')->get('change_login_name') && $isUsernameCompliant)
		{
			$form->setFieldAttribute('username', 'class', '');
			$form->setFieldAttribute('username', 'filter', '');
			$form->setFieldAttribute('username', 'description', 'COM_USERS_PROFILE_NOCHANGE_USERNAME_DESC');
			$form->setFieldAttribute('username', 'validate', '');
			$form->setFieldAttribute('username', 'message', '');
			$form->setFieldAttribute('username', 'readonly', 'true');
			$form->setFieldAttribute('username', 'required', 'false');
		}

		// When multilanguage is set, a user's default site language should also be a Content Language
		if (JLanguageMultilang::isEnabled())
		{
			$form->setFieldAttribute('language', 'type', 'frontend_language', 'params');
		}

		// If the user needs to change their password, mark the password fields as required
		if (JFactory::getUser()->requireReset)
		{
			$form->setFieldAttribute('password1', 'required', 'true');
			$form->setFieldAttribute('password2', 'required', 'true');
		}

		return $form;
	}

	/**
	 * Increment the hit counter for the page.
	 *
	 * @param   integer  $pk  Optional primary key of the page to increment.
	 *
	 * @return  boolean  True if successful; false otherwise and internal error set.
	 */
	public function hit($pk = 0)
	{
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('page.id');
		$table = JTable::getInstance('Page', 'SppagebuilderTable');
		$table->load($pk);
		$table->hit($pk);

		return true;
	}

	public function getMySections() {
      $db = JFactory::getDbo();
      $query = $db->getQuery(true);
      $query->select($db->quoteName(array('id', 'title', 'section')));
      $query->from($db->quoteName('#__sppagebuilder_sections'));
      //$query->where($db->quoteName('profile_key') . ' LIKE '. $db->quote('\'custom.%\''));
      $query->order('id ASC');
      $db->setQuery($query);
      $results = $db->loadObjectList();
      return json_encode($results);
    }

    public function deleteSection($id){
      $db = JFactory::getDbo();

      $query = $db->getQuery(true);

      // delete all custom keys for user 1001.
      $conditions = array(
          $db->quoteName('id') . ' = '.$id
      );

      $query->delete($db->quoteName('#__sppagebuilder_sections'));
      $query->where($conditions);

      $db->setQuery($query);

      return $db->execute();
    }

    public function saveSection($title, $section){
      $db = JFactory::getDbo();
      $user = JFactory::getUser();
      $obj = new stdClass();
      $obj->title = $title;
      $obj->section = $section;
      $obj->created = JFactory::getDate()->toSql();
      $obj->created_by = $user->get('id');

      $db->insertObject('#__sppagebuilder_sections', $obj);

      return $db->insertid();
    }

}
