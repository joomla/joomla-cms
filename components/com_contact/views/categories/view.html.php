<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Content categories view.
 *
 * @package     Joomla.Site
 * @subpackage  com_contact
 * @since       1.6
 */
class ContactViewCategories extends JViewCategories
{
	protected $pagination = null;

	/*
	 * @var  string  Language key for default page heading
	*/

	protected  $pageHeading = 'COM_CONTACT_DEFAULT_PAGE_TITLE';

}
