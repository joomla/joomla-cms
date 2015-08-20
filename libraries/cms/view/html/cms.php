<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  View
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

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
	 * @param   JDocument           $document  The document object.
	 * @param   RendererInterface   $renderer  The renderer object. Defaults to JLayout if not set.
	 * @param   array               $config    An array of config options. Should contain component
	 *                                         name and view name.
	 *
	 * @since   3.4
	 */
	public function __construct(JModelCmsInterface $model, JDocument $document, RendererInterface $renderer = null, $config = array())
	{
		// If we don't have a renderer use the JLayout renderer
		if (!$renderer)
		{
			$options = array();
			$options['paths'] = $this->loadPaths();

			$renderer = new JRendererJlayout($options);
		}

		// Set the renderer.
		$this->setRenderer($renderer);

		parent::__construct($model, $document, $config);
	}

	/**
	 * Add the page title and toolbar. Components should override
	 * this method as necessary
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	protected function addToolbar()
	{
	}

	/**
	 * Retrieves the data array from the default model
	 *
	 * @return  array
	 *
	 * @since   3.4
	 */
	abstract public function getData();

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
		// Add toolbar items as necessary
		$this->addToolbar();

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
