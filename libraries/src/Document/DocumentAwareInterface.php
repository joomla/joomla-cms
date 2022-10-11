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
 * Interface to be implemented by classes depending on a document instance.
 *
 * @since  __DEPLOY_VERSION__
 */
interface DocumentAwareInterface
{
    /**
     * Set the document object to use.
     *
     * @param   Document  $document  The cache controller factory to use.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function setDocument(Document $document): void;

    /**
     * Get the document object to use.
     *
     * @return  Document
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getDocument(): Document;
}
