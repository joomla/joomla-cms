<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Site\Controller;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Reset controller class for Users.
 *
 * @since  1.6
 */
class RemindController extends BaseController
{
    /**
     * Method to request a username reminder.
     *
     * @return  boolean
     *
     * @since   1.6
     */
    public function remind()
    {
        // Check the request token.
        $this->checkToken('post');

        /** @var \Joomla\Component\Users\Site\Model\RemindModel $model */
        $model = $this->getModel('Remind', 'Site');
        $data  = $this->input->post->get('jform', [], 'array');

        // Submit the password reset request.
        $return = $model->processRemindRequest($data);

        // Check for a hard error.
        if ($return == false && JDEBUG) {
            // The request failed.
            // Go back to the request form.
            $message = Text::sprintf('COM_USERS_REMIND_REQUEST_FAILED', $model->getError());
            $this->setRedirect(Route::_('index.php?option=com_users&view=remind', false), $message, 'notice');

            return false;
        }

        // To not expose if the user exists or not we send a generic message.
        $message = Text::_('COM_USERS_REMIND_REQUEST');
        $this->setRedirect(Route::_('index.php?option=com_users&view=login', false), $message, 'notice');

        return true;
    }
}
