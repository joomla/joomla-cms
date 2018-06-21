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
	 * @since  3.8.0
	 */
	protected $db;

	/**
	 * The list of the supported HTTP headers
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	protected $supportedHttpHeaders = [
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

		// Handle CSP Header configuration
		$cspOptions = (int) $this->params->get('contentsecuritypolicy', 0);

		if ($cspOptions)
		{
			$this->setCspHeader();
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
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function setCspHeader(): void
	{
		// Nonce generation 
		$cspNonce = base64_encode(bin2hex(random_bytes(64)));
		$this->app->set('csp_nonce', $cspNonce);

		// Mode Selector
		$cspMode = $this->params->get('contentsecuritypolicy_mode', 'custom');

		// In detecting mode we set this default rule so any report gets collected by com_csp
		if ($cspMode === 'detecting')
		{
			$this->app->setHeader(
				'Content-Security-Policy-Report-Only',
				"default-src 'self'; report-uri index.php?option=com_csp&task=report.log"
			);

			return;
		}

		// In automatic mode we compile the automatic header values and append it to the header
		if ($cspMode === 'automatic')
		{
			$this->app->setHeader(
				'Content-Security-Policy',
				trim(
					implode(
						'; ',
						$this->compileAutomaticCspHeaderValues($cspNonce)
					)
				)
			);

			return;
		}

		// In custom mode we compile the header from the values configured
		$cspValues   = $this->params->get('contentsecuritypolicy_values', array());
		$cspReadOnly = (int) $this->params->get('contentsecuritypolicy_report_only', 0);
		$csp         = $cspReadOnly === 0 ? 'Content-Security-Policy' : 'Content-Security-Policy-Report-Only';

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
				if ($cspValue->directive === 'script-src')
				{
					'nonce-' . $cspNonce . ' ' . $cspValue->value;
				}

				$newCspValues[] = trim($cspValue->directive) . ' ' . trim($cspValue->value);
			}
		}

		if (empty($newCspValues))
		{
			return;
		}

		$this->app->setHeader($csp, trim(implode('; ', $newCspValues)));
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
	 * Set the HSTS header when enabled
	 *
	 * @param  string  $nonce  The System nonce used for script and style tags
	 *
	 * @return  array  An array containing the csp rules found in com_csp
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function compileAutomaticCspHeaderValues($nonce): array
	{
		// Get database
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName(['directive', 'blocked_uri']))
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

		$automaticCspHeader = array();
		$cspHeaderCollection = array();

		foreach ($rows as $row)
		{
			if (!isset($cspHeaderCollection[$row->directive]))
			{
				$cspHeaderCollection = array_fill_keys([$row->directive], '');
			}
	
			$cspHeaderCollection[$row->directive] .= ' ' . $row->blocked_uri;
		}

		foreach ($cspHeaderCollection as $cspHeaderkey => $cspHeaderValue)
		{
			// Append the random $nonce for the script and style tags
			if (in_array($cspHeaderkey, ['script-src', 'style-src']))
			{
				$cspHeaderValue = 'nonce-' . $nonce . $cspHeaderValue;
			}

			$automaticCspHeader[] = $cspHeaderkey . ' ' . trim($cspHeaderValue);
		}

		return $automaticCspHeader;
	}
}
