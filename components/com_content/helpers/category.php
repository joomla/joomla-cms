<?php
/**
 * @version		$Id: category.php 11653 2009-03-08 11:11:10Z hackwar $
 * @package		Joomla
 * @subpackage	Content
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// Component Helper
jimport('joomla.application.component.helper');
jimport('joomla.application.categorytree');

/**
 * Content Component Category Tree
 *
 * @static
 * @package		Joomla
 * @subpackage	Content
 * @since 1.6
 */
class ContentCategories extends JCategoryTree
{
	public function __construct($options = array())
	{
		$options['table'] = '#__content';
		$options['extension'] = 'com_content';
		parent::__construct($options);
	}
}