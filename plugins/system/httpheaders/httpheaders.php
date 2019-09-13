<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.HttpHeaders
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseDriver;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Joomla\Filesystem\File;
use Joomla\Registry\Registry;

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
	 * The static header configuration as array
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	private $staticHeaderConfiguration = [];

	/**
	 * Defines the Server config file type none
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	const SERVER_CONFIG_FILE_NONE = '';

	/**
	 * Defines the Server config file type htaccess
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	const SERVER_CONFIG_FILE_HTACCESS = '.htaccess';

	/**
	 * Defines the Server config file type web.config
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	const SERVER_CONFIG_FILE_WEBCONFIG = 'web.config';

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
			'onAfterInitialise'    => 'setHttpHeaders',
			'onExtensionAfterSave' => 'writeStaticHttpHeaders',
			'onAfterRender'        => 'applyHashesToCspRule',
		];
	}

	/**
	 * The `applyHashesToCspRule` method makes sure the csp hashes are added to the csp header when enabled
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function applyHashesToCspRule(): void
	{
		// CSP is only relevant on html pages. Let's early exit here.
		if (Factory::getDocument()->getType() !== 'html')
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

		// Make sure the getHeadData method exists
		if (!method_exists(Factory::getDocument(), 'getHeadData'))
		{
			return;
		}

		$headData     = Factory::getDocument()->getHeadData();
		$scriptHashes = [];
		$styleHashes  = [];

		if ($scriptHashesEnabled)
		{
			// Generate the hashes for the script-src
			$inlineScripts = is_array($headData['script']) ? $headData['script'] : [];

			foreach ($inlineScripts as $type => $scriptContent)
			{
				$scriptHashes[] = "'sha256-" . base64_encode(hash('sha256', $scriptContent, true)) . "'";
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

		// CSP is only relevant on html pages. Let's early exit here.
		if (Factory::getDocument()->getType() != 'html')
		{
			return;
		}

		// Handle CSP Header configuration
		$cspOptions = (int) $this->comCspParams->get('contentsecuritypolicy', 0);

		if ($cspOptions)
		{
			$this->setCspHeader();
		}
	}

	/**
	 * On saving this plugin we may want to generate the latest static headers
	 *
	 * @param   Event  $event  The Event Object with the passed arguments
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function writeStaticHttpHeaders(Event $event): void
	{
		// Read the arguments from the event object
		$context = $event->getArgument('0');
		$table   = $event->getArgument('1');
		$isNew   = $event->getArgument('2');

		// When the updated extension is not PLG_SYSTEM_HTTPHEADERS we don't do anything
		if ($context !== 'com_plugins.plugin' || $table->element !== $this->_name || $table->folder !== $this->_type)
		{
			return;
		}

		// Get the new params saved by the plugin
		$pluginParams = new Registry($table->get('params'));

		// When the option is disabled we don't do anything here.
		if (!$pluginParams->get('write_static_headers', 0))
		{
			return;
		}

		$serverConfigFile = $this->getServerConfigFile();

		if (!$serverConfigFile)
		{
			$this->app->enqueueMessage(
				Text::_('PLG_SYSTEM_HTTPHEADERS_MESSAGE_STATICHEADERS_NOT_WRITTEN_NO_SERVER_CONFIGFILE_FOUND'),
				'warning'
			);

			return;
		}

		// Get the StaticHeaderConfiguration
		$this->staticHeaderConfiguration = $this->getStaticHeaderConfiguration($pluginParams);

		// Write the static headers
		$result = $this->writeStaticHeaders();

		if (!$result)
		{
			// Something did not work tell them that and how to update themself.
			$this->app->enqueueMessage(
				Text::sprintf(
					'PLG_SYSTEM_HTTPHEADERS_MESSAGE_STATICHEADERS_NOT_WRITTEN',
					$serverConfigFile,
					$this->getRulesForStaticHeaderConfiguration($serverConfigFile)
				),
				'error'
			);

			return;
		}

		// Show messge that everything was done
		$this->app->enqueueMessage(
			Text::sprintf(
				'PLG_SYSTEM_HTTPHEADERS_MESSAGE_STATICHEADERS_WRITTEN',
				$serverConfigFile
			),
			'message'
		);
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
		$cspValues           = $this->comCspParams->get('contentsecuritypolicy_values', []);
		$nonceEnabled        = (int) $this->comCspParams->get('nonce_enabled', 0);
		$scriptHashesEnabled = (int) $this->comCspParams->get('script_hashes_enabled', 0);
		$styleHashesEnabled  = (int) $this->comCspParams->get('style_hashes_enabled', 0);

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

		$automaticCspHeader  = [];
		$cspHeaderCollection = [];
		$nonceEnabled        = (int) $this->comCspParams->get('nonce_enabled', 0);
		$scriptHashesEnabled = (int) $this->comCspParams->get('script_hashes_enabled', 0);
		$styleHashesEnabled  = (int) $this->comCspParams->get('style_hashes_enabled', 0);

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
			if ($scriptHashesEnabled && strpos($cspValue->directive, 'script-src') === 0)
			{
				$cspHeaderValue = '{script-hashes} ' . $cspHeaderValue;
			}

			// Append the style hashes placeholder
			if ($styleHashesEnabled && strpos($cspValue->directive, 'style-src') === 0)
			{
				$cspHeaderValue = '{style-hashes} ' . $cspHeaderValue;
			}

			// By default we should whitelist 'self' on any directive
			$automaticCspHeader[] = $cspHeaderkey . " 'self' " . trim($cspHeaderValue);
		}

		return $automaticCspHeader;
	}

	/**
	 * Return the server config file constant
	 *
	 * @return  string  Constante pointing to the correct server config file or none
	 *
	 * @since   4.0.0
	 */
	private function getServerConfigFile(): string
	{
		if (file_exists($this->getServerConfigFilePath(self::SERVER_CONFIG_FILE_HTACCESS))
			&& substr(strtolower($_SERVER['SERVER_SOFTWARE']), 0, 6) === 'apache')
		{
			return self::SERVER_CONFIG_FILE_HTACCESS;
		}

		// We are not on an apache so lets just check whether the web.config file exits
		if (file_exists($this->getServerConfigFilePath(self::SERVER_CONFIG_FILE_WEBCONFIG)))
		{
			return self::SERVER_CONFIG_FILE_WEBCONFIG;
		}

		return self::SERVER_CONFIG_FILE_NONE;
	}

	/**
	 * Return the path to the server config file we check
	 *
	 * @param   string  $file  Constante pointing to the correct server config file or none
	 *
	 * @return  string  Expected path to the requested file; Or false on error
	 *
	 * @since   4.0.0
	 */
	private function getServerConfigFilePath($file): string
	{
		return JPATH_ROOT . DIRECTORY_SEPARATOR . $file;
	}

	/**
	 * Return the static Header Configuration based on the server config file
	 *
	 * @param   string  $serverConfigFile  Constant holding the server configuration file
	 *
	 * @return  string  Buffer style text of the Header Configuration based on the server config file
	 *
	 * @since   4.0.0
	 */
	private function getRulesForStaticHeaderConfiguration($serverConfigFile): string
	{
		if ($serverConfigFile === self::SERVER_CONFIG_FILE_HTACCESS)
		{
			return $this->getHtaccessRulesForStaticHeaderConfiguration();
		}

		if ($serverConfigFile === self::SERVER_CONFIG_FILE_WEBCONFIG)
		{
			return $this->getWebConfigRulesForStaticHeaderConfiguration();
		}

		return false;
	}

	/**
	 * Return the static Header Configuration based in the .htaccess format
	 *
	 * @return  string  Buffer style text of the Header Configuration based on the server config file; empty string on error
	 *
	 * @since   4.0.0
	 */
	private function getHtaccessRulesForStaticHeaderConfiguration(): string
	{
		$oldHtaccessBuffer = file($this->getServerConfigFilePath(self::SERVER_CONFIG_FILE_HTACCESS), FILE_IGNORE_NEW_LINES);
		$newHtaccessBuffer = '';

		if (!$oldHtaccessBuffer)
		{
			// `file` couldn't read the htaccess we can't do anything at this point
			return '';
		}

		$scriptLines = false;

		foreach ($oldHtaccessBuffer as $id => $line)
		{
			if ($line === '### MANAGED BY PLG_SYSTEM_HTTPHEADERS DO NOT MANUALLY EDIT! - START ###')
			{
				$scriptLines = true;
				continue;
			}

			if ($line === '### MANAGED BY PLG_SYSTEM_HTTPHEADERS DO NOT MANUALLY EDIT! - END ###'
				|| $line === '##############################################################')
			{
				$scriptLines = false;
				continue;
			}

			if ($scriptLines)
			{
				// When we are between our makers all content should be removed
				continue;
			}

			$newHtaccessBuffer .= $line . PHP_EOL;
		}

		$newHtaccessBuffer .= '##############################################################' . PHP_EOL;
		$newHtaccessBuffer .= '### MANAGED BY PLG_SYSTEM_HTTPHEADERS DO NOT MANUALLY EDIT! - START ###' . PHP_EOL;
		$newHtaccessBuffer .= '<IfModule mod_headers.c>' . PHP_EOL;

		foreach ($this->staticHeaderConfiguration as $headerAndClient => $value)
		{
			$headerAndClient = explode('#', $headerAndClient);

			if (!in_array(strtolower($headerAndClient[0]), ['content-security-policy', 'content-security-policy-report-only']))
			{
				$newHtaccessBuffer .= '    Header set ' . $headerAndClient[0] . ' "' . $value . '"' . PHP_EOL;
			}
		}

		$newHtaccessBuffer .= '</IfModule>' . PHP_EOL;
		$newHtaccessBuffer .= '### MANAGED BY PLG_SYSTEM_HTTPHEADERS DO NOT MANUALLY EDIT! - END ###' . PHP_EOL;
		$newHtaccessBuffer .= '##############################################################' . PHP_EOL;
		$newHtaccessBuffer .= PHP_EOL;

		return $newHtaccessBuffer;
	}

	/**
	 * Return the static Header Configuration based in the web.config format
	 *
	 * @return  string  Buffer style text of the Header Configuration based on the server config file or false on error.
	 *
	 * @since   4.0.0
	 */
	private function getWebConfigRulesForStaticHeaderConfiguration(): string
	{
		$webConfigDomDoc = new DOMDocument('1.0', 'UTF-8');

		// We want a nice output
		$webConfigDomDoc->formatOutput = true;
		$webConfigDomDoc->preserveWhiteSpace = false;

		// Load the current file into our object
		$webConfigDomDoc->load($this->getServerConfigFilePath(self::SERVER_CONFIG_FILE_WEBCONFIG));

		// Get an DOMXPath Object mathching our file
		$xpath = new DOMXPath($webConfigDomDoc);

		// We require an correct tree containing an system.webServer node!
		$systemWebServer = $xpath->query("/configuration/location/system.webServer");

		if ($systemWebServer->length === 0 || $systemWebServer->length > 1)
		{
			// There is only one (or none) so we don't do anything here
			return '';
		}

		// Check what configurations exists already
		$httpProtocol  = $xpath->query("/configuration/location/system.webServer/httpProtocol");
		$customHeaders = $xpath->query("/configuration/location/system.webServer/httpProtocol/customHeaders");

		// Does the httpProtocol node exist?
		if ($httpProtocol->length === 0)
		{
			$newHttpProtocol = $webConfigDomDoc->createElement('httpProtocol');
			$newCustomHeaders = $webConfigDomDoc->createElement('customHeaders');

			foreach ($this->staticHeaderConfiguration as $headerAndClient => $value)
			{
				$headerAndClient = explode('#', $headerAndClient);

				if (!in_array(strtolower($headerAndClient[0]), ['content-security-policy', 'content-security-policy-report-only']))
				{
					$newHeader = $webConfigDomDoc->createElement('add');

					$newHeader->setAttribute('name', $headerAndClient[0]);
					$newHeader->setAttribute('value', $value);
					$newCustomHeaders->appendChild($newHeader);
				}
			}

			$newHttpProtocol->appendChild($newCustomHeaders);
			$systemWebServer[0]->appendChild($newHttpProtocol);
		}
		// It seams there are a httpProtocol node so does the customHeaders node exist?
		elseif ($customHeaders->length === 0)
		{
			$newCustomHeaders = $webConfigDomDoc->createElement('customHeaders');

			foreach ($this->staticHeaderConfiguration as $headerAndClient => $value)
			{
				$headerAndClient = explode('#', $headerAndClient);

				if (!in_array(strtolower($headerAndClient[0]), ['content-security-policy', 'content-security-policy-report-only']))
				{
					$newHeader = $webConfigDomDoc->createElement('add');

					$newHeader->setAttribute('name', $headerAndClient[0]);
					$newHeader->setAttribute('value', $value);
					$newCustomHeaders->appendChild($newHeader);
				}
			}

			$httpProtocol[0]->appendChild($newCustomHeaders);
		}
		// Well It seams httpProtocol and customHeaders exists lets check now the individual header (add) nodes
		else
		{
			$oldCustomHeaders = $xpath->query("/configuration/location/system.webServer/httpProtocol/customHeaders/add");

			// Here we check all headers actually exists with the correct value
			foreach ($this->staticHeaderConfiguration as $headerAndClient => $value)
			{
				$headerAndClient = explode('#', $headerAndClient);

				// When no headers exitsts at all we can't find anything :D
				if ($oldCustomHeaders->length === 0)
				{
					$found = false;
				}

				// Check if the header is currently set or not
				foreach ($oldCustomHeaders as $oldCustomHeader)
				{
					$found = false;
					$customHeadersName = $oldCustomHeader->getAttribute('name');

					if ($headerAndClient[0] === $customHeadersName)
					{
						// We found it, well done.
						$found = true;
						break;
					}
				}

				// The header wasn't found we need to create it
				if (!$found)
				{
					if (!in_array(strtolower($headerAndClient[0]), ['content-security-policy', 'content-security-policy-report-only']))
					{
						// Generate the new header Element
						$newHeader = $webConfigDomDoc->createElement('add');
						$newHeader->setAttribute('name', $headerAndClient[0]);
						$newHeader->setAttribute('value', $value);

						// Append the new header
						$customHeaders[0]->appendChild($newHeader);
					}
				}

				$customHeadersValue = $oldCustomHeader->getAttribute('value');

				if ($value === $customHeadersValue)
				{
					continue;
				}

				$oldCustomHeader->setAttribute('value', $value);
			}
		}

		return $webConfigDomDoc->saveXML();
	}

	/**
	 * Wirte the static headers.
	 *
	 * @return  boolean  True on success; false on any error
	 *
	 * @since   4.0.0
	 */
	private function writeStaticHeaders(): bool
	{
		$pathToHtaccess  = $this->getServerConfigFilePath(self::SERVER_CONFIG_FILE_HTACCESS);
		$pathToWebConfig = $this->getServerConfigFilePath(self::SERVER_CONFIG_FILE_WEBCONFIG);

		if (file_exists($pathToHtaccess))
		{
			$htaccessContent = $this->getHtaccessRulesForStaticHeaderConfiguration();

			if (is_readable($pathToHtaccess) && !empty($htaccessContent))
			{
				// Write the htaccess using the Frameworks File Class
				return File::write($pathToHtaccess, $htaccessContent);
			}
		}

		if (file_exists($pathToWebConfig))
		{
			$webConfigContent = $this->getWebConfigRulesForStaticHeaderConfiguration();

			if (is_readable($pathToWebConfig) && !empty($webConfigContent))
			{
				// Setup and than write the web.config write using DOMDocument
				$webConfigDomDoc = new DOMDocument;
				$webConfigDomDoc->formatOutput = true;
				$webConfigDomDoc->preserveWhiteSpace = false;
				$webConfigDomDoc->loadXML($webConfigContent);

				// When the return code is an integer we got the bytes and everything went well if not something broke..
				return is_integer($webConfigDomDoc->save($pathToWebConfig)) ? true : false;
			}
		}
	}

	/**
	 * Get the configured static headers.
	 *
	 * @param   Registry  $pluginParams  An Registry Object containing the plugin parameters
	 *
	 * @return  array  We return the array of static headers with its values.
	 *
	 * @since   4.0.0
	 */
	private function getStaticHeaderConfiguration($pluginParams = false): array
	{
		$staticHeaderConfiguration = [];

		// Fallback to $this->params when no params has been passed
		if ($pluginParams === false)
		{
			$pluginParams = $this->params;
		}

		// X-frame-options
		if ($pluginParams->get('xframeoptions'))
		{
			$staticHeaderConfiguration['x-frame-options#both'] = 'SAMEORIGIN';
		}

		// Referrer-policy
		$referrerPolicy = (string) $pluginParams->get('referrerpolicy', 'no-referrer-when-downgrade');

		if ($referrerPolicy !== 'disabled')
		{
			$staticHeaderConfiguration['referrer-policy#both'] = $referrerPolicy;
		}

		// Generate the strict-transport-security header
		$strictTransportSecurity = (int) $pluginParams->get('hsts', 0);

		if ($strictTransportSecurity)
		{
			$maxAge        = (int) $pluginParams->get('hsts_maxage', 31536000);
			$hstsOptions   = [];
			$hstsOptions[] = $maxAge < 300 ? 'max-age=300' : 'max-age=' . $maxAge;

			if ($pluginParams->get('hsts_subdomains', 0))
			{
				$hstsOptions[] = 'includeSubDomains';
			}

			if ($pluginParams->get('hsts_preload', 0))
			{
				$hstsOptions[] = 'preload';
			}

			$staticHeaderConfiguration['strict-transport-security#both'] = implode('; ', $hstsOptions);
		}

		// Generate the additional headers
		$additionalHttpHeaders = $pluginParams->get('additional_httpheader', []);

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
		$this->staticHeaderConfiguration = $this->getStaticHeaderConfiguration($this->params);

		if (empty($this->staticHeaderConfiguration))
		{
			return;
		}

		foreach ($this->staticHeaderConfiguration as $headerAndClient => $value)
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
