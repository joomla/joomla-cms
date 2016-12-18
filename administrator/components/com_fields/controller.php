<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * Fields Controller
 *
 * @since  __DEPLOY_VERSION__
 */
class FieldsController extends JControllerLegacy
{
	/**
	 * The default view.
	 *
	 * @var    string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected $default_view = 'fields';

	/**
	 * Typical view method for MVC based architecture
	 *
	 * This function is provide as a default implementation, in most cases
	 * you will need to override it in your own controllers.
	 *
	 * @param   boolean     $cachable   If true, the view output will be cached
	 * @param   array|bool  $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}
	 *
	 * @return JControllerLegacy|boolean  A JControllerLegacy object to support chaining.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function display($cachable = false, $urlparams = false)
	{
		// Set the default view name and format from the Request.
		$vName   = $this->input->get('view', 'fields');
		$id      = $this->input->getInt('id');

		// Check for edit form.
		if ($vName == 'field' && !$this->checkEditId('com_fields.edit.field', $id))
		{
			// Somehow the person just went to the form - we don't allow that.
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_fields&view=fields&context=' . $this->input->get('context'), false));

			return false;
		}

		return parent::display($cachable, $urlparams);
	}
}
