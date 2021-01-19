<?php
/**
 * Akeeba Engine
 *
 * @package   akeebaengine
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Engine\Postproc;

defined('AKEEBAENGINE') || die();

class None extends Base
{
	public function __construct()
	{
		// No point in breaking the step; we simply do nothing :)
		$this->recommendsBreakAfter           = false;
		$this->recommendsBreakBefore          = false;
		$this->advisesDeletionAfterProcessing = false;
	}

	public function processPart($localFilepath, $remoteBaseName = null)
	{
		// Really nothing to do!!
		return true;
	}

	protected function makeConnector()
	{
		// I have to return an object to satisfy the definition.
		return (object) [
			'foo' => 'bar',
		];
	}
}
