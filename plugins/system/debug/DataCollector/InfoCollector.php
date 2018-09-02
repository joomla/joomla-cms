<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.Debug
 *
 * @copyright   Copyright (C) 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Debug\DataCollector;

use DebugBar\DataCollector\AssetProvider;
use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\User\User;
use Joomla\Component\Admin\Administrator\Model\SysInfoModel;
use Joomla\Plugin\System\Debug\AbstractDataCollector;
use Joomla\Registry\Registry;
use Psr\Http\Message\ResponseInterface;

/**
 * InfoDataCollector
 *
 * @since  __DEPLOY_VERSION__
 */
class InfoCollector extends AbstractDataCollector implements AssetProvider
{
	/**
	 * Collector name.
	 *
	 * @var   string
	 * @since __DEPLOY_VERSION__
	 */
	private $name = 'info';

	/**
	 * Request ID.
	 *
	 * @var   string
	 * @since __DEPLOY_VERSION__
	 */
	private $requestId;

	/**
	 * InfoDataCollector constructor.
	 *
	 * @param   Registry  $params     Parameters
	 * @param   string    $requestId  Request ID
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function __construct(Registry $params, $requestId)
	{
		$this->requestId = $requestId;

		parent::__construct($params);
	}

	/**
	 * Returns the unique name of the collector
	 *
	 * @since  __DEPLOY_VERSION__
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * Returns a hash where keys are control names and their values
	 * an array of options as defined in {@see DebugBar\JavascriptRenderer::addControl()}
	 *
	 * @since  __DEPLOY_VERSION__
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
			]
		];
	}

	/**
	 * Returns an array with the following keys:
	 *  - base_path
	 *  - base_url
	 *  - css: an array of filenames
	 *  - js: an array of filenames
	 *
	 * @since  __DEPLOY_VERSION__
	 * @return array
	 */
	public function getAssets(): array
	{
		return array(
			'js' => \JUri::root(true) . '/media/plg_system_debug/widgets/info/widget.min.js',
			'css' => \JUri::root(true) . '/media/plg_system_debug/widgets/info/widget.min.css',
		);
	}

	/**
	 * Called by the DebugBar when data needs to be collected
	 *
	 * @since  __DEPLOY_VERSION__
	 *
	 * @return array Collected data
	 */
	public function collect(): array
	{
		/* @type SiteApplication|AdministratorApplication $application */
		$application = Factory::getApplication();

		// @todo autoloadability ??
		\JLoader::register(SysInfoModel::class, JPATH_ADMINISTRATOR . '/components/com_admin/Model/SysinfoModel.php');

		$model = new SysInfoModel;

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
	 * @since __DEPLOY_VERSION__
	 *
	 * @return array
	 */
	private function getIdentityInfo(User $identity): array
	{
		if (!$identity->id)
		{
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
	 * @since __DEPLOY_VERSION__
	 *
	 * @return array
	 */
	private function getResponseInfo(ResponseInterface $response): array
	{
		return [
			'status_code' => $response->getStatusCode()
		];
	}

	/**
	 * Get template info.
	 *
	 * @param   object  $template  The template.
	 *
	 * @since __DEPLOY_VERSION__
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
	 * @since __DEPLOY_VERSION__
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
		];
	}
}
