<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Versioning;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/**
 * Defines the trait for a Versionable Controller Class.
 *
 * @since  4.0.0
 */
trait VersionableControllerTrait
{
	/**
	 * Method to load a row from version history
	 *
	 * @return  mixed  True if the record can be added, an error object if not.
	 *
	 * @since   4.0.0
	 */
	public function loadhistory()
	{
		$model = $this->getModel();
		$table = $model->getTable();
		$historyId = $this->input->getInt('version_id', null);

		if (!$model->loadhistory($historyId, $table))
		{
			$this->setMessage($model->getError(), 'error');

			$this->setRedirect(
				Route::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_list
					. $this->getRedirectToListAppend(), false
				)
			);

			return false;
		}

		// Determine the name of the primary key for the data.
		if (empty($key))
		{
			$key = $table->getKeyName();
		}

		$recordId = $table->$key;

		// To avoid data collisions the urlVar may be different from the primary key.
		$urlVar = empty($this->urlVar) ? $key : $this->urlVar;

		// Access check.
		if (!$this->allowEdit(array($key => $recordId), $key))
		{
			$this->setMessage(Text::_('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED'), 'error');

			$this->setRedirect(
				Route::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_list
					. $this->getRedirectToListAppend(), false
				)
			);
			$table->checkin();

			return false;
		}

		$table->check();
		$table->store();
		$this->setRedirect(
			Route::_(
				'index.php?option=' . $this->option . '&view=' . $this->view_item
				. $this->getRedirectToItemAppend($recordId, $urlVar), false
			)
		);

		$this->setMessage(
			Text::sprintf(
				'JLIB_APPLICATION_SUCCESS_LOAD_HISTORY', $model->getState('save_date'), $model->getState('version_note')
			)
		);

		// Invoke the postSave method to allow for the child class to access the model.
		$this->postSaveHook($model);

		return true;
	}
}
