<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Task.stats
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Task\Stats\Field;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\User\UserHelper;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Unique ID Field class for the Stats Plugin.
 *
 * @since  __DEPLOY_VERSION__
 */
class UniqueidField extends FormField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    protected $type = 'Uniqueid';

    protected function getInput()
    {
        $component = Factory::getApplication()->bootComponent('com_scheduler');

        /** @var TaskModel $model */
        $model  = $component->getMVCFactory()->createModel('Task', 'Administrator', ['ignore_request' => true]);
        $input  = Factory::getApplication()->getInput();
        $id     = $input->get('id', 0, 'int');
        $task   = $model->getItem($id);
        $params = new Registry($task->params);

        if (\is_null($params->get('unique_id'))) {
            $this->value = hash('sha1', UserHelper::genRandomPassword(28) . time());
        }

        $inputTag = '<input type="hidden" '
            . 'name="jform[params][unique_id]" '
            . 'id="jform_params_unique_id" '
            . 'value="' . $this->value . '" '
            . 'class="form-control readonly" '
            . 'readonly="">';

        PluginHelper::importPlugin('task', 'stats');

        $result            = Factory::getApplication()->triggerEvent('onGetStatsData', ['unique_id' => $this->value]);
        $data['statsData'] = $result ? reset($result) : [];

        $div = '<a class="btn btn-primary" role="button" href="#collapseData" data-bs-toggle="collapse" aria-expanded="false" aria-controls="collapseData">'
            . Text::_('PLG_TASK_STATS_MSG_WHAT_DATA_WILL_BE_SENT') . '</a>';
        $html = '<div id="collapseData" class="collapse"><table class="table mb-3">
                    <caption>'
            . Text::_('PLG_TASK_STATS_MSG_JOOMLA_WANTS_TO_SEND_DATA')
            . '</caption><thead><tr><th scope="col" class="w-15">'
            . Text::_('PLG_TASK_STATS_SETTING')
            . '</th><th scope="col">'
            . Text::_('PLG_TASK_STATS_VALUE')
            . '</th></tr></thead><tbody>';
        $row = '';

        foreach ($data['statsData'] as $key => $value) {
            $row = $row . '<tr><th scope="row">'
                . Text::_('PLG_TASK_STATS_LABEL_' . strtoupper($key))
                . '</th><td>'
                . $value
                . '</td></tr>';
        }

        return $inputTag . $div . $html . $row . '</tbody></table></div>';
    }
}
