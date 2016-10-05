<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.vote
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Vote plugin.
 *
 * @since  1.5
 */
class PlgContentVote extends JPlugin
{
	/**
	 * Application object
	 *
	 * @var    JApplicationCms
	 * @since  __DEPLOY_VERSION__
	 */
	protected $app;

	/**
	 * Displays the voting area if in an article
	 *
	 * @param   string   $context  The context of the content being passed to the plugin
	 * @param   object   &$row     The article object
	 * @param   object   &$params  The article params
	 * @param   integer  $page     The 'page' number
	 *
	 * @return  string|boolean  HTML string containing code for the votes if in com_content else boolean false
	 *
	 * @since   1.6
	 */
	public function onContentBeforeDisplay($context, &$row, &$params, $page=0)
	{
		$parts = explode(".", $context);

		if ($parts[0] != 'com_content')
		{
			return false;
		}

		if (empty($params) || !$params->get('show_vote', null))
		{
			return '';
		}

		// Load plugin language files only when needed (ex: they are not needed if show_vote is not active).
		$this->loadLanguage();

		// Get the path for the rating summary layout file
		$path = JPluginHelper::getLayoutPath('content', 'vote', 'rating');

		// Render the layout
		ob_start();
		include $path;
		$html = ob_get_clean();

		if ($this->app->input->getString('view', '') == 'article' && $row->state == 1)
		{
			// Get the path for the voting form layout file
			$path = JPluginHelper::getLayoutPath('content', 'vote', 'vote');

			// Render the layout
			ob_start();
			include $path;
			$html .= ob_get_clean();
		}

		return $html;
	}
}
