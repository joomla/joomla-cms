<?php
/**
 * @package     Joomla.Tests
 * @subpackage  Acceptance.tests
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
use Step\Acceptance\Administrator\Category as CategoryStep;
use Page\Acceptance\Administrator\CategoryManagerPage;

/**
 * Tests for com_content category list view
 */
class ContentCategoryListCest
{
	public function __construct()
	{
		$this->categoryTitle = 'Category title';
	}

	public function _before(AcceptanceTester $I)
	{
		$I->doAdministratorLogin();
	}

	public function Category(AcceptanceTester $I, $scenario)
	{
		$I = new CategoryStep($scenario);
		$I->createContentCategory($this->categoryTitle);
	}
}
