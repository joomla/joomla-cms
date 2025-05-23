<?php

/**
 * @package     Joomla.API
 * @subpackage  com_joomlaupdate
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Joomlaupdate\Api\View\Updates;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Joomlaupdate\Administrator\Model\UpdateModel;
use Tobscure\JsonApi\Resource;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The updates view
 *
 * @since  __DEPLOY_VERSION__
 */
class JsonapiView extends BaseApiView
{
    /**
     * Generates the update output
     *
     * @return string  The rendered data
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getUpdate()
    {
        /**
         * @var UpdateModel $model
         */
        $model = $this->getModel();

        $latestVersion = $model->getAutoUpdateVersion();

        if (!$latestVersion || version_compare(JVERSION, $latestVersion) >= 0) {
            $latestVersion = null;
        }

        // Check pre-update states, unset update if not matched
        $preUpdateState = $model->getAutoUpdateRequirementsState();

        if (!$preUpdateState) {
            $latestVersion = null;
        }

        // Prepare response
        $element = (new Resource((object) ['availableUpdate' => $latestVersion, 'id' => 'getUpdate'], $this->serializer))
            ->fields(['updates' => ['availableUpdate']]);

        $this->getDocument()->setData($element);
        $this->getDocument()->addLink('self', Uri::current());

        return $this->getDocument()->render();
    }

    /**
     * Prepares the update by setting up the update.php and returns password and file size
     *
     * @param string $targetVersion The target version to prepare
     *
     * @return string  The rendered data
     *
     * @since  __DEPLOY_VERSION__
     */
    public function prepareUpdate(string $targetVersion): string
    {
        /**
         * @var UpdateModel $model
         */
        $model = $this->getModel();

        $fileinformation = $model->prepareAutoUpdate($targetVersion);

        $fileinformation['id'] = 'prepareUpdate';

        $element = (new Resource((object) $fileinformation, $this->serializer))
            ->fields(['updates' => ['password', 'filesize']]);

        $this->getDocument()->setData($element);
        $this->getDocument()->addLink('self', Uri::current());

        return $this->getDocument()->render();
    }

    /**
     * Run the finalize update steps
     *
     * @param string $fromVersion The from version
     *
     * @return string  The rendered data
     *
     * @since  __DEPLOY_VERSION__
     */
    public function finalizeUpdate($fromVersion)
    {
        /**
         * @var UpdateModel $model
         */
        $model = $this->getModel();

        // Write old version to state for usage in model
        Factory::getApplication()->setUserState('com_joomlaupdate.oldversion', $fromVersion);

        try {
            // Perform the finalization action
            $model->finaliseUpgrade();
        } catch (\Throwable $e) {
            $model->collectError('finaliseUpgrade', $e);
        }

        try {
            // Load actionlog plugins.
            PluginHelper::importPlugin('actionlog');

            // Perform the cleanup action
            $model->cleanUp();
        } catch (\Throwable $e) {
            $model->collectError('cleanUp', $e);
        }

        // Reset source
        $model->resetUpdateSource();

        $success = true;

        if ($model->getErrors()) {
            $success = false;
        }

        $element = (new Resource((object) ['success' => $success, 'id' => 'finalizeUpdate'], $this->serializer))
            ->fields(['updates' => ['success']]);

        $this->getDocument()->setData($element);
        $this->getDocument()->addLink('self', Uri::current());

        return $this->getDocument()->render();
    }
}
