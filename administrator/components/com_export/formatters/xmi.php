<?php
/**
 * @version $Id: xmi.php 137 2005-09-12 10:21:17Z eddieajau $
 * @package Joomla
 * @subpackage Export
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

$exportFormatters['xmi'] = 'xmiFormatter';

class xmiFormatter {
	function options( &$tmpl ) {
		$tmpl->readTemplatesFromInput( '../formatters/xmi.html' );
		return $tmpl->getParsedTemplate( 'xmi-formatter-options' );
	}

	/**
	 * Creates an XMI document that can be imported into Visual Paradigm
	 */
	function export( &$tables, &$table_fields, &$table_creates, &$options ) {
		return '<?xml version="1.0"?>' . "\n" . $this->_createXMI( $table_fields );
	}

	function _createXMI( &$table_fields ) {
		global $_VERSION;

		mosFS::load( '@domit' );

		$xmlDoc = new DOMIT_Lite_Document();

	// root element
		$elem = new DOMIT_Lite_Element( 'XMI' );
		$elem->setAttribute( 'xmi.version', '1.2' );
		$elem->setAttribute( 'xmlns:UML', 'org.omg/UML/1.4' );

		$xmlDoc->setDocumentElement( $elem );

		$root =& $xmlDoc->documentElement;

	// XMI Header

		$expo =& $xmlDoc->createElement( 'XMI.exporter' );
	    $expo->setText( 'Joomla! Open Source CMS ' . $_VERSION->RELEASE . '.' . $_VERSION->DEV_LEVEL . ' ' . $_VERSION->DEV_STATUS );

		$docu =& $xmlDoc->createElement( 'XMI.documentation' );
		$docu->appendChild( $expo );

		$meta =& $xmlDoc->createElement( 'XMI.metamodel' );
		$meta->setAttribute( 'xmi.name', 'UML' );
		$meta->setAttribute( 'xmi.version', '1.4' );

		$head =& $xmlDoc->createElement( 'XMI.header' );
		$head->appendChild( $docu );
		$head->appendChild( $meta );

		$root->appendChild( $head );

	// XMI Content

		$cont =& $xmlDoc->createElement( 'XMI.content' );

		$model =& $xmlDoc->createElement( 'UML:Model' );
		$model->setAttribute( 'xmi.id', 'M.1' );
		$model->setAttribute( 'name', 'Mambo' );
		$model->setAttribute( 'visibility', 'public' );
		$model->setAttribute( 'isSpecification', 'false' );
		$model->setAttribute( 'ownerScope', 'instance' );
		$model->setAttribute( 'isRoot', 'false' );
		$model->setAttribute( 'isLeaf', 'false' );
		$model->setAttribute( 'isAbstract', 'false' );

		$namesp =& $xmlDoc->createElement( 'UML:Namespace.ownedElement' );

		$classCount = 1;
		$attribCount = 1;
		foreach ($table_fields as $table=>$fields) {
			$class =& $xmlDoc->createElement( 'UML:Class' );
			$class->setAttribute( 'name', $table );
			$class->setAttribute( 'xmi.id', 'C.' . $classCount );
			$class->setAttribute( 'visibility', 'public' );
			$class->setAttribute( 'isSpecification', 'false' );
			$class->setAttribute( 'namespace', 'M.1' );
			$class->setAttribute( 'isRoot', 'true' );
			$class->setAttribute( 'isLeaf', 'true' );
			$class->setAttribute( 'isAbstract', 'false' );
			$class->setAttribute( 'isActive', 'true' );

			$classif =& $xmlDoc->createElement( 'UML:Classifier.feature' );

			foreach ($fields as $name=>$type) {
				$attrib =& $xmlDoc->createElement( 'UML:Attribute' );
				$attrib->setAttribute( 'xmi.id', 'A.' . $attribCount );
				$attrib->setAttribute( 'name', $name );
				$attrib->setAttribute( 'type', $type );
				$attrib->setAttribute( 'visibility', 'public' );
				$attrib->setAttribute( 'isSpecification', 'false' );
				$attrib->setAttribute( 'ownerScope', 'instance' );

				$classif->appendChild( $attrib );
				$attribCount++;
			}

			$class->appendChild( $classif );
			$namesp->appendChild( $class );
			$classCount++;
		}
		$model->appendChild( $namesp );
		$cont->appendChild( $model );
		$root->appendChild( $cont );

		return $xmlDoc->toNormalizedString();
	}

}


?>