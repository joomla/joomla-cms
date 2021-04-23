<?php
/**
 * @package     Joomla.Tests
 * @subpackage  Acceptance.tests
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Page\Acceptance\Administrator\ContentListPage;
use Step\Acceptance\Administrator\Content;

/**
 * Tests for com_content list view.
 *
 * @since    4.0.0
 */
class ContentListCest
{
	/**
	 * Runs before every test.
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
	 * Test that it loads without php notices and warnings.
	 *
	 * @param   mixed   AcceptanceTester  $I  I
	 *
	 * @return void
	 *
	 * @since   4.0.0
	 *
	 * @throws Exception
	 */
	public function loadsWithoutPhpNoticesAndWarnings(AcceptanceTester $I)
	{
		$I->wantToTest('that it loads without php notices and warnings.');
		$I->amOnPage(ContentListPage::$url);
		$I->waitForElement(ContentListPage::$adminForm);
		$I->checkForPhpNoticesOrWarnings();
	}

	/**
	 * Test create a new article.
	 *
	 * @param   mixed   \Step\Acceptance\Administrator\Content  $I  I
	 *
	 * @return void
	 *
	 * @since   4.0.0
	 *
	 * @throws Exception
	 */
	public function ArticleCRUD(Content $I)
	{
		$testArticle = [
			'title'   => 'Test Article',
			'alias'   => 'test-article',
			'state'   => 1,
		];
		$I->wantToTest('that it is possible to create a new articles using "new" toolbar button.');
		$I->amOnPage(ContentListPage::$url);
		$I->createArticle($testArticle);
		$I->featureArticle($testArticle['title']);
		$I->publishArticle($testArticle['title']);
		$I->unPublishArticle($testArticle['title']);
		$I->trashArticle($testArticle['title']);
		$I->deleteArticle($testArticle['title']);
	}
}
