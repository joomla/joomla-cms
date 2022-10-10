<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\View;

// phpcs:disable PSR1.Files.SideEffects
use Joomla\CMS\Document\JsonDocument;

\defined('JPATH_PLATFORM') or die;
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
    protected $_output = array();

    /**
     * Constructor
     *
     * @since   4.0.0
     */
    public function __construct(JsonDocument $document)
    {
		parent::__construct($document);

        // Set a base path for use by the view
        if ($this->_basePath === null) {
            $this->_basePath = JPATH_COMPONENT;
        }
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
        $this->document->setBuffer($result);
    }
}
