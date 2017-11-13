<?php
/**
 * @package     Joomla\CMS\Button
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

namespace Joomla\CMS\Button;

use Joomla\CMS\Layout\LayoutHelper;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * The TaskButton class.
 *
 * @since  __DEPLOY_VERSION__
 */
class ActionButton
{
	/**
	 * Property row.
	 *
	 * @var  int
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $row = 0;

	/**
	 * Property value.
	 *
	 * @var  mixed
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $value;

	/**
	 * Property states.
	 *
	 * @var  array
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $states = [
		'_default' => [
			'value'     => '_default',
			'task'      => '',
			'icon'      => 'icon-question',
			'title'     => 'Unknown state',
			'options'   => [
				'disabled'  => false,
				'only_icon' => false,
				'tip' => true,
				'tip_title' => '',
				'task_prefix' => '',
				'checkbox_name' => 'cb'
			]
		]
	];

	/**
	 * Property options.
	 *
	 * @var  Registry
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $options;

	/**
	 * Property template.
	 *
	 * @var  string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $layout = 'joomla.button.action-button';

	/**
	 * create
	 *
	 * @param array $options
	 *
	 * @return static
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public static function create(array $options = [])
	{
		return new static($options);
	}

	/**
	 * StateButton constructor.
	 *
	 * @param array $options
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function __construct(array $options = [])
	{
		$this->options = new Registry($options);

		$this->preprocess();
	}

	/**
	 * Configure this object.
	 *
	 * @return  void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected function preprocess()
	{
		// Implement this method.
	}

	/**
	 * addState
	 *
	 * @param string|integer $value
	 * @param string         $task
	 * @param string         $icon
	 * @param null           $title
	 * @param array          $options
	 *
	 * @return static
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function addState($value, $task, $icon = 'ok', $title = null, array $options = [])
	{
		// Force type to prevent null data
		$this->states[$value] = [
			'value'   => (string) $value,
			'task'    => (string) $task,
			'icon'    => (string) $icon,
			'title'   => (string) $title,
			'options' => (array) $options
		];

		return $this;
	}

	/**
	 * getState
	 *
	 * @param   string|integer $value
	 *
	 * @return  array
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getState($value)
	{
		return isset($this->states[$value]) ? $this->states[$value] : null;
	}

	/**
	 * removeState
	 *
	 * @param   string|integer  $value
	 *
	 * @return  static
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function removeState($value)
	{
		if (isset($this->states[$value]))
		{
			unset($this->states[$value]);
		}

		return $this;
	}

	/**
	 * Render action button by item value.
	 *
	 * @param   mixed    $value  Current value of this item.
	 * @param   integer  $row    The row number of this item.
	 *
	 * @return  string  Rendered HTML.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function render($value = null, $row = null)
	{
		$data = $this->getState($value);

		$data = $data ? : $this->getState('_default');

		$data = ArrayHelper::mergeRecursive($this->getState('_default'), $data, ['options' => $this->options->toArray()]);

		$data['row'] = (int) $row;

		return LayoutHelper::render($this->layout, $data);
	}

	/**
	 * __toString
	 *
	 * @return  string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function __toString()
	{
		try
		{
			return $this->render();
		}
		catch (\Throwable $e)
		{
			return (string) $e;
		}
	}

	/**
	 * Method to get property Template
	 *
	 * @return  string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getLayout()
	{
		return $this->layout;
	}

	/**
	 * Method to set property template
	 *
	 * @param   string $layout
	 *
	 * @return  static  Return self to support chaining.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function setLayout($layout)
	{
		$this->layout = $layout;

		return $this;
	}

	/**
	 * Method to get property Value
	 *
	 * @return  mixed
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * Method to set property value
	 *
	 * @param   mixed $value
	 *
	 * @return  static  Return self to support chaining.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function setValue($value)
	{
		$this->value = $value;

		return $this;
	}

	/**
	 * Method to get property Row
	 *
	 * @return  int
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getRow()
	{
		return $this->row;
	}

	/**
	 * Method to set property row
	 *
	 * @param   int $row
	 *
	 * @return  static  Return self to support chaining.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function setRow($row)
	{
		$this->row = $row;

		return $this;
	}

	/**
	 * Method to get property Options
	 *
	 * @return  array
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getOptions()
	{
		return $this->options->toArray();
	}

	/**
	 * Method to set property options
	 *
	 * @param   array $options
	 *
	 * @return  static  Return self to support chaining.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setOptions(array $options)
	{
		$this->options = new Registry($options);

		return $this;
	}

	/**
	 * getOption
	 *
	 * @param string $name
	 * @param mixed  $default
	 *
	 * @return  mixed
	 */
	public function getOption($name, $default = null)
	{
		return $this->options->get($name, $default);
	}

	/**
	 * setOption
	 *
	 * @param string $name
	 * @param mixed  $value
	 *
	 * @return  static
	 */
	public function setOption($name, $value)
	{
		$this->options->set($name, $value);

		return $this;
	}
}
