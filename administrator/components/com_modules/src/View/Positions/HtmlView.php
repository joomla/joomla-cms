<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Modules\Administrator\View\Positions;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\Component\Modules\Administrator\Helper\ModulesHelper;
use Joomla\Component\Templates\Administrator\Helper\TemplatesHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * HTML View class for the Modules positions selector (modal).
 *
 * @since  __DEPLOY_VERSION__
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The grouped positions array
     *
     * @var  array
     * @since  __DEPLOY_VERSION__
     */
    protected $positions = [];

    /**
     * The client ID (0 = site, 1 = administrator)
     *
     * @var  int
     * @since  __DEPLOY_VERSION__
     */
    protected $clientId = 0;

    /**
     * Display the view
     *
     * @param   string  $tpl  The name of the template file to parse.
     *
     * @return  void
     *
     * @since  __DEPLOY_VERSION__
     */
    public function display($tpl = null)
    {
        $this->clientId  = Factory::getApplication()->getInput()->getInt('client_id', 0);
        $this->positions = HTMLHelper::_('modules.positions', $this->clientId, 1);

        // Enrich "Active Positions" labels with template names
        $templates           = array_keys(ModulesHelper::getTemplates($this->clientId, 1));
        $positionToTemplates = [];

        foreach ($templates as $template) {
            $templatePositions = TemplatesHelper::getPositions($this->clientId, $template);

            if (\is_array($templatePositions)) {
                foreach ($templatePositions as $position) {
                    $positionToTemplates[$position][] = $template;
                }
            }
        }

        $customGroupKey = Text::_('COM_MODULES_CUSTOM_POSITION');

        if (isset($this->positions[$customGroupKey]['items'])) {
            foreach ($this->positions[$customGroupKey]['items'] as &$option) {
                if (!empty($option->value) && isset($positionToTemplates[$option->value])) {
                    $option->text = $option->value . ' [' . implode(', ', $positionToTemplates[$option->value]) . ']';
                }
            }

            unset($option);
        }

        parent::display($tpl);
    }
}
