<?php

/**
 * @package     Joomla.API
 * @subpackage  com_joomlaupdate
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Joomlaupdate\Api\View\Notification;

use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Joomlaupdate\Administrator\Model\UpdateModel;
use Tobscure\JsonApi\Resource;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The notification view
 *
 * @since  5.4.0
 */
class JsonapiView extends BaseApiView
{
    /**
     * Sends the update result notification
     *
     * @return string  The rendered data
     *
     * @since   5.4.0
     */
    public function notification($type, $oldVersion, $newVersion)
    {
        /**
         * @var UpdateModel $model
         */
        $model   = $this->getModel();
        $success = true;

        try {
            // Perform the finalization action
            $model->sendNotification($type, $oldVersion, $newVersion);
        } catch (\Throwable $e) {
            $success = false;
        }

        $element = (new Resource((object) ['success' => $success, 'id' => $type], $this->serializer))
            ->fields(['notification' => ['success']]);

        $this->getDocument()->setData($element);
        $this->getDocument()->addLink('self', Uri::current());

        return $this->getDocument()->render();
    }
}
