<?php
/**
 * @package     Joomla.Tests
 * @subpackage  Acceptance.tests
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
use Step\Acceptance\Administrator\Category as CategoryStep;
use Page\Acceptance\Administrator\CategoryManagerPage;

/**
 * Tests for com_content category list view
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
	 * @param   AcceptanceTester  $I
	 *
	 *
	 * @since   4.0.0
	 */
	public function _before(AcceptanceTester $I)
	{
		$I->doAdministratorLogin();
	}

	/**
	 * @param   AcceptanceTester  $I
	 * @param                     $scenario
	 *
	 *
	 * @since    4.0.0
	 * @throws Exception
	 */
	public function Category(AcceptanceTester $I, $scenario)
	{
		$I = new CategoryStep($scenario);
		$I->createContentCategory($this->categoryTitle);
	}
}
