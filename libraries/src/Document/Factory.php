<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Document;

\defined('_JEXEC') or die;

/**
 * Default factory for creating Document objects
 *
 * @since  4.0.0
 */
class Factory implements FactoryInterface
{
	/**
	 * Creates a new Document object for the requested format.
	 *
	 * @param   string  $type        The document type to instantiate
	 * @param   array   $attributes  Array of attributes
	 *
	 * @return  Document
	 *
	 * @since   4.0.0
	 */
	public function createDocument(string $type = 'html', array $attributes = []): Document
	{
		$type  = preg_replace('/[^A-Z0-9_\.-]/i', '', $type);
		$ntype = null;

		$class = __NAMESPACE__ . '\\' . ucfirst($type) . 'Document';

		if (!class_exists($class))
		{
			$class = 'JDocument' . ucfirst($type);
		}

		if (!class_exists($class))
		{
			$ntype = $type;
			$class = RawDocument::class;
		}

		// Inject this factory into the document unless one was provided
		if (!isset($attributes['factory']))
		{
			$attributes['factory'] = $this;
		}

		/** @var Document $instance */
		$instance = new $class($attributes);

		if (!\is_null($ntype))
		{
			// Set the type to the Document type originally requested
			$instance->setType($ntype);
		}

		return $instance;
	}

	/**
	 * Creates a new renderer object.
	 *
	 * @param   Document  $document  The Document instance to attach to the renderer
	 * @param   string    $type      The renderer type to instantiate
	 * @param   string    $docType   The document type the renderer is part of
	 *
	 * @return  RendererInterface
	 *
	 * @since   4.0.0
	 */
	public function createRenderer(Document $document, string $type, string $docType = ''): RendererInterface
	{
		$docType = $docType ? ucfirst($docType) : ucfirst($document->getType());

		// Determine the path and class
		$class = __NAMESPACE__ . '\\Renderer\\' . $docType . '\\' . ucfirst($type) . 'Renderer';

		if (!class_exists($class))
		{
			$class = 'JDocumentRenderer' . $docType . ucfirst($type);
		}

		if (!class_exists($class))
		{
			// "Legacy" class name structure
			$class = '\\JDocumentRenderer' . $type;

			if (!class_exists($class))
			{
				throw new \RuntimeException(sprintf('Unable to load renderer class %s', $type), 500);
			}
		}

		return new $class($document);
	}
}
