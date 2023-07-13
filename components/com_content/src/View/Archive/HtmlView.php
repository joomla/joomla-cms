<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Site\View\Archive;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Plugin\PluginHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * HTML View class for the Content component
 *
 * @since  1.5
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The model state
     *
     * @var   \Joomla\CMS\Object\CMSObject
     */
    protected $state = null;

    /**
     * An array containing archived articles
     *
     * @var   \stdClass[]
     */
    protected $items = [];

    /**
     * The pagination object
     *
     * @var   \Joomla\CMS\Pagination\Pagination|null
     */
    protected $pagination = null;

    /**
     * The years that are available to filter on.
     *
     * @var   array
     *
     * @since 3.6.0
     */
    protected $years = [];

    /**
     * Object containing the year, month and limit field to be displayed
     *
     * @var    \stdClass|null
     *
     * @since  4.0.0
     */
    protected $form = null;

    /**
     * The page parameters
     *
     * @var    \Joomla\Registry\Registry|null
     *
     * @since  4.0.0
     */
    protected $params = null;

    /**
     * The search query used on any archived articles (note this may not be displayed depending on the value of the
     * filter_field component parameter)
     *
     * @var    string
     *
     * @since  4.0.0
     */
    protected $filter = '';

    /**
     * The user object
     *
     * @var    \Joomla\CMS\User\User
     *
     * @since  4.0.0
     */
    protected $user = null;

    /**
     * The page class suffix
     *
     * @var    string
     *
     * @since  4.0.0
     */
    protected $pageclass_sfx = '';

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @throws  GenericDataException
     */
    public function display($tpl = null)
    {
        $user       = $this->getCurrentUser();
        $state      = $this->get('State');
        $items      = $this->get('Items');
        $pagination = $this->get('Pagination');

        if ($errors = $this->getModel()->getErrors()) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        // Flag indicates to not add limitstart=0 to URL
        $pagination->hideEmptyLimitstart = true;

        // Get the page/component configuration
        $params = &$state->params;

        PluginHelper::importPlugin('content');

        foreach ($items as $item) {
            $item->slug = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;

            // No link for ROOT category
            if ($item->parent_alias === 'root') {
                $item->parent_id = null;
            }

            $item->event = new \stdClass();

            // Old plugins: Ensure that text property is available
            if (!isset($item->text)) {
                $item->text = $item->introtext;
            }

            Factory::getApplication()->triggerEvent('onContentPrepare', ['com_content.archive', &$item, &$item->params, 0]);

            // Old plugins: Use processed text as introtext
            $item->introtext = $item->text;

            $results                        = Factory::getApplication()->triggerEvent('onContentAfterTitle', ['com_content.archive', &$item, &$item->params, 0]);
            $item->event->afterDisplayTitle = trim(implode("\n", $results));

            $results                           = Factory::getApplication()->triggerEvent('onContentBeforeDisplay', ['com_content.archive', &$item, &$item->params, 0]);
            $item->event->beforeDisplayContent = trim(implode("\n", $results));

            $results                          = Factory::getApplication()->triggerEvent('onContentAfterDisplay', ['com_content.archive', &$item, &$item->params, 0]);
            $item->event->afterDisplayContent = trim(implode("\n", $results));
        }

        $form = new \stdClass();

        // Month Field
        $months = [
            ''   => Text::_('COM_CONTENT_MONTH'),
            '1'  => Text::_('JANUARY_SHORT'),
            '2'  => Text::_('FEBRUARY_SHORT'),
            '3'  => Text::_('MARCH_SHORT'),
            '4'  => Text::_('APRIL_SHORT'),
            '5'  => Text::_('MAY_SHORT'),
            '6'  => Text::_('JUNE_SHORT'),
            '7'  => Text::_('JULY_SHORT'),
            '8'  => Text::_('AUGUST_SHORT'),
            '9'  => Text::_('SEPTEMBER_SHORT'),
            '10' => Text::_('OCTOBER_SHORT'),
            '11' => Text::_('NOVEMBER_SHORT'),
            '12' => Text::_('DECEMBER_SHORT'),
        ];
        $form->monthField = HTMLHelper::_(
            'select.genericlist',
            $months,
            'month',
            [
                'list.attr'   => 'class="form-select"',
                'list.select' => $state->get('filter.month'),
                'option.key'  => null,
            ]
        );

        // Year Field
        $this->years = $this->getModel()->getYears();
        $years       = [];
        $years[]     = HTMLHelper::_('select.option', null, Text::_('JYEAR'));

        for ($i = 0, $iMax = count($this->years); $i < $iMax; $i++) {
            $years[] = HTMLHelper::_('select.option', $this->years[$i], $this->years[$i]);
        }

        $form->yearField = HTMLHelper::_(
            'select.genericlist',
            $years,
            'year',
            ['list.attr' => 'class="form-select"', 'list.select' => $state->get('filter.year')]
        );
        $form->limitField = $pagination->getLimitBox();

        // Escape strings for HTML output
        $this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx', ''));

        $this->filter     = $state->get('list.filter');
        $this->form       = &$form;
        $this->items      = &$items;
        $this->params     = &$params;
        $this->user       = &$user;
        $this->pagination = &$pagination;
        $this->pagination->setAdditionalUrlParam('month', $state->get('filter.month'));
        $this->pagination->setAdditionalUrlParam('year', $state->get('filter.year'));

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
        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menu = Factory::getApplication()->getMenu()->getActive();

        if ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        } else {
            $this->params->def('page_heading', Text::_('JGLOBAL_ARTICLES'));
        }

        $this->setDocumentTitle($this->params->get('page_title', ''));

        if ($this->params->get('menu-meta_description')) {
            $this->document->setDescription($this->params->get('menu-meta_description'));
        }

        if ($this->params->get('robots')) {
            $this->document->setMetaData('robots', $this->params->get('robots'));
        }
    }
}
