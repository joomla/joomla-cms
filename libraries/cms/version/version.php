<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Version
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Version information class for the Joomla CMS.
 *
 * @package     Joomla.Libraries
 * @subpackage  Version
 * @since       1.0
 */
final class JVersion
{
	/** @var  string  Product name. */
	public $PRODUCT = 'Joomla!';

	/** @var  string  Release version. */
	public $RELEASE = '3.2';

	/** @var  string  Maintenance version. */
	public $DEV_LEVEL = '0.alpha';

	/** @var  string  Development STATUS. */
	public $DEV_STATUS = 'Alpha';

	/** @var  string  Build number. */
	public $BUILD = '';

	/** @var  string  Code name. */
	public $CODENAME = 'Ember';

	/** @var  string  Release date. */
	public $RELDATE = '10-September-2013';

	/** @var  string  Release time. */
	public $RELTIME = '14:00';

	/** @var  string  Release timezone. */
	public $RELTZ = 'GMT';

	/** @var  string  Copyright Notice. */
	public $COPYRIGHT = 'Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.';

	/** @var  string  Link text. */
	public $URL = '<a href="http://www.joomla.org">Joomla!</a> is Free Software released under the GNU General Public License.';

	/**
	 * Compares two a "PHP standardized" version number against the current Joomla version.
	 *
	 * @param   string  $minimum  The minimum version of the Joomla which is compatible.
	 *
	 * @return  bool    True if the version is compatible.
	 *
	 * @see     http://www.php.net/version_compare
	 * @since   1.0
	 */
	public function isCompatible($minimum)
	{
		return version_compare(JVERSION, $minimum, 'ge');
	}

	/**
	 * Method to get the help file version.
	 *
	 * @return  string  Version suffix for help files.
	 *
	 * @since   1.0
	 */
	public function getHelpVersion()
	{
		return '.' . str_replace('.', '', $this->RELEASE);
	}

	/**
	 * Gets a "PHP standardized" version string for the current Joomla.
	 *
	 * @return  string  Version string.
	 *
	 * @since   1.5
	 */
	public function getShortVersion()
	{
		return $this->RELEASE . '.' . $this->DEV_LEVEL;
	}

	/**
	 * Gets a version string for the current Joomla with all release information.
	 *
	 * @return  string  Complete version string.
	 *
	 * @since   1.5
	 */
	public function getLongVersion()
	{
		return $this->PRODUCT . ' ' . $this->RELEASE . '.' . $this->DEV_LEVEL . ' '
				. $this->DEV_STATUS . ' [ ' . $this->CODENAME . ' ] ' . $this->RELDATE . ' '
				. $this->RELTIME . ' ' . $this->RELTZ;
	}

	/**
	 * Returns the user agent.
	 *
	 * @param   string  $component    Name of the component.
	 * @param   bool    $mask         Mask as Mozilla/5.0 or not.
	 * @param   bool    $add_version  Add version afterwards to component.
	 *
	 * @return  string  User Agent.
	 *
	 * @since   1.0
	 */
	public function getUserAgent($component = null, $mask = false, $add_version = true)
	{
		if ($component === null)
		{
			$component = 'Framework';
		}

		if ($add_version)
		{
			$component .= '/' . $this->RELEASE;
		}

		// If masked pretend to look like Mozilla 5.0 but still identify ourselves.
		if ($mask)
		{
			return 'Mozilla/5.0 ' . $this->PRODUCT . '/' . $this->RELEASE . '.' . $this->DEV_LEVEL . ($component ? ' ' . $component : '');
		}
		else
		{
			return $this->PRODUCT . '/' . $this->RELEASE . '.' . $this->DEV_LEVEL . ($component ? ' ' . $component : '');
		}
	}

	/**
	 * Gets a media tag which is used to append to Joomla core media files.
	 *
	 * This media tag is used to append to Joomla core media in order to trick browsers into
	 * reloading the CSS and JavaScript, because they think the files are renewed.
	 * The media tag is renewed after Joomla core update, install, discover_install and uninstallation.
	 *
	 * @return  string  The media tag.
	 *
	 * @since	3.1.5
	 */
	public function getMediaTag()
	{
		// Load the media tag and cache it for future use
		static $mediaTag = null;

		if ($mediaTag === null)
		{
			$db = JFactory::getDbo();
			$config = JFactory::getConfig();
			$debugEnabled = $config->get('debug', 0);

			// Get the parameters of the Joomla library
			$query = $db->getQuery(true)
				->select($db->quoteName('params'))
				->from($db->quoteName('#__extensions'))
				->where($db->quoteName('type').' = '.$db->quote('library'))
				->where($db->quoteName('element').' = '.$db->quote('joomla'));
			$db->setQuery($query);
			$rawparams = $db->loadResult();
			$params = new JRegistry();
			$params->loadString($rawparams, 'JSON');

			// Get the mediatag
			$mediaTag = $params->get('mediatag', '');

			// Refresh assets in debug mode or when $mediaTag is not set
			if ($debugEnabled || empty($mediaTag))
			{
				$date = new JDate();
				$mediaTag = md5($this->getLongVersion() . $config->get('secret') . $date->toUnix());

				$this->setMediaTag($mediaTag);
			}
		}

		return $mediaTag;
	}

	/**
	 * Sets the media tag which is used to append to Joomla core media files.
	 *
	 * @param	string	$mediaTag	The media tag.
	 *
	 * @return  JVersion instance of $this to allow chaining.
	 *
	 * @since	3.1.5
	 */
	public function setMediaTag($mediaTag)
	{
		$db = JFactory::getDbo();

		// Get the parameters of the Joomla library
		$query = $db->getQuery(true)
			->select($db->quoteName('params'))
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('type').' = '.$db->quote('library'))
			->where($db->quoteName('element').' = '.$db->quote('joomla'));
		$db->setQuery($query);
		$rawparams = $db->loadResult();
		$params = new JRegistry();
		$params->loadString($rawparams, 'JSON');

		// Set $mediaTag
		$params->set('mediatag', $mediaTag);

		// Store the updated $params
		$data = $params->toString('JSON');
		$query = $db->getQuery(true)
			->update($db->quoteName('#__extensions'))
			->set($db->quoteName('params') . ' = ' . $db->quote($data))
			->where($db->quoteName('type') . ' = ' . $db->quote('library'))
			->where($db->quoteName('element') . ' = ' . $db->quote('joomla'));
		$db->setQuery($query);
		$db->execute();

		return $this;
	}
}
