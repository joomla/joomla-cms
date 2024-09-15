<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Document\Opensearch;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Data object representing an OpenSearch URL
 *
 * @since  1.7.0
 */
class OpensearchUrl
{
    /**
     * Type item element
     *
     * required
     *
     * @var    string
     * @since  1.7.0
     */
    public $type = 'text/html';

    /**
     * Rel item element
     *
     * required
     *
     * @var    string
     * @since  1.7.0
     */
    public $rel = 'results';

    /**
     * Template item element. Has to contain the {searchTerms} parameter to work.
     *
     * required
     *
     * @var    string
     * @since  1.7.0
     */
    public $template;
}
