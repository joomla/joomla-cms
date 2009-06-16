<?php
/**
 * @version		$Id$
 * @package	Joomla.Framework
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Version information
 *
 * @package	Joomla.Framework
 * @since	1.0
 */
class JVersion
{
	/** @var string Product */
	var $PRODUCT 	= 'Joomla!';
	/** @var int Main Release Level */
	var $RELEASE 	= '1.6';
	/** @var string Development Status */
	var $DEV_STATUS = 'Dev';
	/** @var int Sub Release Level */
	var $DEV_LEVEL 	= '0';
	/** @var int build Number */
	var $BUILD	= '';
	/** @var string Codename */
	var $CODENAME 	= 'Wohmamni';
	/** @var string Date */
	var $RELDATE 	= '27-March-2009';
	/** @var string Time */
	var $RELTIME 	= '23:00';
	/** @var string Timezone */
	var $RELTZ 	= 'GMT';
	/** @var string Copyright Text */
	var $COPYRIGHT 	= 'Copyright (C) 2005 - 2009 Open Source Matters. All rights reserved.';
	/** @var string URL */
	var $URL 	= '<a href="http://www.joomla.org">Joomla!</a> is Free Software released under the GNU General Public License.';

	/**
	 *
	 *
	 * @return string Long format version
	 */
	function getLongVersion()
	{
		return $this->PRODUCT .' '. $this->RELEASE .'.'. $this->DEV_LEVEL .' '
			. $this->DEV_STATUS
			.' [ '.$this->CODENAME .' ] '. $this->RELDATE .' '
			. $this->RELTIME .' '. $this->RELTZ;
	}

	/**
	 *
	 *
	 * @return string Short version format
	 */
	function getShortVersion() {
		return $this->RELEASE .'.'. $this->DEV_LEVEL;
	}

	/**
	 *
	 *
	 * @return string Version suffix for help files
	 */
	function getHelpVersion()
	{
		if ($this->RELEASE > '1.0') {
			return '.' . str_replace('.', '', $this->RELEASE);
		} else {
			return '';
		}
	}

	/**
	 * Compares two "A PHP standardized" version number against the current Joomla! version
	 *
	 * @return boolean
	 * @see http://www.php.net/version_compare
	 */
	function isCompatible ($minimum) {
		return (version_compare(JVERSION, $minimum, 'eq') == 1);
	}

	/**
	 * Returns the user agent
	 * @param string name of the component
	 * @param bool Mask as Mozilla/5.0 or not
	 * @param bool Add version afterwards to component
	 * @return string User Agent
	 */
	function getUserAgent($component=NULL, $mask=false, $add_version=true) {
		if($component === NULL) $component = 'Framework';
		if($add_version) $component .= '/'.$this->RELEASE;
		// if masked pretend to look like Mozilla 5.0 but still identify ourselves
		if($mask) return 'Mozilla/5.0 '. $this->PRODUCT .'/'. $this->RELEASE . '.'.$this->DEV_LEVEL . ($component ? ' '. $component : '');
		else return $this->PRODUCT .'/'. $this->RELEASE . '.'.$this->DEV_LEVEL . ($component ? ' '. $component : '');
	}
}
