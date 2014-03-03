<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.vote
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Vote plugin.
 *
 * @package     Joomla.Plugin
 * @subpackage  Content.vote
 * @since       1.5
 */
class PlgContentVote extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * Displays the voting area if in an article
	 *
	 * @param   string   $context  The context of the content being passed to the plugin
	 * @param   object   &$row     The article object
	 * @param   object   &$params  The article params
	 * @param   integer  $page     The 'page' number
	 *
	 * @return  mixed  html string containing code for the votes if in com_content else boolean false
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

		if (!empty($params) && $params->get('show_vote', null))
		{
			$view = JFactory::getApplication()->input->getString('view', '');
			$params->set('showVoteForm', ($view == 'article' && $row->state == 1));

			return JLayoutHelper::render(
				'plugins.content.vote',
				array(
					'context' => $context,
					'row'     => $row,
					'params'  => $params,
					'page'    => $page
				)
			);
		}

		return null;
	}
}
