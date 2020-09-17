<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\Model;

// Protect from unauthorized access
defined('_JEXEC') || die();

use Akeeba\Engine\Archiver\Directftp;
use Akeeba\Engine\Archiver\Directsftp;
use Akeeba\Engine\Factory;
use Akeeba\Engine\Platform;
use Akeeba\Engine\Util\Transfer\FtpCurl;
use Akeeba\Engine\Util\Transfer\SftpCurl;
use Exception;
use FOF30\Model\Model;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use RuntimeException;

class Configuration extends Model
{
	/**
	 * Save the engine configuration
	 *
	 * @return  void
	 */
	public function saveEngineConfig()
	{
		$data = $this->getState('engineconfig', []);

		// Forbid stupidly selecting the site's root as the output or temporary directory
		if (array_key_exists('akeeba.basic.output_directory', $data))
		{
			$folder = $data['akeeba.basic.output_directory'];
			$folder = Factory::getFilesystemTools()->translateStockDirs($folder, true, true);
			$check  = Factory::getFilesystemTools()->translateStockDirs('[SITEROOT]', true, true);

			if ($check == $folder)
			{
				$data['akeeba.basic.output_directory'] = '[DEFAULT_OUTPUT]';
			}
		}

		// Unprotect the configuration and merge it
		$config        = Factory::getConfiguration();
		$protectedKeys = $config->getProtectedKeys();
		$config->resetProtectedKeys();
		$config->mergeArray($data, false, false);
		$config->setProtectedKeys($protectedKeys);

		// Save configuration
		Platform::getInstance()->save_configuration();
	}

	/**
	 * Test the FTP connection.
	 *
	 * @return  void
	 * @throws  RuntimeException
	 */
	public function testFTP()
	{
		$config = [
			'host'    => $this->getState('host'),
			'port'    => $this->getState('port'),
			'user'    => $this->getState('user'),
			'pass'    => $this->getState('pass'),
			'initdir' => $this->getState('initdir'),
			'usessl'  => $this->getState('usessl'),
			'passive' => $this->getState('passive'),
		];

		// Check for bad settings
		if (substr($config['host'], 0, 6) == 'ftp://')
		{
			throw new RuntimeException(Text::_('COM_AKEEBA_CONFIG_FTPTEST_BADPREFIX'), 500);
		}

		// Special case for cURL transport
		if ($this->getState('isCurl'))
		{
			$this->testFtpCurl();

			return;
		}

		// Perform the FTP connection test
		$test = new Directftp();

		$test->initialize('', $config);
	}

	/**
	 * Test the SFTP connection.
	 *
	 * @return  void
	 * @throws  RuntimeException
	 */
	public function testSFTP()
	{
		$config = [
			'host'    => $this->getState('host'),
			'port'    => $this->getState('port'),
			'user'    => $this->getState('user'),
			'pass'    => $this->getState('pass'),
			'privkey' => $this->getState('privkey'),
			'pubkey'  => $this->getState('pubkey'),
			'initdir' => $this->getState('initdir'),
		];

		// Check for bad settings
		if (substr($config['host'], 0, 7) == 'sftp://')
		{
			throw new RuntimeException(Text::_('COM_AKEEBA_CONFIG_SFTPTEST_BADPREFIX'), 500);
		}

		// Special case for cURL transport
		if ($this->getState('isCurl'))
		{
			$this->testSftpCurl();

			return;
		}

		// Perform the FTP connection test
		$test = new Directsftp();

		$test->initialize('', $config);
	}

	/**
	 * Opens an OAuth window for the selected post-processing engine
	 *
	 * @return  void
	 * @throws  Exception
	 */
	public function dpeOuthOpen()
	{
		$engine = $this->getState('engine');
		$params = $this->getState('params', []);

		// Get a callback URI for OAuth 2
		$params['callbackURI'] = rtrim(Uri::base(), '/') . '/index.php?option=com_akeeba&view=Configuration&task=dpecustomapiraw&engine=' . $engine;

		// Get the Input object
		$params['input'] = $this->input->getData();

		// Get the engine
		$engineObject = Factory::getPostprocEngine($engine);

		if ($engineObject === false)
		{
			return;
		}

		$engineObject->oauthOpen($params);
	}

	/**
	 * Runs a custom API call for the selected post-processing engine
	 *
	 * @return  mixed
	 */
	public function dpeCustomAPICall()
	{
		$engine = $this->getState('engine');
		$method = $this->getState('method');
		$params = $this->getState('params', []);

		// Get the Input object
		$params['input'] = $this->input->getData();

		$engineObject = Factory::getPostprocEngine($engine);

		if ($engineObject === false)
		{
			return false;
		}

		return $engineObject->customAPICall($method, $params);
	}

	/**
	 * Test the connection to a remote FTP server using cURL transport
	 *
	 * @return  void
	 * @throws  RuntimeException
	 */
	private function testFtpCurl()
	{
		$options = [
			'host'        => $this->getState('host'),
			'port'        => $this->getState('port'),
			'username'    => $this->getState('user'),
			'password'    => $this->getState('pass'),
			'directory'   => $this->getState('initdir'),
			'usessl'      => $this->getState('usessl'),
			'passive'     => $this->getState('passive'),
			'passive_fix' => $this->getState('passive_mode_workaround'),
		];

		$sftpTransfer = new FtpCurl($options);

		$sftpTransfer->connect();
	}

	/**
	 * Test the connection to a remote SFTP server using cURL transport
	 *
	 * @return  void
	 * @throws  RuntimeException
	 */
	private function testSftpCurl()
	{
		$options = [
			'host'       => $this->getState('host'),
			'port'       => $this->getState('port'),
			'username'   => $this->getState('user'),
			'password'   => $this->getState('pass'),
			'directory'  => $this->getState('initdir'),
			'privateKey' => $this->getState('privkey'),
			'publicKey'  => $this->getState('pubkey'),
		];

		$sftpTransfer = new SftpCurl($options);

		$sftpTransfer->connect();
	}
}
