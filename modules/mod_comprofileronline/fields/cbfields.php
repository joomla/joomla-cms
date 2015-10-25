<?php
/**
 * Community Builder (TM)
 * @version $Id: $
 * @package CommunityBuilder
 * @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

use CB\Database\Table\FieldTable;
use CBLib\Language\CBTxt;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

JFormHelper::loadFieldClass( 'list' );

class JFormFieldcbfields extends JFormFieldList {
	protected $type = 'cbfields';

	protected function getOptions() {
		global $_CB_database;

		if ( ( ! file_exists( JPATH_SITE . '/libraries/CBLib/CBLib/Core/CBLib.php' ) ) || ( ! file_exists( JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php' ) ) ) {
			return array();
		}

		/** @noinspection PhpIncludeInspection */
		include_once( JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php' );

		cbimport( 'cb.html' );
		cbimport( 'language.front' );

		$query					=	'SELECT f.*'
								. 	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_fields' ) . " AS f"
								.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__comprofiler_tabs' ) . " AS t"
								.	' ON t.' . $_CB_database->NameQuote( 'tabid' ) . ' = f.' . $_CB_database->NameQuote( 'tabid' )
								.	"\n WHERE f." . $_CB_database->NameQuote( 'published' ) . " = 1"
								.	"\n AND f." . $_CB_database->NameQuote( 'name' ) . " != " . $_CB_database->Quote( 'NA' )
								.	"\n ORDER BY t." . $_CB_database->NameQuote( 'ordering' ) . ", f." . $_CB_database->NameQuote( 'ordering' );
		$_CB_database->setQuery( $query );
		$fields					=	$_CB_database->loadObjectList( null, '\CB\Database\Table\FieldTable', array( &$_CB_database ) );

		$options				=	array();

		/** @var FieldTable[] $fields */
		if ( $fields ) foreach ( $fields as $field ) {
			if ( count( $field->getTableColumns() ) ) {
				$title			=	CBTxt::T( $field->title );

				$options[]		=	JHtml::_( 'select.option', $field->name, ( $title ? $title . ' (' . $field->name . ')' : $field->name ) );
			}
		}

		return $options;
	}
}
