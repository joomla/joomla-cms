<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.Debug
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Debug\DataCollector;

use DebugBar\DataCollector\AssetProvider;
use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;
use Joomla\Plugin\System\Debug\AbstractDataCollector;
use Joomla\Registry\Registry;
use Psr\Http\Message\ResponseInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * InfoDataCollector
 *
 * @since  4.0.0
 */
class InfoCollector extends AbstractDataCollector implements AssetProvider
{
    /**
     * Collector name.
     *
     * @var   string
     * @since 4.0.0
     */
    private $name = 'info';

    /**
     * Request ID.
     *
     * @var   string
     * @since 4.0.0
     */
    private $requestId;

    /**
     * InfoDataCollector constructor.
     *
     * @param   Registry  $params     Parameters
     * @param   string    $requestId  Request ID
     *
     * @since  4.0.0
     */
    public function __construct(Registry $params, $requestId)
    {
        $this->requestId = $requestId;

        parent::__construct($params);
    }

    /**
     * Returns the unique name of the collector
     *
     * @since  4.0.0
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns a hash where keys are control names and their values
     * an array of options as defined in {@see \DebugBar\JavascriptRenderer::addControl()}
     *
     * @since  4.0.0
     * @return array
     */
    public function getWidgets(): array
    {
        return [
            'info' => [
                'icon' => 'info-circle',
                'title' => 'J! Info',
                'widget'  => 'PhpDebugBar.Widgets.InfoWidget',
                'map'     => $this->name,
                'default' => '{}',
            ],
        ];
    }

    /**
     * Returns an array with the following keys:
     *  - base_path
     *  - base_url
     *  - css: an array of filenames
     *  - js: an array of filenames
     *
     * @since  4.0.0
     * @return array
     */
    public function getAssets(): array
    {
        return [
            'js' => Uri::root(true) . '/media/plg_system_debug/widgets/info/widget.min.js',
            'css' => Uri::root(true) . '/media/plg_system_debug/widgets/info/widget.min.css',
        ];
    }

    /**
     * Called by the DebugBar when data needs to be collected
     *
     * @since  4.0.0
     *
     * @return array Collected data
     */
    public function collect(): array
    {
        /** @type SiteApplication|AdministratorApplication $application */
        $application = Factory::getApplication();

        $model = $application->bootComponent('com_admin')
            ->getMVCFactory()->createModel('Sysinfo', 'Administrator');

        return [
            'phpVersion' => PHP_VERSION,
            'joomlaVersion' => JVERSION,
            'requestId' => $this->requestId,
            'identity' => $this->getIdentityInfo($application->getIdentity()),
            'response' => $this->getResponseInfo($application->getResponse()),
            'template' => $this->getTemplateInfo($application->getTemplate(true)),
            'database' => $this->getDatabaseInfo($model->getInfo()),
        ];
    }

    /**
     * Get Identity info.
     *
     * @param   User  $identity  The identity.
     *
     * @since 4.0.0
     *
     * @return array
     */
    private function getIdentityInfo(User $identity): array
    {
        if (!$identity->id) {
            return ['type' => 'guest'];
        }

        return [
            'type' => 'user',
            'id' => $identity->id,
            'name' => $identity->name,
            'username' => $identity->username,
        ];
    }

    /**
     * Get response info.
     *
     * @param   ResponseInterface  $response  The response.
     *
     * @since 4.0.0
     *
     * @return array
     */
    private function getResponseInfo(ResponseInterface $response): array
    {
        return [
            'status_code' => $response->getStatusCode(),
        ];
    }

    /**
     * Get template info.
     *
     * @param   object  $template  The template.
     *
     * @since 4.0.0
     *
     * @return array
     */
    private function getTemplateInfo($template): array
    {
        return [
            'template' => $template->template ?? '',
            'home' => $template->home ?? '',
            'id' => $template->id ?? '',
        ];
    }

    /**
     * Get database info.
     *
     * @param   array  $info  General information.
     *
     * @since 4.0.0
     *
     * @return array
     */
    private function getDatabaseInfo(array $info): array
    {
        return [
            'dbserver' => $info['dbserver'] ?? '',
            'dbversion' => $info['dbversion'] ?? '',
            'dbcollation' => $info['dbcollation'] ?? '',
            'dbconnectioncollation' => $info['dbconnectioncollation'] ?? '',
            'dbconnectionencryption' => $info['dbconnectionencryption'] ?? '',
            'dbconnencryptsupported' => $info['dbconnencryptsupported'] ?? '',
        ];
    }
}
