<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Error;

use Joomla\CMS\Document\Document;
use Joomla\CMS\Document\FactoryInterface;
use Joomla\CMS\Factory;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Base class for error page renderers
 *
 * @since  4.0.0
 */
abstract class AbstractRenderer implements RendererInterface
{
    /**
     * The Document instance
     *
     * @var    Document
     * @since  4.0.0
     */
    protected $document;

    /**
     * The format (type) of the error page
     *
     * @var    string
     * @since  4.0.0
     */
    protected $type;

    /**
     * Retrieve the Document instance attached to this renderer
     *
     * @return  Document
     *
     * @since   4.0.0
     */
    public function getDocument(): Document
    {
        // Load the document if not already
        if (!$this->document) {
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
     * @since   4.0.0
     * @throws  \InvalidArgumentException
     */
    public static function getRenderer(string $type)
    {
        // Build the class name
        $class = __NAMESPACE__ . '\\Renderer\\' . ucfirst(strtolower($type)) . 'Renderer';

        // First check if an object may exist in the container and prefer that over everything else
        if (Factory::getContainer()->has($class)) {
            return Factory::getContainer()->get($class);
        }

        // Next check if a local class exists and use that
        if (class_exists($class)) {
            return new $class();
        }

        // 404 Resource Not Found
        throw new \InvalidArgumentException(sprintf('There is not an error renderer for the "%s" format.', $type));
    }

    /**
     * Create the Document object for this renderer
     *
     * @return  Document
     *
     * @since   4.0.0
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
        if (Factory::$language) {
            $attributes['language']  = Factory::getLanguage()->getTag();
            $attributes['direction'] = Factory::getLanguage()->isRtl() ? 'rtl' : 'ltr';
        }

        return Factory::getContainer()->get(FactoryInterface::class)->createDocument($this->type, $attributes);
    }
}
