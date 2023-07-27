<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\View;

use Joomla\CMS\Document\Document;
use Joomla\CMS\Document\JsonDocument;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Base class for a Joomla Json View
 *
 * Class holding methods for displaying presentation data.
 *
 * @since  4.0.0
 */
class JsonView extends AbstractView
{
    /**
     * The base path of the view
     *
     * @var    string
     * @since  4.0.0
     */
    protected $_basePath = null;

    /**
     * The output of the view.
     *
     * @var    array
     * @since  4.0.0
     */
    protected $_output = [];

    /**
     * Constructor
     *
     * @param   array  $config  The active document object
     *
     * @since   4.0.0
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        // Set a base path for use by the view
        if ($this->_basePath === null) {
            $this->_basePath = JPATH_COMPONENT;
        }
    }

    /**
     * Method to set the document object
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \InvalidArgumentException
     */
    public function setDocument(Document $document): void
    {
        if (!$document instanceof JsonDocument) {
            throw new \InvalidArgumentException(sprintf('%s requires an instance of %s', static::class, JsonDocument::class));
        }

        parent::setDocument($document);
    }

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function display($tpl = null)
    {
        // Serializing the output
        $result = json_encode($this->_output);

        // Pushing output to the document
        $this->getDocument()->setBuffer($result);
    }
}
