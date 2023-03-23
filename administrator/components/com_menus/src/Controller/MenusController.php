<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Menus\Administrator\Controller;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The Menu List Controller
 *
 * @since  1.6
 */
class MenusController extends BaseController
{
    /**
     * Display the view
     *
     * @param   boolean  $cachable   If true, the view output will be cached.
     * @param   array    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link \JFilterInput::clean()}.
     *
     * @return  void
     *
     * @since   1.6
     */
    public function display($cachable = false, $urlparams = false)
    {
    }

    /**
     * Method to get a model object, loading it if required.
     *
     * @param   string  $name    The model name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  object  The model.
     *
     * @since   1.6
     */
    public function getModel($name = 'Menu', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Remove an item.
     *
     * @return  void
     *
     * @since   1.6
     */
    public function delete()
    {
        // Check for request forgeries
        $this->checkToken();

        $user = $this->app->getIdentity();
        $cids = (array) $this->input->get('cid', [], 'int');

        // Remove zero values resulting from input filter
        $cids = array_filter($cids);

        if (empty($cids)) {
            $this->setMessage(Text::_('COM_MENUS_NO_MENUS_SELECTED'), 'warning');
        } else {
            // Access checks.
            foreach ($cids as $i => $id) {
                if (!$user->authorise('core.delete', 'com_menus.menu.' . (int) $id)) {
                    // Prune items that you can't change.
                    unset($cids[$i]);
                    $this->app->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'), 'error');
                }
            }

            if (count($cids) > 0) {
                // Get the model.
                /** @var \Joomla\Component\Menus\Administrator\Model\MenuModel $model */
                $model = $this->getModel();

                // Remove the items.
                if (!$model->delete($cids)) {
                    $this->setMessage($model->getError(), 'error');
                } else {
                    $this->setMessage(Text::plural('COM_MENUS_N_MENUS_DELETED', count($cids)));
                }
            }
        }

        $this->setRedirect('index.php?option=com_menus&view=menus');
    }

    /**
     * Temporary method. This should go into the 1.5 to 1.6 upgrade routines.
     *
     * @return  void
     *
     * @since   1.6
     *
     * @deprecated  5.0 Will be removed without replacement as it was only used for the 1.5 to 1.6 upgrade
     */
    public function resync()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $parts = null;

        try {
            $query->select(
                [
                    $db->quoteName('element'),
                    $db->quoteName('extension_id'),
                ]
            )
                ->from($db->quoteName('#__extensions'))
                ->where($db->quoteName('type') . ' = ' . $db->quote('component'));
            $db->setQuery($query);

            $components = $db->loadAssocList('element', 'extension_id');
        } catch (\RuntimeException $e) {
            $this->setMessage($e->getMessage(), 'warning');

            return;
        }

        // Load all the component menu links
        $query->select(
            [
                $db->quoteName('id'),
                $db->quoteName('link'),
                $db->quoteName('component_id'),
            ]
        )
            ->from($db->quoteName('#__menu'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('component.item'));
            $db->setQuery($query);

        try {
            $items = $db->loadObjectList();
        } catch (\RuntimeException $e) {
            $this->setMessage($e->getMessage(), 'warning');

            return;
        }

        $query = $db->getQuery(true)
            ->update($db->quoteName('#__menu'))
            ->set($db->quoteName('component_id') . ' = :componentId')
            ->where($db->quoteName('id') . ' = :itemId')
            ->bind(':componentId', $componentId, ParameterType::INTEGER)
            ->bind(':itemId', $itemId, ParameterType::INTEGER);

        foreach ($items as $item) {
            // Parse the link.
            parse_str(parse_url($item->link, PHP_URL_QUERY), $parts);
            $itemId = $item->id;

            // Tease out the option.
            if (isset($parts['option'])) {
                $option = $parts['option'];

                // Lookup the component ID
                if (isset($components[$option])) {
                    $componentId = $components[$option];
                } else {
                    // Mismatch. Needs human intervention.
                    $componentId = -1;
                }

                // Check for mis-matched component ids in the menu link.
                if ($item->component_id != $componentId) {
                    // Update the menu table.
                    $log = "Link $item->id refers to $item->component_id, converting to $componentId ($item->link)";
                    echo "<br>$log";

                    try {
                        $db->setQuery($query)->execute();
                    } catch (\RuntimeException $e) {
                        $this->setMessage($e->getMessage(), 'warning');

                        return;
                    }
                }
            }
        }
    }
}
