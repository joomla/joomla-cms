<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');

/**
 * Comment model.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_comments
 * @since		1.6
 */
class CommentsModelComment extends JModelForm
{
	/**
	 * Method to auto-populate the model state.
	 */
	protected function _populateState()
	{
		$app = JFactory::getApplication('administrator');

		// Load the User state.
		if (!($pk = (int) $app->getUserState('com_comments.edit.comment.id'))) {
			$pk = (int) JRequest::getInt('id');
		}
		$this->setState('comment.id', $pk);

		// Load the parameters.
		$params	= JComponentHelper::getParams('com_comments');
		$this->setState('params', $params);
	}

	/**
	 * Returns a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	*/
	public function getTable($type = 'Comment', $prefix = 'CommentsTable', $config = array())
	{
		JTable::addIncludePath(JPATH_SITE.'/components/com_comments/tables');

		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get a single record.
	 *
	 * @param	integer	The id of the primary key.
	 *
	 * @return	mixed	Object on success, false on failure.
	 */
	public function &getItem($pk = null)
	{
		static $cache = null;

		// Initialise variables.
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('comment.id');
		$false	= false;

		if (empty($cache)) {
			$cache = array();
		}

		if (empty($cache[$pk])) {
			// Get a row instance.
			$table = $this->getTable();

			// Attempt to load the row.
			$return = $table->load($pk);

			// Check for a table object error.
			if ($return === false && $table->getError()) {
				$this->setError($table->getError());
				return $false;
			}

			// Prime required properties.
			if (empty($table->id))
			{
				// Prepare data for a new record.
			}

			// Convert to the JObject before adding other data.
			$cache[$pk] = JArrayHelper::toObject($table->getProperties(1), 'JObject');
		}

		return $cache[$pk];
	}

	/**
	 * Method to get the record form.
	 *
	 * @return	mixed	JForm object on success, false on failure.
	 */
	public function getForm()
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		// Get the form.
		$form = parent::getForm('comment', 'com_comments.comment', array('array' => 'jform', 'event' => 'onPrepareForm'));

		// Check for an error.
		if (JError::isError($form)) {
			$this->setError($form->getMessage());
			return false;
		}

		// Check the session for previously entered form data.
		$data = $app->getUserState('com_comments.edit.comment.data', array());

		// Bind the form data if present.
		if (!empty($data)) {
			$form->bind($data);
		}

		return $form;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param	array	The form data.
	 *
	 * @return	boolean	True on success.
	 */
	public function save($data = array())
	{
		// Initialise variables.
		$dispatcher = JDispatcher::getInstance();
		$table		= $this->getTable();
		$pk			= (!empty($data['id'])) ? $data['id'] : (int) $this->getState('comment.id');
		$isNew		= true;

		// Include the content plugins for the onSave events.
		JPluginHelper::importPlugin('content');

		// Load the row if saving an existing record.
		if ($pk > 0) {
			$table->load($pk);
			$isNew = false;
		}

		// Bind the data.
		if (!$table->bind($data)) {
			$this->setError(JText::sprintf('JTable_Error_Bind_failed', $table->getError()));
			return false;
		}

		// Prepare the row for saving
		$this->_prepareTable($table);

		// Check the data.
		if (!$table->check()) {
			$this->setError($table->getError());
			return false;
		}

		// Trigger the onBeforeSaveContent event.
		$result = $dispatcher->trigger('onBeforeContentSave', array(&$table, $isNew));
		if (in_array(false, $result, true)) {
			$this->setError($table->getError());
			return false;
		}

		// Store the data.
		if (!$table->store()) {
			$this->setError($table->getError());
			return false;
		}

		// Clean the cache.
		$cache = JFactory::getCache('com_comment');
		$cache->clean();

		// Trigger the onAfterContentSave event.
		$dispatcher->trigger('onAfterContentSave', array(&$table, $isNew));

		$this->setState('comment.id', $table->id);

		return true;
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 */
	protected function _prepareTable(&$table)
	{
	}

	/**
	 * Method to delete rows.
	 *
	 * @param	array	An array of item ids.
	 *
	 * @return	boolean	Returns true on success, false on failure.
	 */
	public function delete(&$pks)
	{
		// Typecast variable.
		$pks = (array) $pks;

		// Get a row instance.
		$table = &$this->getTable();

		// Iterate the items to delete each one.
		foreach ($pks as $i => $pk) {
			if ($table->load($pk)) {
				// Access checks.
				$allow = $user->authorise('core.delete', 'com_comments');

				if ($allow) {
					if (!$table->delete($pk)) {
						$this->setError($table->getError());
						return false;
					}
				} else {
					// Prune items that you can't change.
					unset($pks[$i]);
					JError::raiseWarning(403, JText::_('JError_Core_Edit_State_not_permitted'));
				}
			} else {
				$this->setError($table->getError());
				return false;
			}
		}

		return true;
	}

	/**
	 * Method to set the moderation state on a list of comments
	 *
	 * @access	public
	 * @param	array	$ids	The list of {COMMENT_ID}=>{STATE} values to set
	 * @return	boolean	True on success
	 * @since	1.0
	 */
	function moderate($ids)
	{
		$db		= &$this->getDBO();
		$config = $this->getState('config');

		$useAkismet= false;
		if ($config->get('enable_akismet')) {
			jimport('joomla.webservices.akismet');
			$akismet = new JXAkismet(JURI::base(), $config->get('akismet_key'));

			$valid = $akismet->validateAPIKey();
			if ($valid and !JError::isError($valid)) {
				$useAkismet = true;
			} else {
				JError::raiseNotice(500, JText::_('COMMENTS_INVALID_AKISMET_KEY'));
			}
		}

		// iterate over the ids to moderate.
		foreach ($ids as $id => $state) {
			$db->setQuery(
				'SELECT *' .
				' FROM `#__social_comments`' .
				' WHERE `id` = '.(int)$id
			);
			$c = $db->loadObject();

			if (($state == 2) and ($c->published != 2)) {
				// notify Akismet of spam
				if ($useAkismet and is_object($akismet)) {
					// create and populate the comment object
					$comment = new JObject();
					$comment->set('author', $c->name);
					$comment->set('email', $c->email);
					$comment->set('website', $c->url);
					$comment->set('body', $c->body);
					$comment->set('permalink', $c->referer);

					// set the comment to the Akismet handler and set the comment as spam
					$akismet->setComment($comment);
					$akismet->submitSpam();
				}
			} elseif (($state == 1) and ($c->published == 2)) {
				// notify Akismet of ham
				if ($useAkismet and is_object($akismet)) {
					// create and populate the comment object
					$comment = new JObject();
					$comment->set('author', $c->name);
					$comment->set('email', $c->email);
					$comment->set('website', $c->url);
					$comment->set('body', $c->body);
					$comment->set('permalink', $c->referer);

					// set the comment to the Akismet handler and set the comment as spam
					$akismet->setComment($comment);
					$akismet->submitHam();
				}
			}

			if ($state === -1) {
				// delete the comment
				$db->setQuery(
					'DELETE FROM `#__social_comments`' .
					' WHERE `id` = '.(int)$id
				);
				$db->query();
			} else {
				// set the actual state of the comment
				$db->setQuery(
					'UPDATE `#__social_comments`' .
					' SET `published` = '.(int)$state .
					' WHERE `id` = '.(int)$id
				);
				$db->query();
			}

		}
		return true;
	}

	/**
	 * Method to get a thread for a comment.
	 */
	public function getThread()
	{
		$comment = $this->getItem();

		if ($threadId = (int) $comment->thread_id) {
			$db = $this->getDbo();
			$query = $db->getQuery(true);
			$query->select('b.id AS thread_id, b.context, b.context_id, b.page_title, b.page_route, b.page_url')
				->from('#__social_threads AS b')
				->where('b.id = '.$threadId);
			$thread = $db->setQuery($query)->loadObject();

			if ($error = $db->getErrorMsg()) {
				$this->setError($error);
				return false;
			}

			return $thread;
		} else {
			$this->setError('Comment_Error_Thread_not_found');
			return false;
		}
	}

	/**
	 * Method to return a short list of comment objects in a given tread in descending order of date
	 *
	 * @access	public
	 * @return	array	Array list of comment objects in the same thread
	 * @since	1.0
	 */
	function &getListByThread()
	{
		if (empty($this->_listByContext)) {
			$item = &$this->getItem();

			// lets get a list of comments
			$db = &$this->getDBO();
			$db->setQuery(
				'SELECT a.*' .
				' FROM `#__social_comments` AS a' .
				' WHERE a.`thread_id` = '.(int)$item->thread_id .
				' AND a.`created_date` < '.$db->Quote($item->created_date) .
				' AND a.`id` != '.(int)$item->id .
				' ORDER BY a.`created_date` DESC',
				0, 5
			);
			$this->_listByContext = $db->loadObjectList();

			if (empty($this->_listByContext)) {
				$this->_listByContext = array();
			}
		}
		return $this->_listByContext;
	}

	/**
	 * Method to return a short list of comment objects with a given author name in descending order of date
	 *
	 * @access	public
	 * @return	array	Array list of comment objects with the same author name
	 * @since	1.0
	 */
	function &getListByName()
	{
		if (empty($this->_listByName)) {
			$item = &$this->getItem();

			// lets get a list of comments with the same author name in descending order of created date
			$db = &$this->getDBO();
			$db->setQuery(
				'SELECT *' .
				' FROM `#__social_comments`' .
				' WHERE `name` = '.$db->Quote($item->name) .
				' AND `id` != '.(int)$item->id .
				' ORDER BY `created_date` DESC',
				0, 5
			);
			$this->_listByName = $db->loadObjectList();

			if (empty($this->_listByName)) {
				$this->_listByName = array();
			}
		}
		return $this->_listByName;
	}

	/**
	 * Method to return a short list of comment objects with the same IP in descending order of date
	 *
	 * @access	public
	 * @return	array	Array list of comment objects with the same IP
	 * @since	1.0
	 */
	function &getListByIP()
	{
		if (empty($this->_listByIP)) {
			$item = &$this->getItem();

			// lets get a list of comments with the same IP in descending order of created date
			$db = &$this->getDBO();
			$db->setQuery(
				'SELECT *' .
				' FROM `#__social_comments`' .
				' WHERE `address` = '.$db->Quote($item->address) .
				' AND `id` != '.(int)$item->id .
				' ORDER BY `created_date` DESC',
				0, 5
			);
			$this->_listByIP = $db->loadObjectList();

			if (empty($this->_listByIP)) {
				$this->_listByIP = array();
			}
		}
		return $this->_listByIP;
	}
}