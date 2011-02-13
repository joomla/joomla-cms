<?php
/**
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @package     Joomla.Platform
 * @subpackage  Installer
 */

defined('JPATH_PLATFORM') or die;

/**
 * Extension object
 *
 * @package		Joomla.Platform
 * @subpackage	Installer
 * @since		11.1
 */
class JExtension extends JObject
{
	/** @var string $filename Filename of the extension */
	var $filename = '';
	/** @var string $type Type of the extension */
	var $type = '';
	/** @var string $id Unique Identifier for the extension */
	var $id = '';
	/** @var boolean $published The status of the extension */
	var $published = false; // published status
	/** @var string $client String representation of client. Valid for modules, templates and languages */
	var $client = 'site'; // valid for modules, templates and languages; set by default
	/** @var string $group The group name of the plugin. Not used for other known extension types */
	var $group =  ''; // valid for plugins
	/** @var Object $manifest_cache An object representation of the manifest file */
	var $manifest_cache = null; // manifest cache; stored metadata
	/** @var Object $params An object representation of the extension params */
	var $params = null;	// extension params

	/**
	 * Constructor
	 * @param JXMLElement $element a JXMLElement from which to load data from
	 */
	function __construct(JXMLElement $element = null)
	{
		if ($element && is_a($element, 'JXMLElement'))
		{
			$this->type = (string)$element->attributes()->type;
			$this->id = (string)$element->attributes()->id;

			switch($this->type)
			{
				case 'component':
					// by default a component doesn't have anything
					break;

				case 'module':
				case 'template':
				case 'language':
					$this->client = (string)$element->attributes()->client;
					$tmp_client_id = JApplicationHelper::getClientInfo($this->client, 1);
					if($tmp_client_id == null) {
						JError::raiseWarning(100, JText::_('JLIB_INSTALLER_ERROR_EXTENSION_INVALID_CLIENT_IDENTIFIER'));
					} else {
						$this->client_id = $tmp_client_id->id;
					}
					break;

				case 'plugin':
					$this->group = (string)$element->attributes()->group;
					break;

				default:
					// catch all
					// get and set client and group if we don't recognise the extension
					if ($client = (string)$element->attributes()->client)
					{
						$this->client_id = JApplicationHelper::getClientInfo($this->client, 1);
						$this->client_id = $this->client_id->id;
					}
					if ($group = (string)$element->attributes()->group) {
						$this->group = (string)$element->attributes()->group;
					}
					break;
			}
			$this->filename = (string)$element;
		}
	}
}
