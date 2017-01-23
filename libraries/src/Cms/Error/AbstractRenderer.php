<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Cms\Error;

/**
 * Base class for error page renderers
 *
 * @since  4.0
 */
abstract class AbstractRenderer implements RendererInterface
{
	/**
	 * The JDocument instance
	 *
	 * @var    \JDocument
	 * @since  4.0
	 */
	protected $document;

	/**
	 * The format (type) of the error page
	 *
	 * @var    string
	 * @since  4.0
	 */
	protected $type;

	/**
	 * Render the error page for the given object
	 *
	 * @param   \Throwable|\Exception  $error  The error object to be rendered
	 *
	 * @return  string
	 *
	 * @since   4.0
	 */
	abstract protected function doRender($error);

	/**
	 * Retrieve the JDocument instance attached to this renderer
	 *
	 * @return  \JDocument
	 *
	 * @since   4.0
	 */
	public function getDocument()
	{
		// Load the document if not already
		if (!$this->document)
		{
			$this->document = $this->loadDocument();
		}

		return $this->document;
	}

	/**
	 * Get a renderer instance for the given type
	 *
	 * @param   string  $type  The type of renderer to fetch
	 *
	 * @return  static
	 *
	 * @since   4.0
	 * @throws  \InvalidArgumentException
	 */
	public static function getRenderer($type)
	{
		// Build the class name
		$class = __NAMESPACE__ . '\\Renderer\\' . ucfirst(strtolower($type)) . 'Renderer';

		// First check if an object may exist in the container and prefer that over everything else
		if (\JFactory::getContainer()->exists($class))
		{
			return \JFactory::getContainer()->get($class);
		}

		// Next check if a local class exists and use that
		if (class_exists($class))
		{
			return new $class;
		}

		// 404 Resource Not Found
		throw new \InvalidArgumentException(sprintf('There is not a error renderer for the "%s" format.', $type));
	}

	/**
	 * Create the JDocument object for this renderer
	 *
	 * @return  \JDocument
	 *
	 * @since   4.0
	 */
	protected function loadDocument()
	{
		$attributes = array(
			'charset'   => 'utf-8',
			'lineend'   => 'unix',
			'tab'       => "\t",
			'language'  => 'en-GB',
			'direction' => 'ltr',
		);

		// If there is a JLanguage instance in JFactory then let's pull the language and direction from its metadata
		if (\JFactory::$language)
		{
			$attributes['language']  = \JFactory::getLanguage()->getTag();
			$attributes['direction'] = \JFactory::getLanguage()->isRtl() ? 'rtl' : 'ltr';
		}

		return \JDocument::getInstance($this->type, $attributes);
	}

	/**
	 * Render the error page for the given object
	 *
	 * @param   \Throwable|\Exception  $error  The error object to be rendered
	 *
	 * @return  string
	 *
	 * @since   4.0
	 * @throws  \InvalidArgumentException if a non-Throwable object was provided
	 */
	public function render($error)
	{
		// If this isn't a Throwable then bail out
		if (!($error instanceof \Throwable) && !($error instanceof \Exception))
		{
			$expectedType = PHP_VERSION_ID >= 70000 ? 'a Throwable' : 'an Exception';

			throw new \InvalidArgumentException(
				sprintf('The error renderer requires %1$s object, a %2$s object was given instead.', $expectedType, get_class($error))
			);
		}

		return $this->doRender($error);
	}
}
