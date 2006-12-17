<?php

/**
 * This is the HTML pseudo-parser for the Yadis library.
 *
 * PHP versions 4 and 5
 *
 * LICENSE: See the COPYING file included in this distribution.
 *
 * @package Yadis
 * @author JanRain, Inc. <openid@janrain.com>
 * @copyright 2005 Janrain, Inc.
 * @license http://www.gnu.org/copyleft/lesser.html LGPL
 */

/**
 * This class is responsible for scanning an HTML string to find META
 * tags and their attributes.  This is used by the Yadis discovery
 * process.  This class must be instantiated to be used.
 *
 * @package Yadis
 */
class Services_Yadis_ParseHTML {

    /**
     * @access private
     */
    var $_re_flags = "si";

    /**
     * @access private
     */
    var $_tag_expr = "<%s\b(?!:)([^>]*?)(?:\/>|>(.*?)(?:<\/?%s\s*>|\Z))";

    /**
     * @access private
     */
    var $_close_tag_expr = "<\/?%s\s*>";

    /**
     * @access private
     */
    var $_removed_re =
           "<!--.*?-->|<!\[CDATA\[.*?\]\]>|<script\b(?!:)[^>]*>.*?<\/script>";

    /**
     * @access private
     */
    var $_attr_find = '\b([-\w]+)=("[^"]*"|\'[^\']*\'|[^\'"\s\/<>]+)';

    function Services_Yadis_ParseHTML()
    {
        $this->_meta_find = sprintf("/<meta\b(?!:)([^>]*)(?!<)>/%s",
                                    $this->_re_flags);

        $this->_removed_re = sprintf("/%s/%s",
                                     $this->_removed_re,
                                     $this->_re_flags);

        $this->_attr_find = sprintf("/%s/%s",
                                    $this->_attr_find,
                                    $this->_re_flags);

        $this->_entity_replacements = array(
                                            'amp' => '&',
                                            'lt' => '<',
                                            'gt' => '>',
                                            'quot' => '"'
                                            );

        $this->_ent_replace =
            sprintf("&(%s);", implode("|",
                                      $this->_entity_replacements));
    }

    /**
     * Replace HTML entities (amp, lt, gt, and quot) as well as
     * numeric entities (e.g. #x9f;) with their actual values and
     * return the new string.
     *
     * @access private
     * @param string $str The string in which to look for entities
     * @return string $new_str The new string entities decoded
     */
    function replaceEntities($str)
    {
        foreach ($this->_entity_replacements as $old => $new) {
            $str = preg_replace(sprintf("/&%s;/", $old), $new, $str);
        }

        // Replace numeric entities because html_entity_decode doesn't
        // do it for us.
        $str = preg_replace('~&#x([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $str);
        $str = preg_replace('~&#([0-9]+);~e', 'chr(\\1)', $str);

        return $str;
    }

    /**
     * Strip single and double quotes off of a string, if they are
     * present.
     *
     * @access private
     * @param string $str The original string
     * @return string $new_str The new string with leading and
     * trailing quotes removed
     */
    function removeQuotes($str)
    {
        $matches = array();
        $double = '/^"(.*)"$/';
        $single = "/^\'(.*)\'$/";

        if (preg_match($double, $str, $matches)) {
            return $matches[1];
        } else if (preg_match($single, $str, $matches)) {
            return $matches[1];
        } else {
            return $str;
        }
    }

    /**
     * Create a regular expression that will match an opening (and
     * optional) closing tag of a given name.
     *
     * @access private
     * @param string $tag_name The tag name to match
     * @param array $close_tags An array of tag names which also
     * constitute closing of the original tag
     * @return string $regex A regular expression string to be used
     * in, say, preg_match.
     */
    function tagMatcher($tag_name, $close_tags = null)
    {
        if ($close_tags) {
            $options = implode("|", array_merge(array($tag_name), $close_tags));
            $closer = sprintf("(?:%s)", $options);
        } else {
            $closer = $tag_name;
        }

        $expr = sprintf($this->_tag_expr, $tag_name, $closer);
        return sprintf("/%s/%s", $expr, $this->_re_flags);
    }

    /**
     * @access private
     */
    function htmlFind($str)
    {
        return $this->tagMatcher('html', array('body'));
    }

    /**
     * @access private
     */
    function headFind()
    {
        return $this->tagMatcher('head', array('body'));
    }

    /**
     * Given an HTML document string, this finds all the META tags in
     * the document, provided they are found in the
     * <HTML><HEAD>...</HEAD> section of the document.  The <HTML> tag
     * may be missing.
     *
     * @access private
     * @param string $html_string An HTMl document string
     * @return array $tag_list Array of tags; each tag is an array of
     * attribute -> value.
     */
    function getMetaTags($html_string)
    {
        $stripped = preg_replace($this->_removed_re,
                                 "",
                                 $html_string);

        // Look for the closing body tag.
        $body_closer = sprintf($this->_close_tag_expr, 'body');
        $body_matches = array();
        preg_match($body_closer, $html_string, $body_matches,
                   PREG_OFFSET_CAPTURE);
        if ($body_matches) {
            $html_string = substr($html_string, 0, $body_matches[0][1]);
        }

        // Look for the opening body tag, and discard everything after
        // that tag.
        $body_re = $this->tagMatcher('body');
        $body_matches = array();
        preg_match($body_re, $html_string, $body_matches, PREG_OFFSET_CAPTURE);
        if ($body_matches) {
            $html_string = substr($html_string, 0, $body_matches[0][1]);
        }

        // If an HTML tag is found at all, it must be in the right
        // order; else, it may be missing (which is a case we allow
        // for).
        $html_re = $this->tagMatcher('html', array('body'));
        preg_match($html_re, $html_string, $html_matches);
        if ($html_matches) {
            $html = $html_matches[0];
        } else {
            $html = $html_string;
        }

        // Try to find the <HEAD> tag.
        $head_re = $this->headFind();
        $head_matches = array();
        if (!preg_match($head_re, $html, $head_matches)) {
            return array();
        }

        $link_data = array();
        $link_matches = array();

        if (!preg_match_all($this->_meta_find, $head_matches[0],
                            $link_matches)) {
            return array();
        }

        foreach ($link_matches[0] as $link) {
            $attr_matches = array();
            preg_match_all($this->_attr_find, $link, $attr_matches);
            $link_attrs = array();
            foreach ($attr_matches[0] as $index => $full_match) {
                $name = $attr_matches[1][$index];
                $value = $this->replaceEntities(
                              $this->removeQuotes($attr_matches[2][$index]));

                $link_attrs[strtolower($name)] = $value;
            }
            $link_data[] = $link_attrs;
        }

        return $link_data;
    }

    /**
     * Looks for a META tag with an "http-equiv" attribute whose value
     * is one of ("x-xrds-location", "x-yadis-location"), ignoring
     * case.  If such a META tag is found, its "content" attribute
     * value is returned.
     *
     * @param string $html_string An HTML document in string format
     * @return mixed $content The "content" attribute value of the
     * META tag, if found, or null if no such tag was found.
     */
    function getHTTPEquiv($html_string)
    {
        $meta_tags = $this->getMetaTags($html_string);

        if ($meta_tags) {
            foreach ($meta_tags as $tag) {
                if (array_key_exists('http-equiv', $tag) &&
                    (in_array(strtolower($tag['http-equiv']),
                              array('x-xrds-location', 'x-yadis-location'))) &&
                    array_key_exists('content', $tag)) {
                    return $tag['content'];
                }
            }
        }

        return null;
    }
}

?>