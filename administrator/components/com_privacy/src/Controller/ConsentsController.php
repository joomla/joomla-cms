<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Privacy\Administrator\Controller;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\Component\Privacy\Administrator\Model\ConsentsModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Consents management controller class.
 *
 * @since  3.9.0
 */
class ConsentsController extends FormController
{
    /**
     * Method to invalidate specific consents.
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public function invalidate()
    {
        // Check for request forgeries
        $this->checkToken();

        $ids = (array) $this->input->get('cid', [], 'int');

        // Remove zero values resulting from input filter
        $ids = array_filter($ids);

        if (empty($ids)) {
            $this->app->enqueueMessage(Text::_('JERROR_NO_ITEMS_SELECTED'), CMSApplication::MSG_ERROR);
        } else {
            /** @var ConsentsModel $model */
            $model = $this->getModel();

            if (!$model->invalidate($ids)) {
                $this->setMessage($model->getError());
            } else {
                $this->setMessage(Text::plural('COM_PRIVACY_N_CONSENTS_INVALIDATED', \count($ids)));
            }
        }

        $this->setRedirect(Route::_('index.php?option=com_privacy&view=consents', false));
    }

    /**
     * Method to invalidate all consents of a specific subject.
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public function invalidateAll()
    {
        // Check for request forgeries
        $this->checkToken();

        $filters = $this->input->get('filter', [], 'array');

        $this->setRedirect(Route::_('index.php?option=com_privacy&view=consents', false));

        if (isset($filters['subject']) && $filters['subject'] != '') {
            $subject = $filters['subject'];
        } else {
            $this->app->enqueueMessage(Text::_('JERROR_NO_ITEMS_SELECTED'));

            return;
        }

        /** @var ConsentsModel $model */
        $model = $this->getModel();

        if (!$model->invalidateAll($subject)) {
            $this->setMessage($model->getError());
        }

        $this->setMessage(Text::_('COM_PRIVACY_CONSENTS_INVALIDATED_ALL'));
    }
}
