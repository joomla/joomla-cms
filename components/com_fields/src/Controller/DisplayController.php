<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_fields
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Fields\Site\Controller;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

/**
 * Base controller class for Fields Component.
 *
 * @since  3.7.0
 */
class DisplayController extends \Joomla\CMS\MVC\Controller\BaseController
{
    /**
     * @param   array                         $config   An optional associative array of configuration settings.
     *                                                  Recognized key values include 'name', 'default_task', 'model_path', and
     *                                                  'view_path' (this list is not meant to be comprehensive).
     * @param   MVCFactoryInterface|null      $factory  The factory.
     * @param   CMSApplication|null           $app      The Application for the dispatcher
     * @param   \Joomla\CMS\Input\Input|null  $input    The request's input object
     *
     * @since   3.7.0
     */
    public function __construct($config = array(), MVCFactoryInterface $factory = null, $app = null, $input = null)
    {
        // Frontpage Editor Fields Button proxying.
        if ($input->get('view') === 'fields' && $input->get('layout') === 'modal') {
            // Load the backend language file.
            $app->getLanguage()->load('com_fields', JPATH_ADMINISTRATOR);

            $config['base_path'] = JPATH_COMPONENT_ADMINISTRATOR;
        }

        parent::__construct($config, $factory, $app, $input);
    }
}
