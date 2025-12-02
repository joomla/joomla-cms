<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_contact
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Contact\Administrator\View\Contacts;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\MVC\View\ListView;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * View class for a list of contacts.
 *
 * @since  1.6
 */
class HtmlView extends ListView
{
    /**
     * The help link for the view
     *
     * @var string
     */
    protected $helpLink = 'Contacts';

    /**
     * Constructor
     *
     * @param   array  $config  An optional associative array of configuration settings.
     */
    public function __construct(array $config)
    {
        if (empty($config['option'])) {
            $config['option'] = 'com_contact';
        }

        $config['toolbar_icon']   = 'address-book contact';
        $config['supports_batch'] = true;

        parent::__construct($config);
    }

    /**
     * Prepare view data
     *
     * @return  void
     */
    protected function initializeView()
    {
        parent::initializeView();

        $user  = $this->getCurrentUser();

        $this->canDo = ContentHelper::getActions('com_contact', 'category', $this->state->get('filter.category_id'));

        // Special case of we can create contact for specific categories show add button
        if (\count($user->getAuthorisedCategories('com_contact', 'core.create')) > 0) {
            $this->canDo->set('core.create', true);
        }

        // We don't need toolbar in the modal window.
        if ($this->getLayout() !== 'modal') {
            // We do not need to filter by language when multilingual is disabled
            if (!Multilanguage::isEnabled()) {
                unset($this->activeFilters['language']);
                $this->filterForm->removeField('language', 'filter');
            }
        } else {
            // In article associations modal we need to remove language filter if forcing a language.
            // We also need to change the category filter to show show categories with All or the forced language.
            $forcedLanguage = Factory::getApplication()->getInput()->get('forcedLanguage', '', 'CMD');

            if ($forcedLanguage) {
                // If the language is forced we can't allow to select the language, so transform the language selector filter into a hidden field.
                $languageXml = new \SimpleXMLElement('<field name="language" type="hidden" default="' . $forcedLanguage . '" />');
                $this->filterForm->setField($languageXml, 'filter', true);

                // Also, unset the active language filter so the search tools is not open by default with this filter.
                unset($this->activeFilters['language']);

                // One last changes needed is to change the category filter to just show categories with All language or with the forced language.
                $this->filterForm->setFieldAttribute('category_id', 'language', '*,' . $forcedLanguage, 'filter');
            }

            $this->filterForm
                ->addControlField('forcedLanguage', $forcedLanguage);
        }
    }
}
