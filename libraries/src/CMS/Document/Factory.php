<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Document;

defined('_JEXEC') or die;

/**
 * Default factory for creating JDocument objects
 *
 * @since  __DEPLOY_VERSION__
 */
class Factory implements FactoryInterface
{
	/**
	 * Creates a new JDocument object for the requested format.
	 *
	 * @param   string  $type        The document type to instantiate
	 * @param   array   $attributes  Array of attributes
	 *
	 * @return  \JDocument
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function createDocument($type = 'html', array $attributes = array())
	{
		$type  = preg_replace('/[^A-Z0-9_\.-]/i', '', $type);
		$ntype = null;

		// Determine the path and class
		$class = '\\JDocument' . ucfirst($type);

		if (!class_exists($class))
		{
			$ntype = $type;
			$class = '\\JDocumentRaw';
		}

		// Inject this factory into the document unless one was provided
		if (!isset($attributes['factory']))
		{
			$attributes['factory'] = $this;
		}

		$instance = new $class($attributes);

		if (!is_null($ntype))
		{
			// Set the type to the Document type originally requested
			$instance->setType($ntype);
		}

		return $instance;
	}

	/**
	 * Creates a new renderer object.
	 *
	 * @param   \JDocument  $document  The JDocument instance to attach to the renderer
	 * @param   string      $type      The renderer type to instantiate
	 *
	 * @return  RendererInterface
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function createRenderer(\JDocument $document, $type)
	{
		// New class name format adds the format type to the class name
		$class = '\\JDocumentRenderer' . ucfirst($document->getType()) . ucfirst($type);

		if (!class_exists($class))
		{
			// "Legacy" class name structure
			$class = '\\JDocumentRenderer' . $type;

			if (!class_exists($class))
			{
				throw new \RuntimeException('Unable to load renderer class', 500);
			}
		}

		return new $class($document);
	}
}
