<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Captcha;

use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Form\Field\CaptchaField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\DispatcherAwareInterface;
use Joomla\Event\DispatcherAwareTrait;
use Joomla\Event\DispatcherInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla! Captcha base object
 *
 * @abstract
 * @since  2.5
 */
class Captcha implements DispatcherAwareInterface
{
    use DispatcherAwareTrait;

    /**
     * Captcha Plugin object
     *
     * @var    CMSPlugin
     * @since  2.5
     */
    private $captcha;

    /**
     * Captcha Plugin name
     *
     * @var    string
     * @since  2.5
     */
    private $name;

    /**
     * Array of instances of this class.
     *
     * @var    Captcha[]
     * @since  2.5
     */
    private static $instances = [];

    /**
     * Class constructor.
     *
     * @param   string  $captcha  The plugin to use.
     * @param   array   $options  Associative array of options.
     *
     * @since   2.5
     * @throws  \RuntimeException
     */
    public function __construct($captcha, $options)
    {
        $this->name = $captcha;

        if (!empty($options['dispatcher']) && $options['dispatcher'] instanceof DispatcherInterface) {
            $this->setDispatcher($options['dispatcher']);
        } else {
            $this->setDispatcher(Factory::getApplication()->getDispatcher());
        }

        $this->_load($options);
    }

    /**
     * Returns the global Captcha object, only creating it
     * if it doesn't already exist.
     *
     * @param   string  $captcha  The plugin to use.
     * @param   array   $options  Associative array of options.
     *
     * @return  Captcha|null  Instance of this class.
     *
     * @since   2.5
     * @throws  \RuntimeException
     */
    public static function getInstance($captcha, array $options = [])
    {
        $signature = md5(serialize([$captcha, $options]));

        if (empty(self::$instances[$signature])) {
            self::$instances[$signature] = new Captcha($captcha, $options);
        }

        return self::$instances[$signature];
    }

    /**
     * Fire the onInit event to initialise the captcha plugin.
     *
     * @param   string  $id  The id of the field.
     *
     * @return  boolean  True on success
     *
     * @since   2.5
     * @throws  \RuntimeException
     */
    public function initialise($id)
    {
        $arg = ['id' => $id];

        $this->update('onInit', $arg);

        return true;
    }

    /**
     * Get the HTML for the captcha.
     *
     * @param   string  $name   The control name.
     * @param   string  $id     The id for the control.
     * @param   string  $class  Value for the HTML class attribute
     *
     * @return  string  The return value of the function "onDisplay" of the selected Plugin.
     *
     * @since   2.5
     * @throws  \RuntimeException
     */
    public function display($name, $id, $class = '')
    {
        // Check if captcha is already loaded.
        if ($this->captcha === null) {
            return '';
        }

        // Initialise the Captcha.
        if (!$this->initialise($id)) {
            return '';
        }

        $arg = [
            'name'  => $name,
            'id'    => $id ?: $name,
            'class' => $class,
        ];

        $result = $this->update('onDisplay', $arg);

        return $result;
    }

    /**
     * Checks if the answer is correct.
     *
     * @param   string  $code  The answer.
     *
     * @return  bool    Whether the provided answer was correct
     *
     * @since   2.5
     * @throws  \RuntimeException
     */
    public function checkAnswer($code)
    {
        // Check if captcha is already loaded
        if ($this->captcha === null) {
            return false;
        }

        $arg = ['code' => $code];

        $result = $this->update('onCheckAnswer', $arg);

        return $result;
    }

    /**
     * Method to react on the setup of a captcha field. Gives the possibility
     * to change the field and/or the XML element for the field.
     *
     * @param   CaptchaField       $field    Captcha field instance
     * @param   \SimpleXMLElement  $element  XML form definition
     *
     * @return void
     */
    public function setupField(CaptchaField $field, \SimpleXMLElement $element)
    {
        if ($this->captcha === null) {
            return;
        }

        $arg = ['field' => $field, 'element' => $element];

        return $this->update('onSetupField', $arg);
    }

    /**
     * Method to call the captcha callback if it exist.
     *
     * @param   string  $name   Callback name
     * @param   array   &$args  Arguments
     *
     * @return  mixed
     *
     * @since   4.0.0
     */
    private function update($name, &$args)
    {
        if (method_exists($this->captcha, $name)) {
            return call_user_func_array([$this->captcha, $name], array_values($args));
        }

        return null;
    }

    /**
     * Load the Captcha plugin.
     *
     * @param   array  $options  Associative array of options.
     *
     * @return  void
     *
     * @since   2.5
     * @throws  \RuntimeException
     */
    private function _load(array $options = [])
    {
        // Build the path to the needed captcha plugin
        $name = InputFilter::getInstance()->clean($this->name, 'cmd');

        // Boot the captcha plugin
        $this->captcha = Factory::getApplication()->bootPlugin($name, 'captcha');

        // Check if the captcha can be loaded
        if (!$this->captcha) {
            throw new \RuntimeException(Text::sprintf('JLIB_CAPTCHA_ERROR_PLUGIN_NOT_FOUND', $name));
        }
    }
}
