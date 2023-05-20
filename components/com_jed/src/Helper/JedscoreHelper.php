<?php

/**
 * @package    JED
 *
 * @copyright  (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Jed\Component\Jed\Site\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * JED Extension Score Helper
 *
 * @package   JED
 * @since     4.0.0
 */
class JedscoreHelper
{
    /**
     * @param $score
     *
     * @return string
     *
     * @since version
     */
    public static function getStars($score = 0): string
    {
        if ($score == 0) {
            return 'Not Rated';
        }
        $star_score = self::getStarScore($score);
        if (!is_numeric($star_score)) {
            return '';
        }


        $whole = floor($star_score);
        $half  = $star_score > $whole ? 1 : 0;
        $empty = 5 - $whole - $half;

        $html = '<div class="stars stars-' . self::getClass($star_score) . '">';
        $html .= str_repeat('<span class="star star-full fa fa-star"></span>', $whole);
        $html .= str_repeat('<span class="star star-half fa fa-star-half"></span>', $half);
        $html .= str_repeat('<span class="star star-empty fa fa-star-empty"></span>', $empty);
        // $html .= '<span class="tooltiptext">Score of '.$star_score.'</span>';
        $html .= '</div>';
        if ($star_score == 3.5) {
            //  echo "<pre>";print_r($html);echo "</pre>";exit();
        }
        //echo "<pre>";print_r($html);echo "</pre>";exit();
        return $html;
    }

    /**
     * @param $score
     *
     * @return string
     *
     * @since version
     */
    public static function getStarsShort($score = 0): string
    {
        $star_score = self::getStarScore($score);

        if (!is_numeric($star_score)) {
            return '';
        }

        $html = '<div class="stars-short stars-' . self::getClass($score) . '">';
        $html .= '<span class="fa fa-star"></span>';
        $html .= $star_score;
        $html .= '</div>';

        return $html;
    }

    /**
     * @param $score
     *
     * @return false|float|int
     *
     * @since version
     */
    public static function getStarScore($score)
    {
        if (!$score) {
            return false;
        }

        // convert 1-100 score to 0 - 5 value
        return round($score / 10) / 2;
    }

    /**
     * @param $score
     *
     * @return string
     *
     * @since version
     */
    public static function getClass($score): string
    {
        $star_score = self::getStarScore($score);

        if (!is_numeric($star_score)) {
            return 'none';
        }

        if ($star_score <= 2) {
            return 'low';
        }

        if ($star_score <= 4) {
            return 'medium';
        }

        return 'high';
    }
}
