<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Administrator\Controller;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Input\Input;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Router\Route;
use Joomla\CMS\User\UserFactory;
use Joomla\CMS\User\UserFactoryAwareTrait;
use Joomla\Component\Users\Administrator\Model\UserModel;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Users list controller class.
 *
 * @since  1.6
 */
class UsersController extends AdminController
{
    use UserFactoryAwareTrait;
    /**
     * @var    string  The prefix to use with controller messages.
     * @since  1.6
     */
    protected $text_prefix = 'COM_USERS_USERS';

    /**
     * Constructor.
     *
     * @param   array                $config   An optional associative array of configuration settings.
     * @param   MVCFactoryInterface  $factory  The factory.
     * @param   CMSApplication       $app      The CMSApplication for the dispatcher
     * @param   Input                $input    Input
     *
     * @since  1.6
     * @see    BaseController
     * @throws \Exception
     */
    public function __construct($config = [], MVCFactoryInterface $factory = null, $app = null, $input = null)
    {
        parent::__construct($config, $factory, $app, $input);

        $this->registerTask('block', 'changeBlock');
        $this->registerTask('unblock', 'changeBlock');
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
    public function getModel($name = 'User', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Method to change the block status on a record.
     *
     * @return  void
     *
     * @since   1.6
     */
    public function changeBlock()
    {
        // Check for request forgeries.
        $this->checkToken();

        $ids    = (array) $this->input->get('cid', [], 'int');
        $values = ['block' => 1, 'unblock' => 0];
        $task   = $this->getTask();
        $value  = ArrayHelper::getValue($values, $task, 0, 'int');

        // Remove zero values resulting from input filter
        $ids = array_filter($ids);

        if (empty($ids)) {
            $this->setMessage(Text::_('COM_USERS_USERS_NO_ITEM_SELECTED'), 'warning');
        } else {
            // Get the model.
            $model = $this->getModel();

            // Change the state of the records.
            if (!$model->block($ids, $value)) {
                $this->setMessage($model->getError(), 'error');
            } else {
                if ($value == 1) {
                    $this->setMessage(Text::plural('COM_USERS_N_USERS_BLOCKED', count($ids)));
                } elseif ($value == 0) {
                    $this->setMessage(Text::plural('COM_USERS_N_USERS_UNBLOCKED', count($ids)));
                }
            }
        }

        $this->setRedirect('index.php?option=com_users&view=users');
    }

    /**
     * Method to activate a record.
     *
     * @return  void
     *
     * @since   1.6
     */
    public function activate()
    {
        // Check for request forgeries.
        $this->checkToken();

        $ids = (array) $this->input->get('cid', [], 'int');

        // Remove zero values resulting from input filter
        $ids = array_filter($ids);

        if (empty($ids)) {
            $this->setMessage(Text::_('COM_USERS_USERS_NO_ITEM_SELECTED'), 'error');
        } else {
            // Get the model.
            $model = $this->getModel();

            // Change the state of the records.
            if (!$model->activate($ids)) {
                $this->setMessage($model->getError(), 'error');
            } else {
                $this->setMessage(Text::plural('COM_USERS_N_USERS_ACTIVATED', count($ids)));
            }
        }

        $this->setRedirect('index.php?option=com_users&view=users');
    }

    /**
     * Method to get the number of active users
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function getQuickiconContent()
    {
        $model = $this->getModel('Users');

        $model->setState('filter.state', 0);

        $amount = (int) $model->getTotal();

        $result = [];

        $result['amount'] = $amount;
        $result['sronly'] = Text::plural('COM_USERS_N_QUICKICON_SRONLY', $amount);
        $result['name']   = Text::plural('COM_USERS_N_QUICKICON', $amount);

        echo new JsonResponse($result);
    }

    /**
     * Removes an item.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function delete()
    {
        $error = false;

        // Check for request forgeries
        $this->checkToken();

        // Get items to remove from the request.
        $cid = (array) $this->input->get('cid', [], 'int');

        // Remove zero values resulting from input filter
        $cid = array_filter($cid);

        if (empty($cid)) {
            $this->app->getLogger()->warning(
                Text::_($this->text_prefix . '_NO_ITEM_SELECTED'),
                ['category' => 'jerror']
            );

            $error = true;
        }

        $fallbackUserId = (int) $this->input->get('beforeDeleteUser')['fallbackUserIdOnDelete'] ?? 0;
        $userFactory    = Factory::getContainer()->get(UserFactory::class);
        $isUserExists   = $fallbackUserId && $userFactory->loadUserById($fallbackUserId)->id === $fallbackUserId;

        if (!$error && empty($fallbackUserId)) {
            $this->app->getLogger()->error(
                Text::_('COM_USERS_BEFORE_DELETE_USER_ERROR_FALLBACK_USER_NOT_SET_MSG'),
                ['category' => 'jerror']
            );

            $error = true;
        }

        if (!$error && in_array($fallbackUserId, $cid)) {
            $this->app->getLogger()->error(
                Text::_('COM_USERS_BEFORE_DELETE_USER_ERROR_FALLBACK_USER_CONNECTED_MSG'),
                ['category' => 'jerror']
            );

            $error = true;
        }

        if (!$error && !$isUserExists) {
            $this->app->getLogger()->error(
                Text::sprintf(
                    'COM_USERS_BEFORE_DELETE_USER_ERROR_FALLBACK_USER_ID_NOT_EXISTS_MSG',
                    $fallbackUserId,
                ),
                ['category' => 'jerror']
            );

            $error = true;
        }

        if ($error) {
            $this->app->getLogger()->error(
                Text::_('COM_USERS_BEFORE_DELETE_USER_ERROR_USER_NOT_DELETED_MSG'),
                ['category' => 'jerror']
            );
        } else {
            // Get the model.
            /** @var UserModel $model */
            $model = $this->getModel();

            // Remove the items.
            if ($model->delete($cid)) {
                $this->setMessage(Text::plural($this->text_prefix . '_N_ITEMS_DELETED', \count($cid)));
            } else {
                $this->setMessage($model->getError(), 'error');
            }

            // Invoke the postDelete method to allow for the child class to access the model.
            $this->postDeleteHook($model, $cid);
        }

        $this->setRedirect(
            Route::_(
                'index.php?option=' . $this->option . '&view=' . $this->view_list
                . $this->getRedirectToListAppend(),
                false
            )
        );
    }
}
