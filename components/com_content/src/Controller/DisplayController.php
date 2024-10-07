<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Site\Controller;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Input\Input;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Content Component Controller
 *
 * @since  1.5
 */
class DisplayController extends \Joomla\CMS\MVC\Controller\BaseController
{
    /**
     * @param   array                 $config   An optional associative array of configuration settings.
     *                                          Recognized key values include 'name', 'default_task', 'model_path', and
     *                                          'view_path' (this list is not meant to be comprehensive).
     * @param   ?MVCFactoryInterface  $factory  The factory.
     * @param   ?CMSApplication       $app      The Application for the dispatcher
     * @param   ?Input                $input    The Input object for the request
     *
     * @since   3.0.1
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null, $app = null, $input = null)
    {
        $this->input = Factory::getApplication()->getInput();

        // Article frontpage Editor pagebreak proxying:
        if ($this->input->get('view') === 'article' && $this->input->get('layout') === 'pagebreak') {
            $config['base_path'] = JPATH_COMPONENT_ADMINISTRATOR;
        } elseif ($this->input->get('view') === 'articles' && $this->input->get('layout') === 'modal') {
            // Article frontpage Editor article proxying:
            $config['base_path'] = JPATH_COMPONENT_ADMINISTRATOR;
        }

        parent::__construct($config, $factory, $app, $input);
    }

    /**
     * Method to display a view.
     *
     * @param   boolean  $cachable   If true, the view output will be cached.
     * @param   boolean  $urlparams  An array of safe URL parameters and their variable types.
     *                   @see        \Joomla\CMS\Filter\InputFilter::clean() for valid values.
     *
     * @return  DisplayController  This object to support chaining.
     *
     * @since   1.5
     */
    public function display($cachable = false, $urlparams = false)
    {
        $cachable = true;

        /**
         * Set the default view name and format from the Request.
         * Note we are using a_id to avoid collisions with the router and the return page.
         * Frontend is a bit messier than the backend.
         */
        $id    = $this->input->getInt('a_id');
        $vName = $this->input->getCmd('view', 'categories');
        $this->input->set('view', $vName);

        $user = $this->app->getIdentity();

        if (
            $user->id
            || ($this->input->getMethod() === 'POST'
            && (($vName === 'category' && $this->input->get('layout') !== 'blog') || $vName === 'archive'))
        ) {
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
            'Itemid'           => 'INT', ];

        // Check for edit form.
        if ($vName === 'form' && !$this->checkEditId('com_content.edit.article', $id)) {
            // Somehow the person just went to the form - we don't allow that.
            throw new \Exception(Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 403);
        }

        if ($vName === 'article' && \in_array($this->input->getMethod(), ['GET', 'POST'])) {
            // Get/Create the model
            if ($model = $this->getModel($vName)) {
                if (ComponentHelper::getParams('com_content')->get('record_hits', 1) == 1) {
                    $model->hit();
                }
            }
        }

        parent::display($cachable, $safeurlparams);

        return $this;
    }
}
