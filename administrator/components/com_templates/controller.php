<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Templates manager master display controller.
 *
 * @since  1.6
 */
class TemplatesController extends JControllerLegacy
{
	/**
	 * @var		string	The default view.
	 * @since   1.6
	 */
	protected $default_view = 'styles';

	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   boolean  $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  TemplatesController  This object to support chaining.
	 *
	 * @since   1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		$view   = $this->input->get('view', 'styles');
		$layout = $this->input->get('layout', 'default');
		$id     = $this->input->getInt('id');

		$document = JFactory::getDocument();

		// For JSON requests
		if ($document->getType() == 'json')
		{
			$view = new TemplatesViewStyle;

			// Get/Create the model
			if ($model = new TemplatesModelStyle)
			{
				$model->addTablePath(JPATH_ADMINISTRATOR . '/components/com_templates/tables');

				// Push the model into the view (as default)
				$view->setModel($model, true);
			}

			$view->document = $document;

			return $view->display();
		}

		// Check for edit form.
		if ($view == 'style' && $layout == 'edit' && !$this->checkEditId('com_templates.edit.style', $id))
		{
			// Somehow the person just went to the form - we don't allow that.
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_templates&view=styles', false));

			return false;
		}

		return parent::display();
	}
}
