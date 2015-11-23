<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class CjForumCategories extends JCategories
{

	public function __construct ($options = array())
	{
		$options['table'] = '#__cjforum_topics';
		$options['extension'] = 'com_cjforum';
		
		parent::__construct($options);
	}
}
