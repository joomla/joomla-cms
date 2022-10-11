<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Document;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Defines the trait for a Document Aware Class.
 *
 * @since  __DEPLOY_VERSION__
 */
trait DocumentAwareTrait
{
    /**
     * Document Object
     *
     * @var    Document
     * @since  __DEPLOY_VERSION__
     */
    private $document;

    /**
     * Get the Document.
     *
     * @return  Document
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getDocument(): Document
    {
        return $this->document;
    }

    /**
     * Set the Document.
     *
     * @param   Document  $document  The Document object.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function setDocument(Document $document): void
    {
        $this->document = $document;
    }
}
