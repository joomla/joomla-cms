<?php

/**
 * @package     Joomla.API
 * @subpackage  com_languages
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Languages\Api\Controller;

use Joomla\CMS\MVC\Controller\ApiController;

/**
 * The languages controller
 *
 * @since  4.0.0
 */
class LanguagesController extends ApiController
{
    /**
     * The content type of the item.
     *
     * @var    string
     * @since  4.0.0
     */
    protected $contentType = 'languages';

    /**
     * The default view for the display method.
     *
     * @var    string
     * @since  3.0
     */
    protected $default_view = 'languages';
}
