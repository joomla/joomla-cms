<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_modules
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Modules\Site\Controller;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Input\Input;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Modules manager display controller.
 *
 * @since  3.5
 */
class DisplayController extends BaseController
{
    /**
     * @param   array                     $config   An optional associative array of configuration settings.
     *                                              Recognized key values include 'name', 'default_task', 'model_path', and
     *                                              'view_path' (this list is not meant to be comprehensive).
     * @param   MVCFactoryInterface|null  $factory  The factory.
     * @param   CMSApplication|null       $app      The Application for the dispatcher
     * @param   Input|null                $input    The Input object for the request
     *
     * @since   3.0
     */
    public function __construct($config = [], MVCFactoryInterface $factory = null, $app = null, $input = null)
    {
        $this->input = Factory::getApplication()->getInput();

        // Modules frontpage Editor Module proxying.
        if ($this->input->get('view') === 'modules' && $this->input->get('layout') === 'modal') {
            $config['base_path'] = JPATH_COMPONENT_ADMINISTRATOR;
        }

        parent::__construct($config, $factory, $app, $input);
    }
}
