<?php
/**
 * @package     FrameworkOnFramework
 * @subpackage  view
 * @copyright   Copyright (C) 2010 - 2015 Nicholas K. Dionysopoulos / Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('FOF_INCLUDED') or die;

/**
 * FrameworkOnFramework Form class. It preferrably renders an XML view template
 * instead of a traditional PHP-based view template.
 *
 * @package  FrameworkOnFramework
 * @since    2.0
 */
class FOFViewForm extends FOFViewHtml
{
	/** @var FOFForm The form to render */
	protected $form;

	/**
	 * Displays the view
	 *
	 * @param   string  $tpl  The template to use
	 *
	 * @return  boolean|null False if we can't render anything
	 */
	public function display($tpl = null)
	{
		$model = $this->getModel();

		// Get the form
		$this->form = $model->getForm();
		$this->form->setModel($model);
		$this->form->setView($this);

		// Get the task set in the model
		$task = $model->getState('task', 'browse');

		// Call the relevant method
		$method_name = 'on' . ucfirst($task);

		if (method_exists($this, $method_name))
		{
			$result = $this->$method_name($tpl);
		}
		else
		{
			$result = $this->onDisplay();
		}

		// Bail out if we're told not to render anything

		if ($result === false)
		{
			return;
		}

		// Show the view
		// -- Output HTML before the view template
		$this->preRender();

		// -- Try to load a view template; if not exists render the form directly
		$basePath = FOFPlatform::getInstance()->isBackend() ? 'admin:' : 'site:';
		$basePath .= $this->config['option'] . '/';
		$basePath .= $this->config['view'] . '/';
		$path = $basePath . $this->getLayout();

		if ($tpl)
		{
			$path .= '_' . $tpl;
		}

		$viewTemplate = $this->loadAnyTemplate($path);

		// If there was no template file found, display the form
		if ($viewTemplate instanceof Exception)
		{
			$viewTemplate = $this->getRenderedForm();
		}

		// -- Output the view template
		echo $viewTemplate;

		// -- Output HTML after the view template
		$this->postRender();
	}

	/**
	 * Returns the HTML rendering of the FOFForm attached to this view. Very
	 * useful for customising a form page without having to meticulously hand-
	 * code the entire form.
	 *
	 * @return  string  The HTML of the rendered form
	 */
	public function getRenderedForm()
	{
		$html = '';
		$renderer = $this->getRenderer();

		if ($renderer instanceof FOFRenderAbstract)
		{
			// Load CSS and Javascript files defined in the form
			$this->form->loadCSSFiles();
			$this->form->loadJSFiles();

			// Get the form's HTML
			$html = $renderer->renderForm($this->form, $this->getModel(), $this->input);
		}

		return $html;
	}

	/**
	 * The event which runs when we are displaying the Add page
	 *
	 * @param   string  $tpl  The view sub-template to use
	 *
	 * @return  boolean  True to allow display of the view
	 */
	protected function onAdd($tpl = null)
	{
		// Hide the main menu
		JRequest::setVar('hidemainmenu', true);

		// Get the model
		$model = $this->getModel();

		// Assign the item and form to the view
		$this->item = $model->getItem();

		return true;
	}
}
