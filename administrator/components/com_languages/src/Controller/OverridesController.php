<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Languages\Administrator\Controller;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Router\Route;

/**
 * Languages Overrides Controller.
 *
 * @since  2.5
 */
class OverridesController extends AdminController
{
    /**
     * The prefix to use with controller messages.
     *
     * @var     string
     * @since   2.5
     */
    protected $text_prefix = 'COM_LANGUAGES_VIEW_OVERRIDES';

    /**
     * Method for deleting one or more overrides.
     *
     * @return  void
     *
     * @since   2.5
     */
    public function delete()
    {
        // Check for request forgeries.
        $this->checkToken();

        // Get items to delete from the request.
        $cid = (array) $this->input->get('cid', array(), 'string');

        // Remove zero values resulting from input filter
        $cid = array_filter($cid);

        if (empty($cid)) {
            $this->setMessage(Text::_($this->text_prefix . '_NO_ITEM_SELECTED'), 'warning');
        } else {
            // Get the model.
            $model = $this->getModel('overrides');

            // Remove the items.
            if ($model->delete($cid)) {
                $this->setMessage(Text::plural($this->text_prefix . '_N_ITEMS_DELETED', count($cid)));
            } else {
                $this->setMessage($model->getError(), 'error');
            }
        }

        $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
    }

    /**
     * Method to purge the overrider table.
     *
     * @return  void
     *
     * @since   3.4.2
     */
    public function purge()
    {
        // Check for request forgeries.
        $this->checkToken();

        /** @var \Joomla\Component\Languages\Administrator\Model\OverridesModel $model */
        $model = $this->getModel('overrides');
        $model->purge();
        $this->setRedirect(Route::_('index.php?option=com_languages&view=overrides', false));
    }
}
