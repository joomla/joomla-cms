<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Config\Administrator\Controller;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Input\Input;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Note: this view is intended only to be opened in a popup
 *
 * @since  1.5
 */
class ComponentController extends FormController
{
    /**
     * Constructor.
     *
     * @param   array                $config   An optional associative array of configuration settings.
     * Recognized key values include 'name', 'default_task', 'model_path', and
     * 'view_path' (this list is not meant to be comprehensive).
     * @param   MVCFactoryInterface  $factory  The factory.
     * @param   CMSApplication       $app      The Application for the dispatcher
     * @param   Input                $input    Input
     *
     * @since   3.0
     */
    public function __construct($config = [], MVCFactoryInterface $factory = null, $app = null, $input = null)
    {
        parent::__construct($config, $factory, $app, $input);

        // Map the apply task to the save method.
        $this->registerTask('apply', 'save');
    }

    /**
     * Method to save component configuration.
     *
     * @param   string  $key     The name of the primary key of the URL variable.
     * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
     *
     * @return  boolean
     *
     * @since   3.2
     */
    public function save($key = null, $urlVar = null)
    {
        // Check for request forgeries.
        $this->checkToken();

        $data    = $this->input->get('jform', [], 'ARRAY');
        $id      = $this->input->get('id', null, 'INT');
        $option  = $this->input->get('component');
        $user    = $this->app->getIdentity();
        $context = "$this->option.edit.$this->context.$option";

        /** @var \Joomla\Component\Config\Administrator\Model\ComponentModel $model */
        $model = $this->getModel('Component', 'Administrator');
        $model->setState('component.option', $option);
        $form  = $model->getForm();

        // Make sure com_joomlaupdate and com_privacy can only be accessed by SuperUser
        if (\in_array(strtolower($option), ['com_joomlaupdate', 'com_privacy'], true) && !$user->authorise('core.admin')) {
            $this->setRedirect(Route::_('index.php', false), Text::_('JERROR_ALERTNOAUTHOR'), 'error');
        }

        // Check if the user is authorised to do this.
        if (!$user->authorise('core.admin', $option) && !$user->authorise('core.options', $option)) {
            $this->setRedirect(Route::_('index.php', false), Text::_('JERROR_ALERTNOAUTHOR'), 'error');
        }

        // Remove the permissions rules data if user isn't allowed to edit them.
        if (!$user->authorise('core.admin', $option) && isset($data['params']) && isset($data['params']['rules'])) {
            unset($data['params']['rules']);
        }

        $returnUri = $this->input->post->get('return', null, 'base64');

        $redirect = '';

        if (!empty($returnUri)) {
            $redirect = '&return=' . urlencode($returnUri);
        }

        // Validate the posted data.
        $return = $model->validate($form, $data);

        // Check for validation errors.
        if ($return === false) {
            // Save the data in the session.
            $this->app->setUserState($context . '.data', $data);

            // Redirect back to the edit screen.
            $this->setRedirect(
                Route::_('index.php?option=com_config&view=component&component=' . $option . $redirect, false),
                $model->getError(),
                'error'
            );

            return false;
        }

        // Attempt to save the configuration.
        $data = [
            'params' => $return,
            'id'     => $id,
            'option' => $option,
        ];

        try {
            $model->save($data);
        } catch (\RuntimeException $e) {
            // Save the data in the session.
            $this->app->setUserState($context . '.data', $data);

            // Save failed, go back to the screen and display a notice.
            $this->setRedirect(
                Route::_('index.php?option=com_config&view=component&component=' . $option . $redirect, false),
                Text::_('JERROR_SAVE_FAILED', $e->getMessage()),
                'error'
            );

            return false;
        }

        // Clear session data.
        $this->app->setUserState($context . '.data', null);

        // Set the redirect based on the task.
        switch ($this->input->get('task')) {
            case 'apply':
                $this->setRedirect(
                    Route::_('index.php?option=com_config&view=component&component=' . $option . $redirect, false),
                    Text::_('COM_CONFIG_SAVE_SUCCESS'),
                    'message'
                );

                break;

            case 'save':
                $this->setMessage(Text::_('COM_CONFIG_SAVE_SUCCESS'), 'message');

                // No break

            default:
                $redirect = 'index.php?option=' . $option;

                if (!empty($returnUri)) {
                    $redirect = base64_decode($returnUri);
                }

                // Don't redirect to an external URL.
                if (!Uri::isInternal($redirect)) {
                    $redirect = Uri::base();
                }

                $this->setRedirect(Route::_($redirect, false));
        }

        return true;
    }

    /**
     * Method to cancel global configuration component.
     *
     * @param   string  $key  The name of the primary key of the URL variable.
     *
     * @return  boolean
     *
     * @since   3.2
     */
    public function cancel($key = null)
    {
        $component = $this->input->get('component');

        // Clear session data.
        $this->app->setUserState("$this->option.edit.$this->context.$component.data", null);

        // Calculate redirect URL
        $returnUri = $this->input->post->get('return', null, 'base64');

        $redirect = 'index.php?option=' . $component;

        if (!empty($returnUri)) {
            $redirect = base64_decode($returnUri);
        }

        // Don't redirect to an external URL.
        if (!Uri::isInternal($redirect)) {
            $redirect = Uri::base();
        }

        $this->setRedirect(Route::_($redirect, false));

        return true;
    }
}
