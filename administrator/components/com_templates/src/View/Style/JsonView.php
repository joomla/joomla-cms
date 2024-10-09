<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Templates\Administrator\View\Style;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\Component\Templates\Administrator\Model\StyleModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * View to edit a template style.
 *
 * @since  1.6
 */
class JsonView extends BaseHtmlView
{
    /**
     * The item
     *
     * @var   \stdClass
     */
    protected $item;

    /**
     * The form object
     *
     * @var  \Joomla\CMS\Form\Form
     */
    protected $form;

    /**
     * The model state
     *
     * @var   \Joomla\Registry\Registry
     */
    protected $state;

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  mixed  A string if successful, otherwise an Error object.
     *
     * @since   1.6
     */
    public function display($tpl = null)
    {
        /** @var StyleModel $model */
        $model = $this->getModel();

        try {
            $this->item = $model->getItem();
        } catch (\Exception $e) {
            $app = Factory::getApplication();
            $app->enqueueMessage($e->getMessage(), 'error');

            return false;
        }

        $paramsList = (array) $this->item;

        unset($paramsList['xml']);

        $paramsList = json_encode($paramsList);

        return $paramsList;
    }
}
