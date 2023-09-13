<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.sef
 *
 * @copyright   (C) 2007 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Sef\Extension;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla! SEF Plugin.
 *
 * @since  1.5
 */
final class Sef extends CMSPlugin
{
    /**
     * Add the canonical uri to the head.
     *
     * @return  void
     *
     * @since   3.5
     */
    public function onAfterDispatch()
    {
        $doc = $this->getApplication()->getDocument();

        if (!$this->getApplication()->isClient('site') || $doc->getType() !== 'html') {
            return;
        }

        $sefDomain = $this->params->get('domain', false);

        // Don't add a canonical html tag if no alternative domain has added in SEF plugin domain field.
        if (empty($sefDomain)) {
            return;
        }

        // Check if a canonical html tag already exists (for instance, added by a component).
        $canonical = '';

        foreach ($doc->_links as $linkUrl => $link) {
            if (isset($link['relation']) && $link['relation'] === 'canonical') {
                $canonical = $linkUrl;
                break;
            }
        }

        // If a canonical html tag already exists get the canonical and change it to use the SEF plugin domain field.
        if (!empty($canonical)) {
            // Remove current canonical link.
            unset($doc->_links[$canonical]);

            // Set the current canonical link but use the SEF system plugin domain field.
            $canonical = $sefDomain . Uri::getInstance($canonical)->toString(['path', 'query', 'fragment']);
        } else {
            // If a canonical html doesn't exists already add a canonical html tag using the SEF plugin domain field.
            $canonical = $sefDomain . Uri::getInstance()->toString(['path', 'query', 'fragment']);
        }

        // Add the canonical link.
        $doc->addHeadLink(htmlspecialchars($canonical), 'canonical');
    }

    /**
     * Convert the site URL to fit to the HTTP request.
     *
     * @return  void
     */
    public function onAfterRender()
    {
        if (!$this->getApplication()->isClient('site')) {
            return;
        }

        // Replace src links.
        $base   = Uri::base(true) . '/';
        $buffer = $this->getApplication()->getBody();

        // For feeds we need to search for the URL with domain.
        $prefix = $this->getApplication()->getDocument()->getType() === 'feed' ? Uri::root() : '';

        // Replace index.php URI by SEF URI.
        if (strpos($buffer, 'href="' . $prefix . 'index.php?') !== false) {
            preg_match_all('#href="' . $prefix . 'index.php\?([^"]+)"#m', $buffer, $matches);

            foreach ($matches[1] as $urlQueryString) {
                $buffer = str_replace(
                    'href="' . $prefix . 'index.php?' . $urlQueryString . '"',
                    'href="' . trim($prefix, '/') . Route::_('index.php?' . $urlQueryString) . '"',
                    $buffer
                );
            }

            $this->checkBuffer($buffer);
        }

        // Check for all unknown protocols (a protocol must contain at least one alphanumeric character followed by a ":").
        $protocols  = '[a-zA-Z0-9\-]+:';
        $attributes = ['href=', 'src=', 'poster='];

        foreach ($attributes as $attribute) {
            if (strpos($buffer, $attribute) !== false) {
                $regex  = '#\s' . $attribute . '"(?!/|' . $protocols . '|\#|\')([^"]*)"#m';
                $buffer = preg_replace($regex, ' ' . $attribute . '"' . $base . '$1"', $buffer);
                $this->checkBuffer($buffer);
            }
        }

        if (strpos($buffer, 'srcset=') !== false) {
            $regex = '#\s+srcset="([^"]+)"#m';

            $buffer = preg_replace_callback(
                $regex,
                function ($match) use ($base, $protocols) {
                    preg_match_all('#(?:[^\s]+)\s*(?:[\d\.]+[wx])?(?:\,\s*)?#i', $match[1], $matches);

                    foreach ($matches[0] as &$src) {
                        $src = preg_replace('#^(?!/|' . $protocols . '|\#|\')(.+)#', $base . '$1', $src);
                    }

                    return ' srcset="' . implode($matches[0]) . '"';
                },
                $buffer
            );

            $this->checkBuffer($buffer);
        }

        // Replace all unknown protocols in javascript window open events.
        if (strpos($buffer, 'window.open(') !== false) {
            $regex  = '#onclick="window.open\(\'(?!/|' . $protocols . '|\#)([^/]+[^\']*?\')#m';
            $buffer = preg_replace($regex, 'onclick="window.open(\'' . $base . '$1', $buffer);
            $this->checkBuffer($buffer);
        }

        // Replace all unknown protocols in onmouseover and onmouseout attributes.
        $attributes = ['onmouseover=', 'onmouseout='];

        foreach ($attributes as $attribute) {
            if (strpos($buffer, $attribute) !== false) {
                $regex  = '#' . $attribute . '"this.src=([\']+)(?!/|' . $protocols . '|\#|\')([^"]+)"#m';
                $buffer = preg_replace($regex, $attribute . '"this.src=$1' . $base . '$2"', $buffer);
                $this->checkBuffer($buffer);
            }
        }

        // Replace all unknown protocols in CSS background image.
        if (strpos($buffer, 'style=') !== false) {
            $regex_url  = '\s*url\s*\(([\'\"]|\&\#0?3[49];)?(?!/|\&\#0?3[49];|' . $protocols . '|\#)([^\)\'\"]+)([\'\"]|\&\#0?3[49];)?\)';
            $regex      = '#style=\s*([\'\"])(.*):' . $regex_url . '#m';
            $buffer     = preg_replace($regex, 'style=$1$2: url($3' . $base . '$4$5)', $buffer);
            $this->checkBuffer($buffer);
        }

        // Replace all unknown protocols in OBJECT param tag.
        if (strpos($buffer, '<param') !== false) {
            // OBJECT <param name="xx", value="yy"> -- fix it only inside the <param> tag.
            $regex  = '#(<param\s+)name\s*=\s*"(movie|src|url)"[^>]\s*value\s*=\s*"(?!/|' . $protocols . '|\#|\')([^"]*)"#m';
            $buffer = preg_replace($regex, '$1name="$2" value="' . $base . '$3"', $buffer);
            $this->checkBuffer($buffer);

            // OBJECT <param value="xx", name="yy"> -- fix it only inside the <param> tag.
            $regex  = '#(<param\s+[^>]*)value\s*=\s*"(?!/|' . $protocols . '|\#|\')([^"]*)"\s*name\s*=\s*"(movie|src|url)"#m';
            $buffer = preg_replace($regex, '<param value="' . $base . '$2" name="$3"', $buffer);
            $this->checkBuffer($buffer);
        }

        // Replace all unknown protocols in OBJECT tag.
        if (strpos($buffer, '<object') !== false) {
            $regex  = '#(<object\s+[^>]*)data\s*=\s*"(?!/|' . $protocols . '|\#|\')([^"]*)"#m';
            $buffer = preg_replace($regex, '$1data="' . $base . '$2"', $buffer);
            $this->checkBuffer($buffer);
        }

        // Use the replaced HTML body.
        $this->getApplication()->setBody($buffer);
    }

    /**
     * Check the buffer.
     *
     * @param   string  $buffer  Buffer to be checked.
     *
     * @return  void
     */
    private function checkBuffer($buffer)
    {
        if ($buffer === null) {
            switch (preg_last_error()) {
                case PREG_BACKTRACK_LIMIT_ERROR:
                    $message = 'PHP regular expression limit reached (pcre.backtrack_limit)';
                    break;
                case PREG_RECURSION_LIMIT_ERROR:
                    $message = 'PHP regular expression limit reached (pcre.recursion_limit)';
                    break;
                case PREG_BAD_UTF8_ERROR:
                    $message = 'Bad UTF8 passed to PCRE function';
                    break;
                default:
                    $message = 'Unknown PCRE error calling PCRE function';
            }

            throw new \RuntimeException($message);
        }
    }
}
