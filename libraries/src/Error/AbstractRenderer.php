<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Error;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Document\Document;
use Joomla\CMS\Factory;

/**
 * Base class for error page renderers
 *
 * @since  4.0
 */
abstract class AbstractRenderer implements RendererInterface
{
	/**
	 * The Document instance
	 *
	 * @var    Document
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
	 * Retrieve the Document instance attached to this renderer
	 *
	 * @return  Document
	 *
	 * @since   4.0
	 */
	public function getDocument(): Document
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
	public static function getRenderer(string $type)
	{
		// Build the class name
		$class = __NAMESPACE__ . '\\Renderer\\' . ucfirst(strtolower($type)) . 'Renderer';

		// First check if an object may exist in the container and prefer that over everything else
		if (Factory::getContainer()->has($class))
		{
			return Factory::getContainer()->get($class);
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
	 * Create the Document object for this renderer
	 *
	 * @return  Document
	 *
	 * @since   4.0
	 */
	protected function loadDocument(): Document
	{
		$attributes = [
			'charset'   => 'utf-8',
			'lineend'   => 'unix',
			'tab'       => "\t",
			'language'  => 'en-GB',
			'direction' => 'ltr',
		];

		// If there is a Language instance in Factory then let's pull the language and direction from its metadata
		if (Factory::$language)
		{
			$attributes['language']  = Factory::getLanguage()->getTag();
			$attributes['direction'] = Factory::getLanguage()->isRtl() ? 'rtl' : 'ltr';
		}

		return Document::getInstance($this->type, $attributes);
	}
}
