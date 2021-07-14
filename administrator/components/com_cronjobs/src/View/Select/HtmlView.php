<?php
/**
 * Declares the MVC View for SelectPluginModel.
 *
 * @package    Joomla.Administrator
 * @subpackage com_cronjobs
 *
 * @copyright (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license   GPL v3
 */

namespace Joomla\Component\Cronjobs\Administrator\View\Select;

// Restrict direct access
\defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

/**
 * The MVC View Select
 * Should let the user choose from a list of plugin defined Jobs or a CLI job.
 *
 * @package    Joomla.Administrator
 * @subpackage com_cronjobs
 *
 * @since __DEPLOY_VERSION__
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * The Form object
	 *
	 * @var   \JForm
	 * @since __DEPLOY__VERSION__
	 */
	protected $form;

	/**
	 * The active item
	 *
	 * @var   object
	 * @since __DEPLOY__VERSION__
	 */
	protected $item;

	/**
	 * The model state
	 *
	 * @var   \JObject
	 * @since __DEPLOY__VERSION__
	 */
	protected $state;


	/**
	 * @param   string $tpl The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return void
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function display($tpl = null) : void
	{
		// ! TODO
	}

}
