<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Engine\Filter;

// Protection against direct access
defined('AKEEBAENGINE') || die();

use Akeeba\Engine\Factory;

/**
 * Subdirectories exclusion filter. Excludes temporary, cache and backup output
 * directories' contents from being backed up.
 */
class Excludetabledata extends Base
{
	public function __construct()
	{
		$this->object      = 'dbobject';
		$this->subtype     = 'content';
		$this->method      = 'direct';
		$this->filter_name = 'Excludetabledata';

		// We take advantage of the filter class magic to inject our custom filters
		$this->filter_data['[SITEDB]'] = array(
			'#__session',        // Sessions table
			'#__guardxt_runs'    // Guard XT's run log (bloated to the bone)
		);

		parent::__construct();
	}

}
