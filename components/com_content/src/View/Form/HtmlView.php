<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Site\View\Form;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Plugin\PluginHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * HTML Article View class for the Content component
 *
 * @since  1.5
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The Form object
     *
     * @var  \Joomla\CMS\Form\Form
     */
    protected $form;

    /**
     * The item being created
     *
     * @var  \stdClass
     */
    protected $item;

    /**
     * The page to return to after the article is submitted
     *
     * @var  string
     */
    protected $return_page = '';

    /**
     * The model state
     *
     * @var  \Joomla\Registry\Registry
     */
    protected $state;

    /**
     * The page parameters
     *
     * @var    \Joomla\Registry\Registry|null
     *
     * @since  4.0.0
     */
    protected $params = null;

    /**
     * The page class suffix
     *
     * @var    string
     *
     * @since  4.0.0
     */
    protected $pageclass_sfx = '';

    /**
     * The user object
     *
     * @var \Joomla\CMS\User\User
     *
     * @since  4.0.0
     */
    protected $user = null;

    /**
     * Should we show a captcha form for the submission of the article?
     *
     * @var    boolean
     *
     * @since  3.7.0
     */
    protected $captchaEnabled = false;

    /**
     * Should we show Save As Copy button?
     *
     * @var    boolean
     * @since  4.1.0
     */
    protected $showSaveAsCopy = false;

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void|boolean
     */
    public function display($tpl = null)
    {
        $app  = Factory::getApplication();
        $user = $app->getIdentity();

        // Get model data.
        $this->state       = $this->get('State');
        $this->item        = $this->get('Item');
        $this->form        = $this->get('Form');
        $this->return_page = $this->get('ReturnPage');

        if (empty($this->item->id)) {
            $catid = $this->state->params->get('catid');

            if ($this->state->params->get('enable_category') == 1 && $catid) {
                $authorised = $user->authorise('core.create', 'com_content.category.' . $catid);
            } else {
                $authorised = $user->authorise('core.create', 'com_content') || \count($user->getAuthorisedCategories('com_content', 'core.create'));
            }
        } else {
            $authorised = $this->item->params->get('access-edit');
        }

        if ($authorised !== true) {
            $app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
            $app->setHeader('status', 403, true);

            return false;
        }

        $this->item->tags = new TagsHelper();

        if (!empty($this->item->id)) {
            $this->item->tags->getItemTags('com_content.article', $this->item->id);

            $this->item->images = json_decode($this->item->images);
            $this->item->urls   = json_decode($this->item->urls);

            $tmp         = new \stdClass();
            $tmp->images = $this->item->images;
            $tmp->urls   = $this->item->urls;
            $this->form->bind($tmp);
        }

        // Check for errors.
        if (\count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        // Create a shortcut to the parameters.
        $params = &$this->state->params;

        // Escape strings for HTML output
        $this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx', ''));

        $this->params = $params;

        // Override global params with article specific params
        $this->params->merge($this->item->params);
        $this->user   = $user;

        // Propose current language as default when creating new article
        if (empty($this->item->id) && Multilanguage::isEnabled() && $params->get('enable_category') != 1) {
            $lang = $this->getLanguage()->getTag();
            $this->form->setFieldAttribute('language', 'default', $lang);
        }

        $captchaSet = $params->get('captcha', Factory::getApplication()->get('captcha', '0'));

        foreach (PluginHelper::getPlugin('captcha') as $plugin) {
            if ($captchaSet === $plugin->name) {
                $this->captchaEnabled = true;
                break;
            }
        }

        // If the article is being edited and the current user has permission to create article
        if (
            $this->item->id
            && ($user->authorise('core.create', 'com_content') || \count($user->getAuthorisedCategories('com_content', 'core.create')))
        ) {
            $this->showSaveAsCopy = true;
        }

        $this->_prepareDocument();

        parent::display($tpl);
    }

    /**
     * Prepares the document
     *
     * @return  void
     */
    protected function _prepareDocument()
    {
        $app   = Factory::getApplication();

        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menu = $app->getMenu()->getActive();

        if ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        } else {
            $this->params->def('page_heading', Text::_('COM_CONTENT_FORM_EDIT_ARTICLE'));
        }

        $title = $this->params->def('page_title', Text::_('COM_CONTENT_FORM_EDIT_ARTICLE'));

        $this->setDocumentTitle($title);

        $app->getPathway()->addItem($title);

        if ($this->params->get('menu-meta_description')) {
            $this->getDocument()->setDescription($this->params->get('menu-meta_description'));
        }

        if ($this->params->get('robots')) {
            $this->getDocument()->setMetaData('robots', $this->params->get('robots'));
        }
    }
}
