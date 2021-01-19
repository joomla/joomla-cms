<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Table;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\ParameterType;
use Joomla\String\StringHelper;

/**
 * Core content table
 *
 * @since  3.1
 */
class CoreContent extends Table
{
	/**
	 * Indicates that columns fully support the NULL value in the database
	 *
	 * @var    boolean
	 * @since  4.0.0
	 */
	protected $_supportNullValue = true;

	/**
	 * Encode necessary fields to JSON in the bind method
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	protected $_jsonEncode = ['core_params', 'core_metadata', 'core_images', 'core_urls', 'core_body'];

	/**
	 * Constructor
	 *
	 * @param   DatabaseDriver  $db  A database connector object
	 *
	 * @since   3.1
	 */
	public function __construct(DatabaseDriver $db)
	{
		parent::__construct('#__ucm_content', 'core_content_id', $db);

		$this->setColumnAlias('published', 'core_state');
		$this->setColumnAlias('checked_out', 'core_checked_out_user_id');
		$this->setColumnAlias('checked_out_time', 'core_checked_out_time');
	}

	/**
	 * Overloaded check function
	 *
	 * @return  boolean  True on success, false on failure
	 *
	 * @see     Table::check()
	 * @since   3.1
	 */
	public function check()
	{
		try
		{
			parent::check();
		}
		catch (\Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		if (trim($this->core_title) === '')
		{
			$this->setError(Text::_('JLIB_CMS_WARNING_PROVIDE_VALID_NAME'));

			return false;
		}

		if (trim($this->core_alias) === '')
		{
			$this->core_alias = $this->core_title;
		}

		$this->core_alias = ApplicationHelper::stringURLSafe($this->core_alias);

		if (trim(str_replace('-', '', $this->core_alias)) === '')
		{
			$this->core_alias = Factory::getDate()->format('Y-m-d-H-i-s');
		}

		// Not Null sanity check
		if (empty($this->core_images))
		{
			$this->core_images = '{}';
		}

		if (empty($this->core_urls))
		{
			$this->core_urls = '{}';
		}

		// Check the publish down date is not earlier than publish up.
		if ($this->core_publish_up !== null
			&& $this->core_publish_down !== null
			&& $this->core_publish_down < $this->core_publish_up
			&& $this->core_publish_down > $this->_db->getNullDate())
		{
			// Swap the dates.
			$temp = $this->core_publish_up;
			$this->core_publish_up = $this->core_publish_down;
			$this->core_publish_down = $temp;
		}

		// Clean up keywords -- eliminate extra spaces between phrases
		// and cr (\r) and lf (\n) characters from string
		if (!empty($this->core_metakey))
		{
			// Only process if not empty

			// Array of characters to remove
			$bad_characters = array("\n", "\r", "\"", '<', '>');

			// Remove bad characters
			$after_clean = StringHelper::str_ireplace($bad_characters, '', $this->core_metakey);

			// Create array using commas as delimiter
			$keys = explode(',', $after_clean);

			$clean_keys = array();

			foreach ($keys as $key)
			{
				if (trim($key))
				{
					// Ignore blank keywords
					$clean_keys[] = trim($key);
				}
			}

			// Put array back together delimited by ", "
			$this->core_metakey = implode(', ', $clean_keys);
		}

		return true;
	}

	/**
	 * Override JTable delete method to include deleting corresponding row from #__ucm_base.
	 *
	 * @param   integer  $pk  primary key value to delete. Must be set or throws an exception.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.1
	 * @throws  \UnexpectedValueException
	 */
	public function delete($pk = null)
	{
		$baseTable = Table::getInstance('Ucm', 'JTable', array('dbo' => $this->getDbo()));

		return parent::delete($pk) && $baseTable->delete($pk);
	}

	/**
	 * Method to delete a row from the #__ucm_content table by content_item_id.
	 *
	 * @param   integer  $contentItemId  value of the core_content_item_id to delete. Corresponds to the primary key of the content table.
	 * @param   string   $typeAlias      Alias for the content type
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.1
	 * @throws  \UnexpectedValueException
	 */
	public function deleteByContentId($contentItemId = null, $typeAlias = null)
	{
		$contentItemId = (int) $contentItemId;

		if ($contentItemId === 0)
		{
			throw new \UnexpectedValueException('Null content item key not allowed.');
		}

		if ($typeAlias === null)
		{
			throw new \UnexpectedValueException('Null type alias not allowed.');
		}

		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('core_content_id'))
			->from($db->quoteName('#__ucm_content'))
			->where(
				[
					$db->quoteName('core_content_item_id') . ' = :contentItemId',
					$db->quoteName('core_type_alias') . ' = :typeAlias',
				]
			)
			->bind(':contentItemId', $contentItemId, ParameterType::INTEGER)
			->bind(':typeAlias', $typeAlias);

		$db->setQuery($query);

		if ($ucmId = $db->loadResult())
		{
			return $this->delete($ucmId);
		}
		else
		{
			return true;
		}
	}

	/**
	 * Overrides Table::store to set modified data and user id.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.1
	 */
	public function store($updateNulls = true)
	{
		$date = Factory::getDate();
		$user = Factory::getUser();

		if ($this->core_content_id)
		{
			// Existing item
			$this->core_modified_time = $date->toSql();
			$this->core_modified_user_id = $user->get('id');
			$isNew = false;
		}
		else
		{
			// New content item. A content item core_created_time and core_created_user_id field can be set by the user,
			// so we don't touch either of these if they are set.
			if (!(int) $this->core_created_time)
			{
				$this->core_created_time = $date->toSql();
			}

			if (empty($this->core_created_user_id))
			{
				$this->core_created_user_id = $user->get('id');
			}

			if (!(int) $this->core_modified_time)
			{
				$this->core_modified_time = $this->core_created_time;
			}

			if (empty($this->core_modified_user_id))
			{
				$this->core_modified_user_id = $this->core_created_user_id;
			}

			$isNew = true;
		}

		$oldRules = $this->getRules();

		if (empty($oldRules))
		{
			$this->setRules('{}');
		}

		$result = parent::store($updateNulls);

		return $result && $this->storeUcmBase($updateNulls, $isNew);
	}

	/**
	 * Insert or update row in ucm_base table
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 * @param   boolean  $isNew        if true, need to insert. Otherwise update.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.1
	 */
	protected function storeUcmBase($updateNulls = true, $isNew = false)
	{
		// Store the ucm_base row
		$db         = $this->getDbo();
		$query      = $db->getQuery(true);
		$languageId = ContentHelper::getLanguageId($this->core_language);

		// Selecting "all languages" doesn't give a language id - we can't store a blank string in non mysql databases, so save 0 (the default value)
		if (!$languageId)
		{
			$languageId = 0;
		}

		if ($isNew)
		{
			$query->insert($db->quoteName('#__ucm_base'))
				->columns(
					[
						$db->quoteName('ucm_id'),
						$db->quoteName('ucm_item_id'),
						$db->quoteName('ucm_type_id'),
						$db->quoteName('ucm_language_id'),
					]
				)
				->values(
					implode(
						',',
						$query->bindArray(
							[
								$this->core_content_id,
								$this->core_content_item_id,
								$this->core_type_id,
								$languageId,
							]
						)
					)
				);
		}
		else
		{
			$query->update($db->quoteName('#__ucm_base'))
				->set(
					[
						$db->quoteName('ucm_item_id') . ' = :coreContentItemId',
						$db->quoteName('ucm_type_id') . ' = :typeId',
						$db->quoteName('ucm_language_id') . ' = :languageId',
					]
				)
				->where($db->quoteName('ucm_id') . ' = :coreContentId')
				->bind(':coreContentItemId', $this->core_content_item_id, ParameterType::INTEGER)
				->bind(':typeId', $this->core_type_id, ParameterType::INTEGER)
				->bind(':languageId', $languageId, ParameterType::INTEGER)
				->bind(':coreContentId', $this->core_content_id, ParameterType::INTEGER);
		}

		$db->setQuery($query);

		return $db->execute();
	}
}
