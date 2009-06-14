<?php
/**
 * @version		$Id: category.php 11653 2009-03-08 11:11:10Z hackwar $
 * @package		Joomla
 * @subpackage	Content
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

// Component Helper
jimport('joomla.application.component.helper');
jimport('joomla.application.categories');

/**
 * Content Component Category Tree
 *
 * @static
 * @package		Joomla
 * @subpackage	Content
 * @since 1.6
 */
class NewsfeedsCategories extends JCategories
{
	public function __construct($options = array())
	{
		$options['table'] = '#__newsfeeds';
		$options['extension'] = 'com_newsfeeds';
		parent::__construct($options);
	}
}