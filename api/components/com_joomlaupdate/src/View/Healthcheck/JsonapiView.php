<?php

/**
 * @package     Joomla.API
 * @subpackage  com_joomlaupdate
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Joomlaupdate\Api\View\Healthcheck;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Joomlaupdate\Administrator\Model\UpdateModel;
use Joomla\Database\DatabaseInterface;
use Tobscure\JsonApi\Resource;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The healthcheck view
 *
 * @since  5.4.0
 */
class JsonapiView extends BaseApiView
{
    /**
     * Generates the health check output
     *
     * @return string  The rendered data
     *
     * @since   5.4.0
     */
    public function healthCheck()
    {
        $data = $this->getStatsData();

        $data['id'] = 'healthcheck';

        $element = (new Resource((object) $data, $this->serializer))
            ->fields(['healthcheck' => ['php_version', 'db_type', 'db_version', 'cms_version', 'server_os', 'update_requirement_state']]);

        $this->getDocument()->setData($element);
        $this->getDocument()->addLink('self', Uri::current());

        return $this->getDocument()->render();
    }

    /**
     * Get the data that will be sent to the update server.
     *
     * @return  array
     *
     * @since   5.4.0
     */
    protected function getStatsData()
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        /** @var UpdateModel $updateModel */
        $updateModel = Factory::getApplication()->bootComponent('com_joomlaupdate')
            ->getMVCFactory()->createModel('Update', 'Administrator', ['ignore_request' => true]);

        $data = [
            'php_version'              => PHP_VERSION,
            'db_type'                  => $db->name,
            'db_version'               => $db->getVersion(),
            'cms_version'              => JVERSION,
            'server_os'                => php_uname('s') . ' ' . php_uname('r'),
            'update_requirement_state' => $updateModel->getAutoUpdateRequirementsState(),
        ];

        // Check if we have a MariaDB version string and extract the proper version from it
        if (preg_match('/^(?:5\.5\.5-)?(mariadb-)?(?P<major>\d+)\.(?P<minor>\d+)\.(?P<patch>\d+)/i', $data['db_version'], $versionParts)) {
            $data['db_version'] = $versionParts['major'] . '.' . $versionParts['minor'] . '.' . $versionParts['patch'];
        }

        return $data;
    }
}
