<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Contact\Site\Controller;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Contact Component Controller
 *
 * @since  1.5
 */
class DisplayController extends BaseController
{
    /**
     * @param   array                     $config   An optional associative array of configuration settings.
     *                                              Recognized key values include 'name', 'default_task', 'model_path', and
     *                                              'view_path' (this list is not meant to be comprehensive).
     * @param   ?MVCFactoryInterface      $factory  The factory.
     * @param   ?CMSApplication           $app      The Application for the dispatcher
     * @param   ?\Joomla\CMS\Input\Input  $input    The Input object for the request
     *
     * @since   3.0
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null, $app = null, $input = null)
    {
        // Contact frontpage Editor contacts proxying.
        $input = Factory::getApplication()->getInput();

        if ($input->get('view') === 'contacts' && $input->get('layout') === 'modal') {
            $config['base_path'] = JPATH_ADMINISTRATOR . '/components/com_contact';
        }

        parent::__construct($config, $factory, $app, $input);
    }

    /**
     * Method to display a view.
     *
     * @param   boolean  $cachable   If true, the view output will be cached
     * @param   array    $urlparams  An array of safe URL parameters and their variable types.
     *                   @see        \Joomla\CMS\Filter\InputFilter::clean() for valid values.
     *
     * @return  DisplayController  This object to support chaining.
     *
     * @since   1.5
     */
    public function display($cachable = false, $urlparams = [])
    {
        if ($this->app->getUserState('com_contact.contact.data') === null) {
            $cachable = true;
        }

        // Set the default view name and format from the Request.
        $vName = $this->input->get('view', 'categories');
        $this->input->set('view', $vName);

        if ($this->app->getIdentity()->id) {
            $cachable = false;
        }

        $safeurlparams = [
            'catid'            => 'INT',
            'id'               => 'INT',
            'cid'              => 'ARRAY',
            'year'             => 'INT',
            'month'            => 'INT',
            'limit'            => 'UINT',
            'limitstart'       => 'UINT',
            'showall'          => 'INT',
            'return'           => 'BASE64',
            'filter'           => 'STRING',
            'filter_order'     => 'CMD',
            'filter_order_Dir' => 'CMD',
            'filter-search'    => 'STRING',
            'print'            => 'BOOLEAN',
            'lang'             => 'CMD',
        ];

        parent::display($cachable, $safeurlparams);

        return $this;
    }
}
