<?php

/**
 * @copyright     Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license       GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
defined('JPATH_PLATFORM') or die;

require_once JPATH_ADMINISTRATOR . '/components/com_jce/includes/constants.php';

class JceModelCpanel extends JModelLegacy
{
    public function getIcons()
    {
        $user = JFactory::getUser();

        $icons = array();

        $views = array(
            'config' => 'equalizer',
            'profiles' => 'users',
            'browser' => 'picture',
            'mediabox' => 'pictures',
        );

        foreach ($views as $name => $icon) {

            // if its mediabox, check the plugin is installed and enabled
            if ($name === "mediabox" && !JPluginHelper::isEnabled('system', 'jcemediabox')) {
                continue;
            }

            // check if its allowed...
            if (!$user->authorise('jce.' . $name, 'com_jce')) {
                continue;
            }

            $link = 'index.php?option=com_jce&amp;view=' . $name;
            $title = JText::_('WF_' . strtoupper($name));

            if ($name === "browser") {
                $title = JText::_('WF_' . strtoupper($name) . '_TITLE');
            }

            $icons[] = '<li><a title="' . JText::_('WF_' . strtoupper($name) . '_DESC') . '" href="' . $link . '" class="btn btn-default"><div class="quickicon-icon d-flex align-items-end"><span class="fa fa-' . $icon . ' icon-' . $icon . '" aria-hidden="true"></span></div><div class="quickicon-text d-flex align-items-center"><span class="j-links-link">' . $title . '</span></div></a></li>';
        }

        return $icons;
    }

    public function getFeeds()
    {
        $app = JFactory::getApplication();
        $params = JComponentHelper::getParams('com_jce');
        $limit = $params->get('feed_limit', 2);

        $feeds = array();
        $options = array(
            'rssUrl' => 'https://www.joomlacontenteditor.net/news?format=feed',
        );

        $xml = simplexml_load_file($options['rssUrl']);

        if (empty($xml)) {
            return $feeds;
        }

        jimport('joomla.filter.input');
        $filter = JFilterInput::getInstance();

        $count = count($xml->channel->item);

        if ($count) {
            $count = ($count > $limit) ? $limit : $count;

            for ($i = 0; $i < $count; ++$i) {
                $feed = new StdClass();
                $item = $xml->channel->item[$i];

                $link = (string) $item->link;
                $feed->link = htmlspecialchars($filter->clean($link));

                $title = (string) $item->title;
                $feed->title = htmlspecialchars($filter->clean($title));

                $description = (string) $item->description;
                $feed->description = htmlspecialchars($filter->clean($description));

                $feeds[] = $feed;
            }
        }

        return $feeds;
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @since   1.6
     */
    protected function populateState($ordering = null, $direction = null)
    {
        $licence = "";
        $version = "";

        if ($xml = simplexml_load_file(JPATH_ADMINISTRATOR . '/components/com_jce/jce.xml')) {
            $licence = (string) $xml->license;
            $version = (string) $xml->version;

            if (WF_EDITOR_PRO) {
                $version = '<span class="badge badge-info badge-primary">Pro</span>&nbsp;' . $version;
            }
        }

        $this->setState('version', $version);
        $this->setState('licence', $licence);
    }
}
