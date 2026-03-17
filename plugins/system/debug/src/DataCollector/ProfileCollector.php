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
use Joomla\CMS\Profiler\Profiler;
use Joomla\Plugin\System\Debug\AbstractDataCollector;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Collects info about the request duration as well as providing
 * a way to log duration of any operations
 *
 * @since  version
 */
class ProfileCollector extends AbstractDataCollector
{
    /**
     * Request start time.
     *
     * @var   float
     * @since 4.0.0
     */
    protected $requestStartTime;

    /**
     * Request end time.
     *
     * @var   float
     * @since 4.0.0
     */
    protected $requestEndTime;

    /**
     * Started measures.
     *
     * @var array
     * @since  4.0.0
     */
    protected $startedMeasures = [];

    /**
     * Measures.
     *
     * @var array
     * @since  4.0.0
     */
    protected $measures = [];

    /**
     * Constructor.
     *
     * @param   Registry  $params  Parameters.
     *
     * @since 4.0.0
     */
    public function __construct(Registry $params)
    {
        if (isset($_SERVER['REQUEST_TIME_FLOAT'])) {
            $this->requestStartTime = $_SERVER['REQUEST_TIME_FLOAT'];
        } else {
            $this->requestStartTime = microtime(true);
        }

        parent::__construct($params);
    }

    /**
     * Starts a measure.
     *
     * @param   string       $name       Internal name, used to stop the measure
     * @param   string|null  $label      Public name
     * @param   string|null  $collector  The source of the collector
     *
     * @return void
     *
     * @since  4.0.0
     */
    public function startMeasure($name, $label = null, $collector = null)
    {
        $start = microtime(true);

        $this->startedMeasures[$name] = [
            'label'     => $label ?: $name,
            'start'     => $start,
            'collector' => $collector,
        ];
    }

    /**
     * Check a measure exists
     *
     * @param   string  $name  Group name.
     *
     * @return bool
     *
     * @since  4.0.0
     */
    public function hasStartedMeasure($name): bool
    {
        return isset($this->startedMeasures[$name]);
    }

    /**
     * Stops a measure.
     *
     * @param   string  $name    Measurement name.
     * @param   array   $params  Parameters
     *
     * @return void
     *
     * @since  4.0.0
     *
     * @throws DebugBarException
     */
    public function stopMeasure($name, array $params = [])
    {
        $end = microtime(true);

        if (!$this->hasStartedMeasure($name)) {
            throw new DebugBarException("Failed stopping measure '$name' because it hasn't been started");
        }

        $this->addMeasure($this->startedMeasures[$name]['label'], $this->startedMeasures[$name]['start'], $end, $params, $this->startedMeasures[$name]['collector']);

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
     * @return void
     *
     * @since  4.0.0
     */
    public function addMeasure($label, $start, $end, array $params = [], $collector = null)
    {
        $this->measures[] = [
            'label'          => $label,
            'start'          => $start,
            'relative_start' => $start - $this->requestStartTime,
            'end'            => $end,
            'relative_end'   => $end - $this->requestEndTime,
            'duration'       => $end - $start,
            'duration_str'   => $this->getDataFormatter()->formatDuration($end - $start),
            'params'         => $params,
            'collector'      => $collector,
        ];
    }

    /**
     * Utility function to measure the execution of a Closure
     *
     * @param   string       $label      A label.
     * @param   \Closure     $closure    A closure.
     * @param   string|null  $collector  A collector.
     *
     * @return void
     *
     * @since  4.0.0
     */
    public function measure($label, \Closure $closure, $collector = null)
    {
        $name = spl_object_hash($closure);
        $this->startMeasure($name, $label, $collector);
        $result = $closure();
        $params = \is_array($result) ? $result : [];
        $this->stopMeasure($name, $params);
    }

    /**
     * Returns an array of all measures
     *
     * @return array
     *
     * @since  4.0.0
     */
    public function getMeasures(): array
    {
        return $this->measures;
    }

    /**
     * Returns the request start time
     *
     * @return float
     *
     * @since  4.0.0
     */
    public function getRequestStartTime(): float
    {
        return $this->requestStartTime;
    }

    /**
     * Returns the request end time
     *
     * @return float
     *
     * @since  4.0.0
     */
    public function getRequestEndTime(): float
    {
        return $this->requestEndTime;
    }

    /**
     * Returns the duration of a request
     *
     * @return float
     *
     * @since  4.0.0
     */
    public function getRequestDuration(): float
    {
        if ($this->requestEndTime !== null) {
            return $this->requestEndTime - $this->requestStartTime;
        }

        return microtime(true) - $this->requestStartTime;
    }

    /**
     * Sets request end time.
     *
     * @param   float  $time  Request end time.
     *
     * @return $this
     *
     * @since  4.4.0
     */
    public function setRequestEndTime($time): self
    {
        $this->requestEndTime = $time;

        return $this;
    }

    /**
     * Called by the DebugBar when data needs to be collected
     *
     * @return array Collected data
     *
     * @since  4.0.0
     */
    public function collect(): array
    {
        $this->requestEndTime ??= microtime(true);

        $start = $this->requestStartTime;

        $marks = Profiler::getInstance('Application')->getMarks();

        foreach ($marks as $mark) {
            $mem   = $this->getDataFormatter()->formatBytes(abs($mark->memory) * 1048576);
            $label = $mark->label . " ($mem)";
            $end   = $start + $mark->time / 1000;
            $this->addMeasure($label, $start, $end);
            $start = $end;
        }

        foreach (array_keys($this->startedMeasures) as $name) {
            $this->stopMeasure($name);
        }

        usort(
            $this->measures,
            function ($a, $b) {
                if ($a['start'] === $b['start']) {
                    return 0;
                }

                return $a['start'] < $b['start'] ? -1 : 1;
            }
        );

        return [
            'start'        => $this->requestStartTime,
            'end'          => $this->requestEndTime,
            'duration'     => $this->getRequestDuration(),
            'duration_str' => $this->getDataFormatter()->formatDuration($this->getRequestDuration()),
            'measures'     => array_values($this->measures),
            'rawMarks'     => $marks,
        ];
    }

    /**
     * Returns the unique name of the collector
     *
     * @return string
     *
     * @since  4.0.0
     */
    public function getName(): string
    {
        return 'profile';
    }

    /**
     * Returns a hash where keys are control names and their values
     * an array of options as defined in {@see \DebugBar\JavascriptRenderer::addControl()}
     *
     * @return array
     *
     * @since  4.0.0
     */
    public function getWidgets(): array
    {
        return [
            'profileTime' => [
                'icon'    => 'clock-o',
                'tooltip' => 'Request Duration',
                'map'     => 'profile.duration_str',
                'default' => "'0ms'",
            ],
            'profile' => [
                'icon'    => 'clock-o',
                'widget'  => 'PhpDebugBar.Widgets.TimelineWidget',
                'map'     => 'profile',
                'default' => '{}',
            ],
        ];
    }
}
