<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * Version information.
 *
 * @package	Joomla.Framework
 * @since	1.0
 */
class JVersion
{
	/** @public string Product */
	public $PRODUCT	= 'Joomla!';
	/** @public int Main Release Level */
	public $RELEASE	= '1.6';
	/** @public string Development Status */
	public $DEV_STATUS	= 'Beta15';
	/** @public int Sub Release Level */
	public $DEV_LEVEL	= '0';
	/** @public int build Number */
	public $BUILD		= '';
	/** @public string Codename */
	public $CODENAME	= 'Onward';
	/** @public string Date */
	public $RELDATE	= '29-Nov-2010';
	/** @public string Time */
	public $RELTIME	= '23:00';
	/** @public string Timezone */
	public $RELTZ		= 'GMT';
	/** @public string Copyright Text */
	public $COPYRIGHT	= 'Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.';
	/** @public string URL */
	public $URL		= '<a href="http://www.joomla.org">Joomla!</a> is Free Software released under the GNU General Public License.';

	/**
	 * Method to get the long version information.
	 *
	 * @return	string	Long format version.
	 */
	public function getLongVersion()
	{
		return $this->PRODUCT .' '. $this->RELEASE .'.'. $this->DEV_LEVEL .' '
			. $this->DEV_STATUS
			.' [ '.$this->CODENAME .' ] '. $this->RELDATE .' '
			. $this->RELTIME .' '. $this->RELTZ;
	}

	/**
	 * Method to get the short version information.
	 *
	 * @return	string	Short version format.
	 */
	public function getShortVersion() {
		return $this->RELEASE .'.'. $this->DEV_LEVEL;
	}

	/**
	 * Method to get the help file version.
	 *
	 * @return	string	Version suffix for help files.
	 */
	public function getHelpVersion()
	{
		if ($this->RELEASE > '1.0') {
			return '.' . str_replace('.', '', $this->RELEASE);
		} else {
			return '';
		}
	}

	/**
	 * Compares two "A PHP standardized" version number against the current Joomla! version.
	 *
	 * @return	boolean
	 * @see		http://www.php.net/version_compare
	 */
	public function isCompatible ($minimum) {
		return (version_compare(JVERSION, $minimum, 'eq') == 1);
	}

	/**
	 * Returns the user agent.
	 *
	 * @param	string	Name of the component.
	 * @param	bool	Mask as Mozilla/5.0 or not.
	 * @param	bool	Add version afterwards to component.
	 * @return	string	User Agent.
	 */
	public function getUserAgent($component = null, $mask = false, $add_version = true)
	{
		if ($component === null) {
			$component = 'Framework';
		}

		if ($add_version) {
			$component .= '/'.$this->RELEASE;
		}

		// If masked pretend to look like Mozilla 5.0 but still identify ourselves.
		if ($mask) {
			return 'Mozilla/5.0 '. $this->PRODUCT .'/'. $this->RELEASE . '.'.$this->DEV_LEVEL . ($component ? ' '. $component : '');
		}
		else {
			return $this->PRODUCT .'/'. $this->RELEASE . '.'.$this->DEV_LEVEL . ($component ? ' '. $component : '');
		}
	}
}
