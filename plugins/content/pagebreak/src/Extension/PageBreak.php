<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Content.pagebreak
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Content\PageBreak\Extension;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Utility\Utility;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomla\String\StringHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Page break plugin
 *
 * <strong>Usage:</strong>
 * <code><hr class="system-pagebreak" /></code>
 * <code><hr class="system-pagebreak" title="The page title" /></code>
 * or
 * <code><hr class="system-pagebreak" alt="The first page" /></code>
 * or
 * <code><hr class="system-pagebreak" title="The page title" alt="The first page" /></code>
 * or
 * <code><hr class="system-pagebreak" alt="The first page" title="The page title" /></code>
 *
 * @since  1.6
 */
final class PageBreak extends CMSPlugin
{
    /**
     * The navigation list with all page objects if parameter 'multipage_toc' is active.
     *
     * @var    array
     * @since  4.0.0
     */
    protected $list = [];

    /**
     * Plugin that adds a pagebreak into the text and truncates text at that point
     *
     * @param   string   $context  The context of the content being passed to the plugin.
     * @param   object   &$row     The article object.  Note $article->text is also available
     * @param   mixed    &$params  The article params
     * @param   integer  $page     The 'page' number
     *
     * @return  void
     *
     * @since   1.6
     */
    public function onContentPrepare($context, &$row, &$params, $page = 0)
    {
        $canProceed = $context === 'com_content.article';

        if (!$canProceed) {
            return;
        }

        $style = $this->params->get('style', 'pages');

        // Expression to search for.
        $regex = '#<hr(.*)class="system-pagebreak"(.*)\/?>#iU';

        $input = $this->getApplication()->getInput();

        $print   = $input->getBool('print');
        $showall = $input->getBool('showall');

        if (!$this->params->get('enabled', 1)) {
            $print = true;
        }

        if ($print) {
            $row->text = preg_replace($regex, '<br>', $row->text);

            return;
        }

        // Simple performance check to determine whether bot should process further.
        if (StringHelper::strpos($row->text, 'class="system-pagebreak') === false) {
            if ($page > 0) {
                throw new \Exception($this->getApplication()->getLanguage()->_('JERROR_PAGE_NOT_FOUND'), 404);
            }

            return;
        }

        $view = $input->getString('view');
        $full = $input->getBool('fullview');

        if (!$page) {
            $page = 0;
        }

        if ($full || $view !== 'article' || $params->get('intro_only') || $params->get('popup')) {
            $row->text = preg_replace($regex, '', $row->text);

            return;
        }

        // Load plugin language files only when needed (ex: not needed if no system-pagebreak class exists).
        $this->loadLanguage();

        // Find all instances of plugin and put in $matches.
        $matches = [];
        preg_match_all($regex, $row->text, $matches, PREG_SET_ORDER);

        if ($showall && $this->params->get('showall', 1)) {
            $hasToc = $this->params->get('multipage_toc', 1);

            if ($hasToc) {
                // Display TOC.
                $page = 1;
                $this->createToc($row, $matches, $page);
            } else {
                $row->toc = '';
            }

            $row->text = preg_replace($regex, '<br>', $row->text);

            return;
        }

        // Split the text around the plugin.
        $text = preg_split($regex, $row->text);

        if (!isset($text[$page])) {
            throw new \Exception($this->getApplication()->getLanguage()->_('JERROR_PAGE_NOT_FOUND'), 404);
        }

        // Count the number of pages.
        $n = count($text);

        // We have found at least one plugin, therefore at least 2 pages.
        if ($n > 1) {
            $title  = $this->params->get('title', 1);
            $hasToc = $this->params->get('multipage_toc', 1);

            // Adds heading or title to <site> Title.
            if ($title && $page && isset($matches[$page - 1][0])) {
                $attrs = Utility::parseAttributes($matches[$page - 1][0]);

                if (isset($attrs['title'])) {
                    $row->page_title = $attrs['title'];
                }
            }

            // Reset the text, we already hold it in the $text array.
            $row->text = '';

            if ($style === 'pages') {
                // Display TOC.
                if ($hasToc) {
                    $this->createToc($row, $matches, $page);
                } else {
                    $row->toc = '';
                }

                // Traditional mos page navigation
                $pageNav = new Pagination($n, $page, 1);

                // Flag indicates to not add limitstart=0 to URL
                $pageNav->hideEmptyLimitstart = true;

                // Page counter.
                $row->text .= '<div class="pagenavcounter">';
                $row->text .= $pageNav->getPagesCounter();
                $row->text .= '</div>';

                // Page text.
                $text[$page] = str_replace('<hr id="system-readmore" />', '', $text[$page]);
                $row->text .= $text[$page];

                // $row->text .= '<br>';
                $row->text .= '<div class="pager">';

                // Adds navigation between pages to bottom of text.
                if ($hasToc) {
                    $this->createNavigation($row, $page, $n);
                }

                // Page links shown at bottom of page if TOC disabled.
                if (!$hasToc) {
                    $row->text .= $pageNav->getPagesLinks();
                }

                $row->text .= '</div>';
            } else {
                $t[] = $text[0];

                if ($style === 'tabs') {
                    $t[] = (string) HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'article' . $row->id . '-' . $style . '0', 'view' => 'tabs']);
                } else {
                    $t[] = (string) HTMLHelper::_('bootstrap.startAccordion', 'myAccordion', ['active' => 'article' . $row->id . '-' . $style . '0']);
                }

                foreach ($text as $key => $subtext) {
                    $index = 'article' . $row->id . '-' . $style . $key;

                    if ($key >= 1) {
                        $match = $matches[$key - 1];
                        $match = (array) Utility::parseAttributes($match[0]);

                        if (isset($match['alt'])) {
                            $title = stripslashes($match['alt']);
                        } elseif (isset($match['title'])) {
                            $title = stripslashes($match['title']);
                        } else {
                            $title = Text::sprintf('PLG_CONTENT_PAGEBREAK_PAGE_NUM', $key + 1);
                        }

                        if ($style === 'tabs') {
                            $t[] = (string) HTMLHelper::_('uitab.addTab', 'myTab', $index, $title);
                        } else {
                            $t[] = (string) HTMLHelper::_('bootstrap.addSlide', 'myAccordion', $title, $index);
                        }

                        $t[] = (string) $subtext;

                        if ($style === 'tabs') {
                            $t[] = (string) HTMLHelper::_('uitab.endTab');
                        } else {
                            $t[] = (string) HTMLHelper::_('bootstrap.endSlide');
                        }
                    }
                }

                if ($style === 'tabs') {
                    $t[] = (string) HTMLHelper::_('uitab.endTabSet');
                } else {
                    $t[] = (string) HTMLHelper::_('bootstrap.endAccordion');
                }

                $row->text = implode(' ', $t);
            }
        }
    }

    /**
     * Creates a Table of Contents for the pagebreak
     *
     * @param   object   &$row      The article object.  Note $article->text is also available
     * @param   array    &$matches  Array of matches of a regex in onContentPrepare
     * @param   integer  &$page     The 'page' number
     *
     * @return  void
     *
     * @since  1.6
     */
    private function createToc(&$row, &$matches, &$page)
    {
        $heading     = $row->title ?? $this->getApplication()->getLanguage()->_('PLG_CONTENT_PAGEBREAK_NO_TITLE');
        $input       = $this->getApplication()->getInput();
        $limitstart  = $input->getUint('limitstart', 0);
        $showall     = $input->getInt('showall', 0);
        $headingtext = '';

        if ($this->params->get('article_index', 1) == 1) {
            $headingtext = $this->getApplication()->getLanguage()->_('PLG_CONTENT_PAGEBREAK_ARTICLE_INDEX');

            if ($this->params->get('article_index_text')) {
                $headingtext = htmlspecialchars($this->params->get('article_index_text'), ENT_QUOTES, 'UTF-8');
            }
        }

        // TOC first Page link.
        $this->list[1]         = new \stdClass();
        $this->list[1]->link   = RouteHelper::getArticleRoute($row->slug, $row->catid, $row->language);
        $this->list[1]->title  = $heading;
        $this->list[1]->active = ($limitstart === 0 && $showall === 0);

        $i = 2;

        foreach ($matches as $bot) {
            if (@$bot[0]) {
                $attrs2 = Utility::parseAttributes($bot[0]);

                if (@$attrs2['alt']) {
                    $title = stripslashes($attrs2['alt']);
                } elseif (@$attrs2['title']) {
                    $title = stripslashes($attrs2['title']);
                } else {
                    $title = Text::sprintf('PLG_CONTENT_PAGEBREAK_PAGE_NUM', $i);
                }
            } else {
                $title = Text::sprintf('PLG_CONTENT_PAGEBREAK_PAGE_NUM', $i);
            }

            $this->list[$i]         = new \stdClass();
            $this->list[$i]->link   = RouteHelper::getArticleRoute($row->slug, $row->catid, $row->language) . '&limitstart=' . ($i - 1);
            $this->list[$i]->title  = $title;
            $this->list[$i]->active = ($limitstart === $i - 1);

            $i++;
        }

        if ($this->params->get('showall')) {
            $this->list[$i]         = new \stdClass();
            $this->list[$i]->link   = RouteHelper::getArticleRoute($row->slug, $row->catid, $row->language) . '&showall=1';
            $this->list[$i]->title  = $this->getApplication()->getLanguage()->_('PLG_CONTENT_PAGEBREAK_ALL_PAGES');
            $this->list[$i]->active = ($limitstart === $i - 1);
        }

        $list = $this->list;
        $path = PluginHelper::getLayoutPath('content', 'pagebreak', 'toc');
        ob_start();
        include $path;
        $row->toc = ob_get_clean();
    }

    /**
     * Creates the navigation for the item
     *
     * @param   object  &$row  The article object.  Note $article->text is also available
     * @param   int     $page  The page number
     * @param   int     $n     The total number of pages
     *
     * @return  void
     *
     * @since   1.6
     */
    private function createNavigation(&$row, $page, $n)
    {
        $links = [
            'next'     => '',
            'previous' => '',
        ];

        if ($page < $n - 1) {
            $links['next'] = RouteHelper::getArticleRoute($row->slug, $row->catid, $row->language) . '&limitstart=' . ($page + 1);
        }

        if ($page > 0) {
            $links['previous'] = RouteHelper::getArticleRoute($row->slug, $row->catid, $row->language);

            if ($page > 1) {
                $links['previous'] .= '&limitstart=' . ($page - 1);
            }
        }

        $path = PluginHelper::getLayoutPath('content', 'pagebreak', 'navigation');
        ob_start();
        include $path;
        $row->text .= ob_get_clean();
    }
}
