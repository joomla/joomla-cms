<?php
/**
 * This file is part of the DebugBar package.
 *
 * @copyright  (c) 2013 Maxime Bouroumeau-Fuseau
 * @license    For the full copyright and license information, please view the LICENSE
 *             file that was distributed with this source code.
 */

namespace Joomla\Plugin\System\Debug\DataCollector;

use DebugBar\DebugBarException;
use Joomla\Registry\Registry;
use Joomla\Plugin\System\Debug\AbstractDataCollector;

/**
 * Collects info about the request duration as well as providing
 * a way to log duration of any operations
 *
 * @since  version
 */
class ProfileCollector extends AbstractDataCollector
{
	/**
	 * @var float
	 * @since  version
	 */
	protected $requestStartTime;

	/**
	 * @var float
	 * @since  version
	 */
	protected $requestEndTime;

	/**
	 * @var array
	 * @since  version
	 */
	protected $startedMeasures = array();

	/**
	 * @var array
	 * @since  version
	 */
	protected $measures = array();

	/**
	 * Constructor.
	 *
	 * @param   Registry  $params  Parameters.
	 *
	 * @since 4.0
	 */
	public function __construct(Registry $params)
	{
		if (isset($_SERVER['REQUEST_TIME_FLOAT']))
		{
			$this->requestStartTime = $_SERVER['REQUEST_TIME_FLOAT'];
		}
		else
		{
			$this->requestStartTime = microtime(true);
		}

		return parent::__construct($params);
	}

	/**
	 * Starts a measure.
	 *
	 * @param   string       $name       Internal name, used to stop the measure
	 * @param   string|null  $label      Public name
	 * @param   string|null  $collector  The source of the collector
	 *
	 * @since verion
	 * @return void
	 */
	public function startMeasure($name, $label = null, $collector = null)
	{
		$start = microtime(true);

		$this->startedMeasures[$name] = array(
			'label'     => $label ?: $name,
			'start'     => $start,
			'collector' => $collector,
		);
	}

	/**
	 * Check a measure exists
	 *
	 * @param   string  $name  Group name.
	 *
	 * @since verion
	 * @return bool
	 */
	public function hasStartedMeasure($name)
	{
		return isset($this->startedMeasures[$name]);
	}

	/**
	 * Stops a measure.
	 *
	 * @param   string  $name    Measurement name.
	 * @param   array   $params  Parameters
	 *
	 * @since version
	 * @throws DebugBarException
	 * @return void
	 */
	public function stopMeasure($name, $params = array())
	{
		$end = microtime(true);
		if (!$this->hasStartedMeasure($name))
		{
			throw new DebugBarException("Failed stopping measure '$name' because it hasn't been started");
		}
		$this->addMeasure(
			$this->startedMeasures[$name]['label'],
			$this->startedMeasures[$name]['start'],
			$end,
			$params,
			$this->startedMeasures[$name]['collector']
		);
		unset($this->startedMeasures[$name]);
	}

	/**
	 * Adds a measure
	 *
	 * @param   string       $label      A label.
	 * @param   float        $start      Start of request.
	 * @param   float        $end        End of request.
	 * @param   array        $params     Parameters.
	 * @param   string|null  $collector  A collector.
	 *
	 * @since version
	 * @return void
	 */
	public function addMeasure($label, $start, $end, $params = array(), $collector = null)
	{
		$this->measures[] = array(
			'label'          => $label,
			'start'          => $start,
			'relative_start' => $start - $this->requestStartTime,
			'end'            => $end,
			'relative_end'   => $end - $this->requestEndTime,
			'duration'       => $end - $start,
			'duration_str'   => $this->getDataFormatter()->formatDuration($end - $start),
			'params'         => $params,
			'collector'      => $collector,
		);
	}

	/**
	 * Utility function to measure the execution of a Closure
	 *
	 * @param   string       $label      A label.
	 * @param   \Closure     $closure    A closure.
	 * @param   string|null  $collector  A collector.
	 *
	 * @since version
	 * @return void
	 */
	public function measure($label, \Closure $closure, $collector = null)
	{
		$name = spl_object_hash($closure);
		$this->startMeasure($name, $label, $collector);
		$result = $closure();
		$params = is_array($result) ? $result : array();
		$this->stopMeasure($name, $params);
	}

	/**
	 * Returns an array of all measures
	 *
	 * @since version
	 * @return array
	 */
	public function getMeasures()
	{
		return $this->measures;
	}

	/**
	 * Returns the request start time
	 *
	 * @since version
	 * @return float
	 */
	public function getRequestStartTime()
	{
		return $this->requestStartTime;
	}

	/**
	 * Returns the request end time
	 *
	 * @since version
	 * @return float
	 */
	public function getRequestEndTime()
	{
		return $this->requestEndTime;
	}

	/**
	 * Returns the duration of a request
	 *
	 * @since version
	 * @return float
	 */
	public function getRequestDuration()
	{
		if ($this->requestEndTime !== null)
		{
			return $this->requestEndTime - $this->requestStartTime;
		}

		return microtime(true) - $this->requestStartTime;
	}

	/**
	 * Called by the DebugBar when data needs to be collected
	 *
	 * @since  version
	 *
	 * @return array Collected data
	 */
	public function collect()
	{
		$this->requestEndTime = microtime(true);

		$start = $this->requestStartTime;

		foreach (\JProfiler::getInstance('Application')->getMarks() as $mark)
		{
			$end = $start + $mark->time / 1000;
			$this->addMeasure($mark->label, $start, $end);
			$start = $end;
		}

		foreach (array_keys($this->startedMeasures) as $name)
		{
			$this->stopMeasure($name);
		}

		usort(
			$this->measures,
			function ($a, $b)
			{
				if ($a['start'] == $b['start'])
				{
					return 0;
				}

				return $a['start'] < $b['start'] ? -1 : 1;
			}
		);

		return array(
			'start'        => $this->requestStartTime,
			'end'          => $this->requestEndTime,
			'duration'     => $this->getRequestDuration(),
			'duration_str' => $this->getDataFormatter()->formatDuration($this->getRequestDuration()),
			'measures'     => array_values($this->measures),
		);
	}

	/**
	 * Returns the unique name of the collector
	 *
	 * @since  version
	 *
	 * @return string
	 */
	public function getName()
	{
		return 'profile';
	}

	/**
	 * Returns a hash where keys are control names and their values
	 * an array of options as defined in {@see DebugBar\JavascriptRenderer::addControl()}
	 *
	 * @since  version
	 *
	 * @return array
	 */
	public function getWidgets()
	{
		return array(
			'profileTime' => array(
				'icon'    => 'clock-o',
				'tooltip' => 'Request Duration',
				'map'     => 'profile.duration_str',
				'default' => "'0ms'",
			),
			'profile'     => array(
				'icon'    => 'clock-o',
				'widget'  => 'PhpDebugBar.Widgets.TimelineWidget',
				'map'     => 'profile',
				'default' => '{}',
			),
		);
	}
}
