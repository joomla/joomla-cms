<?php
/**
 * @package     Joomla.Test
 * @subpackage  AcceptanceTester.Step
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Step\Acceptance\Administrator;

use Behat\Gherkin\Node\TableNode;
use Page\Acceptance\Administrator\ContentCategoryListPage;

/**
 * Acceptance Step object class contains suits for Category Manager.
 *
 * @package  Step\Acceptance\Administrator
 *
 * @since    __DEPLOY_VERSION__
 */
class Category extends Admin
{

	public function createContentCategory($title)
	{
		$this->amOnPage(ContentCategoryListPage::$url);
		$this->waitForText("Articles: Categories", TIMEOUT, "//h1");
		$this->clickToolbarButton("New");
		$this->waitForElement(ContentCategoryListPage::$title);
		$this->fillField(ContentCategoryListPage::$title, $title);
		$this->click(ContentCategoryListPage::$dropDownToggle);
		$this->clickToolbarButton("Save & Close");

		// Qucikfix: we need to refactor the test
		$this->seeInDatabase('categories', [
			'title' => $title,
			'extension' => 'com_content',
		]);
	}
}
