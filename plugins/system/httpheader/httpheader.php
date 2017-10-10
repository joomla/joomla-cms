<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.HttpHeader
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;

/**
 * Plugin class for Http Header
 *
 * @since  1.0
 */
class PlgSystemHttpHeader extends CMSPlugin implements SubscriberInterface
{
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  1.0
	 */
	 protected $autoloadLanguage = true;

	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 * @since  1.0
	 */
	protected $app;

	/**
	 * The list of the suported HTTP headers
	 *
	 * @var    array
	 * @since  1.0
	 */
	protected $supportedHttpHeaders = [
		'Strict-Transport-Security',
		'Content-Security-Policy',
		'Content-Security-Policy-Report-Only',
		'X-Frame-Options',
		'X-XSS-Protection',
		'X-Content-Type-Options',
		'Referrer-Policy',
		// Upcoming Header
		'Expect-CT',
	];

	/**
	 * Returns an array of events this subscriber will listen to.
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getSubscribedEvents()
	{
		return [
			'onAfterInitialise' => 'setHttpHeaders',
		];
	}

	/**
	 * The `setHttpHeaders` methode handle the setting of the configured HTTP Headers
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function setHttpHeaders()
	{
		$this->setDefaultHeader();

		// Handle the additional httpheader
		$httpHeaders = $this->params->get('additional_httpheader', array());

		foreach ($httpHeaders as $httpHeader)
		{
			// Handle the client settings foreach header
			if (!$this->app->isClient($httpHeader->client) && $httpHeader->client != 'both')
			{
				continue;
			}

			if (in_array($httpHeader->key, $this->supportedHttpHeaders))
			{
				$this->app->setHeader($httpHeader->key, $httpHeader->value);
			}
		}
	}

	/**
	 * Set the default headers when enabled
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	private function setDefaultHeader()
	{
		// X-Frame-Options
		$xFrameOptions = $this->params->get('xframeoptions', 1);

		if ($xFrameOptions)
		{
			$this->app->setHeader('X-Frame-Options', 'SAMEORIGIN');
		}

		// X-XSS-Protection
		$xXssProtection = $this->params->get('xxssprotection', 1);

		if ($xXssProtection)
		{
			$this->app->setHeader('X-XSS-Protection', '1; mode=block');
		}

		// X-Content-Type-Options
		$xContentTypeOptions = $this->params->get('xcontenttypeoptions', 1);
		
		if ($xContentTypeOptions)
		{
			$this->app->setHeader('X-Content-Type-Options', 'nosniff');
		}

		// Referrer-Policy
		$this->app->setHeader('Referrer-Policy', $this->params->get('referrerpolicy', 'no-referrer-when-downgrade'));
	}
}
