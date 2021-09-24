<?php
/**
 * This file is part of the DebugBar package.
 *
 * @copyright  (c) 2013 Maxime Bouroumeau-Fuseau
 * @license    For the full copyright and license information, please view the LICENSE
 *             file that was distributed with this source code.
 */

namespace Joomla\Plugin\System\Debug\DataCollector;

use Joomla\Plugin\System\Debug\AbstractDataCollector;
use Joomla\Registry\Registry;

/**
 * Collects info about the request duration as well as providing
 * a way to log duration of any operations
 *
 * @since  __DEPLOY_VERSION__
 */
class MemoryCollector extends AbstractDataCollector
{
	/**
	 * @var boolean
	 *
	 * @since __DEPLOY_VERSION__
	 */
	protected $realUsage = false;

	/**
	 * @var float
	 *
	 * @since __DEPLOY_VERSION__
	 */
	protected $peakUsage = 0;

	/**
	 * @param   Registry  $params Parameters.
	 * @param   float     $peakUsage
	 * @param   boolean   $realUsage
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function __construct(Registry $params, $peakUsage = null, $realUsage = null)
	{
		parent::__construct($params);

		if ($peakUsage !== null)
		{
			$this->peakUsage = $peakUsage;
		}

		if ($realUsage !== null)
		{
			$this->realUsage = $realUsage;
		}
	}

	/**
	 * Returns whether total allocated memory page size is used instead of actual used memory size
	 * by the application.  See $real_usage parameter on memory_get_peak_usage for details.
	 *
	 * @return boolean
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function getRealUsage()
	{
		return $this->realUsage;
	}

	/**
	 * Sets whether total allocated memory page size is used instead of actual used memory size
	 * by the application.  See $real_usage parameter on memory_get_peak_usage for details.
	 *
	 * @param boolean $realUsage
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function setRealUsage($realUsage)
	{
		$this->realUsage = $realUsage;
	}

	/**
	 * Returns the peak memory usage
	 *
	 * @return integer
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function getPeakUsage()
	{
		return $this->peakUsage;
	}

	/**
	 * Updates the peak memory usage value
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function updatePeakUsage()
	{
		if ($this->peakUsage === null)
		{
			$this->peakUsage = memory_get_peak_usage($this->realUsage);
		}
	}

	/**
	 * @return array
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function collect()
	{
		$this->updatePeakUsage();

		return [
			'peak_usage'     => $this->peakUsage,
			'peak_usage_str' => $this->getDataFormatter()->formatBytes($this->peakUsage, 3),
		];
	}

	/**
	 * @return string
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function getName()
	{
		return 'memory';
	}

	/**
	 * @return array
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function getWidgets()
	{
		return [
			'memory' => [
				'icon'    => 'cogs',
				'tooltip' => 'Memory Usage',
				'map'     => 'memory.peak_usage_str',
				'default' => "'0B'",
			],
		];
	}
}
