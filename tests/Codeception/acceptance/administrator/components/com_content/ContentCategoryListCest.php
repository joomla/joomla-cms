<?php
/**
 * @package     Joomla.Tests
 * @subpackage  Acceptance.tests
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Step\Acceptance\Administrator\Category as CategoryStep;

/**
 * Tests for com_content category list view.
 *
 * @since   4.0.0
 */
class ContentCategoryListCest
{
	/**
	 * ContentCategoryListCest constructor.
	 *
	 * @since   4.0.0
	 */
	public function __construct()
	{
		$this->categoryTitle = 'Category title';
	}

	/**
	 * Before constructor.
	 *
	 * @param   mixed   AcceptanceTester  $I  I
	 *
	 * @return void
	 *
	 * @since   4.0.0
	 */
	public function _before(AcceptanceTester $I)
	{
		$I->doAdministratorLogin();
	}

	/**
	 * Category constructor.
	 *
	 * @param   mixed  AcceptanceTester  $I  I
	 * @param   mixed  $scenario             Scenario
	 *
	 * @return void
	 *
	 * @since    4.0.0
	 *
	 * @throws Exception
	 */
	public function Category(AcceptanceTester $I, $scenario)
	{
		$I = new CategoryStep($scenario);
		$I->createContentCategory($this->categoryTitle);
	}
}
