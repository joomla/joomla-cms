<?php
/**
 * @version $Id$
 * @package	Joomla.Framework
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

/**
 * Version information
 *
 * @package	Joomla.Framework
 * @since	1.0
 */
class JVersion {
	/** @var string Product */
	var $PRODUCT 	= 'Joomla!';
	/** @var int Main Release Level */
	var $RELEASE 	= '1.1';
	/** @var string Development Status */
	var $DEV_STATUS = 'Alpha 2';
	/** @var int Sub Release Level */
	var $DEV_LEVEL 	= '0';
	/** @var int build Number */
	var $BUILD	 	= '$Revision: 224_ $';
	/** @var string Codename */
	var $CODENAME 	= 'Nightlight';
	/** @var string Date */
	var $RELDATE 	= '06-Feb-2006';
	/** @var string Time */
	var $RELTIME 	= '**:**';
	/** @var string Timezone */
	var $RELTZ 		= 'UTC';
	/** @var string Copyright Text */
	var $COPYRIGHT 	= 'Copyright (C) 2005 Open Source Matters. All rights reserved.';
	/** @var string URL */
	var $URL 		= '<a href="http://www.joomla.org">Joomla!</a> is Free Software released under the GNU/GPL License.';

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
		if ($this->RELEASE > '1.0') {
			return '.' . str_replace( '.', '', $this->RELEASE );
		} else {
			return '';
		}
	}
}
?>
