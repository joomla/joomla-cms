<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Input
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Joomla! Input Files Class
 *
 * @since  11.1
 */
class JInputFiles extends JInput
{
	/**
	 * The pivoted data from a $_FILES or compatible array.
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected $decodedData = array();

	/**
	 * The class constructor.
	 *
	 * @param   array  $source   The source argument is ignored. $_FILES is always used.
	 * @param   array  $options  An optional array of configuration options:
	 *                           filter : a custom JFilterInput object.
	 *
	 * @since   12.1
	 */
	public function __construct(array $source = null, array $options = array())
	{
		if (isset($options['filter']))
		{
			$this->filter = $options['filter'];
		}
		else
		{
			$this->filter = JFilterInput::getInstance();
		}

		// Set the data source.
		$this->data = & $_FILES;

		// Set the options for the class.
		$this->options = $options;
	}

	/**
	 * Gets a value from the input data.
	 *
	 * @param   string  $name     The name of the input property (usually the name of the files INPUT tag) to get.
	 * @param   mixed   $default  The default value to return if the named property does not exist.
	 * @param   string  $filter   The filter to apply to the value.
	 *
	 * @return  mixed  The filtered input value.
	 *
	 * @see     JFilterInput::clean()
	 * @since   11.1
	 */
	public function get($name, $default = null, $filter = 'cmd')
	{
		if (isset($this->data[$name]))
		{
			$results = $this->decodeData(
				array(
					$this->data[$name]['name'],
					$this->data[$name]['type'],
					$this->data[$name]['tmp_name'],
					$this->data[$name]['error'],
					$this->data[$name]['size']
				)
			);

			// Prevent returning an unsafe file unless specifically requested
			if ($filter != 'raw')
			{
				$isSafe = JFilterInput::isSafeFile($results);

				if (!$isSafe)
				{
					return $default;
				}
			}

			return $results;
		}

		return $default;
	}

	/**
	 * Method to decode a data array.
	 *
	 * @param   array  $data  The data array to decode.
	 *
	 * @return  array
	 *
	 * @since   11.1
	 */
	protected function decodeData(array $data)
	{
		$result = array();

		if (is_array($data[0]))
		{
			foreach ($data[0] as $k => $v)
			{
				$result[$k] = $this->decodeData(array($data[0][$k], $data[1][$k], $data[2][$k], $data[3][$k], $data[4][$k]));
			}

			return $result;
		}

		return array('name' => $data[0], 'type' => $data[1], 'tmp_name' => $data[2], 'error' => $data[3], 'size' => $data[4]);
	}

	/**
	 * Sets a value.
	 *
	 * @param   string  $name   The name of the input property to set.
	 * @param   mixed   $value  The value to assign to the input property.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function set($name, $value)
	{
	}
}
