<?php

/**
 * @package        JED
 *
 * @copyright  (C) 2023 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Component\Menus\Administrator\Table\MenuTable;
use Joomla\Component\Menus\Administrator\Table\MenuTypeTable;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')->useScript('form.validate');
HTMLHelper::_('bootstrap.tooltip');
?>
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <p>Clicking GO below will create a new front end menu called jeddemo and populate it with menu links to
                JED Component</p>
        </div>
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-md-12"><a href="<?php
            echo $_SERVER['REQUEST_URI'] . '&task=GO'; ?>"
            <button class="btn btn-primary" type="button">Go</button>
            </a></div>
    </div>
</div>
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <?php
            if ($this->task === "GO") {
                try {
                    $db    = Factory::getContainer()->get('DatabaseDriver');
                    $query = $db->getQuery(true)->select('extension_id')
                                                ->from($db->quoteName('#__extensions'))->where('name = "com_jed"');
                    $db->setQuery($query);
                    $extension_id = $db->loadResult();
                    echo "Extension com_jed found - " . $extension_id . '<br/>';
                    $mtt              = new MenuTypeTable($db);
                    $mtt->menutype    = 'jeddemo';
                    $mtt->title       = 'Joomla Extension Directory Demo Menu';
                    $mtt->description = '';
                    $mtt->client_id   = 0;
                    $mtt->store();
                    echo "<br/>Created jeddemo Menu Type<br/><br/>";
                    $mi          = new MenuTable($db);
                    $menuitems[] = [
                        'title'  => 'Home',
                        'alias'  => 'home',
                        'path'   => 'home',
                        'link'   => 'index.php?option=com_content&view=featured',
                        'params' => '{"layout_type":"blog",'
                            . '"num_leading_articles":1,'
                            . '"blog_class_leading":"",'
                            . '"num_intro_articles":3,'
                            . '"blog_class":"",'
                            . '"num_columns":"",'
                            . '"multi_column_order":"",'
                            . '"num_links":0,'
                            . '"link_intro_image":"",'
                            . '"orderby_pri":"",'
                            . '"orderby_sec":"front",'
                            . '"order_date":"",'
                            . '"show_pagination":"2",'
                            . '"show_pagination_results":"1",'
                            . '"show_title":"",'
                            . '"link_titles":"",'
                            . '"show_intro":"",'
                            . '"info_block_position":"",'
                            . '"info_block_show_title":"",'
                            . '"show_category":"",'
                            . '"link_category":"",'
                            . '"show_parent_category":"",'
                            . '"link_parent_category":"",'
                            . '"show_author":"",'
                            . '"link_author":"",'
                            . '"show_create_date":"",'
                            . '"show_modify_date":"",'
                            . '"show_publish_date":"",'
                            . '"show_item_navigation":"",'
                            . '"show_readmore":"",'
                            . '"show_readmore_title":"",'
                            . '"show_hits":"",'
                            . '"show_tags":"",'
                            . '"show_noauth":"",'
                            . '"show_feed_link":"1",'
                            . '"feed_summary":"",'
                            . '"menu-anchor_title":"",'
                            . '"menu-anchor_css":"",'
                            . '"menu_icon_css":"",'
                            . '"menu_image":"",'
                            . '"menu_image_css":"",'
                            . '"menu_text":1,'
                            . '"menu_show":1,'
                            . '"page_title":"Joomla Extension Directory",'
                            . '"show_page_heading":"0",'
                            . '"page_heading":"",'
                            . '"pageclass_sfx":"",'
                            . '"menu-meta_description":"",'
                            . '"robots":""}'
                    ];
                    $menuitems[] = [
                        'title'  => 'JED Homepage',
                        'alias'  => 'jed-homepage',
                        'path'   => 'jed-homepage',
                        'link'   => 'index.php?option=com_jed&view=homepage',
                        'params' => '{"menu-anchor_title":"",'
                            . '"menu-anchor_css":"",'
                            . '"menu_image":"",'
                            . '"menu_image_css":"",'
                            . '"menu_text":1,'
                            . '"menu_show":1,'
                            . '"page_title":"",'
                            . '"show_page_heading":"",'
                            . '"page_heading":"",'
                            . '"pageclass_sfx":"",'
                            . '"menu-meta_description":"",'
                            . '"robots":""}'
                    ];
                    $menuitems[] = [
                        'title'  => 'Live VEL Items',
                        'alias'  => 'live-vel-items',
                        'path'   => 'jed-homepage/live-vel-items',
                        'link'   => 'index.php?option=com_jed&view=velliveitems',
                        'params' => '{"menu-anchor_title":"",'
                            . '"menu-anchor_css":"",'
                            . '"menu_image":"",'
                            . '"menu_image_css":"",'
                            . '"menu_text":1,'
                            . '"menu_show":1,'
                            . '"page_title":"",'
                            . '"show_page_heading":"",'
                            . '"page_heading":"",'
                            . '"pageclass_sfx":"",'
                            . '"menu-meta_description":"",'
                            . '"robots":""}'
                    ];
                    $menuitems[] = [
                        'title'  => 'Patched VEL Items List',
                        'alias'  => 'patched-vel-items-list',
                        'path'   => 'jed-homepage/patched-vel-items-list',
                        'link'   => 'index.php?option=com_jed&view=velpatcheditems',
                        'params' => '{"menu-anchor_title":"",'
                            . '"menu-anchor_css":"",'
                            . '"menu_image":"",'
                            . '"menu_image_css":"",'
                            . '"menu_text":1,'
                            . '"menu_show":1,'
                            . '"page_title":"",'
                            . '"show_page_heading":"",'
                            . '"page_heading":"",'
                            . '"pageclass_sfx":"",'
                            . '"menu-meta_description":"",'
                            . '"robots":""}'
                    ];
                    $menuitems[] = [
                        'title'  => 'Report a Vulnerable Item',
                        'alias'  => 'report-a-vulnerable-item',
                        'path'   => 'jed-homepage/report-a-vulnerable-item',
                        'link'   => 'index.php?option=com_jed&view=velreportform',
                        'params' => '{"menu-anchor_title":"",'
                            . '"menu-anchor_css":"",'
                            . '"menu_image":"",'
                            . '"menu_image_css":"",'
                            . '"menu_text":1,'
                            . '"menu_show":1,'
                            . '"page_title":"",'
                            . '"show_page_heading":"",'
                            . '"page_heading":"",'
                            . '"pageclass_sfx":"",'
                            . '"menu-meta_description":"",'
                            . '"robots":""}'
                    ];
                    $menuitems[] = [
                        'title'  => 'Show VEL Developer Update Form',
                        'alias'  => 'show-vel-developer-update-form',
                        'path'   => 'jed-homepage/show-vel-developer-update-form',
                        'link'   => 'index.php?option=com_jed&view=veldeveloperupdateform',
                        'params' => '{"menu-anchor_title":"",'
                            . '"menu-anchor_css":"",'
                            . '"menu_image":"",'
                            . '"menu_image_css":"",'
                            . '"menu_text":1,'
                            . '"menu_show":1,'
                            . '"page_title":"",'
                            . '"show_page_heading":"",'
                            . '"page_heading":"",'
                            . '"pageclass_sfx":"",'
                            . '"menu-meta_description":"",'
                            . '"robots":""}'
                    ];
                    $menuitems[] = [
                        'title'  => 'Report an Abandoned Item',
                        'alias'  => 'report-an-abandoned-item',
                        'path'   => 'jed-homepage/report-an-abandoned-item',
                        'link'   => 'index.php?option=com_jed&view=velabandonedreportform',
                        'params' => '{"menu-anchor_title":"",'
                            . '"menu-anchor_css":"",'
                            . '"menu_image":"",'
                            . '"menu_image_css":"",'
                            . '"menu_text":1,'
                            . '"menu_show":1,'
                            . '"page_title":"",'
                            . '"show_page_heading":"",'
                            . '"page_heading":"",'
                            . '"pageclass_sfx":"",'
                            . '"menu-meta_description":"",'
                            . '"robots":""}'
                    ];
                    $menuitems[] = [
                        'title'  => 'Abandoned Items ',
                        'alias'  => 'abandoned-items',
                        'path'   => 'jed-homepage/abandoned-items',
                        'link'   => 'index.php?option=com_jed&view=velabandoneditems',
                        'params' => '{"menu-anchor_title":"",'
                            . '"menu-anchor_css":"",'
                            . '"menu_image":"",'
                            . '"menu_image_css":"",'
                            . '"menu_text":1,'
                            . '"menu_show":1,'
                            . '"page_title":"",'
                            . '"show_page_heading":"",'
                            . '"page_heading":"",'
                            . '"pageclass_sfx":"",'
                            . '"menu-meta_description":"",'
                            . '"robots":""}'
                    ];
                    $menuitems[] = [
                        'title'  => 'My Reported Vulnerable Items',
                        'alias'  => 'my-reported-vulnerable-items',
                        'path'   => 'jed-homepage/my-reported-vulnerable-items',
                        'link'   => 'index.php?option=com_jed&view=velreports',
                        'params' => '{"menu-anchor_title":"",'
                            . '"menu-anchor_css":"",'
                            . '"menu_image":"",'
                            . '"menu_image_css":"",'
                            . '"menu_text":1,'
                            . '"menu_show":1,'
                            . '"page_title":"",'
                            . '"show_page_heading":"",'
                            . '"page_heading":"",'
                            . '"pageclass_sfx":"",'
                            . '"menu-meta_description":"",'
                            . '"robots":""}'
                    ];
                    $menuitems[] = [
                        'title'  => 'My Developer Updates',
                        'alias'  => 'my-developer-updates',
                        'path'   => 'jed-homepage/my-developer-updates',
                        'link'   => 'index.php?option=com_jed&view=veldeveloperupdates',
                        'params' => '{"menu-anchor_title":"",'
                            . '"menu-anchor_css":"",'
                            . '"menu_image":"",'
                            . '"menu_image_css":"",'
                            . '"menu_text":1,'
                            . '"menu_show":1,'
                            . '"page_title":"",'
                            . '"show_page_heading":"",'
                            . '"page_heading":"",'
                            . '"pageclass_sfx":"",'
                            . '"menu-meta_description":"",'
                            . '"robots":""}'
                    ];
                    $menuitems[] = [
                        'title'  => 'My Reported Abandoned Items',
                        'alias'  => 'my-reported-abandoned-items',
                        'path'   => 'jed-homepage/my-reported-abandoned-items',
                        'link'   => 'index.php?option=com_jed&view=velabandonedreports',
                        'params' => '{"menu-anchor_title":"",'
                            . '"menu-anchor_css":"",'
                            . '"menu_image":"",'
                            . '"menu_image_css":"",'
                            . '"menu_text":1,'
                            . '"menu_show":1,'
                            . '"page_title":"",'
                            . '"show_page_heading":"",'
                            . '"page_heading":"",'
                            . '"pageclass_sfx":"",'
                            . '"menu-meta_description":"",'
                            . '"robots":""}'
                    ];
                    $menuitems[] = [
                        'title'  => 'My Tickets',
                        'alias'  => 'my-tickets',
                        'path'   => 'jed-homepage/my-tickets',
                        'link'   => 'index.php?option=com_jed&view=jedtickets',
                        'params' => '{"menu-anchor_title":"",'
                            . '"menu-anchor_css":"",'
                            . '"menu_image":"",'
                            . '"menu_image_css":"",'
                            . '"menu_text":1,'
                            . '"menu_show":1,'
                            . '"page_title":"",'
                            . '"show_page_heading":"",'
                            . '"page_heading":"",'
                            . '"pageclass_sfx":"",'
                            . '"menu-meta_description":"",'
                            . '"robots":""}'
                    ];
                    $menuitems[] = [
                        'title'  => 'Show Extension Categories',
                        'alias'  => 'show-extension-categories',
                        'path'   => 'jed-homepage/show-extension-categories',
                        'link'   => 'index.php?option=com_jed&view=categories&id=0',
                        'params' => '{"show_base_description":"",'
                            . '"categories_description":"",'
                            . '"maxLevelcat":"",'
                            . '"show_empty_categories_cat":"",'
                            . '"show_subcat_desc_cat":"",'
                            . '"show_cat_num_articles_cat":"",'
                            . '"show_category_title":"",'
                            . '"show_description":"",'
                            . '"show_description_image":"",'
                            . '"maxLevel":"",'
                            . '"show_empty_categories":"",'
                            . '"show_no_articles":"",'
                            . '"show_subcat_desc":"",'
                            . '"show_cat_num_articles":"",'
                            . '"num_leading_articles":"",'
                            . '"num_intro_articles":"",'
                            . '"num_columns":"",'
                            . '"num_links":"",'
                            . '"multi_column_order":"",'
                            . '"show_subcategory_content":"",'
                            . '"orderby_pri":"",'
                            . '"orderby_sec":"",'
                            . '"order_date":"",'
                            . '"show_pagination_limit":"",'
                            . '"filter_field":"",'
                            . '"show_headings":"",'
                            . '"list_show_date":"",'
                            . '"date_format":"",'
                            . '"list_show_hits":"",'
                            . '"list_show_author":"",'
                            . '"display_num":"10",'
                            . '"show_pagination":"",'
                            . '"show_pagination_results":"",'
                            . '"show_title":"",'
                            . '"link_titles":"",'
                            . '"show_intro":"",'
                            . '"show_category":"",'
                            . '"link_category":"",'
                            . '"show_parent_category":"",'
                            . '"link_parent_category":"",'
                            . '"show_author":"",'
                            . '"link_author":"",'
                            . '"show_create_date":"",'
                            . '"show_modify_date":"",'
                            . '"show_publish_date":"",'
                            . '"show_item_navigation":"",'
                            . '"show_vote":"",'
                            . '"show_readmore":"",'
                            . '"show_readmore_title":"",'
                            . '"show_icons":"",'
                            . '"show_print_icon":"",'
                            . '"show_email_icon":"",'
                            . '"show_hits":"",'
                            . '"show_noauth":"",'
                            . '"show_feed_link":"",'
                            . '"feed_summary":"",'
                            . '"menu-anchor_title":"",'
                            . '"menu-anchor_css":"",'
                            . '"menu_icon_css":"",'
                            . '"menu_image":"",'
                            . '"menu_image_css":"",'
                            . '"menu_text":1,'
                            . '"menu_show":1,'
                            . '"page_title":"",'
                            . '"show_page_heading":"",'
                            . '"page_heading":"",'
                            . '"pageclass_sfx":"",'
                            . '"menu-meta_description":"",'
                            . '"robots":""}'
                    ];

                    foreach ($menuitems as $m) {
                        $mi               = new MenuTable($db);
                        $mi->menutype     = 'jeddemo';
                        $mi->title        = htmlspecialchars_decode($m['title']);
                        $mi->alias        = $m['alias'] . '-jvp';
                        $mi->path         = $m['path'];
                        $mi->link         = $m['link'];
                        $mi->params       = $m['params'];
                        $mi->type         = "component";
                        $mi->published    = 1;
                        $mi->parent_id    = 1;
                        $mi->client_id    = 0;
                        $mi->level        = 1;
                        $mi->component_id = $extension_id;
                        $mi->setLocation(1, 'last-child');
                        $mi->img               = '';
                        $mi->language          = "*";
                        $mi->note              = '';
                        $mi->browserNav        = 0;
                        $mi->template_style_id = 0;
                        $mi->home              = 0;
                        if ($mi->store()) {
                            echo "Successfully Created Menu Item - " . $m['title'] . '<br/>' . $mi->getError() . '<br/>';
                        } else {
                            echo "Failed to create Menu Item - " . $m['title'] . '<br/>' . $mi->getError() . '<br/>';
                        }
                    }
                } catch (Exception $e) {
                    throw new Exception('Something went really wrong. ' . $e->getMessage(), 500);
                }
            }

            ?>

        </div>
    </div>
</div>
