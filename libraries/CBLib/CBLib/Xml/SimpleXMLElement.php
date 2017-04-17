<?php
/**
 * CBLib, Community Builder Library(TM)
 * @version $Id: 10.06.13 15:47 $
 * @package CBLib\Core
 * @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */

namespace CBLib\Xml;

defined('CBLIB') or die();

/**
 * SimpleXML Element extended for CB.
 *
 */
class SimpleXMLElement extends \SimpleXMLElement implements \Countable {
	/**
	 * This function is only here to satisfy a phplint bug giving false warnings:
	 *
	 * Finds children of given node
	 *
	 * @param string $ns [optional] An XML namespace.
	 * @param bool $is_prefix [optional] If true, ns will be regarded as a prefix. If false, ns will be regarded as a namespace URL.
	 * @return SimpleXMLElement[]  a SimpleXMLElement element, whether the node has children or not.
	 */
	public function children( $ns = null, $is_prefix = false ) {
		return parent::children( $ns, $is_prefix );
	}
	/**
	 * Get the name of the element (same as getName() for compatibility reasons).
	 * @deprecated 2.0 : Use ->getName() instead.
	 *
	 * @return string  The getName of the element
	 */
	public function name( ) {
		return $this->getName();
	}

	/**
	 * Get the an attribute or all attributes of the element
	 *
	 * @param  string  $attribute  The name of the attribute if only one attribute is fetched
	 * @param  bool    $is_prefix  [optional] defaults to False
	 * @return array|bool|string   string      If an attribute is given will return the attribute if it exist.
	 *                             boolean     Null if attribute is given but doesn't exist
	 * 				               array       If no attribute is given will return the complete attributes array
	 */
	public function attributes( $attribute = null, $is_prefix = false )
	{
		if( isset( $attribute ) ) {
			return ( isset( $this[$attribute]) ? (string) $this[$attribute] : null );
		}
		$array	=	array();
		foreach ( parent::attributes() as $k => $v ) {
			$array[$k]	=	(string) $v;
		}
		return $array;
	}

	/**
	 * Get the data of the element
	 *
	 * @return string
	 */
	public function data( ) {
		return (string) $this;
	}

	/**
	 * Adds an attribute to the element, override if it already exists
	 *
	 * @param  string  $name   Name of attribute
	 * @param  null    $value  Value of attribute
	 * @param  null    $ns     Name-space
	 * @return void
	 */
	public function addAttribute( $name, $value = null, $ns = null ) {
		$this[$name]	=	$value;
	}

	/**
	 * Get an element in the document by / separated path
	 * or FALSE
	 *
	 * @param	string	$path          The / separated path to the element
	 * @return	SimpleXMLElement|null  Element or NULL
	 */
	public function getElementByPathOrNull( $path )
	{
		$elem			=	$this->getElementByPath( $path );

		if ( $elem === false ) {
			$elem		=	null;
		}

		return $elem;
	}

	/**
	 * Get an element in the document by / separated path
	 * or FALSE
	 *
	 * @param	string	$path          The / separated path to the element
	 * @return	SimpleXMLElement|bool  Element or FALSE
	 */
	public function & getElementByPath( $path ) {
		$parts				=	explode( '/', trim($path, '/') );

		$tmp				=	$this;
		$found			    =	false;
		foreach ( $parts as $node ) {
			$found			=	false;
			foreach ( $tmp->children() as $child ) {
				/** @var SimpleXMLElement $child */
				if ( $child->getName() == $node ) {
					$tmp	=	$child;
					$found	=	true;
					break;
				}
			}
			if ( ! $found ) {
				break;
			}
		}
		if ( $found ) {
			return $tmp;
		} else {
			$false			=	false;
			return $false;
		}
	}

	/**
	 * Adds a direct child to the element
	 *
	 * @param  string  $name       Name tag of element
	 * @param  string  $value      Text value of element
	 * @param  string  $nameSpace  [optional] Namespace
	 * @param  array   $attrs      [optional] Keyed Attributes
	 * @return SimpleXMLElement    The created child
	 */
	public function & addChildWithAttr( $name, $value, $nameSpace = null, $attrs = null ) {
		if ( $attrs === null ) {
			$attrs		=	array();
		}
		$child			=	parent::addChild( $name, htmlspecialchars( $value ), $nameSpace );
		foreach ( $attrs as $k => $v ) {
			$child->addAttribute( $k, $v );
		}
		return $child;
	}

	/**
	 * Removes a child node from $this
	 * CB 2.0 Experimental API (subject to change/remove)
	 *
	 * @param SimpleXMLElement $node The child to remove
	 */
	public function removeChild( $node ) {
		$domNode		=	dom_import_simplexml( $this );
		$childDomNode	=	dom_import_simplexml( $node );
		$domNode->removeChild( $childDomNode );
	}

	/**
	 * Appends a child node to $this
	 * CB 2.0 Experimental API (subject to change/remove)
	 *
	 * @param SimpleXMLElement $node The child to append
	 */
	public function appendChild( $node ) {
		$domNode		=	dom_import_simplexml( $this );
		$childDomNode	=	dom_import_simplexml( $node );
		$domNode->appendChild( $childDomNode );
	}

	/**
	 * Replace $this by $replacementXmlNode and its children in the XML tree, and extract $this from the tree
	 *
	 * @since 1.2.4
	 *
	 * @param  SimpleXMLElement  $replacementXmlNode  New XML node to replace $this
	 * @param  callback          $callBack            To check/transform data or attributes of a node: $destinationData = function ( string|array $sourceData, SimpleXMLElement $sourceNode, SimpleXMLElement $destinationParentNode );
	 * @return SimpleXMLElement                       $this : Old extracted $this XML node
	 */
	public function & replaceNodeAndChildren( &$replacementXmlNode, $callBack = null ) {
		$domNode				=	dom_import_simplexml( $this );
		$otherDomReplacement	=	dom_import_simplexml( $replacementXmlNode );
		/** @var \DOMElement $domReplacement */
		$domReplacement			=	$domNode->ownerDocument->importNode( $otherDomReplacement );
		$domNode->parentNode->replaceChild( $domReplacement, $domNode );
		$this->_domCopyChildrenCallbackonNode( $domReplacement, $replacementXmlNode, $callBack );
		return $this;
	}
	/**
	 * Inserts $xmlNodeToInsert (and its children) as sibbling BEFORE $this in the XML tree, and returns the new XML node
	 *
	 * @since 1.2.4
	 *
	 * @param  SimpleXMLElement  $xmlNodeToInsert  New XML node to insert BEFORE $this
	 * @param  callback          $callBack         To check/transform data or attributes of a node: $destinationData = function ( string|array $sourceData, SimpleXMLElement $sourceNode, SimpleXMLElement $destinationParentNode );
	 * @return SimpleXMLElement                    New node
	 */
	public function & insertNodeAndChildrenBefore( &$xmlNodeToInsert, $callBack = null ) {
		$domNode				=	dom_import_simplexml( $this );
		$otherDomNodeToInsert	=	dom_import_simplexml( $xmlNodeToInsert );
		$domNodeToInsert		=	$domNode->ownerDocument->importNode( $otherDomNodeToInsert );
		/** @var \DOMElement $newNode */
		$newNode				=	$domNode->parentNode->insertBefore( $domNodeToInsert, $domNode );
		$sibbling				=	$this->_domCopyChildrenCallbackonNode( $newNode, $xmlNodeToInsert, $callBack );
		return $sibbling;
	}
	/**
	 * Inserts $xmlNodeToInsert (and its children) as sibbling AFTER $this in the XML tree, and returns the new XML node
	 *
	 * @since 1.2.4
	 *
	 * @param  SimpleXMLElement  $xmlNodeToInsert  New XML node to insert AFTER $this
	 * @param  callback          $callBack         To check/transform data or attributes of a node: $destinationData = function ( string|array $sourceData, SimpleXMLElement $sourceNode, SimpleXMLElement $destinationParentNode );
	 * @return SimpleXMLElement                    New node
	 */
	public function & insertNodeAndChildrenAfter( &$xmlNodeToInsert, $callBack = null ) {
		$domNode				=	dom_import_simplexml( $this );
		$otherDomNodeToInsert	=	dom_import_simplexml( $xmlNodeToInsert );
		$domNodeToInsert		=	$domNode->ownerDocument->importNode( $otherDomNodeToInsert );
		if( $domNode->nextSibling ) {
			/** @var \DOMElement $newNode */
			$newNode			=	$domNode->parentNode->insertBefore( $domNodeToInsert, $domNode->nextSibling );
			$sibling			=	$this->_domCopyChildrenCallbackonNode( $newNode, $xmlNodeToInsert, $callBack );
		} else {
			// $newNode			=	$domNode->parentNode->appendNode( $domNodeToInsert, $domNode->nextSibling );
			/** @var SimpleXMLElement[] $parent    SimpleXMLElement way which adds descendants too */
			$parent				=	$this->xpath( '..' );
			$sibling			=	$parent[0]->addChildWithDescendants( $xmlNodeToInsert, $callBack );
		}
		return $sibling;
	}
	/**
	 * Protected internal function that handles missing DOM functionality:
	 * $callBack to the copied attributes and data, as well as copying of children.
	 *
	 * @since 1.2.4
	 *
	 * @param  \DOMElement       $newNode         New DOM (incomplete) node just inserted/replaced
	 * @param  SimpleXMLElement  $xmlSourceNode   Original XML node that got copied into DOM
	 * @param  callback          $callBack        To check/transform data or attributes of a node: $destinationData = function ( string|array $sourceData, SimpleXMLElement $sourceNode, SimpleXMLElement $destinationParentNode );
	 * @return SimpleXMLElement                   New XML node
	 */
	protected function & _domCopyChildrenCallbackonNode( &$newNode, $xmlSourceNode, $callBack ) {
		/** @var SimpleXMLElement $newNodeXML */
		$newNodeXML				=	simplexml_import_dom( $newNode, get_class( $this ) );
		if ( $callBack === null ) {
			$newNode->nodeValue	=	$xmlSourceNode->data();			//FIXME: side-effect: this removes children from $xmlSourceNode IF both are in the same ownerDocument, but removing that line makes copying children infinite looping below
		} else {
			$newNode->nodeValue	=	call_user_func_array( $callBack, array( $xmlSourceNode->data(), $xmlSourceNode, $newNodeXML ) );
			$copiedAttributes	=	$newNode->attributes;
			foreach ( $copiedAttributes as $k => $v ) {
				$newNode->removeAttribute( $k );
			}
			// the new set of $attributes can be different from old one, thus we needed to remove old set (copied in PHP 5.3 only) first, then copy new:
			$attributes			=	call_user_func_array( $callBack, array( $xmlSourceNode->attributes(), $xmlSourceNode, $newNodeXML ) );
			foreach ( $attributes as $k => $v ) {
				$newNode->setAttribute( $k, $v );
			}
		}
		foreach ($xmlSourceNode->children() as $child ) {

			$newNodeXML->addChildWithDescendants( $child, $callBack );
		}
		return $newNodeXML;
	}
	/**
	 * Get the first child element in matching all the attributes $attributes
	 *
	 * @param   string	$name          The name tag of the element searched
	 * @param   array   $attributes    array of attribute => value which must match also
	 * @return  SimpleXMLElement|bool  Child or false if no child matches
	 */
	public function &getChildByNameAttributes( $name, $attributes = null ) {
		if ( $attributes === null ) {
			$attributes			=	array();
		}
		foreach ( $this->children() as $child ) {
			/** @var SimpleXMLElement $child */
			if ( $child->getName() == $name ) {
				$found			=	true;
				foreach ( $attributes as $atr => $val ) {
					if ( $child->attributes( $atr ) != $val ) {
						$found	=	false;
						break;
					}
				}
				if ( $found ) {
					return $child;
				}
			}
		}
		$false					=	false;
		return $false;
	}
	/**
	 * Get the first child element in matching the attribute
	 *
	 * @param   string	$name          The name tag of the element searched
	 * @param   string  $attribute     Attribute name to check
	 * @param   string  $value         Attribute value which must also match
	 * @return	SimpleXMLElement|bool  Child or false if no child matches
	 */
	public function &getChildByNameAttr( $name, $attribute, $value = null ) {
		foreach ( $this->children() as $child ) {
			/** @var SimpleXMLElement $child */
			if ( $child->getName() == $name ) {
					if ( $child->attributes( $attribute ) == $value ) {
						return $child;
					}
			}
		}
		$false	= false;
		return $false;
	}
	/**
	 * Get the first child or childs' child (recursing) element in matching the attiribute
	 *
	 * @param   string	$name          The name tag of the element searched
	 * @param   string  $attribute     Attribute name to check
	 * @param   string  $value         Attribute value which must also match
	 * @return	SimpleXMLElement|bool  Child or false if no child matches
	 */
	public function &getAnyChildByNameAttr( $name, $attribute, $value = null ) {
		$children				=	$this->children();			// this is needed due to a bug in PHP 4.4.2 where you can have only 1 iterator per array reference, so doing second iteration on same array within first iteration kills this.
		foreach ( $children as $child ) {
			/** @var SimpleXMLElement $child */
			if ( $child->getName() == $name ) {
					if ( $child->attributes( $attribute ) == $value ) {
						return $child;
					}
			}
			if ( count( $child->children() ) > 0 ) {
				$grandchild		=	$child->getAnyChildByNameAttr( $name, $attribute, $value );	// recurse
				if ( $grandchild ) {
					return $grandchild;
				}
			}
		}
		$false					=	false;
		return $false;
	}

	/**
	 * Appends (copies) a child $source and all its descendants to $this node
	 *
	 * @param  SimpleXMLElement|\SimpleXMLElement  $source
	 * @param  callback                            $callBack to check/transform data or attributes of a node: $destinationData = function ( string|array $sourceData, SimpleXMLElement $sourceNode, SimpleXMLElement $destinationParentNode );
	 * @return SimpleXMLElement
	 */
	public function & addChildWithDescendants( &$source, $callBack = null ) {
		if ( $callBack === null ) {
			$child				=	$this->addChildWithAttr( $source->getName(), $source->data(), null, $source->attributes() );
		} else {
			$child				=	$this->addChildWithAttr( $source->getName(), call_user_func_array( $callBack, array( $source->data(), $source, $this ) ), null, call_user_func_array( $callBack, array( $source->attributes(), $source, $this ) ) );
		}
        foreach ( $source->children() as $sourceChild ) {
            $child->addChildWithDescendants( $sourceChild, $callBack );
        }
        return $child;
	}
}
