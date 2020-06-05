<?php
/**
 * @package        Joomla.UnitTest
 * @subpackage     AccessiblemediaField
 *
 * @copyright      Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Form\Field;

use Joomla\CMS\Form\Field\AccessiblemediaField;

/**
 * Test class for AccessiblemediaField.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 * @since       __DEPLOY_VERSION__
 */
class AccessiblemediaFieldTest extends \PHPUnit\Framework\TestCase
{
	/**
	 * Tests the constructor
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testIsConstructable()
	{
		$this->assertInstanceOf(AccessiblemediaField::class, $this->createAccessiblemediaField());
	}

	/**
	 * Helper function to create a AccessiblemediaField
	 *
	 * @param   string   $name  Name
	 *
	 * @return  AccessiblemediaField
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function createAccessiblemediaField(): AccessiblemediaField
	{
		return new AccessiblemediaField;
	}
}
