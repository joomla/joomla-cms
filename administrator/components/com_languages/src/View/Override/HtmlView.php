<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Languages\Administrator\View\Override;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Languages\Administrator\Model\OverrideModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * View to edit a language override
 *
 * @since  2.5
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The form to use for the view.
     *
     * @var     object
     * @since   2.5
     */
    protected $form;

    /**
     * The item to edit.
     *
     * @var     object
     * @since   2.5
     */
    protected $item;

    /**
     * The model state.
     *
     * @var     object
     * @since   2.5
     */
    protected $state;

    /**
     * Displays the view.
     *
     * @param   string  $tpl  The name of the template file to parse
     *
     * @return  void
     *
     * @since   2.5
     */
    public function display($tpl = null)
    {
        /** @var OverrideModel $model */
        $model = $this->getModel();

        $this->form  = $model->getForm();
        $this->item  = $model->getItem();
        $this->state = $model->getState();

        $app = Factory::getApplication();

        $languageClient = $app->getUserStateFromRequest('com_languages.overrides.language_client', 'language_client');

        if ($languageClient == null) {
            $app->enqueueMessage(Text::_('COM_LANGUAGES_OVERRIDE_FIRST_SELECT_MESSAGE'), 'warning');

            $app->redirect('index.php?option=com_languages&view=overrides');
        }

        // Check for errors.
        if (\count($errors = $model->getErrors())) {
            throw new GenericDataException(implode("\n", $errors));
        }

        // Check whether the cache has to be refreshed.
        $cached_time = Factory::getApplication()->getUserState(
            'com_languages.overrides.cachedtime.' . $this->state->get('filter.client') . '.' . $this->state->get('filter.language'),
            0
        );

        if (time() - $cached_time > 60 * 5) {
            $this->state->set('cache_expired', true);
        }

        // Add strings for translations in \Javascript.
        Text::script('COM_LANGUAGES_VIEW_OVERRIDE_NO_RESULTS');
        Text::script('COM_LANGUAGES_VIEW_OVERRIDE_REQUEST_ERROR');

        $this->addToolbar();
        parent::display($tpl);
    }

    /**
     * Adds the page title and toolbar.
     *
     * @return void
     *
     * @since   2.5
     */
    protected function addToolbar()
    {
        Factory::getApplication()->getInput()->set('hidemainmenu', true);

        $canDo   = ContentHelper::getActions('com_languages');
        $toolbar = $this->getDocument()->getToolbar();

        ToolbarHelper::title(Text::_('COM_LANGUAGES_VIEW_OVERRIDE_EDIT_TITLE'), 'comments langmanager');

        if ($canDo->get('core.edit')) {
            $toolbar->apply('override.apply');
        }

        $saveGroup = $toolbar->dropdownButton('save-group');

        $saveGroup->configure(
            function (Toolbar $childBar) use ($canDo) {
                if ($canDo->get('core.edit')) {
                    $childBar->save('override.save');
                }

                // This component does not support Save as Copy.
                if ($canDo->get('core.edit') && $canDo->get('core.create')) {
                    $childBar->save2new('override.save2new');
                }
            }
        );

        if (empty($this->item->key)) {
            $toolbar->cancel('override.cancel', 'JTOOLBAR_CANCEL');
        } else {
            $toolbar->cancel('override.cancel');
        }

        $toolbar->divider();
        $toolbar->help('Languages:_Edit_Override');
    }
}
