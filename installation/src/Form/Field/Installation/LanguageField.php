<?php

/**
 * @package    Joomla.Installation
 *
 * @copyright  (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installation\Form\Field\Installation;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Installation\Model\SetupModel;
use Joomla\CMS\Language\LanguageHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Installation Language field.
 *
 * @since  1.6
 */
class LanguageField extends ListField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  1.6
     */
    protected $type = 'Language';

    /**
     * Method to attach a Form object to the field.
     *
     * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
     * @param   mixed              $value    The form field value to validate.
     * @param   string             $group    The field name group control value. This acts as as an array container for the field.
     *                                       For example if the field has name="foo" and the group value is set to "bar" then the
     *                                       full field name would end up being "bar[foo]".
     *
     * @return  boolean  True on success.
     *
     * @since   4.2.0
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $value = $this->getNativeLanguage();

        return parent::setup($element, $value, $group);
    }

    /**
     * Method to get the field options.
     *
     * @return  array  The field option objects.
     *
     * @since   1.6
     */
    protected function getOptions()
    {
        $native = $this->getNativeLanguage();

        // Get the list of available languages.
        $options = LanguageHelper::createLanguageList($native);

        // Fix wrongly set parentheses in RTL languages
        if (Factory::getLanguage()->isRtl()) {
            foreach ($options as &$option) {
                $option['text'] .= '&#x200E;';
            }
        }

        if (!$options || $options instanceof \Exception) {
            $options = [];
        } else {
            // Sort languages by name
            usort($options, [$this, '_sortLanguages']);
        }

        // Merge any additional options in the XML definition.
        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }

    /**
     * Method to sort languages by name.
     *
     * @param   array  $a  The first value to determine sort
     * @param   array  $b  The second value to determine sort
     *
     * @return  integer
     *
     * @since   3.1
     */
    protected function _sortLanguages($a, $b)
    {
        return strcmp($a['text'], $b['text']);
    }

    /**
     * Determinate the native language to select
     *
     * @return  string  The native language to use
     *
     * @since   4.2.0
     */
    protected function getNativeLanguage()
    {
        static $native;

        if (isset($native)) {
            return $native;
        }

        $app = Factory::getApplication();

        if ($app->isClient('cli_installation')) {
            $native = 'en-GB';

            return $native;
        }

        // Detect the native language.
        $native = LanguageHelper::detectLanguage();

        if (empty($native)) {
            $native = 'en-GB';
        }

        // Get a forced language if it exists.
        $forced = $app->getLocalise();

        if (!empty($forced['language'])) {
            $native = $forced['language'];
        }

        // If a language is already set in the session, use this instead
        $model   = new SetupModel();
        $options = $model->getOptions();

        if (isset($options['language'])) {
            $native = $options['language'];
        }

        return $native;
    }
}
