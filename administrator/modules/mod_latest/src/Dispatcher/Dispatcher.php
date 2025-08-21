<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_latest
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Latest\Administrator\Dispatcher;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Layout\LayoutHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Dispatcher class for mod_latest
 *
 * @since  5.4.0
 */
class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    /**
     * Runs the dispatcher.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION_
     */
    public function dispatch()
    {
        $this->loadLanguage();

        $displayData = $this->getLayoutData();

        // Stop when display data is false
        if ($displayData === false) {
            return;
        }

        // Execute the layout without the module context
        $loader = static function (array $displayData) {
            // If $displayData doesn't exist in extracted data, unset the variable.
            if (!\array_key_exists('displayData', $displayData)) {
                extract($displayData);
                unset($displayData);
            } else {
                extract($displayData);
            }

            /**
             * Extracted variables
             * -----------------
             * @var   \stdClass  $module
             * @var   Registry   $params
             */

            if (\count($list)) {
                require ModuleHelper::getLayoutPath('mod_latest', $params->get('layout', 'default'));
            } else {
                $app->getLanguage()->load('com_content');

                echo LayoutHelper::render('joomla.content.emptystate_module', [
                        'textPrefix' => 'COM_CONTENT',
                        'icon'       => 'icon-copy',
                    ]);
            }
            // End of extracted variables
        };

        $loader($displayData);
    }

    /**
     * Returns the layout data.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION_
     */
    protected function getLayoutData()
    {
        $data   = parent::getLayoutData();
        $helper = $this->getHelperFactory()->getHelper('LatestHelper');

        $model                    = $data['app']->bootComponent('com_content')->getMVCFactory()->createModel('Articles', 'Administrator', ['ignore_request' => true]);
        $data['list']             = $helper->getArticles($data['params'], $model, $data['app']);
        $data['workflow_enabled'] = ComponentHelper::getParams('com_content')->get('workflow_enabled');

        if ($data['workflow_enabled']) {
            $data['app']->getLanguage()->load('com_workflow');
        }

        if ($data['params']->get('automatic_title', 0)) {
            $data['module']->title = $helper->getModuleTitle($data['params'], $data['app']);
        }

        return $data;
    }
}
