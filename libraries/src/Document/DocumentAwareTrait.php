<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Document;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Defines the trait for a document aware class.
 *
 * @since  __DEPLOY_VERSION__
 */
trait DocumentAwareTrait
{
    /**
     * Document
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
     * @throws  \UnexpectedValueException May be thrown if the document has not been set.
     */
    protected function getDocument(): Document
    {
        if ($this->document) {
            return $this->document;
        }

        throw new \UnexpectedValueException('Document not set in ' . __CLASS__);
    }

    /**
     * Set the document to use.
     *
     * @param   Document  $document  The document to use
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
