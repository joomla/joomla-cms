<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Template style controller class.
 *
 * @since  1.6
 */
class TemplatesControllerStyle extends JControllerForm
{
	/**
	 * The prefix to use with controller messages.
	 *
	 * @var		string
	 * @since   1.6
	 */
	protected $text_prefix = 'COM_TEMPLATES_STYLE';

	/**
	 * Method to save a template style.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if successful, false otherwise.
	 *
	 * @since   1.6
	 */
	public function save($key = null, $urlVar = null)
	{
		$this->checkToken();

		$document = JFactory::getDocument();

		if ($document->getType() === 'json')
		{
			$app   = JFactory::getApplication();
			$lang  = JFactory::getLanguage();
			$model = $this->getModel();
			$table = $model->getTable();
			$data  = $this->input->post->get('params', array(), 'array');
			$checkin = property_exists($table, 'checked_out');
			$context = $this->option . '.edit.' . $this->context;
			$task = $this->getTask();

			$item = $model->getItem($app->getTemplate(true)->id);

			// Setting received params
			$item->set('params', $data);

			$data = $item->getProperties();
			unset($data['xml']);

			$key = $table->getKeyName();

			// Access check.
			if (!$this->allowSave($data, $key))
			{
				$app->enqueueMessage(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'), 'error');

				return false;
			}

			JForm::addFormPath(JPATH_ADMINISTRATOR . '/components/com_templates/models/forms');

			// Validate the posted data.
			// Sometimes the form needs some posted data, such as for plugins and modules.
			$form = $model->getForm($data, false);

			if (!$form)
			{
				$app->enqueueMessage($model->getError(), 'error');

				return false;
			}

			// Test whether the data is valid.
			$validData = $model->validate($form, $data);

			if ($validData === false)
			{
				// Get the validation messages.
				$errors = $model->getErrors();

				// Push up to three validation messages out to the user.
				for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
				{
					if ($errors[$i] instanceof Exception)
					{
						$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
					}
					else
					{
						$app->enqueueMessage($errors[$i], 'warning');
					}
				}

				// Save the data in the session.
				$app->setUserState($context . '.data', $data);

				return false;
			}

			if (!isset($validData['tags']))
			{
				$validData['tags'] = null;
			}

			// Attempt to save the data.
			if (!$model->save($validData))
			{
				// Save the data in the session.
				$app->setUserState($context . '.data', $validData);

				$app->enqueueMessage(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()), 'error');

				return false;
			}

			// Save succeeded, so check-in the record.
			if ($checkin && $model->checkin($validData[$key]) === false)
			{
				// Save the data in the session.
				$app->setUserState($context . '.data', $validData);

				// Check-in failed, so go back to the record and display a notice.
				$app->enqueueMessage(JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()), 'error');

				return false;
			}

			// Redirect the user and adjust session state
			// Set the record data in the session.
			$recordId = $model->getState($this->context . '.id');
			$this->holdEditId($context, $recordId);
			$app->setUserState($context . '.data', null);
			$model->checkout($recordId);

			// Invoke the postSave method to allow for the child class to access the model.
			$this->postSaveHook($model, $validData);

			return true;
		}
		else
		{
			parent::save($key, $urlVar);
		}
	}
}
