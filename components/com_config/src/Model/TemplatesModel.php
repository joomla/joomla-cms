<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_config
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Config\Site\Model;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\Filesystem\Path;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Template style model.
 *
 * @since  3.2
 */
class TemplatesModel extends FormModel
{
    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @return  null
     *
     * @since   3.2
     */
    protected function populateState()
    {
        parent::populateState();

        $this->setState('params', ComponentHelper::getParams('com_templates'));
    }

    /**
     * Method to get the record form.
     *
     * @param   array    $data      An optional array of data for the form to interrogate.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  Form|bool    A Form object on success, false on failure
     *
     * @since   3.2
     */
    public function getForm($data = [], $loadData = true)
    {
        try {
            // Get the form.
            $form = $this->loadForm('com_config.templates', 'templates', ['load_data' => $loadData]);

            $data = [];
            $this->preprocessForm($form, $data);

            // Load the data into the form
            $form->bind($data);
        } catch (\Exception $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage());

            return false;
        }

        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * Method to preprocess the form
     *
     * @param   Form    $form   A form object.
     * @param   mixed   $data   The data expected for the form.
     * @param   string  $group  Plugin group to load
     *
     * @return  void
     *
     * @since   3.2
     * @throws  \Exception if there is an error in the form event.
     */
    protected function preprocessForm(Form $form, $data, $group = 'content')
    {
        $lang = Factory::getLanguage();

        $template = Factory::getApplication()->getTemplate();

        // Load the core and/or local language file(s).
        $lang->load('tpl_' . $template, JPATH_BASE)
        || $lang->load('tpl_' . $template, JPATH_BASE . '/templates/' . $template);

        // Look for com_config.xml, which contains fields to display
        $formFile = Path::clean(JPATH_BASE . '/templates/' . $template . '/com_config.xml');

        if (!file_exists($formFile)) {
            // If com_config.xml not found, fall back to templateDetails.xml
            $formFile = Path::clean(JPATH_BASE . '/templates/' . $template . '/templateDetails.xml');
        }

        // Get the template form.
        if (file_exists($formFile) && !$form->loadFile($formFile, false, '//config')) {
            throw new \Exception(Text::_('JERROR_LOADFILE_FAILED'));
        }

        // Attempt to load the xml file.
        if (!$xml = simplexml_load_file($formFile)) {
            throw new \Exception(Text::_('JERROR_LOADFILE_FAILED'));
        }

        // Trigger the default form events.
        parent::preprocessForm($form, $data, $group);
    }
}
