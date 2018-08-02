<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Content\Site\View\Debug;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

/**
 * HTML Article View class for the Content component
 *
 * @since  1.5
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * The item
	 *
	 * @var  array
	 *
	 * @since __DEPLOY_VERSION__
	 */
	protected $item;

	/**
	 * Meta information
	 *
	 * @var  array
	 *
	 * @since __DEPLOY_VERSION__
	 */
	protected $meta;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 */
	public function display($tpl = null)
	{
		$item = $this->get('Item');

		$this->meta = $item['__meta'];

		unset ($item['__meta']);

		$this->item = $item;

		return parent::display($tpl);
	}
}
