<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_associations
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Associations\Administrator\Dispatcher;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Dispatcher\ComponentDispatcher;
use Joomla\CMS\Language\Text;
use Joomla\Component\Associations\Administrator\Helper\AssociationsHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * ComponentDispatcher class for com_associations
 *
 * @since  4.0.0
 */
class Dispatcher extends ComponentDispatcher
{
    /**
     * Method to check component access permission
     *
     * @since   4.0.0
     *
     * @return  void
     *
     * @throws  \Exception|NotAllowed
     */
    protected function checkAccess()
    {
        parent::checkAccess();

        // Check if user has permission to access the component item type.
        $itemType = $this->input->get('itemtype', '', 'string');

        if ($itemType !== '') {
            list($extensionName, $typeName) = explode('.', $itemType);

            if (!AssociationsHelper::hasSupport($extensionName)) {
                throw new \Exception(
                    Text::sprintf('COM_ASSOCIATIONS_COMPONENT_NOT_SUPPORTED', $this->app->getLanguage()->_($extensionName)),
                    404
                );
            }

            if (!$this->app->getIdentity()->authorise('core.manage', $extensionName)) {
                throw new NotAllowed($this->app->getLanguage()->_('JERROR_ALERTNOAUTHOR'), 403);
            }
        }
    }
}
