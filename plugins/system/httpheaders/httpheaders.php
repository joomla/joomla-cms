<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.HttpHeader
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;

/**
 * Plugin class for HTTP Header
 *
 * @since  4.0.0
 */
class PlgSystemHttpHeaders extends CMSPlugin implements SubscriberInterface
{
	/**
	 * If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  4.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 * @since  4.0.0
	 */
	protected $app;

	/**
	 * The list of the supported HTTP headers
	 *
	 * @var    array
	 * @since  4.0.0
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
	 * @since   4.0.0
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onAfterInitialise' => 'setHttpHeaders',
		];
	}

	/**
	 * The `setHttpHeaders` method handle the setting of the configured HTTP Headers
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function setHttpHeaders()
	{
		$this->setDefaultHeader();

		// Handle the additional httpheader
		$httpHeaders = $this->params->get('additional_httpheaders', array());

		foreach ($httpHeaders as $httpHeader)
		{
			// Handle the client settings for each header
			if (!$this->app->isClient($httpHeader->client) && $httpHeader->client != 'both')
			{
				continue;
			}

			if (in_array($httpHeader->key, $this->supportedHttpHeaders))
			{
				$this->app->setHeader($httpHeader->key, $httpHeader->value, true);
			}
		}
	}

	/**
	 * Set the default headers when enabled
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	private function setDefaultHeader()
	{
		// X-Frame-Options
		if ($this->params->get('xframeoptions', '1') === '1')
		{
			$this->app->setHeader('X-Frame-Options', 'SAMEORIGIN');
		}

		// X-XSS-Protection
		if ($this->params->get('xxssprotection', '1') === '1')
		{
			$this->app->setHeader('X-XSS-Protection', '1; mode=block');
		}

		// X-Content-Type-Options
		if ($this->params->get('xcontenttypeoptions', '1') === '1')
		{
			$this->app->setHeader('X-Content-Type-Options', 'nosniff');
		}

		// Referrer-Policy
		$referrerpolicy = $this->params->get('referrerpolicy', 'no-referrer-when-downgrade');

		if ($referrerpolicy !== 'disabled')
		{
			$this->app->setHeader('Referrer-Policy', $referrerpolicy);
		}

		$nonce = base64_encode(bin2hex(random_bytes(64)));
		JFactory::getApplication()->set('script_nonce', $nonce);
		JFactory::getApplication()->setHeader('Content-Security-Policy', 'default-src \'none\'; style-src \'self\' \'unsafe-inline\' https://fonts.googleapis.com/; script-src \'self\' \'nonce-' . $nonce . '\'; font-src \'self\' https://fonts.gstatic.com; img-src \'self\'; connect-src \'self\'; frame-src \'self\'', true);
	}
}
