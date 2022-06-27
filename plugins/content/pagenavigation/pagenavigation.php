<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Content.pagenavigation
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt

 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Access\Access;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomla\Database\ParameterType;

/**
 * Pagenavigation plugin class.
 *
 * @since  1.5
 */
class PlgContentPagenavigation extends CMSPlugin
{
    /**
     * If in the article view and the parameter is enabled shows the page navigation
     *
     * @param   string   $context  The context of the content being passed to the plugin
     * @param   object   &$row     The article object
     * @param   mixed    &$params  The article params
     * @param   integer  $page     The 'page' number
     *
     * @return  mixed  void or true
     *
     * @since   1.6
     */
    public function onContentBeforeDisplay($context, &$row, &$params, $page = 0)
    {
        $app   = Factory::getApplication();
        $view  = $app->input->get('view');
        $print = $app->input->getBool('print');

        if ($print) {
            return false;
        }

        if ($context === 'com_content.article' && $view === 'article' && $params->get('show_item_navigation')) {
            $db         = Factory::getDbo();
            $user       = Factory::getUser();
            $lang       = Factory::getLanguage();
            $now        = Factory::getDate()->toSql();
            $query      = $db->getQuery(true);
            $uid        = $row->id;
            $option     = 'com_content';
            $canPublish = $user->authorise('core.edit.state', $option . '.article.' . $row->id);

            /**
             * The following is needed as different menu items types utilise a different param to control ordering.
             * For Blogs the `orderby_sec` param is the order controlling param.
             * For Table and List views it is the `orderby` param.
             */
            $params_list = $params->toArray();

            if (array_key_exists('orderby_sec', $params_list)) {
                $order_method = $params->get('orderby_sec', '');
            } else {
                $order_method = $params->get('orderby', '');
            }

            // Additional check for invalid sort ordering.
            if ($order_method === 'front') {
                $order_method = '';
            }

            if (in_array($order_method, ['date', 'rdate'])) {
                // Get the order code
                $orderDate = $params->get('order_date');

                switch ($orderDate) {
                    // Use created if modified is not set
                    case 'modified':
                        $orderby = 'CASE WHEN ' . $db->quoteName('a.modified') . ' IS NULL THEN ' .
                            $db->quoteName('a.created') . ' ELSE ' . $db->quoteName('a.modified') . ' END';
                        break;

                    // Use created if publish_up is not set
                    case 'published':
                        $orderby = 'CASE WHEN ' . $db->quoteName('a.publish_up') . ' IS NULL THEN ' .
                            $db->quoteName('a.created') . ' ELSE ' . $db->quoteName('a.publish_up') . ' END';
                        break;

                    // Use created as default
                    default:
                        $orderby = $db->quoteName('a.created');
                        break;
                }

                if ($order_method === 'rdate') {
                    $orderby .= ' DESC';
                }
            } else {
                // Determine sort order.
                switch ($order_method) {
                    case 'alpha':
                        $orderby = $db->quoteName('a.title');
                        break;
                    case 'ralpha':
                        $orderby = $db->quoteName('a.title') . ' DESC';
                        break;
                    case 'hits':
                        $orderby = $db->quoteName('a.hits');
                        break;
                    case 'rhits':
                        $orderby = $db->quoteName('a.hits') . ' DESC';
                        break;
                    case 'author':
                        $orderby = $db->quoteName(['a.created_by_alias', 'u.name']);
                        break;
                    case 'rauthor':
                        $orderby = $db->quoteName('a.created_by_alias') . ' DESC, ' .
                            $db->quoteName('u.name') . ' DESC';
                        break;
                    case 'front':
                        $orderby = $db->quoteName('f.ordering');
                        break;
                    default:
                        $orderby = $db->quoteName('a.ordering');
                        break;
                }
            }

            $query->order($orderby);

            $case_when = ' CASE WHEN ' . $query->charLength($db->quoteName('a.alias'), '!=', '0')
                . ' THEN ' . $query->concatenate([$query->castAsChar($db->quoteName('a.id')), $db->quoteName('a.alias')], ':')
                . ' ELSE ' . $query->castAsChar('a.id') . ' END AS ' . $db->quoteName('slug');

            $case_when1 = ' CASE WHEN ' . $query->charLength($db->quoteName('cc.alias'), '!=', '0')
                . ' THEN ' . $query->concatenate([$query->castAsChar($db->quoteName('cc.id')), $db->quoteName('cc.alias')], ':')
                . ' ELSE ' . $query->castAsChar('cc.id') . ' END AS ' . $db->quoteName('catslug');

            $query->select($db->quoteName(['a.id', 'a.title', 'a.catid', 'a.language']))
                ->select([$case_when, $case_when1])
                ->from($db->quoteName('#__content', 'a'))
                ->join('LEFT', $db->quoteName('#__categories', 'cc'), $db->quoteName('cc.id') . ' = ' . $db->quoteName('a.catid'));

            if ($order_method === 'author' || $order_method === 'rauthor') {
                $query->select($db->quoteName(['a.created_by', 'u.name']));
                $query->join('LEFT', $db->quoteName('#__users', 'u'), $db->quoteName('u.id') . ' = ' . $db->quoteName('a.created_by'));
            }

            $query->where(
                [
                    $db->quoteName('a.catid') . ' = :catid',
                    $db->quoteName('a.state') . ' = :state',
                ]
            )
                ->bind(':catid', $row->catid, ParameterType::INTEGER)
                ->bind(':state', $row->state, ParameterType::INTEGER);

            if (!$canPublish) {
                $query->whereIn($db->quoteName('a.access'), Access::getAuthorisedViewLevels($user->id));
            }

            $query->where(
                [
                    '(' . $db->quoteName('publish_up') . ' IS NULL OR ' . $db->quoteName('publish_up') . ' <= :nowDate1)',
                    '(' . $db->quoteName('publish_down') . ' IS NULL OR ' . $db->quoteName('publish_down') . ' >= :nowDate2)',
                ]
            )
                ->bind(':nowDate1', $now)
                ->bind(':nowDate2', $now);

            if ($app->isClient('site') && $app->getLanguageFilter()) {
                $query->whereIn($db->quoteName('a.language'), [$lang->getTag(), '*'], ParameterType::STRING);
            }

            $db->setQuery($query);
            $list = $db->loadObjectList('id');

            // This check needed if incorrect Itemid is given resulting in an incorrect result.
            if (!is_array($list)) {
                $list = [];
            }

            reset($list);

            // Location of current content item in array list.
            $location = array_search($uid, array_keys($list));
            $rows     = array_values($list);

            $row->prev = null;
            $row->next = null;

            if ($location - 1 >= 0) {
                // The previous content item cannot be in the array position -1.
                $row->prev = $rows[$location - 1];
            }

            if (($location + 1) < count($rows)) {
                // The next content item cannot be in an array position greater than the number of array positions.
                $row->next = $rows[$location + 1];
            }

            if ($row->prev) {
                $row->prev_label = ($this->params->get('display', 0) == 0) ? Text::_('JPREV') : $row->prev->title;
                $row->prev = RouteHelper::getArticleRoute($row->prev->slug, $row->prev->catid, $row->prev->language);
            } else {
                $row->prev_label = '';
                $row->prev = '';
            }

            if ($row->next) {
                $row->next_label = ($this->params->get('display', 0) == 0) ? Text::_('JNEXT') : $row->next->title;
                $row->next = RouteHelper::getArticleRoute($row->next->slug, $row->next->catid, $row->next->language);
            } else {
                $row->next_label = '';
                $row->next = '';
            }

            // Output.
            if ($row->prev || $row->next) {
                // Get the path for the layout file
                $path = PluginHelper::getLayoutPath('content', 'pagenavigation');

                // Render the pagenav
                ob_start();
                include $path;
                $row->pagination = ob_get_clean();

                $row->paginationposition = $this->params->get('position', 1);

                // This will default to the 1.5 and 1.6-1.7 behavior.
                $row->paginationrelative = $this->params->get('relative', 0);
            }
        }
    }
}
