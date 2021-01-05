<?php
/**
 * @package     Joomla.Installation
 * @subpackage  Model
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installation\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Base Model for the installation model classes
 *
 * @since  4.0.0
 */
class BaseInstallationModel extends BaseDatabaseModel
{
	/**
	 * Constructor
	 *
	 * @param   array                     $config   An array of configuration options (name, state, dbo, table_path, ignore_request).
	 * @param   MVCFactoryInterface|null  $factory  The factory.
	 *
	 * @since   3.0
	 * @throws  \Exception
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null)
	{
		// @TODO remove me when the base model is db free
		$config['dbo'] = null;

		parent::__construct($config, $factory);
	}

	/**
	 * Get the current setup options from the session.
	 *
	 * @return  array  An array of options from the session.
	 *
	 * @since   3.1
	 */
	public function getOptions()
	{
		if (!empty(Factory::getSession()->get('setup.options', array())))
		{
			return Factory::getSession()->get('setup.options', array());
		}
	}

	/**
	 * Store the current setup options in the session.
	 *
	 * @param   array  $options  The installation options.
	 *
	 * @return  array  An array of options from the session.
	 *
	 * @since   3.1
	 */
	public function storeOptions($options)
	{
		// Get the current setup options from the session.
		$old = (array) $this->getOptions();

		// Ensure that we have language
		if (!isset($options['language']) || empty($options['language']))
		{
			$options['language'] = Factory::getLanguage()->getTag();
		}

		// Store passwords as a separate key that is not used in the forms
		foreach (array('admin_password', 'db_pass', 'ftp_pass') as $passwordField)
		{
			if (isset($options[$passwordField]))
			{
				$plainTextKey = $passwordField . '_plain';

				$options[$plainTextKey] = $options[$passwordField];

				unset($options[$passwordField]);
			}
		}

		// Get the session
		$session = Factory::getSession();
		$options['helpurl'] = $session->get('setup.helpurl', null);

		// Merge the new setup options into the current ones and store in the session.
		$options = array_merge($old, (array) $options);
		$session->set('setup.options', $options);

		return $options;
	}
}
