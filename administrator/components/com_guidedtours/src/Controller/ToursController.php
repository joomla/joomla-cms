<?php

/**
 * @package       Joomla.Administrator
 * @subpackage    com_guidedtours
 *
 * @copyright     (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Guidedtours\Administrator\Controller;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Router\Route;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Guidedtours list controller class.
 *
 * @since 4.3.0
 */

class ToursController extends AdminController
{
    /**
     * Proxy for getModel.
     *
     * @param   string $name   The model name. Optional.
     * @param   string $prefix The class prefix. Optional.
     * @param   array  $config The array of possible config values. Optional.
     *
     * @return \Joomla\CMS\MVC\Model\BaseDatabaseModel
     *
     * @since 4.3.0
     */
    public function getModel($name = 'Tour', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }

    public function duplicate()
    {
        $this->checkToken();
        $pks = (array) $this->input->post->get('cid', [], 'int');
        $pks = array_filter($pks);
        try {
            if (empty($pks)) {
                throw new \Exception(Text::_('COM_GUIDEDTOURS_ERROR_NO_GUIDEDTOURS_SELECTED'));
            }
            $model = $this->getModel();
            $model->duplicate($pks);
            $this->setMessage(Text::plural('COM_GUIDEDTOURS_TOURS_DUPLICATED', \count($pks)));
        } catch (\Exception $e) {
            $this->app->enqueueMessage($e->getMessage(), 'warning');
        }
        $this->setRedirect(Route::_('index.php?option=com_guidedtours&view=tours' . $this->getRedirectToListAppend(), false));
    }
}
