<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.HttpHeaders
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseDriver;
use Joomla\Event\SubscriberInterface;

/**
 * Plugin class for HTTP Headers
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
	 * @var    CMSApplication
	 * @since  4.0.0
	 */
	protected $app;

	/**
	 * Database object.
	 *
	 * @var    DatabaseDriver
	 * @since  4.0.0
	 */
	protected $db;

	/**
	 * The generated csp nonce value
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	private $cspNonce;

	/**
	 * The params of the com_csp component
	 *
	 * @var    \Joomla\Registry\Registry
	 * @since  4.0.0
	 */
	private $comCspParams;

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
		'referrer-policy',
		'expect-ct',
		'feature-policy',
		'cross-origin-opener-policy',
	];

	/**
	 * The list of directives supporting nonce
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	private $nonceDirectives = [
		'script-src',
		'style-src',
	];

	/**
	 * Constructor.
	 *
	 * @param   object  &$subject  The object to observe.
	 * @param   array   $config    An optional associative array of configuration settings.
	 *
	 * @since   4.0.0
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		// Get the com_csp params that include the content-security-policy configuration
		$this->comCspParams = ComponentHelper::getParams('com_csp');

		// Nonce generation
		$this->cspNonce = base64_encode(bin2hex(random_bytes(64)));
		$this->app->set('csp_nonce', $this->cspNonce);
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
			'onAfterRender'     => 'applyHashesToCspRule',
		];
	}

	/**
	 * The `applyHashesToCspRule` method makes sure the csp hashes are added to the csp header when enabled
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function applyHashesToCspRule(): void
	{
		// CSP is only relevant on html pages. Let's early exit here.
		if ($this->app->getDocument()->getType() !== 'html')
		{
			return;
		}

		$scriptHashesEnabled = (int) $this->comCspParams->get('script_hashes_enabled', 0);
		$styleHashesEnabled  = (int) $this->comCspParams->get('style_hashes_enabled', 0);

		// Early exit when both options are disabled
		if (!$scriptHashesEnabled && !$styleHashesEnabled)
		{
			return;
		}

		$headData     = $this->app->getDocument()->getHeadData();
		$scriptHashes = [];
		$styleHashes  = [];

		if ($scriptHashesEnabled)
		{
			// Generate the hashes for the script-src
			$inlineScripts = is_array($headData['script']) ? $headData['script'] : [];

			foreach ($inlineScripts as $type => $scripts)
			{
				foreach ($scripts as $hash => $scriptContent)
				{
					$scriptHashes[] = "'sha256-" . base64_encode(hash('sha256', $scriptContent, true)) . "'";
				}
			}
		}

		if ($styleHashesEnabled)
		{
			// Generate the hashes for the style-src
			$inlineStyles = is_array($headData['style']) ? $headData['style'] : [];

			foreach ($inlineStyles as $type => $styleContent)
			{
				$styleHashes[] = "'sha256-" . base64_encode(hash('sha256', $styleContent, true)) . "'";
			}
		}

		// Replace the hashes in the csp header when set.
		$headers = $this->app->getHeaders();

		foreach ($headers as $id => $headerConfiguration)
		{
			if (strtolower($headerConfiguration['name']) === 'content-security-policy'
				|| strtolower($headerConfiguration['name']) === 'content-security-policy-report-only')
			{
				$newHeaderValue = $headerConfiguration['value'];

				if (!empty($scriptHashes))
				{
					$newHeaderValue = str_replace('{script-hashes}', implode(' ', $scriptHashes), $newHeaderValue);
				}
				else
				{
					$newHeaderValue = str_replace('{script-hashes}', '', $newHeaderValue);
				}

				if (!empty($styleHashes))
				{
					$newHeaderValue = str_replace('{style-hashes}', implode(' ', $styleHashes), $newHeaderValue);
				}
				else
				{
					$newHeaderValue = str_replace('{style-hashes}', '', $newHeaderValue);
				}

				$this->app->setHeader($headerConfiguration['name'], $newHeaderValue, true);
			}
		}
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
		$this->setStaticHeaders();

		// Handle CSP Header configuration
		$cspOptions = (int) $this->comCspParams->get('contentsecuritypolicy', 0);

		if ($cspOptions)
		{
			$this->setCspHeader();
		}
	}

	/**
	 * Set the CSP header when enabled
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	private function setCspHeader(): void
	{
		// Mode Selector
		$cspMode = $this->comCspParams->get('contentsecuritypolicy_mode', 'detect');

		// In detecting mode we set this default rule so any report gets collected by com_csp
		if ($cspMode === 'detect')
		{
			$frontendUrl = str_replace('/administrator', '', Uri::base());

			$this->app->setHeader(
				'content-security-policy-report-only',
				"default-src 'self'; report-uri " . $frontendUrl . "index.php?option=com_csp&task=report.log&client=" . $this->app->getName()
			);

			return;
		}

		$cspReadOnly = (int) $this->comCspParams->get('contentsecuritypolicy_report_only', 1);
		$cspHeader   = $cspReadOnly === 0 ? 'content-security-policy' : 'content-security-policy-report-only';

		// In automatic mode we compile the automatic header values and append it to the header
		if ($cspMode === 'auto')
		{
			$automaticRules = trim(
				implode(
					'; ',
					$this->compileAutomaticCspHeaderRules()
				)
			);

			// Set the header
			$this->app->setHeader($cspHeader, $automaticRules);

			return;
		}

		// In custom mode we compile the header from the values configured
		$cspValues                 = $this->comCspParams->get('contentsecuritypolicy_values', []);
		$nonceEnabled              = (int) $this->comCspParams->get('nonce_enabled', 0);
		$scriptHashesEnabled       = (int) $this->comCspParams->get('script_hashes_enabled', 0);
		$styleHashesEnabled        = (int) $this->comCspParams->get('style_hashes_enabled', 0);
		$frameAncestorsSelfEnabled = (int) $this->comCspParams->get('frame_ancestors_self_enabled', 1);
		$frameAncestorsSet         = false;

		foreach ($cspValues as $cspValue)
		{
			// Handle the client settings foreach header
			if (!$this->app->isClient($cspValue->client) && $cspValue->client != 'both')
			{
				continue;
			}

			// We can only use this if this is a valid entry
			if (isset($cspValue->directive) && isset($cspValue->value)
				&& !empty($cspValue->directive) && !empty($cspValue->value))
			{
				if (in_array($cspValue->directive, $this->nonceDirectives) && $nonceEnabled)
				{
					// Append the nonce
					$cspValue->value = str_replace('{nonce}', "'nonce-" . $this->cspNonce . "'", $cspValue->value);
				}

				// Append the script hashes placeholder
				if ($scriptHashesEnabled && strpos($cspValue->directive, 'script-src') === 0)
				{
					$cspValue->value = '{script-hashes} ' . $cspValue->value;
				}

				// Append the style hashes placeholder
				if ($styleHashesEnabled && strpos($cspValue->directive, 'style-src') === 0)
				{
					$cspValue->value = '{style-hashes} ' . $cspValue->value;
				}

				if ($cspValue->directive === 'frame-ancestors')
				{
					$frameAncestorsSet = true;
				}

				$newCspValues[] = trim($cspValue->directive) . ' ' . trim($cspValue->value);
			}
		}

		if ($frameAncestorsSelfEnabled && !$frameAncestorsSet)
		{
			$newCspValues[] = 'frame-ancestors \'self\'';
		}

		if (empty($newCspValues))
		{
			return;
		}

		$this->app->setHeader($cspHeader, trim(implode('; ', $newCspValues)));
	}

	/**
	 * Compile the automatic csp header rules based on com_csp / #__csp
	 *
	 * @return  array  An array containing the csp rules found in com_csp
	 *
	 * @since   4.0.0
	 */
	private function compileAutomaticCspHeaderRules(): array
	{
		// Get the published infos from the database
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName(['client', 'directive', 'blocked_uri']))
			->from($this->db->quoteName('#__csp'))
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

		$automaticCspHeader        = [];
		$cspHeaderCollection       = [];
		$nonceEnabled              = (int) $this->comCspParams->get('nonce_enabled', 0);
		$scriptHashesEnabled       = (int) $this->comCspParams->get('script_hashes_enabled', 0);
		$styleHashesEnabled        = (int) $this->comCspParams->get('style_hashes_enabled', 0);
		$frameAncestorsSelfEnabled = (int) $this->comCspParams->get('frame_ancestors_self_enabled', 1);

		foreach ($rows as $row)
		{
			// Handle the client information for each rule
			if (!$this->app->isClient($row->client))
			{
				continue;
			}

			// Make sure the directive exists as a key
			if (!isset($cspHeaderCollection[$row->directive]))
			{
				$cspHeaderCollection = array_merge($cspHeaderCollection, array_fill_keys([$row->directive], ''));
			}

			// Eval or inline lets us make sure they still work by adding ' before and after
			if (in_array($row->blocked_uri, ['unsafe-eval', 'unsafe-inline']))
			{
				$row->blocked_uri = "'$row->blocked_uri'";
			}

			// Whitelist the blocked_uri for the given directive
			$cspHeaderCollection[$row->directive] .= ' ' . $row->blocked_uri;
		}

		// Add the frame-ancestors when not done already
		if (!isset($cspHeaderCollection['frame-ancestors']) && $frameAncestorsSelfEnabled)
		{
			$cspHeaderCollection = array_merge($cspHeaderCollection, array_fill_keys(['frame-ancestors'], ''));
		}

		// We should have a default-src, script-src and style-src rule
		if (!empty($cspHeaderCollection))
		{
			if (!isset($cspHeaderCollection['default-src']))
			{
				$cspHeaderCollection = array_merge($cspHeaderCollection, array_fill_keys(['default-src'], ''));
			}

			if (!isset($cspHeaderCollection['script-src']) && $nonceEnabled)
			{
				$cspHeaderCollection = array_merge($cspHeaderCollection, array_fill_keys(['script-src'], ''));
			}

			if (!isset($cspHeaderCollection['style-src']) && $nonceEnabled)
			{
				$cspHeaderCollection = array_merge($cspHeaderCollection, array_fill_keys(['style-src'], ''));
			}
		}

		foreach ($cspHeaderCollection as $cspHeaderkey => $cspHeaderValue)
		{
			// Append the random $nonce for the script and style tags if enabled
			if (in_array($cspHeaderkey, $this->nonceDirectives) && $nonceEnabled)
			{
				// Append nonce
				$cspHeaderValue = "'nonce-" . $this->cspNonce . "'" . $cspHeaderValue;
			}

			// Append the script hashes placeholder
			if ($scriptHashesEnabled && strpos($cspHeaderkey, 'script-src') === 0)
			{
				$cspHeaderValue = '{script-hashes} ' . $cspHeaderValue;
			}

			// Append the style hashes placeholder
			if ($styleHashesEnabled && strpos($cspHeaderkey, 'style-src') === 0)
			{
				$cspHeaderValue = '{style-hashes} ' . $cspHeaderValue;
			}

			// By default we should whitelist 'self' on any directive
			$automaticCspHeader[] = $cspHeaderkey . " 'self' " . trim($cspHeaderValue);
		}

		return $automaticCspHeader;
	}

	/**

	 * Get the configured static headers.
	 *
	 * @return  array  We return the array of static headers with its values.
	 *
	 * @since   4.0.0
	 */
	private function getStaticHeaderConfiguration(): array
	{
		$staticHeaderConfiguration = [];

		// X-frame-options
		if ($this->params->get('xframeoptions', 1) === 1)
		{
			$staticHeaderConfiguration['x-frame-options#both'] = 'SAMEORIGIN';
		}

		// Referrer-policy
		$referrerPolicy = (string) $this->params->get('referrerpolicy', 'no-referrer-when-downgrade');

		if ($referrerPolicy !== 'disabled')
		{
			$staticHeaderConfiguration['referrer-policy#both'] = $referrerPolicy;
		}

		// Cross-Origin-Opener-Policy
		$coop = (string) $this->params->get('coop', 'same-origin');

		if ($coop !== 'disabled')
		{
			$staticHeaderConfiguration['cross-origin-opener-policy#both'] = $coop;
		}

		// Generate the strict-transport-security header
		if ($this->params->get('hsts', 0) === 1)
		{
			$hstsOptions   = [];
			$hstsOptions[] = 'max-age=' . (int) $this->params->get('hsts_maxage', 31536000);

			if ($this->params->get('hsts_subdomains', 0) === 1)
			{
				$hstsOptions[] = 'includeSubDomains';
			}

			if ($this->params->get('hsts_preload', 0) === 1)
			{
				$hstsOptions[] = 'preload';
			}

			$staticHeaderConfiguration['strict-transport-security#both'] = implode('; ', $hstsOptions);
		}

		// Generate the additional headers
		$additionalHttpHeaders = $this->params->get('additional_httpheader', []);

		foreach ($additionalHttpHeaders as $additionalHttpHeader)
		{
			if (empty($additionalHttpHeader->key) || empty($additionalHttpHeader->value))
			{
				continue;
			}

			if (!in_array(strtolower($additionalHttpHeader->key), $this->supportedHttpHeaders))
			{
				continue;
			}

			// Allow the custom csp headers to use the random $cspNonce in the rules
			if (in_array(strtolower($additionalHttpHeader->key), ['content-security-policy', 'content-security-policy-report-only']))
			{
				$additionalHttpHeader->value = str_replace('{nonce}', "'nonce-" . $this->cspNonce . "'", $additionalHttpHeader->value);
			}

			$staticHeaderConfiguration[$additionalHttpHeader->key . '#' . $additionalHttpHeader->client] = $additionalHttpHeader->value;
		}

		return $staticHeaderConfiguration;
	}

	/**
	 * Set the static headers when enabled
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	private function setStaticHeaders(): void
	{
		$staticHeaderConfiguration = $this->getStaticHeaderConfiguration();

		if (empty($staticHeaderConfiguration))
		{
			return;
		}

		foreach ($staticHeaderConfiguration as $headerAndClient => $value)
		{
			$headerAndClient = explode('#', $headerAndClient);
			$header = $headerAndClient[0];
			$client = isset($headerAndClient[1]) ? $headerAndClient[1] : 'both';

			if (!$this->app->isClient($client) && $client != 'both')
			{
				continue;
			}

			$this->app->setHeader($header, $value, true);
		}
	}
}
