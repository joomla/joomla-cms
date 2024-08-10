<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Content.vote
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Content\Vote\Extension;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Vote plugin.
 *
 * @since  1.5
 */
final class Vote extends CMSPlugin
{
    /**
     * @var    \Joomla\CMS\Application\CMSApplication
     *
     * @since  3.7.0
     *
     * @deprecated 4.4.0 will be removed in 6.0 as it is there only for layout overrides
     *             Use getApplication() instead
     */
    protected $app;

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
        if ($this->params->get('position', 'top') !== 'top') {
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
        if ($this->params->get('position', 'top') !== 'bottom') {
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

        if ($parts[0] !== 'com_content') {
            return false;
        }

        if (empty($params) || !$params->get('show_vote', null)) {
            return '';
        }

        // Load plugin language files only when needed (ex: they are not needed if show_vote is not active).
        $this->loadLanguage();

        // Get the path for the rating summary layout file
        $path = PluginHelper::getLayoutPath('content', 'vote', 'rating');

        // Render the layout
        ob_start();
        include $path;
        $html = ob_get_clean();

        if ($this->getApplication()->getInput()->getString('view', '') === 'article' && $row->state == 1) {
            // Get the path for the voting form layout file
            $path = PluginHelper::getLayoutPath('content', 'vote', 'vote');

            // Render the layout
            ob_start();
            include $path;
            $html .= ob_get_clean();
        }

        return $html;
    }
}
