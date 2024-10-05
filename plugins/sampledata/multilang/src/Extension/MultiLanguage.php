<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Sampledata.multilang
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\SampleData\MultiLanguage\Extension;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Event\Plugin\AjaxEvent;
use Joomla\CMS\Event\SampleData\GetOverviewEvent;
use Joomla\CMS\Extension\ExtensionHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Language\Language;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Workflow\Workflow;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\Exception\ExecutionFailureException;
use Joomla\Database\ParameterType;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Sampledata - Multilang Plugin
 *
 * @since  4.0.0
 */
final class MultiLanguage extends CMSPlugin implements SubscriberInterface
{
    use DatabaseAwareTrait;

    /**
     * Affects constructor behavior. If true, language files will be loaded automatically.
     *
     * @var     boolean
     *
     * @since   4.0.0
     */
    protected $autoloadLanguage = true;

    /**
     * @var     string
     *
     * @since   4.0.0
     */
    protected $path = null;

    /**
     * @var    integer Id, author of all generated content.
     *
     * @since   4.0.0
     */
    protected $adminId;

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since __DEPLOY_VERSION__
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onSampledataGetOverview'    => 'onSampledataGetOverview',
            'onAjaxSampledataApplyStep1' => 'onAjaxSampledataApplyStep1',
            'onAjaxSampledataApplyStep2' => 'onAjaxSampledataApplyStep2',
            'onAjaxSampledataApplyStep3' => 'onAjaxSampledataApplyStep3',
            'onAjaxSampledataApplyStep4' => 'onAjaxSampledataApplyStep4',
            'onAjaxSampledataApplyStep5' => 'onAjaxSampledataApplyStep5',
            'onAjaxSampledataApplyStep6' => 'onAjaxSampledataApplyStep6',
            'onAjaxSampledataApplyStep7' => 'onAjaxSampledataApplyStep7',
            'onAjaxSampledataApplyStep8' => 'onAjaxSampledataApplyStep8',
        ];
    }

    /**
     * Get an overview of the proposed sampledata.
     *
     * @param  GetOverviewEvent $event Event instance
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function onSampledataGetOverview(GetOverviewEvent $event): void
    {
        if (!$this->getApplication()->getIdentity()->authorise('core.create', 'com_content')) {
            return;
        }

        $data              = new \stdClass();
        $data->name        = $this->_name;
        $data->title       = $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_MULTILANG_OVERVIEW_TITLE');
        $data->description = $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_MULTILANG_OVERVIEW_DESC');
        $data->icon        = 'wifi';
        $data->steps       = 8;

        $event->addResult($data);
    }

    /**
     * First step to enable the Language filter plugin.
     *
     * @param AjaxEvent $event Event instance
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function onAjaxSampledataApplyStep1(AjaxEvent $event): void
    {
        if (!Session::checkToken('get') || $this->getApplication()->getInput()->get('type') != $this->_name) {
            return;
        }

        $languages = LanguageHelper::getContentLanguages([0, 1]);

        if (\count($languages) < 2) {
            $response            = [];
            $response['success'] = false;
            $response['message'] = $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_MULTILANG_MISSING_LANGUAGE');

            $event->addResult($response);
            return;
        }

        if (!$this->enablePlugin('plg_system_languagefilter')) {
            $response            = [];
            $response['success'] = false;

            $lang = $this->getApplication()->getLanguage();
            $lang->load('plg_system_languagefilter', JPATH_ADMINISTRATOR);
            $message = $lang->_('PLG_SYSTEM_LANGUAGEFILTER');

            $response['message'] = Text::sprintf('PLG_SAMPLEDATA_MULTILANG_ERROR_LANGFILTER', 2, $message);

            $event->addResult($response);
            return;
        }

        $response            = [];
        $response['success'] = true;
        $response['message'] = $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_MULTILANG_STEP1_SUCCESS');

        $event->addResult($response);
    }

    /**
     * Second step to add a language switcher module
     *
     * @param AjaxEvent $event Event instance
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function onAjaxSampledataApplyStep2(AjaxEvent $event): void
    {
        if (!Session::checkToken('get') || $this->getApplication()->getInput()->get('type') != $this->_name) {
            return;
        }

        if (!ComponentHelper::isEnabled('com_modules') || !$this->getApplication()->getIdentity()->authorise('core.create', 'com_modules')) {
            $response            = [];
            $response['success'] = true;
            $response['message'] = Text::sprintf('PLG_SAMPLEDATA_MULTILANG_STEP_SKIPPED', 2, 'com_modules');

            $event->addResult($response);
            return;
        }

        if (!$this->addModuleLanguageSwitcher()) {
            $response            = [];
            $response['success'] = false;

            $lang = $this->getApplication()->getLanguage();
            $lang->load('mod_languages', JPATH_SITE);
            $message = $lang->_('MOD_LANGUAGES');

            $response['message'] = Text::sprintf('PLG_SAMPLEDATA_MULTILANG_ERROR_SWITCHER', 2, $message);

            $event->addResult($response);
            return;
        }

        $response            = [];
        $response['success'] = true;
        $response['message'] = $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_MULTILANG_STEP2_SUCCESS');

        $event->addResult($response);
    }

    /**
     * Third step to make sure all content languages are published
     *
     * @param AjaxEvent $event Event instance
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function onAjaxSampledataApplyStep3(AjaxEvent $event): void
    {
        if (!Session::checkToken('get') || $this->getApplication()->getInput()->get('type') != $this->_name) {
            return;
        }

        if (!ComponentHelper::isEnabled('com_languages')) {
            $response            = [];
            $response['success'] = true;
            $response['message'] = Text::sprintf('PLG_SAMPLEDATA_MULTILANG_STEP_SKIPPED', 3, 'com_languages');

            $event->addResult($response);
            return;
        }

        if (!$this->publishContentLanguages()) {
            $response            = [];
            $response['success'] = false;
            $response['message'] = Text::sprintf('PLG_SAMPLEDATA_MULTILANG_ERROR_CONTENTLANGUAGES', 3);

            $event->addResult($response);
            return;
        }

        $response            = [];
        $response['success'] = true;
        $response['message'] = $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_MULTILANG_STEP3_SUCCESS');

        $event->addResult($response);
    }

    /**
     * Fourth step to create Menus and list all categories menu items
     *
     * @param AjaxEvent $event Event instance
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function onAjaxSampledataApplyStep4(AjaxEvent $event): void
    {
        if (!Session::checkToken('get') || $this->getApplication()->getInput()->get('type') != $this->_name) {
            return;
        }

        if (!ComponentHelper::isEnabled('com_menus') || !$this->getApplication()->getIdentity()->authorise('core.create', 'com_menus')) {
            $response            = [];
            $response['success'] = true;
            $response['message'] = Text::sprintf('PLG_SAMPLEDATA_MULTILANG_STEP_SKIPPED', 4, 'com_menus');

            $event->addResult($response);
            return;
        }

        $siteLanguages = $this->getInstalledlangsFrontend();

        foreach ($siteLanguages as $siteLang) {
            if (!$this->addMenuGroup($siteLang)) {
                $response            = [];
                $response['success'] = false;
                $response['message'] = Text::sprintf('PLG_SAMPLEDATA_MULTILANG_ERROR_MENUS', 4, $siteLang->language);

                $event->addResult($response);
                return;
            }

            if (!$tableMenuItem = $this->addAllCategoriesMenuItem($siteLang)) {
                $response            = [];
                $response['success'] = false;
                $response['message'] = Text::sprintf('PLG_SAMPLEDATA_MULTILANG_ERROR_ALLCATEGORIES', 4, $siteLang->language);

                $event->addResult($response);
                return;
            }

            $groupedAssociations['com_menus.item'][$siteLang->language] = $tableMenuItem->id;
        }

        if (!$this->addAssociations($groupedAssociations)) {
            $response            = [];
            $response['success'] = false;
            $response['message'] = Text::sprintf('PLG_SAMPLEDATA_MULTILANG_ERROR_ASSOC_ALLCATEGORIES', 4);

            $event->addResult($response);
            return;
        }

        $response            = [];
        $response['success'] = true;
        $response['message'] = $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_MULTILANG_STEP4_SUCCESS');

        $event->addResult($response);
    }

    /**
     * Fifth step to add menu modules
     *
     * @param AjaxEvent $event Event instance
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function onAjaxSampledataApplyStep5(AjaxEvent $event): void
    {
        if (!Session::checkToken('get') || $this->getApplication()->getInput()->get('type') != $this->_name) {
            return;
        }

        if (!ComponentHelper::isEnabled('com_modules') || !$this->getApplication()->getIdentity()->authorise('core.create', 'com_modules')) {
            $response            = [];
            $response['success'] = true;
            $response['message'] = Text::sprintf('PLG_SAMPLEDATA_MULTILANG_STEP_SKIPPED', 5, 'com_modules');

            $event->addResult($response);
            return;
        }

        $siteLanguages = $this->getInstalledlangsFrontend();

        foreach ($siteLanguages as $siteLang) {
            if (!$this->addModuleMenu($siteLang)) {
                $response            = [];
                $response['success'] = false;
                $response['message'] = Text::sprintf('PLG_SAMPLEDATA_MULTILANG_ERROR_MENUMODULES', 5, $siteLang->language);

                $event->addResult($response);
                return;
            }
        }

        $response            = [];
        $response['success'] = true;
        $response['message'] = $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_MULTILANG_STEP5_SUCCESS');

        $event->addResult($response);
    }

    /**
     * Sixth step to add workflow, categories, articles and blog menu items
     *
     * @param AjaxEvent $event Event instance
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function onAjaxSampledataApplyStep6(AjaxEvent $event): void
    {
        if (!Session::checkToken('get') || $this->getApplication()->getInput()->get('type') != $this->_name) {
            return;
        }

        if (!ComponentHelper::isEnabled('com_content') || !$this->getApplication()->getIdentity()->authorise('core.create', 'com_content')) {
            $response            = [];
            $response['success'] = true;
            $response['message'] = Text::sprintf('PLG_SAMPLEDATA_MULTILANG_STEP_SKIPPED', 6, 'com_content');

            $event->addResult($response);
            return;
        }

        if (!ComponentHelper::isEnabled('com_categories') || !$this->getApplication()->getIdentity()->authorise('core.create', 'com_content.category')) {
            $response            = [];
            $response['success'] = true;
            $response['message'] = Text::sprintf('PLG_SAMPLEDATA_MULTILANG_STEP_SKIPPED', 6, 'com_categories');

            $event->addResult($response);
            return;
        }

        $siteLanguages = $this->getInstalledlangsFrontend();

        ComponentHelper::getParams('com_content')->set('workflow_enabled', 0);

        foreach ($siteLanguages as $siteLang) {
            if (!$tableCategory = $this->addCategory($siteLang)) {
                $response            = [];
                $response['success'] = false;
                $response['message'] = Text::sprintf('PLG_SAMPLEDATA_MULTILANG_ERROR_CATEGORY', 6, $siteLang->language);

                $event->addResult($response);
                return;
            }

            $groupedAssociations['com_categories.item'][$siteLang->language] = $tableCategory->id;

            if (!$tableArticle = $this->addArticle($siteLang, $tableCategory->id)) {
                $response            = [];
                $response['success'] = false;
                $response['message'] = Text::sprintf('PLG_SAMPLEDATA_MULTILANG_ERROR_ARTICLE', 6, $siteLang->language);

                $event->addResult($response);
                return;
            }

            $groupedAssociations['com_content.item'][$siteLang->language] = $tableArticle->id;

            if (!$tableMenuItem = $this->addBlogMenuItem($siteLang, $tableCategory->id)) {
                $response            = [];
                $response['success'] = false;
                $response['message'] = Text::sprintf('PLG_SAMPLEDATA_MULTILANG_ERROR_BLOG', 6, $siteLang->language);

                $event->addResult($response);
                return;
            }

            $groupedAssociations['com_menus.item'][$siteLang->language] = $tableMenuItem->id;
        }

        if (!$this->addAssociations($groupedAssociations)) {
            $response            = [];
            $response['success'] = false;
            $response['message'] = Text::sprintf('PLG_SAMPLEDATA_MULTILANG_ERROR_ASSOC_VARIOUS', 6);

            $event->addResult($response);
            return;
        }

        $response            = [];
        $response['success'] = true;
        $response['message'] = $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_MULTILANG_STEP6_SUCCESS');

        $event->addResult($response);
    }

    /**
     * Seventh step to disable the mainmenu module whose home page is set to All languages.
     *
     * @param AjaxEvent $event Event instance
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function onAjaxSampledataApplyStep7(AjaxEvent $event): void
    {
        if (!Session::checkToken('get') || $this->getApplication()->getInput()->get('type') != $this->_name) {
            return;
        }

        if (!ComponentHelper::isEnabled('com_modules')) {
            $response            = [];
            $response['success'] = true;
            $response['message'] = Text::sprintf('PLG_SAMPLEDATA_MULTILANG_STEP_SKIPPED', 7, 'com_modules');

            $event->addResult($response);
            return;
        }

        if (!$this->disableModuleMainMenu()) {
            $response            = [];
            $response['success'] = false;
            $response['message'] = Text::sprintf('PLG_SAMPLEDATA_MULTILANG_ERROR_MAINMENU_MODULE', 7);

            $event->addResult($response);
            return;
        }

        $response            = [];
        $response['success'] = true;
        $response['message'] = $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_MULTILANG_STEP7_SUCCESS');

        $event->addResult($response);
    }

    /**
     * Final step to show completion of sampledata.
     *
     * @param AjaxEvent $event Event instance
     *
     * @return  void
     *
     * @since  4.0.0
     */
    public function onAjaxSampledataApplyStep8(AjaxEvent $event): void
    {
        if ($this->getApplication()->getInput()->get('type') !== $this->_name) {
            return;
        }

        $response['success'] = true;
        $response['message'] = $this->getApplication()->getLanguage()->_('PLG_SAMPLEDATA_MULTILANG_STEP8_SUCCESS');

        $event->addResult($response);
    }

    /**
     * Enable a Joomla plugin.
     *
     * @param   string  $pluginName  The name of plugin.
     *
     * @return  boolean
     *
     * @since   4.0.0
     */
    private function enablePlugin($pluginName)
    {
        // Create a new db object.
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        $query
            ->update($db->quoteName('#__extensions'))
            ->set($db->quoteName('enabled') . ' = 1')
            ->where($db->quoteName('name') . ' = :pluginname')
            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
            ->bind(':pluginname', $pluginName);

        $db->setQuery($query);

        try {
            $db->execute();
        } catch (ExecutionFailureException $e) {
            return false;
        }

        // Store language filter plugin parameters.
        if ($pluginName == 'plg_system_languagefilter') {
            $params = '{'
                . '"detect_browser":"0",'
                . '"automatic_change":"1",'
                . '"item_associations":"1",'
                . '"remove_default_prefix":"0",'
                . '"lang_cookie":"0",'
                . '"alternate_meta":"1"'
                . '}';
            $query
                ->clear()
                ->update($db->quoteName('#__extensions'))
                ->set($db->quoteName('params') . ' = :params')
                ->where($db->quoteName('name') . ' = ' . $db->quote('plg_system_languagefilter'))
                ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
                ->bind(':params', $params);

            $db->setQuery($query);

            try {
                $db->execute();
            } catch (ExecutionFailureException $e) {
                return false;
            }
        }

        return true;
    }

    /**
     * Disable Default Main Menu Module.
     *
     * @return  boolean
     *
     * @since   4.0.0
     */
    private function disableModuleMainMenu()
    {
        // Create a new db object.
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        // Disable main menu module with Home set to ALL languages.
        $query
            ->update($db->quoteName('#__modules'))
            ->set($db->quoteName('published') . ' = 0')
            ->where(
                [
                    $db->quoteName('client_id') . ' = 0',
                    $db->quoteName('module') . ' = ' . $db->quote('mod_menu'),
                    $db->quoteName('language') . ' = ' . $db->quote('*'),
                    $db->quoteName('position') . ' = ' . $db->quote('sidebar-right'),
                ]
            );
        $db->setQuery($query);

        try {
            $db->execute();
        } catch (ExecutionFailureException $e) {
            return false;
        }

        return true;
    }

    /**
     * Enable the Language Switcher Module.
     *
     * @return  boolean
     *
     * @since   4.0.0
     */
    private function addModuleLanguageSwitcher()
    {
        $tableModule = Table::getInstance('Module', 'Joomla\\CMS\\Table\\');

        $moduleData  = [
            'id'        => 0,
            'title'     => 'Language Switcher',
            'note'      => '',
            'content'   => '',
            'position'  => 'sidebar-right',
            'module'    => 'mod_languages',
            'access'    => 1,
            'showtitle' => 0,
            'params'    => '{"header_text":"","footer_text":"","dropdown":"0","image":"1","inline":"1","show_active":"1",'
                . '"full_name":"1","layout":"_:default","moduleclass_sfx":"","cache":"0","cache_time":"900","cachemode":"itemid",'
                . '"module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}',
            'client_id' => 0,
            'language'  => '*',
            'published' => 1,
            'rules'     => [],
        ];

        // Bind the data.
        if (!$tableModule->bind($moduleData)) {
            return false;
        }

        // Check the data.
        if (!$tableModule->check()) {
            return false;
        }

        // Store the data.
        if (!$tableModule->store()) {
            return false;
        }

        return $this->addModuleInModuleMenu((int) $tableModule->id);
    }

    /**
     * Add Module Menu.
     *
     * @param   \stdClass  $itemLanguage  Language Object.
     *
     * @return  boolean
     *
     * @since   4.0.0
     */
    private function addModuleMenu($itemLanguage)
    {
        $tableModule = Table::getInstance('Module', 'Joomla\\CMS\\Table\\');
        $title       = 'Main menu ' . $itemLanguage->language;

        $moduleData = [
            'id'        => 0,
            'title'     => $title,
            'note'      => '',
            'content'   => '',
            'position'  => 'sidebar-right',
            'module'    => 'mod_menu',
            'access'    => 1,
            'showtitle' => 1,
            'params'    => '{"menutype":"mainmenu-' . strtolower($itemLanguage->language)
                . '","startLevel":"0","endLevel":"0","showAllChildren":"0","tag_id":"","class_sfx":"","window_open":"",'
                . '"layout":"","moduleclass_sfx":"","cache":"1","cache_time":"900","cachemode":"itemid"}',
            'client_id' => 0,
            'language'  => $itemLanguage->language,
            'published' => 1,
            'rules'     => [],
        ];

        // Bind the data.
        if (!$tableModule->bind($moduleData)) {
            return false;
        }

        // Check the data.
        if (!$tableModule->check()) {
            return false;
        }

        // Store the data.
        if (!$tableModule->store()) {
            return false;
        }

        return $this->addModuleInModuleMenu((int) $tableModule->id);
    }

    /**
     * Add Menu Group.
     *
     * @param   \stdClass  $itemLanguage  Language Object.
     *
     * @return  boolean
     *
     * @since   4.0.0
     */
    private function addMenuGroup($itemLanguage)
    {
        // Add Menu Group.
        $menuTable = $this->getApplication()->bootComponent('com_menus')->getMVCFactory()->createTable('MenuType', 'Administrator', ['dbo' => $this->getDatabase()]);

        $menuData = [
            'id'          => 0,
            'menutype'    => 'mainmenu-' . strtolower($itemLanguage->language),
            'title'       => 'Main Menu (' . $itemLanguage->language . ')',
            'description' => 'The main menu for the site in language ' . $itemLanguage->name,
        ];

        // Bind the data.
        if (!$menuTable->bind($menuData)) {
            return false;
        }

        // Check the data.
        if (!$menuTable->check()) {
            return false;
        }

        // Store the data.
        if (!$menuTable->store()) {
            return false;
        }

        return true;
    }

    /**
     * Add List All Categories Menu Item for new router.
     *
     * @param   \stdClass  $itemLanguage  Language Object.
     *
     * @return  Table|boolean Menu Item Object. False otherwise.
     *
     * @since   4.0.0
     */
    private function addAllCategoriesMenuItem($itemLanguage)
    {
        // Add Menu Item.
        $tableItem = $this->getApplication()->bootComponent('com_menus')->getMVCFactory()->createTable('Menu', 'Administrator', ['dbo' => $this->getDatabase()]);

        $newlanguage = new Language($itemLanguage->language, false);
        $newlanguage->load('joomla', JPATH_ADMINISTRATOR, $itemLanguage->language, true);
        $title = $newlanguage->_('JCATEGORIES');
        $alias = 'allcategories_' . $itemLanguage->language;

        $menuItem = [
            'title'        => $title,
            'alias'        => $alias,
            'menutype'     => 'mainmenu-' . strtolower($itemLanguage->language),
            'type'         => 'component',
            'link'         => 'index.php?option=com_content&view=categories&id=0',
            'component_id' => ExtensionHelper::getExtensionRecord('com_content', 'component')->extension_id,
            'published'    => 1,
            'parent_id'    => 1,
            'level'        => 1,
            'home'         => 0,
            'params'       => '{"show_base_description":"","categories_description":"","maxLevelcat":"",'
                . '"show_empty_categories_cat":"","show_subcat_desc_cat":"","show_cat_num_articles_cat":"",'
                . '"show_category_title":"","show_description":"","show_description_image":"","maxLevel":"",'
                . '"show_empty_categories":"","show_no_articles":"","show_subcat_desc":"","show_cat_num_articles":"",'
                . '"num_leading_articles":"","num_intro_articles":"","num_links":"",'
                . '"show_subcategory_content":"","orderby_pri":"","orderby_sec":"",'
                . '"order_date":"","show_pagination_limit":"","filter_field":"","show_headings":"",'
                . '"list_show_date":"","date_format":"","list_show_hits":"","list_show_author":"","display_num":"10",'
                . '"show_pagination":"","show_pagination_results":"","article_layout":"_:default","show_title":"",'
                . '"link_titles":"","show_intro":"","show_category":"","link_category":"","show_parent_category":"",'
                . '"link_parent_category":"","show_author":"","link_author":"","show_create_date":"",'
                . '"show_modify_date":"","show_publish_date":"","show_item_navigation":"","show_vote":"",'
                . '"show_readmore":"","show_readmore_title":"","show_hits":"","show_noauth":"","show_feed_link":"",'
                . '"feed_summary":"","menu-anchor_title":"","menu-anchor_css":"","menu_image":"","menu_image_css":"",'
                . '"menu_text":1,"menu_show":0,"page_title":"","show_page_heading":"","page_heading":"",'
                . '"pageclass_sfx":"","menu-meta_description":"","robots":""}',
            'language' => $itemLanguage->language,
        ];

        // Bind the data.
        if (!$tableItem->bind($menuItem)) {
            return false;
        }

        $tableItem->setLocation($menuItem['parent_id'], 'last-child');

        // Check the data.
        if (!$tableItem->check()) {
            return false;
        }

        // Store the data.
        if (!$tableItem->store()) {
            return false;
        }

        // Rebuild the tree path.
        if (!$tableItem->rebuildPath($tableItem->id)) {
            return false;
        }

        return $tableItem;
    }

    /**
     * Add Blog Menu Item.
     *
     * @param   \stdClass  $itemLanguage  Language Object.
     * @param   integer   $categoryId    The id of the category displayed by the blog.
     *
     * @return  Table|boolean Menu Item Object. False otherwise.
     *
     * @since   4.0.0
     */
    private function addBlogMenuItem($itemLanguage, $categoryId)
    {
        // Add Menu Item.
        $tableItem = $this->getApplication()->bootComponent('com_menus')->getMVCFactory()->createTable('Menu', 'Administrator', ['dbo' => $this->getDatabase()]);

        $newlanguage = new Language($itemLanguage->language, false);
        $newlanguage->load('com_languages', JPATH_ADMINISTRATOR, $itemLanguage->language, true);
        $title = $newlanguage->_('COM_LANGUAGES_HOMEPAGE');
        $alias = 'home_' . $itemLanguage->language;

        $menuItem = [
            'title'        => $title,
            'alias'        => $alias,
            'menutype'     => 'mainmenu-' . strtolower($itemLanguage->language),
            'type'         => 'component',
            'link'         => 'index.php?option=com_content&view=category&layout=blog&id=' . $categoryId,
            'component_id' => ExtensionHelper::getExtensionRecord('com_content', 'component')->extension_id,
            'published'    => 1,
            'parent_id'    => 1,
            'level'        => 1,
            'home'         => 1,
            'params'       => '{"layout_type":"blog","show_category_heading_title_text":"","show_category_title":"",'
                . '"show_description":"","show_description_image":"","maxLevel":"","show_empty_categories":"",'
                . '"show_no_articles":"","show_subcat_desc":"","show_cat_num_articles":"","show_cat_tags":"",'
                . '"blog_class_leading":"","blog_class":"","num_leading_articles":"1","num_intro_articles":"3",'
                . '"num_links":"0","show_subcategory_content":"","link_intro_image":"","orderby_pri":"",'
                . '"orderby_sec":"front","order_date":"","show_pagination":"2","show_pagination_results":"1",'
                . '"show_featured":"","article_layout":"_:default","show_title":"","link_titles":"","show_intro":"","info_block_position":"",'
                . '"info_block_show_title":"","show_category":"","link_category":"","show_parent_category":"",'
                . '"link_parent_category":"","show_associations":"","show_author":"","link_author":"",'
                . '"show_create_date":"","show_modify_date":"","show_publish_date":"","show_item_navigation":"",'
                . '"show_vote":"","show_readmore":"","show_readmore_title":"","show_hits":"","show_tags":"",'
                . '"show_noauth":"","show_feed_link":"1","feed_summary":"","menu-anchor_title":"","menu-anchor_css":"",'
                . '"menu_image":"","menu_image_css":"","menu_text":1,"menu_show":1,"page_title":"","show_page_heading":"1",'
                . '"page_heading":"","pageclass_sfx":"","menu-meta_description":"","robots":""}',
            'language' => $itemLanguage->language,
        ];

        // Bind the data.
        if (!$tableItem->bind($menuItem)) {
            return false;
        }

        $tableItem->setLocation($menuItem['parent_id'], 'last-child');

        // Check the data.
        if (!$tableItem->check()) {
            return false;
        }

        // Store the data.
        if (!$tableItem->store()) {
            return false;
        }

        // Rebuild the tree path.
        if (!$tableItem->rebuildPath($tableItem->id)) {
            return false;
        }

        return $tableItem;
    }

    /**
     * Create the language associations.
     *
     * @param   array  $groupedAssociations  Array of language associations for all items.
     *
     * @return  boolean  True on success.
     *
     * @since   4.0.0
     */
    private function addAssociations($groupedAssociations)
    {
        $db = $this->getDatabase();

        foreach ($groupedAssociations as $context => $associations) {
            $key   = md5(json_encode($associations));
            $query = $db->getQuery(true)
                ->insert($db->quoteName('#__associations'));

            foreach ($associations as $language => $id) {
                $query->values(
                    implode(
                        ',',
                        $query->bindArray(
                            [
                                $id,
                                $context,
                                $key,
                            ],
                            [
                                ParameterType::INTEGER,
                                ParameterType::STRING,
                                ParameterType::STRING,
                            ]
                        )
                    )
                );
            }

            $db->setQuery($query);

            try {
                $db->execute();
            } catch (\RuntimeException $e) {
                return false;
            }
        }

        return true;
    }

    /**
     * Add a Module in Module menus.
     *
     * @param   integer  $moduleId  The Id of module.
     *
     * @return  boolean
     *
     * @since   4.0.0
     */
    private function addModuleInModuleMenu($moduleId)
    {
        // Create a new db object.
        $db       = $this->getDatabase();
        $query    = $db->getQuery(true);
        $moduleId = (int) $moduleId;

        // Add Module in Module menus.
        $query->insert($db->quoteName('#__modules_menu'))
            ->columns($db->quoteName(['moduleid', 'menuid']))
            ->values(':moduleId, 0')
            ->bind(':moduleId', $moduleId, ParameterType::INTEGER);

        $db->setQuery($query);

        try {
            $db->execute();
        } catch (\RuntimeException $e) {
            return false;
        }

        return true;
    }

    /**
     * Method to create a category for a specific language.
     *
     * @param   \stdClass  $itemLanguage  Language Object.
     *
     * @return  Table|boolean Category Object. False otherwise.
     *
     * @since   4.0.0
     */
    public function addCategory($itemLanguage)
    {
        $newlanguage = new Language($itemLanguage->language, false);
        $newlanguage->load('joomla', JPATH_ADMINISTRATOR, $itemLanguage->language, true);
        $title = $newlanguage->_('JCATEGORY');
        $alias = ApplicationHelper::stringURLSafe($title);

        $app = Factory::getApplication();

        // Set unicodeslugs if alias is empty
        if (trim(str_replace('-', '', $alias) == '')) {
            $unicode = $app->set('unicodeslugs', 1);
            $alias   = ApplicationHelper::stringURLSafe($title);
            $app->set('unicodeslugs', $unicode);
        }

        // Initialize a new category.
        $category = $this->getApplication()->bootComponent('com_categories')->getMVCFactory()->createTable('Category', 'Administrator', ['dbo' => $this->getDatabase()]);

        $data = [
            'extension'       => 'com_content',
            'title'           => $title . ' (' . strtolower($itemLanguage->language) . ')',
            'alias'           => $alias . ' (' . strtolower($itemLanguage->language) . ')',
            'description'     => '',
            'published'       => 1,
            'access'          => 1,
            'params'          => '{"target":"","image":""}',
            'metadesc'        => '',
            'metakey'         => '',
            'metadata'        => '{"page_title":"","author":"","robots":""}',
            'created_time'    => Factory::getDate()->toSql(),
            'created_user_id' => (int) $this->getAdminId(),
            'language'        => $itemLanguage->language,
            'rules'           => [],
            'parent_id'       => 1,
        ];

        // Set the location in the tree.
        $category->setLocation(1, 'last-child');

        // Bind the data to the table
        if (!$category->bind($data)) {
            return false;
        }

        // Check to make sure our data is valid.
        if (!$category->check()) {
            return false;
        }

        // Store the category.
        if (!$category->store(true)) {
            return false;
        }

        // Build the path for our category.
        $category->rebuildPath($category->id);

        return $category;
    }

    /**
     * Create an article in a specific language.
     *
     * @param   \stdClass  $itemLanguage  Language Object.
     * @param   integer   $categoryId    The id of the category where we want to add the article.
     *
     * @return  Table|boolean Article Object. False otherwise.
     *
     * @since   4.0.0
     */
    private function addArticle($itemLanguage, $categoryId)
    {
        $db = $this->getDatabase();

        $newlanguage = new Language($itemLanguage->language, false);
        $newlanguage->load('com_content.sys', JPATH_ADMINISTRATOR, $itemLanguage->language, true);
        $title       = $newlanguage->_('COM_CONTENT_CONTENT_TYPE_ARTICLE');
        $currentDate = Factory::getDate()->toSql();
        $alias       = ApplicationHelper::stringURLSafe($title);

        // Set unicodeslugs if alias is empty
        if (trim(str_replace('-', '', $alias) == '')) {
            $unicode = $this->getApplication()->set('unicodeslugs', 1);
            $alias   = ApplicationHelper::stringURLSafe($title);
            $this->getApplication()->set('unicodeslugs', $unicode);
        }

        // Initialize a new article.
        $article = $this->getApplication()->bootComponent('com_content')->getMVCFactory()->createTable('Article', 'Administrator', ['dbo' => $this->getDatabase()]);

        $data = [
            'title'     => $title . ' (' . strtolower($itemLanguage->language) . ')',
            'alias'     => $alias . ' (' . strtolower($itemLanguage->language) . ')',
            'introtext' => '<p>Lorem ipsum ad his scripta blandit partiendo, eum fastidii accumsan euripidis'
                . ' in, eum liber hendrerit an. Qui ut wisi vocibus suscipiantur, quo dicit'
                . ' ridens inciderint id. Quo mundi lobortis reformidans eu, legimus senserit'
                . 'definiebas an eos. Eu sit tincidunt incorrupte definitionem, vis mutat'
                . ' affert percipit cu, eirmod consectetuer signiferumque eu per. In usu latine'
                . 'equidem dolores. Quo no falli viris intellegam, ut fugit veritus placerat'
                . 'per. Ius id vidit volumus mandamus, vide veritus democritum te nec, ei eos'
                . 'debet libris consulatu.</p>',
            'fulltext'         => '',
            'images'           => json_encode([]),
            'urls'             => json_encode([]),
            'created'          => $currentDate,
            'created_by'       => (int) $this->getAdminId(),
            'created_by_alias' => 'Joomla',
            'publish_up'       => $currentDate,
            'publish_down'     => null,
            'version'          => 1,
            'catid'            => $categoryId,
            'metadata'         => '{"robots":"","author":"","rights":"","tags":null}',
            'metakey'          => '',
            'metadesc'         => '',
            'language'         => $itemLanguage->language,
            'state'            => 1,
            'featured'         => 1,
            'attribs'          => [],
            'rules'            => [],
        ];

        // Bind the data to the table
        if (!$article->bind($data)) {
            return false;
        }

        // Check to make sure our data is valid.
        if (!$article->check()) {
            return false;
        }

        // Now store the category.
        if (!$article->store(true)) {
            return false;
        }

        // Get the new item ID.
        $newId = $article->get('id');

        $query = $db->getQuery(true)
            ->insert($db->quoteName('#__content_frontpage'))
            ->values($newId . ', 0, NULL, NULL');

        $db->setQuery($query);

        try {
            $db->execute();
        } catch (ExecutionFailureException $e) {
            return false;
        }

        $workflow = new Workflow('com_content.article', $this->getApplication(), $db);

        try {
            $stage_id = $workflow->getDefaultStageByCategory($categoryId);

            if ($stage_id) {
                $workflow->createAssociation($newId, $stage_id);
            }
        } catch (ExecutionFailureException $e) {
            return false;
        }

        return $article;
    }

    /**
     * Publish the Installed Content Languages.
     *
     * @return  boolean
     *
     * @since   4.0.0
     */
    private function publishContentLanguages()
    {
        // Publish the Content Languages.
        $tableLanguage = Table::getInstance('Language');

        $siteLanguages = $this->getInstalledlangs('site');

        // For each content language.
        foreach ($siteLanguages as $siteLang) {
            if ($tableLanguage->load(['lang_code' => $siteLang->language, 'published' => 0]) && !$tableLanguage->publish()) {
                $this->getApplication()->enqueueMessage(Text::sprintf('INSTL_DEFAULTLANGUAGE_COULD_NOT_CREATE_CONTENT_LANGUAGE', $siteLang->name), 'warning');
            }
        }

        return true;
    }

    /**
     * Get Languages item data for the Administrator.
     *
     * @return  array
     *
     * @since   4.0.0
     */
    public function getInstalledlangsAdministrator()
    {
        return $this->getInstalledlangs('administrator');
    }

    /**
     * Get Languages item data for the Frontend.
     *
     * @return  array  List of installed languages in the frontend application.
     *
     * @since   4.0.0
     */
    public function getInstalledlangsFrontend()
    {
        return $this->getInstalledlangs('site');
    }

    /**
     * Get Installed Languages.
     *
     * @param   string  $clientName  Name of the cms client.
     *
     * @return  array
     *
     * @since   4.0.0
     */
    protected function getInstalledlangs($clientName = 'administrator')
    {
        // Get information.
        $path     = $this->getPath();
        $client   = $this->getClient($clientName);
        $langlist = $this->getLanguageList($client->id);

        // Compute all the languages.
        $data = [];

        foreach ($langlist as $lang) {
            $file = $path . '/' . $lang . '/' . $lang . '.xml';

            if (!is_file($file)) {
                $file = $path . '/' . $lang . '/langmetadata.xml';
            }

            $info          = Installer::parseXMLInstallFile($file);
            $row           = new \stdClass();
            $row->language = $lang;

            if (!\is_array($info)) {
                continue;
            }

            foreach ($info as $key => $value) {
                $row->$key = $value;
            }

            // If current then set published.
            $params = ComponentHelper::getParams('com_languages');

            if ($params->get($client->name, 'en-GB') == $row->language) {
                $row->published = 1;
            } else {
                $row->published = 0;
            }

            $row->checked_out = null;
            $data[]           = $row;
        }

        usort($data, [$this, 'compareLanguages']);

        return $data;
    }

    /**
     * Get installed languages data.
     *
     * @param   integer  $clientId  The client ID to retrieve data for.
     *
     * @return  object  The language data.
     *
     * @since   4.0.0
     */
    protected function getLanguageList($clientId = 1)
    {
        // Create a new db object.
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        // Select field element from the extensions table.
        $query->select($db->quoteName(['element', 'name']))
            ->from($db->quoteName('#__extensions'))
            ->where(
                [
                    $db->quoteName('type') . ' = ' . $db->quote('language'),
                    $db->quoteName('state') . ' = 0',
                    $db->quoteName('enabled') . ' = 1',
                    $db->quoteName('client_id') . ' = :clientid',
                ]
            )
            ->bind(':clientid', $clientId, ParameterType::INTEGER);

        $db->setQuery($query);

        return $db->loadColumn();
    }

    /**
     * Compare two languages in order to sort them.
     *
     * @param   object  $lang1  The first language.
     * @param   object  $lang2  The second language.
     *
     * @return  integer
     *
     * @since   4.0.0
     */
    protected function compareLanguages($lang1, $lang2)
    {
        return strcmp($lang1->name, $lang2->name);
    }

    /**
     * Get the languages folder path.
     *
     * @return  string  The path to the languages folders.
     *
     * @since   4.0.0
     */
    protected function getPath()
    {
        if ($this->path === null) {
            $client     = $this->getClient();
            $this->path = LanguageHelper::getLanguagePath($client->path);
        }

        return $this->path;
    }

    /**
     * Get the client object of Administrator or Frontend.
     *
     * @param   string  $client  Name of the client object.
     *
     * @return  object
     *
     * @since   4.0.0
     */
    protected function getClient($client = 'administrator')
    {
        return ApplicationHelper::getClientInfo($client, true);
    }

    /**
     * Retrieve the admin user id.
     *
     * @return  integer|boolean  One Administrator ID.
     *
     * @since   4.0.0
     */
    private function getAdminId()
    {
        if ($this->adminId) {
            // Return local cached admin ID.
            return $this->adminId;
        }

        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        // Select the admin user ID
        $query
            ->select($db->quoteName('u.id'))
            ->from($db->quoteName('#__users', 'u'))
            ->join(
                'LEFT',
                $db->quoteName('#__user_usergroup_map', 'map'),
                $db->quoteName('map.user_id') . ' = ' . $db->quoteName('u.id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__usergroups', 'g'),
                $db->quoteName('map.group_id') . ' = ' . $db->quoteName('g.id')
            )
            ->where(
                $db->quoteName('g.title') . ' = ' . $db->quote('Super Users')
            );

        $db->setQuery($query);
        $id = $db->loadResult();

        if (!$id || $id instanceof \Exception) {
            return false;
        }

        return $id;
    }
}
