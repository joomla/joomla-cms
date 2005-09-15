<?php
/**
* @version $Id: components.builder.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Components
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
 * @package Joomla
 * @subpackage Components
 */
class componentCreator {
	/** @var object Internal template object */
	var $_tmpl = null;
	/** @var int Client id */
	var $_client_id = null;
	/** @var string Error */
	var $_error = null;

	/**
	 * Constructor
	 */
	function componentCreator( $client_id ) {
		$this->_tmpl =& $this->createTemplate( array() );
		$this->_tmpl->readTemplatesFromInput( 'php.html' );
		$this->_tmpl->readTemplatesFromInput( 'html.html' );
		$this->_tmpl->readTemplatesFromInput( 'xml.html' );

		$this->_client_id = $client_id;
	}

	/**
	 * Error getter/setter
	 * @param string The error
	 * @return string The error
	 */
	function error( $val = null ) {
		if (!is_null( $val )) {
			$this->_error = $val;
		}
		return $this->_error;
	}

	/**
	 * Static method to create the component object
	 * @param int The client identifier
	 * @param array An array of other standard files to include
	 * @return patTemplate
	 */
	function &createTemplate( $files=null ) {
		$tmpl =& mosFactory::getPatTemplate( $files );
		$tmpl->setRoot( dirname( __FILE__ ) . '/tmpl/create' );
		$tmpl->setNamespace( 'pat' );

		return $tmpl;
	}

	/**
	 * @param array Meta information
	 * @param array Options
	 */
	function make( $baseName, &$meta, &$options ) {
		global $mainframe, $_LANG;

		$meta['name'] = $baseName;
		$meta['basename'] = $baseName;
		$meta['classname'] = ucfirst( $baseName );

		$basePath = $mainframe->getBasePath( $this->_client_id ) . 'components/com_' . $baseName;
		$basePath = mosFS::getNativePath( $basePath );
		$baseTmplPath = mosFS::getNativePath( $basePath . 'tmpl' );

		// create the base path
		if (!mosFS::autocreatePath( $basePath )) {
			$this->error( $_LANG->_( 'Failed to create base folder' ) );
			return false;
		}
		// create the tmpl path
		if (!mosFS::autocreatePath( $baseTmplPath )) {
			$this->error( $_LANG->_( 'Failed to create tmpl folder' ) );
			return false;
		}

		$this->_tmpl->addGlobalVars( $meta );
		$this->_tmpl->addVars( 'options', $options );

		$mainPrefix = $this->_client_id == 1 ? 'admin.' : '';

	// ---- PHP FILES ----

		// blank index file
		$this->_make( 'htmlIndex', $basePath . 'index.html' );

		// main php file
		$this->_make( 'phpMain', $basePath . $mainPrefix . $baseName . '.php' );

		// main html.php file
		$this->_make( 'phpMainHtml', $basePath . $mainPrefix . $baseName . '.html.php' );

		// toolbar php file
		$this->_make( 'phpToolbar', $basePath . 'toolbar.' . $baseName . '.php' );

		// class php file
		$this->_make( 'phpClass', $basePath . $baseName . '.class.php' );

	// ---- PHP HTML TEMPLATE FILES ----

		// blank index file
		$this->_make( 'htmlIndex', $baseTmplPath . 'index.html' );

		// tree template
		$this->_makeHtml( 'html-tree', $baseTmplPath . 'tree.html' );

		// related links template
		$this->_makeHtml( 'html-related', $baseTmplPath . 'relatedLinks.html' );

		// view form template
		if (@$options['hasview']) {
			$this->_makeHtml( 'html-view', $baseTmplPath . 'view.html' );
		}

		// edit form template
		if (@$options['hasedit']) {
			$this->_makeHtml( 'html-edit', $baseTmplPath . 'edit.html' );
		}

		// edit form template
		if (@$options['hasedit']) {
			$this->_makeHtml( 'html-edit', $baseTmplPath . 'edit.html' );
		}

		// about template
		if (@$options['hasabout']) {
			$this->_makeHtml( 'html-about', $baseTmplPath . 'about.html' );
		}

	// ---- HELP FILES ----

		if (@$options['hashelp']) {
			$baseHelpPath = mosFS::getNativePath( $basePath . 'help' );

			// create the help path
			if (!mosFS::autocreatePath( $baseHelpPath )) {
				$this->error( $_LANG->_( 'Failed to create help folder' ) );
				return false;
			}

			// blank index file
			$this->_make( 'htmlIndex', $baseHelpPath . 'index.html' );

			// blank index file
			$this->_makeHtml( 'css-help', $baseHelpPath . 'help.css' );

			if (@$options['hasview']) {
				$this->_makeHtml( 'html-help', $baseHelpPath . 'screen.' . $baseName . '.view.html' );
			}
			if (@$options['hasedit']) {
				$this->_makeHtml( 'html-help', $baseHelpPath . 'screen.' . $baseName . '.edit.html' );
			}
		}

	// ---- XML FILES ----

		// mosinstall
		$this->_make( 'xmlMain', $basePath . $baseName . '.xml' );

	// ---- DATABASE ----
		$query = "
			CREATE TABLE `mos_$baseName` (
			  `id` int(10) unsigned NOT NULL auto_increment,
			  `title` varchar(255) NOT NULL default '',
			  `created_date` datetime NOT NULL default '0000-00-00 00:00:00',
			  `author_id` int(10) unsigned NOT NULL default '0',
			  `modified_id` int(10) unsigned NOT NULL default '0',
			  `checked_out` int(10) unsigned NOT NULL default '0',
			  `checked_out_time` datetime NOT NULL default '0000-00-00 00:00:00',
			  `published` int(2) NOT NULL default '0',
			  `ordering` int(11) NOT NULL default '0',
			  PRIMARY KEY  (`id`)
			) TYPE=MyISAM";

	// ---- Menu Item ----

		return true;
	}

	/**
	 * @param string The type of file
	 * @param string The path of the file to save the output
	 */
	function _make( $type, $file, $param1=null ) {
		$buffer = $this->$type( $this->_tmpl, $param1 );
		mosFS::write( $file, $buffer );
	}

	/**
	 * @param string The type of file
	 * @param string The path of the file to save the output
	 */
	function _makeHtml( $type, $file ) {
		$buffer = $this->_tmpl->getParsedTemplate( 'html-start' );
		$buffer .= $this->_tmpl->getParsedTemplate( $type );
		mosFS::write( $file, $buffer );
	}

	/**
	 * Main PHP File
	 */
	function phpMain( &$tmpl ) {
		$buffer = $tmpl->getParsedTemplate( 'php-start' );
		$buffer .= $tmpl->getParsedTemplate( 'php-tasker' );
		$buffer .= $tmpl->getParsedTemplate( 'php-end' );

		return $buffer;
	}

	/**
	 * Main PHP HTML File
	 */
	function phpMainHtml( &$tmpl ) {
		$buffer = $tmpl->getParsedTemplate( 'php-start' );
		$buffer .= $tmpl->getParsedTemplate( 'php-screens' );
		$buffer .= $tmpl->getParsedTemplate( 'php-end' );

		return $buffer;
	}

	/**
	 * Main PHP HTML File
	 */
	function phpToolbar( &$tmpl ) {
		$buffer = $tmpl->getParsedTemplate( 'php-start' );
		$buffer .= $tmpl->getParsedTemplate( 'php-toolbar' );
		$buffer .= $tmpl->getParsedTemplate( 'php-end' );

		return $buffer;
	}

	/**
	 * Main PHP HTML File
	 */
	function phpClass( &$tmpl ) {
		$buffer = $tmpl->getParsedTemplate( 'php-start' );
		$buffer .= $tmpl->getParsedTemplate( 'php-class' );
		$buffer .= $tmpl->getParsedTemplate( 'php-end' );

		return $buffer;
	}

	/**
	 * HTML Generic
	 */
	function html( &$tmpl, $name ) {
		$buffer = $tmpl->getParsedTemplate( 'html-start' );
		$buffer .= $tmpl->getParsedTemplate( $name );

		return $buffer;
	}

	/**
	 * Blank index file
	 */
	function htmlIndex() {
		return '<html><body></body></html>';
	}

	/**
	 * XML Main
	 */
	function xmlMain( &$tmpl ) {
		$buffer = $tmpl->getParsedTemplate( 'xml-main' );

		return $buffer;
	}


}
?>