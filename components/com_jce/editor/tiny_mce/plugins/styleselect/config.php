<?php

/**
 * @copyright     Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license       GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
class WFStyleselectPluginConfig
{
    /**
     * Return the current site template name.
     */
    private static function getSiteTemplates()
    {
        $db = JFactory::getDBO();
        $app = JFactory::getApplication();
        $id = 0;

        if ($app->getClientId() === 0) {
            $menus = $app->getMenu();
            $menu = $menus->getActive();

            if ($menu) {
                $id = isset($menu->template_style_id) ? $menu->template_style_id : $menu->id;
            }
        }

        $query = $db->getQuery(true);

        $query->select('id, template')->from('#__template_styles')->where(array('client_id = 0', "home = '1'"));

        $db->setQuery($query);
        $templates = $db->loadObjectList();

        $assigned = array();

        foreach ($templates as $template) {
            if ($id == $template->id) {
                array_unshift($assigned, $template->template);
            } else {
                $assigned[] = $template->template;
            }
        }

        // return templates
        return $assigned;
    }

    public static function getConfig(&$settings)
    {
        $wf = WFApplication::getInstance();

        $include = (array) $wf->getParam('styleselect.styles', array('stylesheet', 'custom'));

        if (in_array('custom', $include)) {
            // theme styles list (legacy)
            $theme_advanced_styles = $wf->getParam('editor.theme_advanced_styles', '');

            // list of custom styles
            $custom_classes = $wf->getParam('styleselect.custom_classes', '');

            if (!empty($custom_classes)) {
                $settings['styleselect_custom_classes'] = $custom_classes;
            }

            // add legacy to custom_style_classes
            if (!empty($theme_advanced_styles)) {
                $list1 = explode(',', $custom_classes);
                $list2 = explode(',', $theme_advanced_styles);

                $settings['styleselect_custom_classes'] = implode(',', array_merge($list1, $list2));
            }

            $custom_styles = $wf->getParam('styleselect.custom_styles', $wf->getParam('editor.custom_styles', ''));

            // decode json string
            if (is_string($custom_styles)) {
                $custom_styles = json_decode(htmlspecialchars_decode($custom_styles));
            }

            if (!empty($custom_styles)) {
                $styles = array();

                $blocks = array('section', 'nav', 'article', 'aside', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'header', 'footer', 'address', 'main', 'p', 'pre', 'blockquote', 'figure', 'figcaption', 'div');

                foreach ((array) $custom_styles as $style) {
                    // clean up title
                    if (isset($style->title)) {
                        $style->title = self::cleanString($style->title);
                    }

                    // clean up classes
                    if (isset($style->selector)) {
                        $selector = self::cleanString($style->selector);

                        // convert selector values to lowercase
                        $selector = strtolower($selector);

                        // clean up selector to allow element and class only
                        $selector = preg_replace('#[^a-z0-9,\.]+#', '', $selector);

                        $style->selector = trim($selector);
                    }

                    // clean up classes
                    if (isset($style->classes)) {
                        $style->classes = self::cleanString($style->classes);
                    }

                    if (isset($style->styles)) {
                        $style->styles = self::cleanJSON($style->styles);
                    }

                    if (isset($style->attributes)) {
                        $style->attributes = self::cleanJSON($style->attributes, ' ', '=');
                    }

                    // set block or inline element
                    if (isset($style->element)) {
                        if (in_array($style->element, $blocks)) {
                            $style->block = $style->element;
                        } else {
                            $style->inline = $style->element;
                        }

                        // remove element
                        $style->remove = 'all';
                    }

                    // edge case for forced_root_block=false
                    if ($settings['forced_root_block'] === false) {
                        if (!isset($style->element)) {
                            $style->inline = 'span';
                            $style->selector = '*';
                        }
                    } else {
                        // match all if not set
                        if (!isset($style->selector)) {
                            $style->selector = '*';

                            // set to element
                            if (isset($style->element)) {
                                $style->selector = $style->element;
                            }
                        }
                    }

                    // remove element
                    unset($style->element);

                    $styles[] = $style;
                }

                if (!empty($styles)) {
                    $settings['style_formats'] = json_encode($styles, JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
                }
            }
        }

        // set this value false if stylesheet not included
        if (in_array('stylesheet', $include) === false) {
            $settings['styleselect_stylesheets'] = false;
        } else {
            $stylesheet = $wf->getParam('styleselect.stylesheet', '', '');
            $templates = self::getSiteTemplates();

            if (!empty($stylesheet)) {
                $stylesheet = trim($stylesheet);
                $stylesheet = str_replace('$template', $templates[0], $stylesheet);
                $settings['styleselect_stylesheets'] = $stylesheet;

                // add the stylesheet to the content_css setting
                $stylesheet = trim($stylesheet, '/');
                $etag = '';

                if (is_file(JPATH_SITE.'/'.$stylesheet)) {
                    // create hash
                    $etag = '?'.filemtime(JPATH_SITE.'/'.$stylesheet);

                    // explode to array
                    $content_css = explode(',', $settings['content_css']);
                    $content_css[] = JURI::root(true).'/'.$stylesheet.$etag;

                    // remove duplicates
                    $content_css = array_unique($content_css);

                    // implode to string
                    $settings['content_css'] = implode(',', $content_css);
                }
            }
        }

        $settings['styleselect_sort'] = $wf->getParam('styleselect.sort', 1, 1);
    }

    protected static function cleanString($string)
    {
        $string = trim($string, '"');
        $string = trim($string, "'");
        $string = htmlentities($string, ENT_NOQUOTES, 'UTF-8');

        return trim($string);
    }

    protected static function cleanJSON($string, $delim1 = ';', $delim2 = ':')
    {
        $ret = array();

        foreach (explode($delim1, $string) as $item) {
            $item = trim($item);

            // split style at colon
            $parts = explode($delim2, $item);

            if (count($parts) < 2) {
                continue;
            }

            $key = $parts[0];
            $value = $parts[1];

            $key = self::cleanString($key);
            $value = self::cleanString($value);

            $ret[$key] = $value;
        }

        return $ret;
    }

    /**
     * Get a list of editor font families.
     *
     * @return string font family list
     *
     * @param string $add    Font family to add
     * @param string $remove Font family to remove
     */
    protected static function getFonts()
    {
        $wf = WFApplication::getInstance();

        $add = $wf->getParam('editor.theme_advanced_fonts_add');
        $remove = $wf->getParam('editor.theme_advanced_fonts_remove');

        // Default font list
        $fonts = self::$fonts;

        if (empty($remove) && empty($add)) {
            return '';
        }

        $remove = preg_split('/[;,]+/', $remove);

        if (count($remove)) {
            foreach ($fonts as $key => $value) {
                foreach ($remove as $gone) {
                    if ($gone && preg_match('/^'.$gone.'=/i', $value)) {
                        // Remove family
                        unset($fonts[$key]);
                    }
                }
            }
        }
        foreach (explode(';', $add) as $new) {
            // Add new font family
            if (preg_match('/([^\=]+)(\=)([^\=]+)/', trim($new)) && !in_array($new, $fonts)) {
                $fonts[] = $new;
            }
        }

        natcasesort($fonts);

        return implode(';', $fonts);
    }
}
