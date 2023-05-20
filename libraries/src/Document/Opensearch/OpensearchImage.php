<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Document\Opensearch;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Data object representing an OpenSearch image
 *
 * @since  1.7.0
 */
class OpensearchImage
{
    /**
     * The images MIME type
     *
     * required
     *
     * @var    string
     * @since  1.7.0
     */
    public $type = '';

    /**
     * URL of the image or the image as base64 encoded value
     *
     * required
     *
     * @var    string
     * @since  1.7.0
     */
    public $data = '';

    /**
     * The image's width
     *
     * required
     *
     * @var    string
     * @since  1.7.0
     */
    public $width;

    /**
     * The image's height
     *
     * required
     *
     * @var    string
     * @since  1.7.0
     */
    public $height;
}
