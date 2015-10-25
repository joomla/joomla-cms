<?php
/**
 * Community Builder (TM) form files plugin
 * @version $Id: $
 * @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

use CBLib\Language\CBTxt;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

JFormHelper::loadFieldClass( 'list' );

class JFormFieldCBplugins extends JFormFieldList {
	protected $type		=	'cbplugins';

	protected function getOptions() {
		if ( ( ! file_exists( JPATH_SITE . '/libraries/CBLib/CBLib/Core/CBLib.php' ) ) || ( ! file_exists( JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php' ) ) ) {
			return array();
		}

		/** @noinspection PhpIncludeInspection */
		include_once( JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php' );

		cbimport( 'language.front' );

		$db					=	JFactory::getDBO();

		$options			=	array();

		$query				=	'SELECT ' . $db->quoteName( 'element' ) . ' AS value'
							.	', ' . $db->quoteName( 'name' ) . ' AS text'
							.	"\n FROM " . $db->quoteName( '#__comprofiler_plugin' )
							.	"\n WHERE " . $db->quoteName( 'type' ) . " NOT IN ( " . $db->quote( 'templates' ) . ", " . $db->quote( 'language' ) . " )"
							.	"\n ORDER BY " . $db->quoteName( 'ordering' );
		$db->setQuery( $query );
		$plugins			=	$db->loadObjectList();

		if ( $plugins ) foreach ( $plugins as $plugin ) {
			$options[]		=	JHtml::_( 'select.option', $plugin->value, CBTxt::T( $plugin->text ) );
		}

		return $options;
	}
}
