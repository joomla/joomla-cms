<?php
/**
* @version $Id: version.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Mambo
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * Version information
 * @package Mambo
 */
class mamboVersion {
	/** @var string Product */
	var $PRODUCT = 'Joomla!';
	/** @var int Main Release Level */
	var $RELEASE = '1.1';
	/** @var string Development Status */
	var $DEV_STATUS = 'Dev';
	/** @var int Sub Release Level */
	var $DEV_LEVEL = '0';
	/** @var string Codename */
	var $CODENAME = 'Phoenix';
	/** @var string Date */
	var $RELDATE = 'TBA';
	/** @var string Time */
	var $RELTIME = '00:00';
	/** @var string Timezone */
	var $RELTZ = 'GMT';
	/** @var string Copyright Text */
	var $COPYRIGHT = ' (C) 2005 Joomla!';
	/** @var string URL */
	var $URL = '<a href="http://www.joomla.org">Joomla!</a> is Free Software released under the GNU/GPL License.';

	/**
	 * @return string Long format version
	 */
	function getLongVersion() {
		return $this->PRODUCT .' '. $this->RELEASE .'.'. $this->DEV_LEVEL .' '
			. $this->DEV_STATUS
			.' [ '.$this->CODENAME .' ] '. $this->RELDATE .' '
			. $this->RELTIME .' '. $this->RELTZ;
	}

	/**
	 * @return string Short version format
	 */
	function getShortVersion() {
		return $this->RELEASE .'.'. $this->DEV_LEVEL;
	}

	/**
	 * @return string Version suffix for help files
	 */
	function getHelpVersion() {
		return substr( str_replace( '.', '', $this->RELEASE .'.'. $this->DEV_LEVEL ), 0 , 3 );
	}

}
$_VERSION = new mamboVersion();
?>