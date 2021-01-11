<?php
/**
 * @package     Joomla.Tests
 * @subpackage  AcceptanceTester.Step
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Step\Acceptance\Administrator;

use Exception;
use Page\Acceptance\Administrator\ContentCategoryListPage;

/**
 * Acceptance Step object class contains suits for Category Manager.
 *
 * @package  Step\Acceptance\Administrator
 *
 * @since    4.0.0
 */
class Category extends Admin
{
	/**
	 * Create a content category.
	 *
	 * @param   string  $title  Title
	 *
	 * @return void
	 *
	 * @since   4.0.0
	 *
	 * @throws Exception
	 */
	public function createContentCategory($title)
	{
		$this->amOnPage(ContentCategoryListPage::$url);
		$this->waitForText("Articles: Categories", $this->getConfig('timeout'), "//h1");
		$this->clickToolbarButton("New");
		$this->waitForElement(ContentCategoryListPage::$title);
		$this->fillField(ContentCategoryListPage::$title, $title);
		$this->click(ContentCategoryListPage::$dropDownToggle);
		$this->clickToolbarButton("Save & Close");

		// Quick fix: we need to refactor the test
		$testCategory = [
			'title'     => $title,
			'extension' => 'com_content',
		];

		$this->seeInDatabase('categories', $testCategory);
	}
}
