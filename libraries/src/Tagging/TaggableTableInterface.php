<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Tagging;

/**
 * Interface for a taggaable Table class
 *
 * @since  __DEPLOY_VERSION__
 */
interface TaggableTableInterface
{
	/**
	 * Get the type alias for the tagging table
	 *
	 * The type alias generally is the internal component name with the
	 * content type. Ex.: com_content.article
	 *
	 * @return  string  The alias as described above
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getTypeAlias();
}
