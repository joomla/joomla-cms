<?php
/**
 * @package     Joomla.Cms
 * @subpackage  View
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use Joomla\Renderer\RendererInterface;

/**
 * Prototype JView class.
 *
 * @package     Joomla.Libraries
 * @subpackage  View
 * @since       3.4
 */
abstract class JViewHtmlCms extends JViewCms
{
	/**
	 * The renderer object
	 *
	 * @var    RendererInterface
	 * @since  3.4
	 */
	protected $renderer;

	/**
	 * Method to instantiate the view.
	 *
	 * @param   JModelCmsInterface  $model     The model object.
	 * @param   RendererInterface   $renderer  The renderer object. Defaults to JLayout if not set.
	 * @param   array               $config    An array of config options. Should contain component
	 *                                         name and view name.
	 *
	 * @since   3.4
	 */
	public function __construct(JModelCmsInterface $model, RendererInterface $renderer = null, $config = array())
	{
		// If we don't have a renderer use the JLayout renderer
		if (!$renderer)
		{
			$options = array();
			$options['paths'] = $this->getPaths();

			$renderer = new JRendererJlayout($options);
		}

		// Set the renderer.
		$this->setRenderer($renderer);

		parent::__construct($model, $config);
	}

	/**
	 * The paths for the JLayoutRenderer to check in
	 *
	 * @return  array  The paths for the layout
	 *
	 * @since   3.4
	 */
	protected function getPaths()
	{
		// @todo investigate whether we should inject JApplicationCms in the constructor?
		// I don't really want to though because it's only for the fallback if a renderer isn't set

		// Find the root path - either site or administrator
		$app = JFactory::getApplication();
		$rootPath = $app->isAdmin() ? JPATH_ADMINISTRATOR : JPATH_SITE;

		$input = $app->input;
		$componentFolder = strtolower($this->getOption());
		$viewName = strtolower($this->getName());

		// Add the default paths
		$paths = array();
		$paths[] = $rootPath . '/templates/' . $app->getTemplate() . '/html/' . $componentFolder . '/' . $viewName;
		$paths[] = $rootPath . '/components/' . $componentFolder . '/view/' . $viewName . '/tmpl';

		return $paths;
	}

	/**
	 * Retrieves the renderer object
	 *
	 * @return  RendererInterface
	 *
	 * @since   3.4
	 */
	public function getRenderer()
	{
		return $this->renderer;
	}

	/**
	 * Method to render the view.
	 *
	 * @return  string  The rendered view.
	 *
	 * @since   3.4
	 * @throws  RuntimeException
	 */
	public function render()
	{
		return $this->getRenderer()->render($this->getLayout(), $this->getData());
	}

	/**
	 * Sets the renderer object
	 *
	 * @param   RendererInterface  $renderer  The renderer object.
	 *
	 * @return  $this  Method allows chaining
	 *
	 * @since   3.4
	 */
	public function setRenderer(RendererInterface $renderer)
	{
		$this->renderer = $renderer;

		return $this;
	}
}
