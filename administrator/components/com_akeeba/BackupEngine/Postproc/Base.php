<?php
/**
 * Akeeba Engine
 *
 * @package   akeebaengine
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Engine\Postproc;

defined('AKEEBAENGINE') || die();

use Akeeba\Engine\Platform;
use Akeeba\Engine\Postproc\Exception\BadConfiguration;
use Akeeba\Engine\Postproc\Exception\DeleteNotSupported;
use Akeeba\Engine\Postproc\Exception\DownloadToBrowserNotSupported;
use Akeeba\Engine\Postproc\Exception\DownloadToServerNotSupported;
use Akeeba\Engine\Postproc\Exception\OAuthNotSupported;
use Exception;

/**
 * Akeeba Engine post-processing abstract class. Provides the default implementation of most of the PostProcInterface
 * methods.
 */
abstract class Base implements PostProcInterface
{
	/**
	 * Should we break the step before post-processing?
	 *
	 * The only engine which does not require a step break before is the None engine.
	 *
	 * @var bool
	 */
	protected $recommendsBreakBefore = true;

	/**
	 * Should we break the step after post-processing?
	 *
	 * @var bool
	 */
	protected $recommendsBreakAfter = true;

	/**
	 * Does this engine processes the files in a way that makes deleting the originals safe?
	 *
	 * @var bool
	 */
	protected $advisesDeletionAfterProcessing = true;

	/**
	 * Does this engine support remote file deletes?
	 *
	 * @var bool
	 */
	protected $supportsDelete = false;

	/**
	 * Does this engine support downloads to files?
	 *
	 * @var bool
	 */
	protected $supportsDownloadToFile = false;

	/**
	 * Does this engine support downloads to browser?
	 *
	 * @var bool
	 */
	protected $supportsDownloadToBrowser = false;

	/**
	 * Does this engine push raw data to the browser when downloading a file?
	 *
	 * Set to true if raw data will be dumped to the browser when downloading the file to the browser. Set to false if
	 * a URL is returned instead.
	 *
	 * @var bool
	 */
	protected $inlineDownloadToBrowser = false;

	/**
	 * The remote absolute path to the file which was just processed. Leave null if the file is meant to
	 * be non-retrievable, i.e. sent to email or any other one way service.
	 *
	 * @var string
	 */
	protected $remotePath = null;

	/**
	 * Whitelist of method names you can call using customAPICall().
	 *
	 * @var array
	 */
	protected $allowedCustomAPICallMethods = ['oauthCallback'];

	/**
	 * The connector object for this post-processing engine
	 *
	 * @var object|null
	 */
	private $connector;

	public function delete($path)
	{
		throw new DeleteNotSupported();
	}

	public function downloadToFile($remotePath, $localFile, $fromOffset = null, $length = null)
	{
		throw new DownloadToServerNotSupported();
	}

	public function downloadToBrowser($remotePath)
	{
		throw new DownloadToBrowserNotSupported();
	}

	public final function customAPICall($method, $params = [])
	{
		if (!in_array($method, $this->allowedCustomAPICallMethods) || !method_exists($this, $method))
		{
			header('HTTP/1.0 501 Not Implemented');

			exit();
		}

		return call_user_func_array([$this, $method], [$params]);
	}

	public function oauthOpen($params = [])
	{
		$callback = $params['callbackURI'] . '&method=oauthCallback';

		$url = $this->getOAuth2HelperUrl();
		$url .= (strpos($url, '?') !== false) ? '&' : '?';
		$url .= 'callback=' . urlencode($callback);
		$url .= '&dlid=' . Platform::getInstance()->get_platform_configuration_option('update_dlid', '');

		Platform::getInstance()->redirect($url);
	}

	/**
	 * Fetches the authentication token from the OAuth helper script, after you've run the first step of the OAuth
	 * authentication process. Must be overridden in subclasses.
	 *
	 * @param   array  $params
	 *
	 * @return  void
	 *
	 * @throws  OAuthNotSupported
	 */
	public function oauthCallback(array $params)
	{
		throw new OAuthNotSupported();
	}

	public function recommendsBreakBefore()
	{
		return $this->recommendsBreakBefore;
	}

	public function recommendsBreakAfter()
	{
		return $this->recommendsBreakAfter;
	}

	public function isFileDeletionAfterProcessingAdvisable()
	{
		return $this->advisesDeletionAfterProcessing;
	}

	public function supportsDelete()
	{
		return $this->supportsDelete;
	}

	public function supportsDownloadToFile()
	{
		return $this->supportsDownloadToFile;
	}

	public function supportsDownloadToBrowser()
	{
		return $this->supportsDownloadToBrowser;
	}

	public function doesInlineDownloadToBrowser()
	{
		return $this->inlineDownloadToBrowser;
	}

	public function getRemotePath()
	{
		return $this->remotePath;
	}

	/**
	 * Returns the URL to the OAuth2 helper script. Used by the oauthOpen method. Must be overridden in subclasses.
	 *
	 * @return  string
	 *
	 * @throws  OAuthNotSupported
	 */
	protected function getOAuth2HelperUrl()
	{
		throw new OAuthNotSupported();
	}

	/**
	 * Returns an instance of the connector object.
	 *
	 * @param   bool  $forceNew  Should I force the creation of a new connector object?
	 *
	 * @return  object  The connector object
	 *
	 * @throws  BadConfiguration  If there is a configuration error which prevents creating a connector object.
	 * @throws  Exception
	 */
	final protected function getConnector($forceNew = false)
	{
		if ($forceNew)
		{
			$this->resetConnector();
		}

		if (empty($this->connector))
		{
			$this->connector = $this->makeConnector();
		}

		return $this->connector;
	}

	/**
	 * Resets the connector.
	 *
	 * If the connector requires any special handling upon destruction you must handle it in its __destruct method.
	 *
	 * @return  void
	 */
	final protected function resetConnector()
	{
		$this->connector = null;
	}

	/**
	 * Creates a new connector object based on the engine configuration stored in the backup profile.
	 *
	 * Do not use this method directly. Use getConnector() instead.
	 *
	 * @return  object  The connector object
	 *
	 * @throws  BadConfiguration  If there is a configuration error which prevents creating a connector object.
	 * @throws  Exception  Any other error when creating or initializing the connector object.
	 */
	protected abstract function makeConnector();
}
