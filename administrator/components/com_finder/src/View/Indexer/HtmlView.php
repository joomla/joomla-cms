<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Finder\Administrator\View\Indexer;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Indexer view class for Finder.
 *
 * @since  2.5
 */
class HtmlView extends BaseHtmlView
{
    /**
     * @var   Form  $form
     *
     * @since  5.0.0
     */
    public $form;

    /**
     * Method to display the view.
     *
     * @param   string  $tpl  A template file to load. [optional]
     *
     * @return  void
     *
     * @since   5.0.0
     */
    public function display($tpl = null)
    {
        if ($this->getLayout() == 'debug') {
            $this->form = $this->get('Form');
            $this->addToolbar();
        }

        parent::display($tpl);
    }

    /**
     * Method to configure the toolbar for this view.
     *
     * @return  void
     *
     * @since   5.0.0
     */
    protected function addToolbar()
    {
        /** @var Toolbar $toolbar */
        $toolbar = $this->getDocument()->getToolbar();

        ToolbarHelper::title(Text::_('COM_FINDER_INDEXER_TOOLBAR_TITLE'), 'search-plus finder');

        $toolbar->linkButton('back', 'JTOOLBAR_BACK')
            ->icon('icon-arrow-' . ($this->getLanguage()->isRtl() ? 'right' : 'left'))
            ->url(Route::_('index.php?option=com_finder&view=index'));

        $toolbar->standardButton('index', 'COM_FINDER_INDEX')
            ->icon('icon-play')
            ->onclick('Joomla.debugIndexing();');
    }
}
