<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Site\Controller;

use Joomla\CMS\Router\Route;
use Joomla\Component\Users\Administrator\Controller\MethodsController as AdminMethodsController;

/**
 * Multi-factor Authentication methods selection and management controller
 *
 * @since 4.2.0
 */
class MethodsController extends AdminMethodsController
{
    /**
     * Execute a task by triggering a Method in the derived class.
     *
     * @param   string  $task    The task to perform.
     *
     * @return  mixed   The value returned by the called Method.
     *
     * @throws  \Exception
     * @since   4.2.0
     */
    public function execute($task)
    {
        try {
            return parent::execute($task);
        } catch (\Exception $e) {
            if ($e->getCode() !== 403) {
                throw $e;
            }

            if ($this->app->getIdentity()->guest) {
                $this->setRedirect(Route::_('index.php?option=com_users&view=login', false));

                return null;
            }
        }

        return null;
    }
}
