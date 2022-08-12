<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Captcha;

use Joomla\CMS\Extension\DummyPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\DispatcherAwareInterface;
use Joomla\Event\DispatcherAwareTrait;
use Joomla\Event\DispatcherInterface;

/**
 * Joomla! Captcha base object
 *
 * @abstract
 * @since  2.5
 *
 * @deprecated 5.0 Boot the respective captcha plugin through the application and use that instance
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
     * Editor Plugin name
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
    private static $instances = array();

    /**
     * Class constructor.
     *
     * @param   string  $captcha  The plugin to use.
     * @param   array   $options  Associative array of options.
     *
     * @since   2.5
     * @throws  \RuntimeException
     *
     * @deprecated 5.0 Boot the respective captcha plugin through the application and do not use this class anymore
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
     *
     * @deprecated 5.0 Boot the respective captcha plugin through the application this method has no replacement
     */
    public static function getInstance($captcha, array $options = array())
    {
        $signature = md5(serialize(array($captcha, $options)));

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
     *
     * @deprecated 5.0 Has no replacement as init should be done during display function
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
     *
     * @deprecated 5.0 Boot the respective captcha plugin through the application and execute the display function directly
     */
    public function display($name, $id, $class = '')
    {
        // Check if captcha is already loaded.
        if ($this->captcha === null) {
            return '';
        }

        if ($this->captcha instanceof CaptchaPluginInterface) {
            return $this->captcha->display($id, $class);
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
     *
     * @deprecated 5.0 Boot the respective captcha plugin through the application and execute the checkAnswer function directly
     */
    public function checkAnswer($code)
    {
        // Check if captcha is already loaded
        if ($this->captcha === null) {
            return false;
        }

        if ($this->captcha instanceof CaptchaPluginInterface) {
            return $this->captcha->checkAnswer($code);
        }

        $arg = ['code'  => $code];

        $result = $this->update('onCheckAnswer', $arg);

        return $result;
    }

    /**
     * Method to react on the setup of a captcha field. Gives the possibility
     * to change the field and/or the XML element for the field.
     *
     * @param   \Joomla\CMS\Form\Field\CaptchaField  $field    Captcha field instance
     * @param   \SimpleXMLElement                    $element  XML form definition
     *
     * @return void
     *
     * @deprecated 5.0 Boot the respective captcha plugin through the application and execute the setupField function directly
     */
    public function setupField(\Joomla\CMS\Form\Field\CaptchaField $field, \SimpleXMLElement $element)
    {
        if ($this->captcha === null) {
            return;
        }

        if ($this->captcha instanceof CaptchaPluginInterface) {
            return $this->captcha->setupField($field, $element);
        }

        $arg = [
            'field' => $field,
            'element' => $element,
        ];

        $result = $this->update('onSetupField', $arg);

        return $result;
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
            return call_user_func_array(array($this->captcha, $name), array_values($args));
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
    private function _load(array $options = array())
    {
        // Build the path to the needed captcha plugin
        $name = InputFilter::getInstance()->clean($this->name, 'cmd');

        $plugin = Factory::getApplication()->bootPlugin($name, 'captcha');
        if (!$plugin || $plugin instanceof DummyPlugin) {
            throw new \RuntimeException(Text::sprintf('JLIB_CAPTCHA_ERROR_PLUGIN_NOT_FOUND', $name));
        }

        $this->captcha = $plugin;
    }
}
