<?php

/**
 * @package    JED
 *
 * @copyright  (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Jed\Component\Jed\Site\View\Categories;

// No direct access
// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Pagination\Pagination;

/**
 * View class for a list of Categories.
 *
 * @since  4.0.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * An array of items
     *
     * @var  array
     *
     * @since 4.0.0
     */
    protected array $items;

    /**
     * The pagination object
     *
     * @var  Pagination
     *
     * @since 4.0.0
     */
    protected Pagination $pagination;

    /**
     * The model state
     *
     * @var  CMSObject
     *
     * @since 4.0.0
     */
    protected CMSObject $state;

    /**
     * The components parameters
     *
     * @var  object
     *
     * @since 4.0.0
     */
    protected mixed $params;

    /**
     * Prepares the document
     *
     * @return void
     *
     * @since 4.0.0
     * @throws Exception
     *
     */
    protected function prepareDocument()
    {
        $app   = Factory::getApplication();
        $menus = $app->getMenu();


        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menu = $menus->getActive();

        if ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        } else {
            $this->params->def('page_heading', Text::_('COM_JED_DEFAULT_PAGE_TITLE'));
        }

        $title = $this->params->get('page_title', '');

        if (empty($title)) {
            $title = $app->get('sitename');
        } elseif ($app->get('sitename_pagetitles', 0) == 1) {
            $title = Text::sprintf('JPAGETITLE', $app->get('sitename'), $title);
        } elseif ($app->get('sitename_pagetitles', 0) == 2) {
            $title = Text::sprintf('JPAGETITLE', $title, $app->get('sitename'));
        }

        $this->document->setTitle($title);

        if ($this->params->get('menu-meta_description')) {
            $this->document->setDescription($this->params->get('menu-meta_description'));
        }

        if ($this->params->get('menu-meta_keywords')) {
            $this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
        }

        if ($this->params->get('robots')) {
            $this->document->setMetadata('robots', $this->params->get('robots'));
        }
    }

    /**
     * Display the view
     *
     * @param   string  $tpl  Template name
     *
     * @return void
     *
     * @since 4.0.0
     * @throws Exception
     *
     */
    public function display($tpl = null)
    {
        $app = Factory::getApplication();
        //$this->items = $this->get('Items');
        //       var_dump($this->items);exit();

        /*  $categories = Categories::getInstance('jed',array("access" => false, "countitems"=>1, "table" => "#_jed_extensions", "field" => "category_id"));

           // $categories = Categories::getInstance("jed",array("countitems"=>1, "table" => "jed_extensions", "field" => "category_id"));


            $cat0 = $categories->get('root');

            $cats = $cat0->getChildren(true);
            $catlist = array();
            $counter = 0;
            foreach ($cats as $cat)
            {


                print_r($cat);exit();
                $ncat = null;
                $ncat->id = $cat->id;
                $ncat->title = $cat->title;
                $ncat->alias = $cat->alias;
                $ncat->childrennumitems = $cat->childrennumitems;
                $ncat->level = $cat->level;
                if($cat->parent_id == 'root')
                {
                    $cat->parent_id=0;
                }
                $ncat->parent_id = $cat->parent_id;
                print_r($ncat);echo "<BR />";
                if($cat->level === 1) {

                    $catlist[$cat->id][0] = $ncat;

                }
                else
                {
                    $catlist[$cat->parent_id][$ncat->id] = $ncat;
                }


            }
        //  print_r($catlist);exit();*/
        $this->state      = $this->get('State');
        $this->items      = $this->get('Items');
        //  $this->pagination = $this->get('Pagination');
        $this->params     = $app->getParams('com_jed');


        // Check for errors.
        /*  if (count($errors = $this->get('Errors')))
            {
                throw new Exception(implode("\n", $errors));
            }*/

        $this->prepareDocument();
        parent::display($tpl);
    }

    /**
     * Check if state is set
     *
     * @param   mixed  $state  State
     *
     * @return bool
     *
     * @since 4.0.0
     */
    public function getState($state): bool
    {
        return $this->state->{$state} ?? false;
    }
}
