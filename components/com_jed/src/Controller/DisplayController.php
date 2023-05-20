<?php

/**
 * @package    JED
 *
 * @copyright  (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Jed\Component\Jed\Site\Controller;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Exception;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

use function defined;

/**
 * Display Component Controller
 *
 * @since  4.0.0
 */
class DisplayController extends BaseController
{
    /**
     * Constructor.
     *
     * @param   array                     $config   An optional associative array of configuration settings.
     *                                              Recognized key values include 'name', 'default_task', 'model_path', and
     *                                              'view_path' (this list is not meant to be comprehensive).
     * @param   MVCFactoryInterface|null  $factory  The factory.
     * @param   null                      $app      The JApplication for the dispatcher
     * @param   null                      $input    Input
     *
     * @since  4.0.0
     */
    public function __construct($config = [], MVCFactoryInterface $factory = null, $app = null, $input = null)
    {
        parent::__construct($config, $factory, $app, $input);
    }

    /**
     * Method to display a view.
     *
     * @param   boolean  $cachable   If true, the view output will be cached.
     * @param   boolean  $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link InputFilter::clean()}.
     *
     * @return  BaseController  This object to support chaining.
     *
     * @since  4.0.0
     * @throws Exception
     */
    public function display($cachable = false, $urlparams = []): BaseController
    {

        $view = $this->input->getCmd('view', 'homepage');
        if ($view == 'featured') {
            $view = "homepage";
        }
        $this->input->set('view', $view);


        parent::display($cachable, $urlparams);

        return $this;
    }
}
