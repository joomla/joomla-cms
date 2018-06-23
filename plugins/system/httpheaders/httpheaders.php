<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.HttpHeader
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
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
	 * Database object.
	 *
	 * @var    DatabaseDriver
	 * @since  __DEPLOY_VERSION__
	 */
	protected $db;

	/**
	 * The list of the supported HTTP headers
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	private $supportedHttpHeaders = [
		'strict-transport-security',
		'content-security-policy',
		'content-security-policy-report-only',
		'x-frame-options',
		'x-xss-protection',
		'x-content-type-options',
		'referrer-policy',
		'expect-ct',
	];

	/**
	 * The list of special directives that need to be handled
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	private $specialDirectives = [
		'script-src',
		'style-src',
	];

	/**
	 * Constructor.
	 *
	 * @param   object  &$subject  The object to observe.
	 * @param   array   $config    An optional associative array of configuration settings.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		// Get the application if not done by JPlugin.
		if (!$this->app)
		{
			$this->app = Factory::getApplication();
		}

		// Get the db if not done by JPlugin.
		if (!$this->db)
		{
			$this->db = Factory::getDbo();
		}
	}

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
	public function setHttpHeaders(): void
	{
		// Set the default header when they are enabled
		$this->setDefaultHeader();

		// Nonce generation 
		$cspNonce = base64_encode(bin2hex(random_bytes(64)));
		$this->app->set('csp_nonce', $cspNonce);

		// Handle CSP Header configuration
		$cspOptions = (int) $this->params->get('contentsecuritypolicy', 0);

		if ($cspOptions)
		{
			$this->setCspHeader($cspNonce);
		}

		// Handle HSTS Header configuration
		$hstsOptions = (int) $this->params->get('hsts', 0);

		if ($hstsOptions)
		{
			$this->setHstsHeader();
		}

		// Handle the additional httpheader
		$httpHeaders = $this->params->get('additional_httpheader', array());

		foreach ($httpHeaders as $httpHeader)
		{
			// Handle the client settings for each header
			if (!$this->app->isClient($httpHeader->client) && $httpHeader->client != 'both')
			{
				continue;
			}

			if (empty($httpHeader->key) || empty($httpHeader->value))
			{
				continue;
			}

			if (!in_array(strtolower($httpHeader->key), $this->supportedHttpHeaders))
			{
				continue;
			}

			// Allow the custom csp headers to use the random $cspNonce in the rules
			if (in_array(strtolower($httpHeader->key), ['content-security-policy', 'content-security-policy-report-only']))
			{
				$httpHeader->value = str_replace('{nonce}', $cspNonce, $httpHeader->value);
			}

			$this->app->setHeader($httpHeader->key, $httpHeader->value, true);
		}
	}

	/**
	 * Set the default headers when enabled
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	private function setDefaultHeader(): void
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
	}

	/**
	 * Set the CSP header when enabled
	 *
	 * @param  string  $cspNonce  The one time string for the script and style tag
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function setCspHeader($cspNonce): void
	{
		// Mode Selector
		$cspMode = $this->params->get('contentsecuritypolicy_mode', 'custom');

		// In detecting mode we set this default rule so any report gets collected by com_csp
		if ($cspMode === 'detect')
		{
			$frontendUrl = str_replace('/administrator', '', Uri::base());

			$this->app->setHeader(
				'Content-Security-Policy-Report-Only',
				"default-src 'self'; report-uri " . $frontendUrl . "index.php?option=com_csp&task=report.log&client=" . $this->app->getName()
			);

			return;
		}

		$cspReadOnly = (int) $this->params->get('contentsecuritypolicy_report_only', 1);
		$cspHeader   = $cspReadOnly === 0 ? 'content-security-policy' : 'content-security-policy-report-only';

		// In automatic mode we compile the automatic header values and append it to the header
		if ($cspMode === 'auto')
		{
			$automaticRules = trim(
				implode(
					'; ',
					$this->compileAutomaticCspHeaderRules($cspNonce)
				)
			);

			// Set the header
			$this->app->setHeader($cspHeader, $automaticRules);

			return;
		}

		// In custom mode we compile the header from the values configured
		$cspValues = $this->params->get('contentsecuritypolicy_values', array());

		foreach ($cspValues as $cspValue)
		{
			// Handle the client settings foreach header
			if (!$this->app->isClient($cspValue->client) && $cspValue->client != 'both')
			{
				continue;
			}

			// We can only use this if this is a valid entry
			if (isset($cspValue->directive) && isset($cspValue->value))
			{
				if (in_array($cspValue->directive, $this->specialDirectives))
				{
					$cspValue->value .= "'nonce-" . $cspNonce . "' " . $cspValue->value;
				}

				$newCspValues[] = trim($cspValue->directive) . ' ' . trim($cspValue->value);
			}
		}

		if (empty($newCspValues))
		{
			return;
		}

		$this->app->setHeader($cspHeader, trim(implode('; ', $newCspValues)));
	}

	/**
	 * Set the HSTS header when enabled
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function setHstsHeader(): void
	{
		$maxAge        = (int) $this->params->get('hsts_maxage', 31536000);
		$hstsOptions   = array();
		$hstsOptions[] = $maxAge < 300 ? 'max-age: 300' : 'max-age: ' . $maxAge;

		if ($this->params->get('hsts_subdomains', 0))
		{
			$hstsOptions[] = 'includeSubDomains';
		}

		if ($this->params->get('hsts_preload', 0))
		{
			$hstsOptions[] = 'preload';
		}

		$this->app->setHeader('Strict-Transport-Security', trim(implode('; ', $hstsOptions)));
	}

	/**
	 * Compone the automatic csp header rules based on com_csp / #__csp
	 *
	 * @param  string  $nonce  The System nonce used for script and style tags
	 *
	 * @return  array  An array containing the csp rules found in com_csp
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function compileAutomaticCspHeaderRules($nonce): array
	{
		// Get the published infos form the database
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName(['client', 'directive', 'blocked_uri']))
			->from('#__csp')
			->where($this->db->quoteName('published') . ' = 1');

		$this->db->setQuery($query);

		try
		{
			$rows = (array) $this->db->loadObjectList();
		}
		catch (\RuntimeException $e)
		{
			$this->app->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');

			return [];
		}

		$automaticCspHeader  = [];
		$cspHeaderCollection = [];

		foreach ($rows as $row)
		{
			// Handle the client information foreach rule
			if (!$this->app->isClient($row->client))
			{
				continue;
			}

			// Make sure the directive exists as key
			if (!isset($cspHeaderCollection[$row->directive]))
			{
				$cspHeaderCollection = array_fill_keys([$row->directive], '');
			}

			// Eval or inline lets make sure they still work by adding ' before and after
			if (in_array($row->blocked_uri, ['unsafe-eval', 'unsafe-inline']))
			{
				$row->blocked_uri = "'$row->blocked_uri'";
			}

			// Whiteliste the blocked_uri for the given directive
			$cspHeaderCollection[$row->directive] .= ' ' . $row->blocked_uri;
		}

		// We should have a default-src rule
		if (!empty($cspHeaderCollection) && !isset($cspHeaderCollection['default-src']))
		{
			$cspHeaderCollection = array_fill_keys(['default-src'], '');
		}

		foreach ($cspHeaderCollection as $cspHeaderkey => $cspHeaderValue)
		{
			// Append the random $nonce for the script and style tags
			if (in_array($cspHeaderkey, $this->specialDirectives))
			{
				$cspHeaderValue = "'nonce-" . $nonce . "'" . $cspHeaderValue;
			}

			// By default we should whitelist 'self' on any directive
			$automaticCspHeader[] = $cspHeaderkey . " 'self' " . trim($cspHeaderValue);
		}

		return $automaticCspHeader;
	}
}
