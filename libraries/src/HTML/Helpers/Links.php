<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\HTML\Helpers;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\FileLayout;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Utility class for icons.
 *
 * @since  3.2
 */
abstract class Links
{
    /**
     * Method to generate html code for groups of lists of links
     *
     * @param   array  $groupsOfLinks  Array of links
     *
     * @return  string
     *
     * @since   3.2
     */
    public static function linksgroups($groupsOfLinks)
    {
        $html = [];

        if (\count($groupsOfLinks) > 0) {
            $layout = new FileLayout('joomla.links.groupsopen');
            $html[] = $layout->render('');

            foreach ($groupsOfLinks as $title => $links) {
                if (isset($links[0]['separategroup'])) {
                    $layout = new FileLayout('joomla.links.groupseparator');
                    $html[] = $layout->render($title);
                }

                $layout     = new FileLayout('joomla.links.groupopen');
                $htmlHeader = $layout->render($title);

                $htmlLinks  = HTMLHelper::_('links.links', $links);

                if ($htmlLinks !== '') {
                    $html[] = $htmlHeader;
                    $html[] = $htmlLinks;

                    $layout = new FileLayout('joomla.links.groupclose');
                    $html[] = $layout->render('');
                }
            }

            $layout = new FileLayout('joomla.links.groupsclose');
            $html[] = $layout->render('');
        }

        return implode($html);
    }

    /**
     * Method to generate html code for a list of links
     *
     * @param   array  $links  Array of links
     *
     * @return  string
     *
     * @since   3.2
     */
    public static function links($links)
    {
        $html = [];

        foreach ($links as $link) {
            $html[] = HTMLHelper::_('links.link', $link);
        }

        return implode($html);
    }

    /**
     * Method to generate html code for a single link
     *
     * @param   array  $link  link properties
     *
     * @return  string
     *
     * @since   3.2
     */
    public static function link($link)
    {
        if (isset($link['access'])) {
            if (\is_bool($link['access'])) {
                if ($link['access'] == false) {
                    return '';
                }
            } else {
                // Get the user object to verify permissions
                $user = Factory::getUser();

                // Take each pair of permission, context values.
                for ($i = 0, $n = \count($link['access']); $i < $n; $i += 2) {
                    if (!$user->authorise($link['access'][$i], $link['access'][$i + 1])) {
                        return '';
                    }
                }
            }
        }

        // Instantiate a new FileLayout instance and render the layout
        $layout = new FileLayout('joomla.links.link');

        return $layout->render($link);
    }
}
