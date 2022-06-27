<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Modules\Administrator\Controller;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Response\JsonResponse;

/**
 * Modules list controller class.
 *
 * @since  1.6
 */
class ModulesController extends AdminController
{
    /**
     * Method to clone an existing module.
     *
     * @return  void
     *
     * @since   1.6
     */
    public function duplicate()
    {
        // Check for request forgeries
        $this->checkToken();

        $pks = (array) $this->input->post->get('cid', array(), 'int');

        // Remove zero values resulting from input filter
        $pks = array_filter($pks);

        try {
            if (empty($pks)) {
                throw new \Exception(Text::_('COM_MODULES_ERROR_NO_MODULES_SELECTED'));
            }

            $model = $this->getModel();
            $model->duplicate($pks);
            $this->setMessage(Text::plural('COM_MODULES_N_MODULES_DUPLICATED', count($pks)));
        } catch (\Exception $e) {
            $this->app->enqueueMessage($e->getMessage(), 'warning');
        }

        $this->setRedirect('index.php?option=com_modules&view=modules' . $this->getRedirectToListAppend());
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
    public function getModel($name = 'Module', $prefix = 'Administrator', $config = array('ignore_request' => true))
    {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Method to get the number of frontend modules
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function getQuickiconContent()
    {
        $model = $this->getModel('Modules');

        $model->setState('filter.state', 1);
        $model->setState('filter.client_id', 0);

        $amount = (int) $model->getTotal();

        $result = [];

        $result['amount'] = $amount;
        $result['sronly'] = Text::plural('COM_MODULES_N_QUICKICON_SRONLY', $amount);
        $result['name'] = Text::plural('COM_MODULES_N_QUICKICON', $amount);

        echo new JsonResponse($result);
    }

    /**
     * Gets the URL arguments to append to a list redirect.
     *
     * @return  string  The arguments to append to the redirect URL.
     *
     * @since   4.0.0
     */
    protected function getRedirectToListAppend()
    {
        $append = parent::getRedirectToListAppend();
        $append .= '&client_id=' . $this->input->getInt('client_id');

        return $append;
    }
}
