<?php
/**
 * @package     Joomla.API
 * @subpackage  com_languages
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Languages\Api\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\ApiController;
use Joomla\CMS\MVC\Controller\Exception;
use Joomla\String\Inflector;
use Tobscure\JsonApi\Exception\InvalidParameterException;

/**
 * The overrides controller
 *
 * @since  4.0.0
 */
class OverridesController extends ApiController
{
	/**
	 * The content type of the item.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $contentType = 'overrides';

	/**
	 * The default view for the display method.
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $default_view = 'overrides';

	/**
	 * Basic display of an item view
	 *
	 * @param   integer  $id  The primary key to display. Leave empty if you want to retrieve data from the request
	 *
	 * @return  static  A \JControllerLegacy object to support chaining.
	 *
	 * @since   4.0.0
	 */
	public function displayItem($id = null)
	{
		$this->modelState->set('filter.language', $this->getLanguageFromInput());
		$this->modelState->set('filter.client', $this->getClientFromInput());

		return parent::displayItem($id);
	}

	/**
	 * Basic display of a list view
	 *
	 * @return  static  A \JControllerLegacy object to support chaining.
	 *
	 * @since   4.0.0
	 */
	public function displayList()
	{
		$this->modelState->set('filter.language', $this->getLanguageFromInput());
		$this->modelState->set('filter.client', $this->getClientFromInput());

		return parent::displayList();
	}

	/**
	 * Method to save a record.
	 *
	 * @param   integer  $recordKey  The primary key of the item (if exists)
	 *
	 * @return  integer  The record ID on success, false on failure
	 *
	 * @since   4.0.0
	 */
	protected function save($recordKey = null)
	{
		/** @var \Joomla\CMS\MVC\Model\AdminModel $model */
		$model = $this->getModel(Inflector::singularize($this->contentType));

		$model->setState('filter.language', $this->input->post->get('lang_code'));
		$model->setState('filter.client', $this->input->post->get('app'));

		if (!$model)
		{
			throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_MODEL_CREATE'));
		}

		$data = $this->input->get('data', json_decode($this->input->json->getRaw(), true), 'array');

		// TODO: Not the cleanest thing ever but it works...
		Form::addFormPath(JPATH_COMPONENT_ADMINISTRATOR . '/forms');

		// Validate the posted data.
		$form = $model->getForm($data, false);

		if (!$form)
		{
			throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_FORM_CREATE'));
		}

		// Test whether the data is valid.
		$validData = $model->validate($form, $data);

		// Check for validation errors.
		if ($validData === false)
		{
			$errors   = $model->getErrors();
			$messages = [];

			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof \Exception)
				{
					$messages[] = "{$errors[$i]->getMessage()}";
				}
				else
				{
					$messages[] = "{$errors[$i]}";
				}
			}

			throw new InvalidParameterException(implode("\n", $messages));
		}

		if (!isset($validData['tags']))
		{
			$validData['tags'] = [];
		}

		if (!$model->save($validData))
		{
			throw new Exception\Save(Text::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
		}

		return $validData['key'];
	}

	/**
	 * Removes an item.
	 *
	 * @param   integer  $id  The primary key to delete item.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function delete($id = null)
	{
		$id = $this->input->get('id', '', 'string');

		$this->input->set('model', $this->contentType);

		$this->modelState->set('filter.language', $this->getLanguageFromInput());
		$this->modelState->set('filter.client', $this->getClientFromInput());

		parent::delete($id);
	}

	/**
	 * Get client code from input
	 *
	 * @return string
	 *
	 * @since 4.0.0
	 */
	private function getClientFromInput()
	{
		return $this->input->exists('app') ? $this->input->get('app') : $this->input->post->get('app');
	}

	/**
	 * Get language code from input
	 *
	 * @return string
	 *
	 * @since 4.0.0
	 */
	private function getLanguageFromInput()
	{
		return $this->input->exists('lang_code') ?
			$this->input->get('lang_code') : $this->input->post->get('lang_code');
	}
}
