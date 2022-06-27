<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Toolbar;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\Utilities\ArrayHelper;

/**
 * The ToolbarButton class.
 *
 * @method self text(string $value)
 * @method self task(string $value)
 * @method self icon(string $value)
 * @method self buttonClass(string $value)
 * @method self attributes(array $value)
 * @method self onclick(array $value)
 * @method self listCheck(bool $value)
 * @method self listCheckMessage(string $value)
 * @method self form(string $value)
 * @method self formValidation(bool $value)
 * @method string  getText()
 * @method string  getTask()
 * @method string  getIcon()
 * @method string  getButtonClass()
 * @method array   getAttributes()
 * @method string  getOnclick()
 * @method bool    getListCheck()
 * @method string  getListCheckMessage()
 * @method string  getForm()
 * @method bool    getFormValidation()
 *
 * @since  4.0.0
 */
abstract class ToolbarButton
{
    /**
     * Name of this button.
     *
     * @var  string
     *
     * @since  4.0.0
     */
    protected $name;

    /**
     * Reference to the object that instantiated the element
     *
     * @var    Toolbar
     *
     * @since  4.0.0
     */
    protected $parent;

    /**
     * The layout path to render this button.
     *
     * @var  string
     *
     * @since  4.0.0
     */
    protected $layout;

    /**
     * Button options.
     *
     * @var  array
     *
     * @since  4.0.0
     */
    protected $options = [];

    /**
     * Used to track an ids, to avoid duplication
     *
     * @var    array
     *
     * @since  4.0.0
     */
    protected static $idCounter = [];

    /**
     * Init this class.
     *
     * @param   string  $name     Name of this button.
     * @param   string  $text     The button text, will auto translate.
     * @param   array   $options  Button options.
     *
     * @since  4.0.0
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(string $name = '', string $text = '', array $options = [])
    {
        $this->name($name)
            ->text($text);

        $this->options = ArrayHelper::mergeRecursive($this->options, $options);
    }

    /**
     * Prepare options for this button.
     *
     * @param   array  &$options  The options about this button.
     *
     * @return  void
     *
     * @since  4.0.0
     */
    protected function prepareOptions(array &$options)
    {
        $options['name']  = $this->getName();
        $options['text']  = Text::_($this->getText());
        $options['class'] = $this->getIcon() ?: $this->fetchIconClass($this->getName());
        $options['id']    = $this->ensureUniqueId($this->fetchId());

        if (!empty($options['is_child'])) {
            $options['tagName'] = 'button';
            $options['btnClass'] = ($options['button_class'] ?? '') . ' dropdown-item';
            $options['attributes']['type'] = 'button';

            if ($options['is_first_child']) {
                $options['btnClass'] .= ' first';
            }

            if ($options['is_last_child']) {
                $options['btnClass'] .= ' last';
            }
        } else {
            $options['tagName'] = 'button';
            $options['btnClass'] = ($options['button_class'] ?? 'btn btn-primary');
            $options['attributes']['type'] = 'button';
        }
    }

    /**
     * Get the HTML to render the button
     *
     * @param   array  &$definition  Parameters to be passed
     *
     * @return  string
     *
     * @since   3.0
     *
     * @throws \Exception
     */
    public function render(&$definition = null)
    {
        if ($definition === null) {
            $action = $this->renderButton($this->options);
        } elseif (\is_array($definition)) {
            // For B/C
            $action = $this->fetchButton(...$definition);
        } else {
            throw new \InvalidArgumentException('Wrong argument: $definition, should be NULL or array.');
        }

        // Build the HTML Button
        $layout = new FileLayout('joomla.toolbar.base');

        return $layout->render(
            [
                'action' => $action,
                'options' => $this->options
            ]
        );
    }

    /**
     * Render button HTML.
     *
     * @param   array  &$options  The button options.
     *
     * @return  string  The button HTML.
     *
     * @since   4.0.0
     */
    protected function renderButton(array &$options): string
    {
        $this->prepareOptions($options);

        // Prepare custom attributes.
        unset(
            $options['attributes']['id'],
            $options['attributes']['class']
        );

        $options['htmlAttributes'] = ArrayHelper::toString($options['attributes']);

        // Isolate button class from icon class
        $buttonClass = str_replace('icon-', '', $this->getName());
        $iconclass = $options['btnClass'] ?? '';
        $options['btnClass'] = 'button-' . $buttonClass . ' ' . $iconclass;

        // Instantiate a new LayoutFile instance and render the layout
        $layout = new FileLayout($this->layout);

        return $layout->render($options);
    }

    /**
     * Get the button CSS Id.
     *
     * @return  string  Button CSS Id
     *
     * @since   3.0
     */
    protected function fetchId()
    {
        return $this->parent->getName() . '-' . str_ireplace(' ', '-', $this->getName());
    }

    /**
     * Method to get the CSS class name for an icon identifier
     *
     * Can be redefined in the final class
     *
     * @param   string  $identifier  Icon identification string
     *
     * @return  string  CSS class name
     *
     * @since   3.0
     */
    public function fetchIconClass($identifier)
    {
        // It's an ugly hack, but this allows templates to define the icon classes for the toolbar
        $layout = new FileLayout('joomla.toolbar.iconclass');

        return $layout->render(array('icon' => $identifier));
    }

    /**
     * Get the button
     *
     * Defined in the final button class
     *
     * @return  string
     *
     * @since   3.0
     *
     * @deprecated  5.0 Use render() instead.
     */
    abstract public function fetchButton();

    /**
     * Get parent toolbar instance.
     *
     * @return  Toolbar
     *
     * @since   4.0.0
     */
    public function getParent(): Toolbar
    {
        return $this->parent;
    }

    /**
     * Set parent Toolbar instance.
     *
     * @param   Toolbar  $parent  The parent Toolbar instance to set.
     *
     * @return  static  Return self to support chaining.
     *
     * @since   4.0.0
     */
    public function setParent(Toolbar $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get button options.
     *
     * @return  array
     *
     * @since  4.0.0
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Set all options.
     *
     * @param   array  $options  The button options.
     *
     * @return  static  Return self to support chaining.
     *
     * @since  4.0.0
     */
    public function setOptions(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Get single option value.
     *
     * @param   string  $name     The option name.
     * @param   mixed   $default  The default value if this name not exists.
     *
     * @return  mixed
     *
     * @since  4.0.0
     */
    public function getOption(string $name, $default = null)
    {
        return $this->options[$name] ?? $default;
    }

    /**
     * Set option value.
     *
     * @param   string  $name   The option name to store value.
     * @param   mixed   $value  The option value.
     *
     * @return  static
     *
     * @since  4.0.0
     */
    public function setOption(string $name, $value): self
    {
        $this->options[$name] = $value;

        return $this;
    }

    /**
     * Get button name.
     *
     * @return  string
     *
     * @since  4.0.0
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set button name.
     *
     * @param   string  $name  The button name.
     *
     * @return  static  Return self to support chaining.
     *
     * @since  4.0.0
     */
    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get layout path.
     *
     * @return  string
     *
     * @since  4.0.0
     */
    public function getLayout(): string
    {
        return $this->layout;
    }

    /**
     * Set layout path.
     *
     * @param   string  $layout  The layout path name to render.
     *
     * @return  static  Return self to support chaining.
     *
     * @since  4.0.0
     */
    public function layout(string $layout): self
    {
        $this->layout = $layout;

        return $this;
    }

    /**
     * Make sure the id is unique
     *
     * @param   string  $id  The id string.
     *
     * @return  string
     *
     * @since   4.0.0
     */
    protected function ensureUniqueId(string $id): string
    {
        if (\array_key_exists($id, static::$idCounter)) {
            static::$idCounter[$id]++;

            $id .= static::$idCounter[$id];
        } else {
            static::$idCounter[$id] = 0;
        }

        return $id;
    }

    /**
     * Magiix method to adapt option accessors.
     *
     * @param   string  $name  The method name.
     * @param   array   $args  The method arguments.
     *
     * @return  mixed
     *
     * @throws \LogicException
     *
     * @since  4.0.0
     */
    public function __call(string $name, array $args)
    {
        // Getter
        if (stripos($name, 'get') === 0) {
            $fieldName = static::findOptionName(lcfirst(substr($name, 3)));

            if ($fieldName !== false) {
                return $this->getOption($fieldName);
            }
        } else {
            // Setter
            $fieldName = static::findOptionName($name);

            if ($fieldName !== false) {
                if (!\array_key_exists(0, $args)) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            '%s::%s() miss first argument.',
                            \get_called_class(),
                            $name
                        )
                    );
                }

                return $this->setOption($fieldName, $args[0]);
            }
        }

        throw new \BadMethodCallException(
            sprintf(
                'Method %s() not found in class: %s',
                $name,
                \get_called_class()
            )
        );
    }

    /**
     * Find field option name from accessors.
     *
     * @param   string  $name  The field name.
     *
     * @return  boolean|string
     *
     * @since  4.0.0
     */
    private static function findOptionName(string $name)
    {
        $accessors = static::getAccessors();

        if (\in_array($name, $accessors, true)) {
            return $accessors[array_search($name, $accessors, true)];
        }

        // Getter with alias
        if (isset($accessors[$name])) {
            return $accessors[$name];
        }

        return false;
    }

    /**
     * Method to configure available option accessors.
     *
     * @return  array
     *
     * @since  4.0.0
     */
    protected static function getAccessors(): array
    {
        return [
            'text',
            'task',
            'icon',
            'attributes',
            'onclick',
            'buttonClass' => 'button_class',
            'listCheck',
            'listCheckMessage',
            'form',
            'formValidation',
        ];
    }
}
