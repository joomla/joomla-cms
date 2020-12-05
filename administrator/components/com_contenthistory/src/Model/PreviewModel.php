<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_contenthistory
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Contenthistory\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\CMS\Table\ContentHistory;
use Joomla\CMS\Table\ContentType;
use Joomla\CMS\Table\Table;
use Joomla\Component\Contenthistory\Administrator\Helper\ContenthistoryHelper;

/**
 * Methods supporting a list of contenthistory records.
 *
 * @since  3.2
 */
class PreviewModel extends ItemModel
{
	/**
	 * Method to get a version history row.
	 *
	 * @param   integer  $pk  The id of the item
	 *
	 * @return  \stdClass|boolean    On success, standard object with row data. False on failure.
	 *
	 * @since   3.2
	 *
	 * @throws  NotAllowed   Thrown if not authorised to edit an item
	 */
	public function getItem($pk = null)
	{
		/** @var ContentHistory $table */
		$table = $this->getTable('ContentHistory');
		$versionId = Factory::getApplication()->input->getInt('version_id');

		if (!$versionId || \is_array($versionId) || !$table->load($versionId))
		{
			return false;
		}

		$user = Factory::getUser();

		// Access check
		if (!$user->authorise('core.edit', $table->item_id) && !$this->canEdit($table))
		{
			throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		$result = new \stdClass;
		$result->version_note = $table->version_note;
		$result->data = ContenthistoryHelper::prepareData($table);

		// Let's use custom calendars when present
		$result->save_date = HTMLHelper::_('date', $table->save_date, Text::_('DATE_FORMAT_LC6'));

		$dateProperties = array (
			'modified_time',
			'created_time',
			'modified',
			'created',
			'checked_out_time',
			'publish_up',
			'publish_down',
		);

		$nullDate = $this->getDbo()->getNullDate();

		foreach ($dateProperties as $dateProperty)
		{
			if (property_exists($result->data, $dateProperty)
				&& $result->data->$dateProperty->value !== null
				&& $result->data->$dateProperty->value !== $nullDate)
			{
				$result->data->$dateProperty->value = HTMLHelper::_(
					'date',
					$result->data->$dateProperty->value,
					Text::_('DATE_FORMAT_LC6')
				);
			}
		}

		return $result;
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $type    The table name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  Table   A Table object
	 *
	 * @since   3.2
	 */
	public function getTable($type = 'ContentHistory', $prefix = 'Joomla\\CMS\\Table\\', $config = array())
	{
		return Table::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to test whether a record is editable
	 *
	 * @param   ContentHistory  $record  A Table object.
	 *
	 * @return  boolean  True if allowed to edit the record. Defaults to the permission set in the component.
	 *
	 * @since   3.6
	 */
	protected function canEdit($record)
	{
		$result = false;

		if (!empty($record->item_id))
		{
			/**
			 * Make sure user has edit privileges for this content item. Note that we use edit permissions
			 * for the content item, not delete permissions for the content history row.
			 */
			$user   = Factory::getUser();
			$result = $user->authorise('core.edit', $record->item_id);

			// Finally try session (this catches edit.own case too)
			if (!$result)
			{
				/** @var ContentType $contentTypeTable */
				$contentTypeTable = $this->getTable('ContentType');

				$typeAlias        = explode('.', $record->item_id);
				$id = array_pop($typeAlias);
				$typeAlias        = implode('.', $typeAlias);
				$typeEditables = (array) Factory::getApplication()->getUserState(str_replace('.', '.edit.', $contentTypeTable->type_alias) . '.id');
				$result = in_array((int) $id, $typeEditables);
			}
		}

		return $result;
	}
}
