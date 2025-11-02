<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_messages
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Messages\Administrator\Controller;

use Joomla\CMS\Application\CMSWebApplicationInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Messages Component Message Model
 *
 * @since  1.6
 */
class ConfigController extends BaseController
{
    /**
     * Method to save a record.
     *
     * @return  boolean
     *
     * @since   1.6
     */
    public function save()
    {
        // Check for request forgeries.
        $this->checkToken();

        $model = $this->getModel('Config');
        $data  = $this->input->post->get('jform', [], 'array');

        // Validate the posted data.
        $form = $model->getForm();

        if (!$form) {
            throw new \Exception($model->getError(), 500);
        }

        $data = $model->validate($form, $data);

        // Check for validation errors.
        if ($data === false) {
            // Get the validation messages.
            $errors = $model->getErrors();

            // Push up to three validation messages out to the user.
            for ($i = 0, $n = \count($errors); $i < $n && $i < 3; $i++) {
                if ($errors[$i] instanceof \Exception) {
                    $this->app->enqueueMessage($errors[$i]->getMessage(), CMSWebApplicationInterface::MSG_ERROR);
                } else {
                    $this->app->enqueueMessage($errors[$i], CMSWebApplicationInterface::MSG_ERROR);
                }
            }

            // Redirect back to the main list.
            $this->setRedirect(Route::_('index.php?option=com_messages&view=messages', false));

            return false;
        }

        // Attempt to save the data.
        if (!$model->save($data)) {
            // Redirect back to the main list.
            $this->setMessage(Text::sprintf('JERROR_SAVE_FAILED', $model->getError()), 'warning');
            $this->setRedirect(Route::_('index.php?option=com_messages&view=messages', false));

            return false;
        }

        // Redirect to the list screen.
        $this->setMessage(Text::_('COM_MESSAGES_CONFIG_SAVED'));
        $this->setRedirect(Route::_('index.php?option=com_messages&view=messages', false));

        return true;
    }

    /**
     * Cancel operation.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function cancel()
    {
        $this->setRedirect(Route::_('index.php?option=com_messages&view=messages', false));
    }
}
