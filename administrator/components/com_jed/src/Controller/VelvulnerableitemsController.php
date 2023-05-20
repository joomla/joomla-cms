<?php

/**
 * @package       JED
 *
 * @subpackage    VEL
 *
 * @copyright     (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Jed\Component\Jed\Administrator\Controller;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Exception;
use Jed\Component\Jed\Administrator\Helper\JedHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Router\Route;
use Joomla\Utilities\ArrayHelper;

/**
 * Velvulnerableitems class.
 *
 * @since  4.0.0
 */
class VelvulnerableitemsController extends AdminController
{
    /**
     * Proxy for getModel.
     *
     * @param   string  $name    Optional. Model name
     * @param   string  $prefix  Optional. Class prefix
     * @param   array   $config  Optional. Configuration array for model
     *
     * @return  object    The Model
     *
     * @since 4.0.0
     */
    public function getModel($name = 'Velvulnerableitem', $prefix = 'Administrator', $config = []): object
    {

        return parent::getModel($name, $prefix, ['ignore_request' => true]);
    }

    /**
     * Publish/Unpublish Vulnerable Item
     *
     * @return    void
     *
     * @since    4.0.0
     * @throws Exception
     */
    public function publish()
    {

        // Checking if the user can remove object
        $user = JedHelper::getUser();

        if ($user->authorise('core.edit', 'com_jed') || $user->authorise('core.edit.state', 'com_jed')) {
            $model = $this->getModel();

            // Get the user data.

            $id = $this->input->getInt('cid');

            $values = ['publish' => 1, 'unpublish' => 0, 'deleteOverrideHistory' => -3];
            $task   = $this->getTask();
            $value  = ArrayHelper::getValue($values, $task, 0, 'int');

            $return = $model->publish($id, $value);

            // Check for errors.
            if ($return === false) {
                $this->setMessage(Text::sprintf('Save failed: %s', $model->getError()), 'warning');
            }


            $this->setRedirect(Route::_('index.php?option=com_jed&view=velvulnerableitems', false));
        } else {
            throw new Exception(500);
        }
    }

    /**
     * Method to save the submitted ordering values for records via AJAX.
     *
     * @return  void
     *
     * @since 4.0.0
     *
     * @throws Exception
     */
    public function saveOrderAjax()
    {
        // Get the input
        $input = Factory::getApplication()->input;
        $pks   = $input->post->get('cid', [], 'array');
        $order = $input->post->get('order', [], 'array');

        // Sanitize the input
        ArrayHelper::toInteger($pks);
        ArrayHelper::toInteger($order);

        // Get the model
        $model = $this->getModel();

        // Save the ordering
        $return = $model->saveorder($pks, $order);

        if ($return) {
            echo "1";
        }

        // Close the application
        Factory::getApplication()->close();
    }
}
