<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Menus\Administrator\Controller;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Application\CMSWebApplicationInterface;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\Component\Menus\Administrator\Helper\MenusHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The Menu Type Controller
 *
 * @since  1.6
 */
class MenuController extends FormController
{
    /**
     * Dummy method to redirect back to standard controller
     *
     * @param   boolean  $cachable   If true, the view output will be cached.
     * @param   array    $urlparams  An array of safe URL parameters and their variable types.
     *                   @see        \Joomla\CMS\Filter\InputFilter::clean() for valid values.
     *
     * @return  void
     *
     * @since   1.5
     */
    public function display($cachable = false, $urlparams = false)
    {
        $this->setRedirect(Route::_('index.php?option=com_menus&view=menus', false));
    }

    /**
     * Method to save a menu item.
     *
     * @param   string  $key     The name of the primary key of the URL variable.
     * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
     *
     * @return  boolean  True if successful, false otherwise.
     *
     * @since   1.6
     */
    public function save($key = null, $urlVar = null)
    {
        // Check for request forgeries.
        $this->checkToken();

        $app      = $this->app;
        $data     = $this->input->post->get('jform', [], 'array');
        $context  = 'com_menus.edit.menu';
        $task     = $this->getTask();
        $recordId = $this->input->getInt('id');

        // Prevent using 'main' as menutype as this is reserved for backend menus
        if (strtolower($data['menutype']) == 'main') {
            $this->setMessage(Text::_('COM_MENUS_ERROR_MENUTYPE'), 'error');

            // Redirect back to the edit screen.
            $this->setRedirect(Route::_('index.php?option=com_menus&view=menu&layout=edit' . $this->getRedirectToItemAppend($recordId), false));

            return false;
        }

        $data['menutype'] = InputFilter::getInstance()->clean($data['menutype'], 'TRIM');

        // Populate the row id from the session.
        $data['id'] = $recordId;

        // Get the model and attempt to validate the posted data.
        /** @var \Joomla\Component\Menus\Administrator\Model\MenuModel $model */
        $model = $this->getModel('Menu', '', ['ignore_request' => false]);
        $form  = $model->getForm();

        if (!$form) {
            throw new \Exception($model->getError(), 500);
        }

        $validData = $model->validate($form, $data);

        // Check for validation errors.
        if ($validData === false) {
            // Get the validation messages.
            $errors = $model->getErrors();

            // Push up to three validation messages out to the user.
            for ($i = 0, $n = \count($errors); $i < $n && $i < 3; $i++) {
                if ($errors[$i] instanceof \Exception) {
                    $app->enqueueMessage($errors[$i]->getMessage(), CMSWebApplicationInterface::MSG_ERROR);
                } else {
                    $app->enqueueMessage($errors[$i], CMSWebApplicationInterface::MSG_ERROR);
                }
            }

            // Save the data in the session.
            $app->setUserState($context . '.data', $data);

            // Redirect back to the edit screen.
            $this->setRedirect(Route::_('index.php?option=com_menus&view=menu&layout=edit' . $this->getRedirectToItemAppend($recordId), false));

            return false;
        }

        if (isset($validData['preset'])) {
            $preset = trim($validData['preset']) ?: null;

            unset($validData['preset']);
        }

        // Attempt to save the data.
        if (!$model->save($validData)) {
            // Save the data in the session.
            $app->setUserState($context . '.data', $validData);

            // Redirect back to the edit screen.
            $this->setMessage(Text::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()), 'error');
            $this->setRedirect(Route::_('index.php?option=com_menus&view=menu&layout=edit' . $this->getRedirectToItemAppend($recordId), false));

            return false;
        }

        // Import the preset selected
        if (isset($preset) && $data['client_id'] == 1) {
            // Menu Type has not been saved yet. Make sure items get the real menutype.
            $menutype = ApplicationHelper::stringURLSafe($data['menutype']);

            try {
                MenusHelper::installPreset($preset, $menutype);

                $this->setMessage(Text::_('COM_MENUS_PRESET_IMPORT_SUCCESS'));
            } catch (\Exception $e) {
                // Save was successful but the preset could not be loaded. Let it through with just a warning
                $this->setMessage(Text::sprintf('COM_MENUS_PRESET_IMPORT_FAILED', $e->getMessage()));
            }
        } else {
            $this->setMessage(Text::_('COM_MENUS_MENU_SAVE_SUCCESS'));
        }

        // Redirect the user and adjust session state based on the chosen task.
        switch ($task) {
            case 'apply':
                // Set the record data in the session.
                $recordId = $model->getState($this->context . '.id');
                $this->holdEditId($context, $recordId);
                $app->setUserState($context . '.data', null);

                // Redirect back to the edit screen.
                $this->setRedirect(Route::_('index.php?option=com_menus&view=menu&layout=edit' . $this->getRedirectToItemAppend($recordId), false));
                break;

            case 'save2new':
                // Clear the record id and data from the session.
                $this->releaseEditId($context, $recordId);
                $app->setUserState($context . '.data', null);

                // Redirect back to the edit screen.
                $this->setRedirect(Route::_('index.php?option=com_menus&view=menu&layout=edit', false));
                break;

            default:
                // Clear the record id and data from the session.
                $this->releaseEditId($context, $recordId);
                $app->setUserState($context . '.data', null);

                // Redirect to the list screen.
                $this->setRedirect(Route::_('index.php?option=com_menus&view=menus', false));
                break;
        }

        return true;
    }

    /**
     * Method to display a menu as preset xml.
     *
     * @return  boolean  True if successful, false otherwise.
     *
     * @since   3.8.0
     */
    public function exportXml()
    {
        // Check for request forgeries.
        $this->checkToken();

        $cid = (array) $this->input->get('cid', [], 'int');

        // We know the first element is the one we need because we don't allow multi selection of rows
        $id = empty($cid) ? 0 : reset($cid);

        if ($id === 0) {
            $this->setMessage(Text::_('COM_MENUS_SELECT_MENU_FIRST_EXPORT'), 'warning');

            $this->setRedirect(Route::_('index.php?option=com_menus&view=menus', false));

            return false;
        }

        $model = $this->getModel('Menu');
        $item  = $model->getItem($id);

        if (!$item->menutype) {
            $this->setMessage(Text::_('COM_MENUS_SELECT_MENU_FIRST_EXPORT'), 'warning');

            $this->setRedirect(Route::_('index.php?option=com_menus&view=menus', false));

            return false;
        }

        $this->setRedirect(Route::_('index.php?option=com_menus&view=menu&menutype=' . $item->menutype . '&format=xml', false));

        return true;
    }
}
