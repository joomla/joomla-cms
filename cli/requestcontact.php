<?php
/**
 * @package    Joomla.Cli
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// Initialize Joomla framework
const _JEXEC = 1;

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

// Load the configuration
require_once JPATH_CONFIGURATION . '/configuration.php';

// Fool the system into thinking we are running as JSite.
$_SERVER['HTTP_HOST'] = 'domain.com';
$app = JFactory::getApplication('site');
$app->loadLanguage();
$app->input = new JInputCli;

/**
 * Cron job to send a contact request.
 *
 * @since  __DEPLOY__
 */
class RequestContact extends JApplicationCli
{
	/**
	 * Flag to indicate if contact failed.
	 */
	private $contactSuccessful = true;

	/**
	 * Entry point for the script
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY__
	 */
	public function doExecute()
	{
		// Set the context to be the contacts component.
		define('JPATH_COMPONENT', JPATH_ROOT . '/components/com_contact');
		JLoader::registerPrefix('Contact', JPATH_COMPONENT);
		JModelLegacy::addIncludePath(JPATH_COMPONENT . '/models');
		
		// Load the component language file, falling back to the default language.
		$lang = JFactory::getApplication()->getLanguage();
		$lang->load('com_contact', JPATH_SITE, null, false, false)
			|| $lang->load('com_contact', JPATH_SITE, null, true);

		// Get the command bus.
		$service = new ContactServiceContact;

		// Execute the command.
		try
		{
			$contactId = new JValueContactid($this->input->getInt('id'));
			$data = array(
				'contact_name'		=> $this->input->getString('name'),
				'contact_email'		=> $this->input->getString('email'),
				'contact_subject'	=> $this->input->getString('subject'),
				'contact_message'	=> $this->input->getString('message'),
				'contact_email_copy'=> $this->input->getInt('email_copy', 0),
			);
			$_SERVER['HTTP_HOST'] = $this->input->getString('website', 'domain.com/');
	
			// Execute the command to process the contact request.
			$service->handle((new ContactCommandRequestcontact($contactId, $data)));
		}
		catch (Exception $e)
		{
			$this->out(($e->getMessage()));
			$this->help();
			$this->contactSuccessful = false;
		}

		// If the contact request attempt failed, simply return.
		if ($this->contactSuccessful)
		{
			$this->out(JText::_('COM_CONTACT_EMAIL_THANKS'));
		}
	}

	/**
	 * Show help text.
	 * 
	 * @return  void
	 */
	private function help()
	{
		// @TODO Language strings.
		$this->out('Usage:-');
		$this->out('  php requestcontact.php [arguments]');
		$this->out('  where [arguments] are:-');
		$this->out('    id=[contact id]');
		$this->out('    website=[website URL for reference in contact emails]');
		$this->out('    name=[name of person requesting contact]');
		$this->out('    email=[email address of person requesting contact]');
		$this->out('    subject=[subject line of contact request]');
		$this->out('    message=[message of contact request]');
		$this->out('    email_copy=[optional "1" to send copy of request to person requesting contact]');
	}
}

JApplicationCli::getInstance('RequestContact')->execute();
