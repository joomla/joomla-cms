<?php

/** @package    Joomla.Site
 * @subpackage  com_tags
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Tags\Site\View\Tags;

use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Document\Feed\FeedItem;
use Joomla\CMS\Document\Feed\FeedEnclosure;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\Component\Tags\Site\Model\TagsModel;
use Joomla\CMS\Uri\Uri;

\defined('_JEXEC') or die;
/* HTML View class for the Tags component all tags view
 * @since  3.1  */

class FeedView extends BaseHtmlView 
{
    /** Execute and display a template script.
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     * @return  void */
    
     public function display($tpl = null)
    {
        $app                        = Factory::getApplication();
        $siteName                   = $app->get('sitename');
        $categories                 = Categories::getInstance('Content');
        $this->getDocument()->link  = Route::_('index.php?option=com_tags&view=tags');
        $params                     = $app->getParams();

        if ($params->get('show_feed_link', 1) == 0) {
            throw new \Exception(Text::_('JGLOBAL_RESOURCE_NOT_FOUND'), 404);
        }
        
        $app->getInput()->set('limit', $app->get('feed_limit'));
        $siteEmail = $app->get('mailfrom');
        $fromName  = $app->get('fromname');
        $feedEmail = $app->get('feed_email', 'none');

        $this->getDocument()->editor = $fromName;

        if ($feedEmail !== 'none') $this->getDocument()->editorEmail = $siteEmail;

        /** @var TagModel $model */
        $model = $this->getModel();
        $items = $model->getItems();
        $tagTitle = $model->getName();

        if ($items !== false) {
            foreach ($items as $item) {
                // Load individual item creator class
                $feeditem = new FeedItem();
                $feeditem->title = html_entity_decode($this->escape($item->core_title), ENT_COMPAT, 'UTF-8');
                $feeditem->link = Route::_($item->link);
                $feeditem->description = $item->core_body;
                $feeditem->category = $item->core_category_title;
                $feeditem->author = $item->core_created_by_alias ? : $item->author;
                $feeditem->date = ($item->displayDate ? date('r', strtotime($item->displayDate)) : '');

                $images = json_decode($item->core_images, false);
                if (!empty($images->image_intro)) {
                    if (preg_match('/^([^#]*)/', $images->image_intro, $matches)) $url_img = $matches[1];
                    else $url_img = $images->image_intro;

                    $lastDotPos = strrpos($url_img, '.');
                    if ($lastDotPos !== false) {
                        $extension = substr($url_img, $lastDotPos + 1);
                        $extension = mb_strtolower($extension);
                    } else $extension = '-';

                    // Use of Joomla FeedEnclosure class for items intro images
                    $feedEnclosure = new FeedEnclosure();
                    $feedEnclosure->url = Uri::root() . $url_img;
                    $feedEnclosure->length = filesize($url_img);
                    $feedEnclosure->type = 'image/' . $extension;
                    $feeditem->enclosure = $feedEnclosure;
                }

                if ($feedEmail === 'site') $feeditem->authorEmail = $siteEmail;
                if ($feedEmail === 'author') $feeditem->authorEmail = $item->email;

                $this->getDocument()->addItem($feeditem);
            }
        }
    }
}
