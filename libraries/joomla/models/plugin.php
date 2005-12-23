<?php
/**
 * @version $Id$
 * @package Joomla
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

jimport('joomla.models.model');

/**
 * Class JPluginModel
 *
 * @package Joomla
 * @subpackage JFramework
 * @since 1.1
 */
class JPluginModel extends JModel {

	/**
	 * Primary Key
	 *
	 *  @var int
	 */
	var $id = null;

	/**
	 *
	 *
	 * @var varchar
	 */
	var $name = null;

	/**
	 *
	 *
	 * @var varchar
	 */
	var $element = null;

	/**
	 *
	 *
	 * @var varchar
	 */
	var $folder = null;

	/**
	 *
	 *
	 * @var tinyint unsigned
	 */
	var $access = null;

	/**
	 *
	 *
	 * @var int
	 */
	var $ordering = null;

	/**
	 *
	 *
	 * @var tinyint
	 */
	var $published = null;

	/**
	 *
	 *
	 * @var tinyint
	 */
	var $iscore = null;

	/**
	 *
	 *
	 * @var tinyint
	 */
	var $client_id = null;

	/**
	 *
	 *
	 * @var int unsigned
	 */
	var $checked_out = null;

	/**
	 *
	 *
	 * @var datetime
	 */
	var $checked_out_time = null;

	/**
	 *
	 *
	 * @var text
	 */
	var $params = null;

	function __construct(& $db) {
		parent :: __construct('#__plugins', 'id', $db);
	}
}
?>