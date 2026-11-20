<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Menus\Administrator\Controller;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Router\Route;
use Joomla\Input\Input;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The Menu Item Controller
 *
 * @since  1.6
 */
class ItemsController extends AdminController
{
    /**
     * Constructor.
     *
     * @param   array                 $config   An optional associative array of configuration settings.
     * @param   ?MVCFactoryInterface  $factory  The factory.
     * @param   ?CMSApplication       $app      The Application for the dispatcher
     * @param   ?Input                $input    Input
     *
     * @since  1.6
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null, $app = null, $input = null)
    {
        parent::__construct($config, $factory, $app, $input);

        $this->registerTask('unsetDefault', 'setDefault');
    }

    /**
     * Proxy for getModel.
     *
     * @param   string  $name    The model name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  object  The model.
     *
     * @since   1.6
     */
    public function getModel($name = 'Item', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Method to get the number of published frontend menu items for quickicons
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function getQuickiconContent()
    {
        $model = $this->getModel('Items');

        $model->setState('filter.published', 1);
        $model->setState('filter.client_id', 0);

        $amount = (int) $model->getTotal();

        $result = [];

        $result['amount'] = $amount;
        $result['sronly'] = Text::plural('COM_MENUS_ITEMS_N_QUICKICON_SRONLY', $amount);
        $result['name']   = Text::plural('COM_MENUS_ITEMS_N_QUICKICON', $amount);

        echo new JsonResponse($result);
    }

    /**
     * Rebuild the nested set tree.
     *
     * @return  boolean  False on failure or error, true on success.
     *
     * @since   1.6
     */
    public function rebuild()
    {
        $this->checkToken();

        $this->setRedirect('index.php?option=com_menus&view=items&menutype=' . $this->input->getCmd('menutype'));

        /** @var \Joomla\Component\Menus\Administrator\Model\ItemModel $model */
        $model = $this->getModel();

        if ($model->rebuild()) {
            // Reorder succeeded.
            $this->setMessage(Text::_('COM_MENUS_ITEMS_REBUILD_SUCCESS'));

            return true;
        }

        // Rebuild failed.
        $this->setMessage(Text::sprintf('COM_MENUS_ITEMS_REBUILD_FAILED'), 'error');

        return false;
    }

    /**
     * Method to set the home property for a list of items
     *
     * @return  void
     *
     * @since   1.6
     */
    public function setDefault()
    {
        // Check for request forgeries
        $this->checkToken('request');

        $app = $this->app;

        // Get items to publish from the request.
        $cid   = (array) $this->input->get('cid', [], 'int');
        $data  = ['setDefault' => 1, 'unsetDefault' => 0];
        $task  = $this->getTask();
        $value = ArrayHelper::getValue($data, $task, 0, 'int');

        // Remove zero values resulting from input filter
        $cid = array_filter($cid);

        if (empty($cid)) {
            $this->setMessage(Text::_($this->text_prefix . '_NO_ITEM_SELECTED'), 'warning');
        } else {
            // Get the model.
            $model = $this->getModel();

            // Publish the items.
            if (!$model->setHome($cid, $value)) {
                $this->setMessage($model->getError(), 'warning');
            } else {
                if ($value == 1) {
                    $ntext = 'COM_MENUS_ITEMS_SET_HOME';
                } else {
                    $ntext = 'COM_MENUS_ITEMS_UNSET_HOME';
                }

                $this->setMessage(Text::plural($ntext, \count($cid)));
            }
        }

        $this->setRedirect(
            Route::_(
                'index.php?option=' . $this->option . '&view=' . $this->view_list
                . '&menutype=' . $app->getUserState('com_menus.items.menutype'),
                false
            )
        );
    }

    /**
     * Method to publish a list of items
     *
     * @return  void
     *
     * @since   3.6.0
     */
    public function publish()
    {
        // Check for request forgeries
        $this->checkToken();

        // Get items to publish from the request.
        $cid   = (array) $this->input->get('cid', [], 'int');
        $data  = ['publish' => 1, 'unpublish' => 0, 'trash' => -2, 'report' => -3];
        $task  = $this->getTask();
        $value = ArrayHelper::getValue($data, $task, 0, 'int');

        // Remove zero values resulting from input filter
        $cid = array_filter($cid);

        if (empty($cid)) {
            try {
                Log::add(Text::_($this->text_prefix . '_NO_ITEM_SELECTED'), Log::WARNING, 'jerror');
            } catch (\RuntimeException) {
                $this->setMessage(Text::_($this->text_prefix . '_NO_ITEM_SELECTED'), 'warning');
            }
        } else {
            // Get the model.
            $model = $this->getModel();

            // Publish the items.
            try {
                $model->publish($cid, $value);
                $errors      = $model->getErrors();
                $messageType = 'message';

                if ($value == 1) {
                    if ($errors) {
                        $messageType = 'error';
                        $ntext       = $this->text_prefix . '_N_ITEMS_FAILED_PUBLISHING';
                    } else {
                        $ntext = $this->text_prefix . '_N_ITEMS_PUBLISHED';
                    }
                } elseif ($value == 0) {
                    $ntext = $this->text_prefix . '_N_ITEMS_UNPUBLISHED';
                } else {
                    $ntext = $this->text_prefix . '_N_ITEMS_TRASHED';
                }

                $this->setMessage(Text::plural($ntext, \count($cid)), $messageType);
            } catch (\Exception $e) {
                $this->setMessage($e->getMessage(), 'error');
            }
        }

        $this->setRedirect(
            Route::_(
                'index.php?option=' . $this->option . '&view=' . $this->view_list . '&menutype=' .
                $this->app->getUserState('com_menus.items.menutype'),
                false
            )
        );
    }

    /**
     * Gets the URL arguments to append to a list redirect.
     *
     * @return  string  The arguments to append to the redirect URL.
     *
     * @since   4.0.0
     */
    protected function getRedirectToListAppend()
    {
        return '&menutype=' . $this->app->getUserState('com_menus.items.menutype');
    }
}
