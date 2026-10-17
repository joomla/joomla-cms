<?php

/**
 * @package       Joomla.Administrator
 * @subpackage    com_guidedtours
 *
 * @copyright     (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Guidedtours\Administrator\Controller;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Controller for a single Tour
 *
 * @since 4.3.0
 */
class TourController extends FormController
{
    /**
     * Method to save a record.
     *
     * @param   string  $key     The name of the primary key of the URL variable.
     * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
     *
     * @return  boolean  True if successful, false otherwise.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function save($key = null, $urlVar = null)
    {
        // Check for request forgeries.
        $this->checkToken();

        $model   = $this->getModel();
        $table   = $model->getTable();
        $data    = $this->input->post->get('jform', [], 'array');
        $id      = $this->input->get('id', 0, 'int');

        // extract 'option' url param
        $tUrlParts   = explode("?", $data['url']);
        $uri         = end($tUrlParts);
        $tUriParts   = explode("&", $uri);
        $optionValue = "";
        foreach ($tUriParts as $urlParam) {
            $posOption = strpos($urlParam, "option");
            if ($posOption !== false && $posOption == 0) {
                $tOption = explode("=", $urlParam);
                if (isset($tOption[1])) {
                    $optionValue = $tOption[1];
                    break;
                }
            }
        }
        if (!$optionValue) {
            $this->setMessage(Text::_('COM_GUIDEDTOURS_URL_COMPONENT_EMPTY'), 'error');
            $this->setRedirect(
                Route::_('index.php?option=' . $this->option . '&view=tour&layout=edit&id=' . $id . $this->getRedirectToListAppend(), false)
            );
            return false;
        }

        // check component name in DB
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select('extension_id')
            ->from('#__extensions')
            ->where('`element` = ' . $db->Quote($optionValue));
        $db->setQuery($query);
        $bCompFound = $db->loadResult();

        if (!$bCompFound) {
            // Set the internal error and also the redirect error.
            $this->setMessage(Text::sprintf('COM_GUIDEDTOURS_URL_COMPONENT_NOT_FOUND', $optionValue), 'error');
            $this->setRedirect(
                Route::_('index.php?option=' . $this->option . '&view=' . $this->view_item . '&layout=edit&id=' . $id . $this->getRedirectToListAppend(), false)
            );
            return false;
        }

        $result = parent::save($key, $urlVar);

        if ($this->getTask() === 'save2menu') {
            $this->setRedirect(
                Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false)
            );
        } elseif ($this->input->get('layout') === 'modal' && $this->task === 'save') {
            // When editing in modal then redirect to modalreturn layout
            $id     = $model->getState('guidedtour.id', '');
            $return = 'index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($id);
            $this->setRedirect(Route::_($return, false));
        }

        return $result;
    }
}
