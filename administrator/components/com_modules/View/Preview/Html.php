<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Modules\Administrator\View\Preview;

defined('_JEXEC') or die;

use Joomla\CMS\Editor\Editor;
use Joomla\CMS\View\HtmlView;

/**
 * HTML View class for the Modules component
 *
 * @since  1.6
 */
class Html extends HtmlView
{
	/**
	 * The editor instance
	 *
	 * @var  Editor
	 */
	protected $editor;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$editor = \JFactory::getConfig()->get('editor');

		$this->editor = Editor::getInstance($editor);

		parent::display($tpl);
	}
}
