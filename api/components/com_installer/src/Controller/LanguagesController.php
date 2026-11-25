<?php

/**
 * @package     Joomla.API
 * @subpackage  com_installer
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Installer\Api\Controller;

use Joomla\CMS\MVC\Controller\ApiController;

\defined('_JEXEC') or die;

/**
 * The manage controller
 *
 * @since  5.2.0
 */
class LanguagesController extends ApiController
{
    /**
     * The content type of the item.
     *
     * @var    string
     * @since  5.2.0
     */
    protected $contentType = 'languages';

    /**
     * The default view for the display method.
     *
     * @var    string
     * @since  5.2.0
     */
    protected $default_view = 'languages';
}
