<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_contenthistory
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Contenthistory\Site\Controller;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Input\Input;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * History manager display controller.
 *
 * @since  4.0.0
 */
class DisplayController extends BaseController
{
    /**
     * @param   array                 $config   An optional associative array of configuration settings.
     * @param   ?MVCFactoryInterface  $factory  The factory.
     * @param   ?CMSApplication       $app      The Application for the dispatcher
     * @param   ?Input                $input    The Input object for the request
     *
     * @since   3.0
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null, $app = null, $input = null)
    {
        $config['base_path'] = JPATH_COMPONENT_ADMINISTRATOR;

        parent::__construct($config, $factory, $app, $input);
    }
}
