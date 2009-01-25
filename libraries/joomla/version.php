<?php
/**
 * @version		$Id$
 * @package	Joomla.Framework
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
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
	public $PRODUCT 	= 'Joomla!';
	/** @var int Main Release Level */
	public $RELEASE 	= '1.6';
	/** @var string Development Status */
	public $DEV_STATUS = 'Development/Alpha';
	/** @var int Sub Release Level */
	public $DEV_LEVEL 	= '0';
	/** @var int build Number */
	public $BUILD	= '';
	/** @var string Codename */
	public $CODENAME 	= 'Sparrow';
	/** @var string Date */
	public $RELDATE 	= '';
	/** @var string Time */
	public $RELTIME 	= '';
	/** @var string Timezone */
	public $RELTZ 		= '';
	/** @var string Copyright Text */
	public $COPYRIGHT 	= 'Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.';
	/** @var string URL */
	public $URL 	= '<a href="http://www.joomla.org">Joomla!</a> is Free Software released under the GNU General Public License.';

	/**
	 *
	 *
	 * @return string Long format version
	 */
	public function getLongVersion()
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
	public function getShortVersion() {
		return $this->RELEASE .'.'. $this->DEV_LEVEL;
	}

	/**
	 *
	 *
	 * @return string Version suffix for help files
	 */
	public function getHelpVersion()
	{
		if ($this->RELEASE > '1.0') {
			return '.' . str_replace( '.', '', $this->RELEASE );
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
	public function isCompatible ( $minimum ) {
		return (version_compare( JVERSION, $minimum, 'eq' ) == 1);
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
