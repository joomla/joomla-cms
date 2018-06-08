<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Banners\Site\Service;

defined('_JEXEC') or die;

use Joomla\CMS\Categories\Categories;

/**
 * Banners Component Category Tree
 *
 * @since  1.6
 */
class Category extends Categories
{
	/**
	 * Constructor
	 *
	 * @param   array  $options  Array of options
	 *
	 * @since   1.6
	 */
	public function __construct($options = array())
	{
		$options['table']     = '#__banners';
		$options['extension'] = 'com_banners';

		parent::__construct($options);
	}
}
