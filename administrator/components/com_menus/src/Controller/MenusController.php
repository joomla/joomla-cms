<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Menus\Administrator\Controller;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The Menu List Controller
 *
 * @since  1.6
 */
class MenusController extends AdminController
{
    /**
     * Display the view
     *
     * @param   boolean  $cachable   If true, the view output will be cached.
     * @param   array    $urlparams  An array of safe URL parameters and their variable types.
     *                   @see        \Joomla\CMS\Filter\InputFilter::clean() for valid values.
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

            if (\count($cids) > 0) {
                // Get the model.
                /** @var \Joomla\Component\Menus\Administrator\Model\MenuModel $model */
                $model = $this->getModel();

                // Remove the items.
                if (!$model->delete($cids)) {
                    $this->setMessage($model->getError(), 'error');
                } else {
                    $this->setMessage(Text::plural('COM_MENUS_N_MENUS_DELETED', \count($cids)));
                }
            }
        }

        $this->setRedirect('index.php?option=com_menus&view=menus');
    }
}
