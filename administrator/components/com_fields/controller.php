<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class FieldsController extends JControllerLegacy
{

	protected $context;

	public function __construct ($config = array())
	{
		parent::__construct($config);

		// Guess the JText message prefix. Defaults to the option.
		if (empty($this->context))
		{
			$this->context = $this->input->get('context', 'com_content');
		}
	}

	public function display ($cachable = false, $urlparams = false)
	{
		// Get the document object.
		$document = JFactory::getDocument();

		// Set the default view name and format from the Request.
		$vName = $this->input->get('view', 'fields');
		$vFormat = $document->getType();
		$lName = $this->input->get('layout', 'default', 'string');
		$id = $this->input->getInt('id');

		// Check for edit form.
		if ($vName == 'field' && $lName == 'edit' && ! $this->checkEditId('com_fields.edit.field', $id))
		{
			// Somehow the person just went to the form - we don't allow that.
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_fields&view=fields&context=' . $this->context, false));

			return false;
		}

		// Get and render the view.
		if ($view = $this->getView($vName, $vFormat))
		{
			// Get the model for the view.
			$model = $this->getModel($vName, 'FieldsModel', array(
					'name' => $vName . '.' . substr($this->context, 4)
			));

			// Push the model into the view (as default).
			$view->setModel($model, true);
			$view->setLayout($lName);

			// Push document object into the view.
			$view->document = $document;

			FieldsHelperInternal::addSubmenu($model->getState('filter.context'));
			$view->display();
		}

		return $this;
	}
}
