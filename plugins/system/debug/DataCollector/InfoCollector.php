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
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;
use Joomla\Plugin\System\Debug\AbstractDataCollector;
use Joomla\Registry\Registry;
use Zend\Diactoros\Response;

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
	public function getName()
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
	public function getWidgets()
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
	public function getAssets()
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
	public function collect()
	{
		/* @type SiteApplication|AdministratorApplication $application */
		$application = Factory::getApplication();

		$x = $application->getIdentity();

		$t = $application->getTemplate(true);
		$r = $application->getResponse();

		return [
			'phpVersion' => PHP_VERSION,
			'joomlaVersion' => JVERSION,
			'requestId' => $this->requestId,
			'identity' => $this->getIdentityInfo($application->getIdentity()),
			'response' => $this->getResponseInfo($application->getResponse()),
			'template' => $this->getTemplateInfo($application->getTemplate(true)),
		];
	}

	private function getIdentityInfo(User $identity)
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

	private function getResponseInfo(Response $response)
	{
		return [
			'status_code' => $response->getStatusCode()
		];
	}

	private function getTemplateInfo($template)
	{
		return [
			'template' => $template->template ?? '',
			'home' => $template->home ?? '',
			'id' => $template->id ?? '',
		];
	}
}
