<?php

/**
 * @package    JED
 *
 * @copyright  (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Jed\Component\Jed\Site\Helper;

use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * JED Extension Trophy Helper
 *
 * trophy icons (e.g popular, or version numbers, or module/component
 *
 * @package   JED
 * @since     4.0.0
 */
class JedtrophyHelper
{
    /**
     * @param $versionstr
     *
     * @return string
     *
     * @since version
     */
    public static function getTrophyVersionsString($versionstr): string
    {
        //  echo $versionstr;exit();
        $l_version = str_replace('[', '', $versionstr);

        $l_version = str_replace(']', '', $l_version);
        $l_version = str_replace('"', '', $l_version);

        $trophies = explode(',', $l_version);

        $output = ''; //<div class="trophies versions">';

        foreach ($trophies as $v) {
            $title = Text::_('COM_JED_VERSION_' . $v);
            switch ($v) {
                case '30':
                    $txt = '<span class="fab fa-joomla"></span>&nbsp;3&nbsp;';
                    break;
                case '40':
                    $txt = '<span class="fab fa-joomla"></span>&nbsp;4&nbsp;';
                    break;
                case '41':
                    $txt = '<span class="fab fa-joomla"></span>&nbsp;4.1&nbsp;';
                    break;
            }
            $output .= '<span title="' . $title . '" class="joomla-version-badge">' . $txt . '</span>';
        }

        //$output .= '</div>';
        return $output;
    }

    /**
     * @param $includestr
     *
     * @return string
     *
     * @since version
     */
    public static function getTrophyIncludesString($includestr): string
    {

        $output    = '';
        $l_include = str_replace('[', '', $includestr);

        $l_include = str_replace(']', '', $l_include);
        $l_include = str_replace('"', '', $l_include);
        $trophies  = explode(',', $l_include);

        $output = '<div class="trophies includes">';
        foreach ($trophies as $v) {
            $title = Text::_('COM_JED_EXTENSIONS_FIELD_INCLUDES_' . strtoupper($v));
            $output .= '<span class="hasTooltip" data-toggle="tooltip" title="' . $title . '">	<span  class="badge badge-' . $v . '">' . strtoupper(substr($v, 0, 1)) . '</span>	</span>';
        }

        $output .= '</div>';

        return $output;
    }

    /**
     * @param $includestr
     *
     * @return string
     *
     * @since version
     */
    public static function getTrophyIncludesStringFull($includestr): string
    {
        $l_include = str_replace('[', '', $includestr);

        $l_include = str_replace(']', '', $l_include);
        $l_include = str_replace('"', '', $l_include);
        $trophies  = explode(',', $l_include);

        $output      = '';
        $comma_count = 0;

        foreach ($trophies as $v) {
            $str = "";
            switch ($v) {
                case "com":
                    $str = "Component";
                    $comma_count++;
                    break;
                case "mod":
                    $str = "Module";
                    $comma_count++;
                    break;
                case "plugin":
                    $str = "Plugin";
                    $comma_count++;
                    break;
            }
            if ($comma_count > 1) {
                $output .= ', ' . $str;
            } else {
                $output .= $str;
            }
        }

        return $output;
    }

    /**
     * @param $versionstr
     *
     * @return string
     *
     * @since version
     */
    public static function getTrophyVersionsStringFull($versionstr): string
    {
        //  echo $versionstr;exit();
        $l_version = str_replace('[', '', $versionstr);

        $l_version = str_replace(']', '', $l_version);
        $l_version = str_replace('"', '', $l_version);

        $trophies = explode(',', $l_version);

        $output      = '';//<div class="trophies versions">';
        $comma_count = 0;
        foreach ($trophies as $v) {
            $title = 'Joomla!&nbsp;' . ((float)$v) / 10;
            $comma_count++;

            if ($comma_count > 1) {
                $output .= '<br />' . $title;
            } else {
                $output .= $title;
            }
        }

        //$output .= '</div>';
        return $output;
    }
}
