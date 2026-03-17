<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_associations
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Associations\Administrator\Controller;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Router\Route;
use Joomla\Component\Associations\Administrator\Helper\AssociationsHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Associations controller class.
 *
 * @since  3.7.0
 */
class AssociationsController extends AdminController
{
    /**
     * The URL view list variable.
     *
     * @var    string
     *
     * @since  3.7.0
     */
    protected $view_list = 'associations';

    /**
     * Proxy for getModel.
     *
     * @param   string  $name    The model name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  The array of possible config values. Optional.
     *
     * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel|boolean
     *
     * @since  3.7.0
     */
    public function getModel($name = 'Associations', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Method to purge the associations table.
     *
     * @return  void
     *
     * @since  3.7.0
     */
    public function purge()
    {
        $this->checkToken();

        $this->getModel('associations')->purge();
        $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
    }

    /**
     * Method to delete the orphans from the associations table.
     *
     * @return  void
     *
     * @since  3.7.0
     */
    public function clean()
    {
        $this->checkToken();

        $this->getModel('associations')->clean();
        $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
    }

    /**
     * Method to check in an item from the association item overview.
     *
     * @return  void
     *
     * @since   3.7.1
     */
    public function checkin()
    {
        // Set the redirect so we can just stop processing when we find a condition we can't process
        $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));

        // Figure out if the item supports checking and check it in
        [$extensionName, $typeName] = explode('.', $this->input->get('itemtype'));

        $extension = AssociationsHelper::getSupportedExtension($extensionName);
        $types     = $extension->get('types');

        if (!\array_key_exists($typeName, $types)) {
            return;
        }

        if (AssociationsHelper::typeSupportsCheckout($extensionName, $typeName) === false) {
            // How on earth we came to that point, eject internet
            return;
        }

        $cid = (array) $this->input->get('cid', [], 'int');

        if (empty($cid)) {
            // Seems we don't have an id to work with.
            return;
        }

        // We know the first element is the one we need because we don't allow multi selection of rows
        $id = $cid[0];

        if ($id === 0) {
            // Seems we don't have an id to work with.
            return;
        }

        if (AssociationsHelper::canCheckinItem($extensionName, $typeName, $id) === true) {
            $item = AssociationsHelper::getItem($extensionName, $typeName, $id);

            $item->checkIn($id);

            return;
        }

        $this->setRedirect(
            Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list),
            Text::_('COM_ASSOCIATIONS_YOU_ARE_NOT_ALLOWED_TO_CHECKIN_THIS_ITEM')
        );
    }
}
