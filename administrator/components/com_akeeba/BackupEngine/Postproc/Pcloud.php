<?php
/**
 * Akeeba Engine
 *
 * @package   akeebaengine
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Engine\Postproc;

defined('AKEEBAENGINE') || die();

use Akeeba\Engine\Factory;
use Akeeba\Engine\Platform;
use Akeeba\Engine\Postproc\Connector\Pcloud as ConnectorPCloud;
use Akeeba\Engine\Postproc\Exception\BadConfiguration;
use Akeeba\Engine\Postproc\Exception\RangeDownloadNotSupported;

class Pcloud extends Base
{
	/**
	 * The currently configured directory
	 *
	 * @var string
	 */
	protected $directory;

	/**
	 * The name of the OAuth2 callback method in the parent window (the configuration page)
	 *
	 * @var   string
	 */
	protected $callbackMethod = 'akconfig_pcloud_oauth_callback';

	/**
	 * The key in Akeeba Engine's settings registry for this post-processing method
	 *
	 * @var   string
	 */
	protected $settingsKey = 'pcloud';

	public function __construct()
	{
		/**
		 * Download to browser: only works when API call is made from the same IP the file will be downloaded from.
		 * Delete: No problem.
		 * Download to file: It's like download to browser but by definition our IPs match.
		 */
		$this->supportsDownloadToBrowser = false;
		$this->supportsDelete            = true;
		$this->supportsDownloadToFile    = true;
	}

	public function oauthCallback(array $params)
	{
		$input = $params['input'];

		$data = (object) [
			'access_token'  => $input['access_token'],
		];

		$serialisedData = json_encode($data);

		return sprintf(
			'<script type="application/javascript">window.opener.%s(%s);</script>',
			$this->callbackMethod, $serialisedData
		);
	}

	public function processPart($localFilepath, $remoteBaseName = null)
	{
		// Do not remove, required to set up $this->directory used below.
		/** @var ConnectorPCloud $connector */
		$connector = $this->getConnector();

		// Store the absolute remote path in the class property
		$directory        = $this->directory;
		$basename         = empty($remoteBaseName) ? basename($localFilepath) : $remoteBaseName;
		$this->remotePath = $directory . '/' . $basename;

		// Get the remote file's pathname
		$remotePath = trim($directory, '/') . '/' . basename($localFilepath);

		if (substr($remotePath, -1) != '/')
		{
			$remotePath = '/' . $remotePath;
		}

		// Single part upload
		Factory::getLog()->debug(__METHOD__ . " - Performing simple upload");

		$connector->simpleUpload($remotePath, $localFilepath);

		return true;
	}

	public function downloadToFile($remotePath, $localFile, $fromOffset = null, $length = null)
	{
		if (!is_null($fromOffset))
		{
			// Ranges are not supported
			throw new RangeDownloadNotSupported();
		}

		/** @var ConnectorPCloud $connector */
		$connector = $this->getConnector();

		// Download the file
		$connector->download($remotePath, $localFile);
	}

	public function downloadToBrowser($remotePath)
	{
		/** @var ConnectorPCloud $connector */
		$connector = $this->getConnector();

		return $connector->getSignedUrl($remotePath);
	}

	public function delete($path)
	{
		/** @var ConnectorPCloud $connector */
		$connector = $this->getConnector();

		$connector->delete($path);
	}

	protected function makeConnector()
	{
		// Retrieve engine configuration data
		$config = Factory::getConfiguration();

		$access_token  = trim($config->get('engine.postproc.' . $this->settingsKey . '.access_token', ''));

		$defaultDirectory = $config->get('engine.postproc.' . $this->settingsKey . '.directory', '');
		$this->directory  = $config->get('volatile.postproc.directory', $defaultDirectory);

		// Sanity checks
		if (empty($access_token))
		{
			throw new BadConfiguration('You have not linked Akeeba Backup with your pCloud account');
		}

		if (!function_exists('curl_init'))
		{
			throw new BadConfiguration('cURL is not enabled, please enable it in order to post-process your archives');
		}

		// Fix the directory name, if required
		$this->directory = empty($this->directory) ? '' : $this->directory;
		$this->directory = trim($this->directory);
		$this->directory = ltrim(Factory::getFilesystemTools()->TranslateWinPath($this->directory), '/');
		$this->directory = Factory::getFilesystemTools()->replace_archive_name_variables($this->directory);
		$config->set('volatile.postproc.directory', $this->directory);

		// Get Download ID
		$dlid = Platform::getInstance()->get_platform_configuration_option('update_dlid', '');

		if (empty($dlid))
		{
			throw new BadConfiguration('You must enter your Download ID in the application configuration before using the “Upload to pCloud” feature.');
		}

		return new ConnectorPCloud($access_token, $dlid);
	}

	protected function getOAuth2HelperUrl()
	{
		return ConnectorPCloud::helperUrl;
	}
}
