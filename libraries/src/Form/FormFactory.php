<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form;

use Joomla\Database\DatabaseAwareTrait;
use RuntimeException;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Default factory for creating Form objects.
 *
 * This version restores caching and XML data loading behavior
 * that was originally part of Form::getInstance(), addressing
 * performance and duplication issues noted in #46369.
 *
 * @since  4.0.0
 */
class FormFactory implements FormFactoryInterface
{
    use DatabaseAwareTrait;

    /**
     * Cache of created form instances.
     *
     * @var  Form[]
     */
    private static array $forms = [];

    /**
     * Creates or returns a cached instance of a form.
     *
     * Behaves similarly to the old Form::getInstance():
     * - Returns a cached instance if already loaded.
     * - Loads XML or string data into the form.
     *
     * @param   string       $name     The name of the form.
     * @param   string|null  $data     The XML file path or XML string to load.
     * @param   array        $options  Optional form options.
     * @param   bool         $replace  Whether to replace existing fields.
     * @param   string|null  $xpath    XPath to search for fields.
     *
     * @return  Form
     *
     * @throws  RuntimeException  When the form cannot be loaded.
     *
     * @since   4.0.0
     */
    public function createForm(
        string $name,
        ?string $data = null,
        array $options = [],
        bool $replace = true,
        ?string $xpath = null
    ): Form {
        // If a form with this name already exists, return the cached instance
        if (isset(self::$forms[$name])) {
            return self::$forms[$name];
        }

        // Create a new Form instance
        $form = new Form($name, $options);
        $form->setDatabase($this->getDatabase());

        // Load XML or string data if provided
        if ($data) {
            // Check if data is an XML string or a file path
            if (str_starts_with($data, '<')) {
                // Data is XML string
                if (!$form->load($data, $replace, $xpath)) {
                    throw new RuntimeException(sprintf('%s() could not load XML form data', __METHOD__));
                }
            } else {
                // Data is file path
                if (!$form->loadFile($data, $replace, $xpath)) {
                    throw new RuntimeException(sprintf('%s() could not load form file: %s', __METHOD__, $data));
                }
            }
        }

        // Cache this form instance
        self::$forms[$name] = $form;

        return $form;
    }

    /**
     * Clears cached form instances.
     *
     * Useful for testing or if forms need to be reloaded at runtime.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public static function clearCache(): void
    {
        self::$forms = [];
    }
}
