#!/usr/bin/env php
<?php
/**
 * @package    Joomla.Platform
 *
 * @copyright  (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 */

// @deprecated  4.0  Deprecated without replacement

// We are a valid entry point.
define('_JEXEC', 1);

// Load system defines
if (file_exists(dirname(__DIR__) . '/defines.php'))
{
	require_once dirname(__DIR__) . '/defines.php';
}

if (!defined('_JDEFINES'))
{
	define('JPATH_BASE', dirname(__DIR__));
	require_once JPATH_BASE . '/includes/defines.php';
}

// Get the framework.
require_once JPATH_LIBRARIES . '/import.legacy.php';

// Bootstrap the CMS libraries.
require_once JPATH_LIBRARIES . '/cms.php';

// Import the configuration.
require_once JPATH_CONFIGURATION . '/configuration.php';

// System configuration.
$config = new JConfig;

// Configure error reporting to maximum for CLI output.
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * Keychain Manager.
 *
 * @since  3.1.4
 */
class KeychainManager extends JApplicationCli
{
	/**
	 * @var    boolean  A flag if the keychain has been updated to trigger saving the keychain
	 * @since  3.1.4
	 */
	protected $updated = false;

	/**
	 * @var    JKeychain  The keychain object being manipulated.
	 * @since  3.1.4
	 */
	protected $keychain = null;

	/**
	 * Execute the application
	 *
	 * @return  void
	 *
	 * @since   3.1.4
	 */
	public function execute( )
	{
		if (!count($this->input->args))
		{
			// Check if they passed --help in otherwise display short usage summary
			if ($this->input->get('help', false) === false)
			{
				$this->out("usage: {$this->input->executable} [options] [command] [<args>]");
				exit(1);
			}
			else
			{
				$this->displayHelp();
				exit(0);
			}
		}

		// For all tasks but help and init we use the keychain
		if (!in_array($this->input->args[0], array('help', 'init')))
		{
			$this->loadKeychain();
		}

		switch ($this->input->args[0])
		{
			case 'init':
				$this->initPassphraseFile();
				break;
			case 'list':
				$this->listEntries();
				break;
			case 'create':
				$this->create();
				break;
			case 'change':
				$this->change();
				break;
			case 'delete':
				$this->delete();
				break;
			case 'read':
				$this->read();
				break;
			case 'help':
				$this->displayHelp();
				break;
			default:
				$this->out('Invalid command.');
				break;
		}

		if ($this->updated)
		{
			$this->saveKeychain();
		}

		exit(0);
	}

	/**
	 * Load the keychain from a file.
	 *
	 * @return  void
	 *
	 * @since   3.1.4
	 */
	protected function loadKeychain()
	{
		$keychain = $this->input->get('keychain', '', 'raw');
		$publicKeyFile = $this->input->get('public-key', '', 'raw');
		$passphraseFile = $this->input->get('passphrase', '', 'raw');

		$this->keychain = new JKeychain;

		if (file_exists($keychain))
		{
			if (file_exists($publicKeyFile))
			{
				$this->keychain->loadKeychain($keychain, $passphraseFile, $publicKeyFile);
			}
			else
			{
				$this->out('Public key not specified or missing!');
				exit(1);
			}
		}
	}

	/**
	 * Save this keychain to a file.
	 *
	 * @return  void
	 *
	 * @since   3.1.4
	 */
	protected function saveKeychain()
	{
		$keychain = $this->input->get('keychain', '', 'raw');
		$publicKeyFile = $this->input->get('public-key', '', 'raw');
		$passphraseFile = $this->input->get('passphrase', '', 'raw');

		if (!file_exists($publicKeyFile))
		{
			$this->out("Public key file specified doesn't exist: $publicKeyFile");
			exit(1);
		}

		$this->keychain->saveKeychain($keychain, $passphraseFile, $publicKeyFile);
	}

	/**
	 * Initialise a new passphrase file.
	 *
	 * @return  void
	 *
	 * @since   3.1.4
	 */
	protected function initPassphraseFile()
	{
		$keychain = new JKeychain;

		$passphraseFile = $this->input->get('passphrase', '', 'raw');
		$privateKeyFile = $this->input->get('private-key', '', 'raw');

		if (!strlen($passphraseFile))
		{
			$this->out('A passphrase file must be specified with --passphrase');
			exit(1);
		}

		if (!file_exists($privateKeyFile))
		{
			$this->out("protected key file specified doesn't exist: $privateKeyFile");
			exit(1);
		}

		$this->out('Please enter the new passphrase:');
		$passphrase = $this->in();

		$this->out('Please enter the passphrase for the protected key:');
		$privateKeyPassphrase = $this->in();

		$keychain->createPassphraseFile($passphrase, $passphraseFile, $privateKeyFile, $privateKeyPassphrase);
	}

	/**
	 * Create a new entry
	 *
	 * @return  void
	 *
	 * @since   3.1.4
	 */
	protected function create()
	{
		if (count($this->input->args) != 3)
		{
			$this->out("usage: {$this->input->executable} [options] create entry_name entry_value");
			exit(1);
		}

		if ($this->keychain->exists($this->input->args[1]))
		{
			$this->out('error: entry already exists. To change this entry, use "change"');
			exit(1);
		}

		$this->change();
	}

	/**
	 * Change an existing entry to a new value or create an entry if missing.
	 *
	 * @return  void
	 *
	 * @since   3.1.4
	 */
	protected function change()
	{
		if (count($this->input->args) != 3)
		{
			$this->out("usage: {$this->input->executable} [options] change entry_name entry_value");
			exit(1);
		}

		$this->updated = true;
		$this->keychain->setValue($this->input->args[1], $this->input->args[2]);
	}

	/**
	 * Read an entry from the keychain
	 *
	 * @return  void
	 *
	 * @since   3.1.4
	 */
	protected function read()
	{
		if (count($this->input->args) != 2)
		{
			$this->out("usage: {$this->input->executable} [options] read entry_name");
			exit(1);
		}

		$key = $this->input->args[1];
		$this->out($key . ': ' . $this->dumpVar($this->keychain->get($key)));
	}

	/**
	 * Get the string from var_dump
	 *
	 * @param   mixed  $var  The variable you want to have dumped.
	 *
	 * @return  string  The result of var_dump
	 *
	 * @since   3.1.4
	 */
	private function dumpVar($var)
	{
		ob_start();
		var_dump($var);
		$result = trim(ob_get_contents());
		ob_end_clean();

		return $result;
	}

	/**
	 * Delete an entry from the keychain
	 *
	 * @return  void
	 *
	 * @since   3.1.4
	 */
	protected function delete()
	{
		if (count($this->input->args) != 2)
		{
			$this->out("usage: {$this->input->executable} [options] delete entry_name");
			exit(1);
		}

		$this->updated = true;
		$this->keychain->deleteValue($this->input->args[1]);
	}

	/**
	 * List entries in the keychain
	 *
	 * @return  void
	 *
	 * @since   3.1.4
	 */
	protected function listEntries()
	{
		foreach ($this->keychain->toArray() as $key => $value)
		{
			$line = $key;

			if ($this->input->get('print-values'))
			{
				$line .= ': ' . $this->dumpVar($value);
			}

			$this->out($line);
		}
	}

	/**
	 * Display the help information
	 *
	 * @return  void
	 *
	 * @since   3.1.4
	 */
	protected function displayHelp()
	{
/*
COMMANDS

 - list
 - create entry_name entry_value
 - change entry_name entry_value
 - delete entry_name
 - read   entry_name
*/

		$help = <<<HELP
Keychain Management Utility

usage: {$this->input->executable} [--keychain=/path/to/keychain]
	[--passphrase=/path/to/passphrase.dat] [--public-key=/path/to/public.pem]
	[command] [<args>]

OPTIONS

  --keychain=/path/to/keychain
    Path to a keychain file to manipulate.

  --passphrase=/path/to/passphrase.dat
    Path to a passphrase file containing the encryption/decryption key.

  --public-key=/path/to/public.pem
    Path to a public key file to decrypt the passphrase file.


COMMANDS

  list:
    Usage: list [--print-values]
    Lists all entries in the keychain. Optionally pass --print-values to print the values as well.

  create:
    Usage: create entry_name entry_value
    Creates a new entry in the keychain called "entry_name" with the plaintext value "entry_value".
    NOTE: This is an alias for change.

  change:
    Usage: change entry_name entry_value
    Updates the keychain entry called "entry_name" with the value "entry_value".

  delete:
    Usage: delete entry_name
    Removes an entry called "entry_name" from the keychain.

  read:
    Usage: read entry_name
    Outputs the plaintext value of "entry_name" from the keychain.

  init:
    Usage: init
    Creates a new passphrase file and prompts for a new passphrase.

HELP;
		$this->out($help);
	}
}

try
{
	JApplicationCli::getInstance('KeychainManager')->execute();
}
catch (Exception $e)
{
	echo $e->getMessage() . "\n";
	exit(1);
}
