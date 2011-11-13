<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * @package		Joomla.UnitTest
 * @subpackage  Github
 */
class JGithubObjectMock extends JGithubObject
{
	public function fetchUrl($path, $page = 0, $limit = 0)
	{
		return parent::fetchUrl($path, $page, $limit);
	}
}
