<?php
/**
 * @package     Joomla.Test
 * @subpackage  AcceptanceTester.Step
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Step\Acceptance\Administrator;

use Page\Acceptance\Administrator\ContentListPage;

/**
 * Acceptance Step object class contains suits for Content Manager.
 *
 * @package  Step\Acceptance\Administrator
 *
 * @since    __DEPLOY_VERSION__
 */
class Content extends Admin
{
	/**
	 * Helper function to create a new Article
	 *
	 * @param   string  $title
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function featureArticle($title)
	{
		$I = $this;
		$I->amOnPage(ContentListPage::$url);
		$I->waitForElement(ContentListPage::$filterSearch, TIMEOUT);
		$I->searchForItem($title);
		$I->checkAllResults();
		$I->clickToolbarButton('feature');
		$I->seeNumberOfElements(ContentListPage::$seeFeatured, 1);
	}

	public function setArticleAccessLevel($title, $accessLevel)
	{
		$I = $this;
		$I->amOnPage(ContentListPage::$url);
		$I->waitForElement(ContentListPage::$filterSearch, TIMEOUT);
		$I->searchForItem($title);
		$I->checkAllResults();
		$I->click($title);
		$I->waitForElement(['id' => "jform_access"], TIMEOUT);
		$I->selectOption(['id' => "jform_access"], $accessLevel);
		$I->click(ContentListPage::$dropDownToggle);
		$I->clickToolbarButton('Save & Close');
		$I->waitForElement(ContentListPage::$filterSearch, TIMEOUT);
		$I->see($accessLevel, ContentListPage::$seeAccessLevel);
	}

	public function unPublishArticle($title)
	{
		$I = $this;
		$I->amOnPage(ContentListPage::$url);
		$I->waitForElement(ContentListPage::$filterSearch, TIMEOUT);
		$I->searchForItem($title);
		$I->checkAllResults();
		$I->clickToolbarButton('unpublish');
		$I->seeNumberOfElements(ContentListPage::$seeUnpublished, 1);
	}

	public function trashArticle($title)
	{
		$I = $this;
		$I->amOnPage(ContentListPage::$url);
		$I->waitForElement(ContentListPage::$filterSearch, TIMEOUT);
		$this->articleManagerPage->haveItemUsingSearch($title);
		$I->clickToolbarButton('trash');
		$I->searchForItem($title);
		$I->dontSee($title);
	}
}
