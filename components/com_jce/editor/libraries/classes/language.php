<?php

/**
 * @copyright 	Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
abstract class WFLanguage
{
    protected static $tag;

    /*
     * Check a lnagueg file exists and is the correct version
     */
    protected static function check($tag)
    {
        return file_exists(JPATH_SITE.'/language/'.$tag.'/'.$tag.'.com_jce.ini');
    }

    /**
     * Return the curernt language code.
     *
     * @return language code
     */
    public static function getDir()
    {
        $language = JFactory::getLanguage();

        $tag = self::getTag();

        if ($language->getTag() == $tag) {
            return $language->isRTL() ? 'rtl' : 'ltr';
        }

        return 'ltr';
    }

    /**
     * Return the curernt language code.
     *
     * @return language code
     */
    public static function getTag()
    {
        $tag = JFactory::getLanguage()->getTag();

        if (!isset(self::$tag)) {
            if (self::check($tag)) {
                self::$tag = $tag;
            } else {
                self::$tag = 'en-GB';
            }
        }

        return self::$tag;
    }

    /**
     * Return the curernt language code.
     *
     * @return language code
     */
    public static function getCode()
    {
        $tag = self::getTag();

        return substr($tag, 0, strpos($tag, '-'));
    }

    /**
     * Load a language file.
     *
     * @param string $prefix         Language prefix
     * @param object $path[optional] Base path
     */
    public static function load($prefix, $path = JPATH_SITE)
    {
        JFactory::getLanguage()->load($prefix, $path);
    }
}
