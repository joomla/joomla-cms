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
	 * @since  3.7.0
	 */
	protected $app;

	/**
	 * The position the voting data is displayed in relative to the article.
	 *
	 * @var    string
	 * @since  3.7.0
	 */
	protected $votingPosition;

	/**
	 * Constructor.
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An optional associative array of configuration settings.
	 *
	 * @since   3.7.0
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		$this->votingPosition = $this->params->get('position', 'top');
	}

	/**
	 * Displays the voting area when viewing an article and the voting section is displayed before the article
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
	public function onContentBeforeDisplay($context, &$row, &$params, $page = 0)
	{
		if ($this->votingPosition !== 'top')
		{
			return '';
		}

		return $this->displayVotingData($context, $row, $params, $page);
	}

	/**
	 * Displays the voting area when viewing an article and the voting section is displayed after the article
	 *
	 * @param   string   $context  The context of the content being passed to the plugin
	 * @param   object   &$row     The article object
	 * @param   object   &$params  The article params
	 * @param   integer  $page     The 'page' number
	 *
	 * @return  string|boolean  HTML string containing code for the votes if in com_content else boolean false
	 *
	 * @since   3.7.0
	 */
	public function onContentAfterDisplay($context, &$row, &$params, $page = 0)
	{
		if ($this->votingPosition !== 'bottom')
		{
			return '';
		}

		return $this->displayVotingData($context, $row, $params, $page);
	}

	/**
	 * Displays the voting area
	 *
	 * @param   string   $context  The context of the content being passed to the plugin
	 * @param   object   &$row     The article object
	 * @param   object   &$params  The article params
	 * @param   integer  $page     The 'page' number
	 *
	 * @return  string|boolean  HTML string containing code for the votes if in com_content else boolean false
	 *
	 * @since   3.7.0
	 */
	private function displayVotingData($context, &$row, &$params, $page)
	{
		$parts = explode('.', $context);

		if ($parts[0] !== 'com_content')
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

		if ($this->app->input->getString('view', '') === 'article' && $row->state == 1)
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
