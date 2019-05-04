<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Site\View\Users;

defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use JPagination;

/**
 * View class for Users
 *
 * @since  4.0
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * An array of items
	 *
	 * @var     array
	 * @since   4.0
	 */
	protected $items;

	/**
	 * The pagination object
	 *
	 * @var     JPagination
	 * @since   4.0
	 */
	protected $pagination;

	/**
	 * The model state
	 *
	 * @var     object
	 * @since   4.0
	 */
	protected $state;

	/**
	 * The page parameters
	 *
	 * @var    \Joomla\Registry\Registry|null
	 * @since  4.0.0
	 */
	protected $params = null;

	/**
	 * @param   null  $tpl  Params
	 *
	 * @return  void
	 * @throws  Exception
	 * @since   1.0.0
	 */
	public function display($tpl = null)
	{
		$this->state      = $this->get('State');
		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->params     = $this->state->get('params');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors));
		}

		parent::display($tpl);
	}
}
