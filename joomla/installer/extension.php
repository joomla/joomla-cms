<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * Extension object
 *
 * @package		Joomla.Framework
 * @subpackage	Installer
 * @since		1.6
 */
// TODO: Merge this into JTableExtension?
class JExtension extends JObject
{
	var $filename = '';
	var $type = '';
	var $id = '';
	var $published = false; // published status
	var $client = 'site'; // valid for modules, templates and languages; set by default
	var $group =  ''; // valid for plugins
	var $manifest_cache = null; // manifest cache; stored metadata
	var $params = null;	// extension params

	function __construct($element=null)
	{
		if ($element && is_a($element, 'JSimpleXMLElement'))
		{
			$this->type = $element->attributes('type');
			$this->id = $element->attributes('id');

			switch($this->type)
			{
				case 'component':
					// by default a component doesn't have anything
					break;

				case 'module':
				case 'template':
				case 'language':
					$this->client = $element->attributes('client');
					$this->client_id = JApplicationHelper::getClientInfo($this->client,1);
					$this->client_id = $this->client_id->id;
					break;

				case 'plugin':
					$this->group = $element->attributes('group');
					break;

				default:
					// catch all
					// get and set client and group if we don't recognise the extension
					if ($client = $element->attributes('client'))
					{
						$this->client_id = JApplicationHelper::getClientInfo($this->client,1);
						$this->client_id = $this->client_id->id;
					}
					if ($group = $element->attributes('group')) {
						$this->group = $element->attributes('group');
					}
					break;
			}
			$this->filename = $element->data();
		}
	}
}