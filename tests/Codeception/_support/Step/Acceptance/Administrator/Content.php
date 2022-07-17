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
use Page\Acceptance\Administrator\ContentListPage;

/**
 * Acceptance Step object class contains suits for Content Manager.
 *
 * @package  Step\Acceptance\Administrator
 *
 * @since    4.0.0
 */
class Content extends Admin
{
    /**
     * Flag if workflows are enabled by default
     *
     * @var bool
     */
    private $workflowsEnabled = false;

    /**
     * Method to create an article.
     *
     * @param  array  articleDetails Array with Article Details like Title, Alias, Content etc
     *
     * @return void
     *
     * @since   4.0.0
     *
     * @throws Exception
     */
    public function createArticle($articleDetails)
    {
        $I = $this;
        $I->amOnPage(ContentListPage::$url);
        $I->waitForElement(ContentListPage::$pageTitle);
        $I->clickToolbarButton('New');
        $I->waitForElement(ContentListPage::$articleTitleField, 30);
        $I->fillField(ContentListPage::$articleTitleField, $articleDetails['title']);
        $I->fillField(ContentListPage::$articleAliasField, $articleDetails['alias']);
        $I->clickToolbarButton('Save & Close');
        $I->waitForElement(ContentListPage::$articleSearchField, $I->getConfig('timeout'));
        $I->click(ContentListPage::$systemMessageAlertClose);
        $I->fillField(ContentListPage::$articleSearchField, $articleDetails['title']);
        $I->click(ContentListPage::$searchButton);
        $I->see($articleDetails['title']);
    }

    /**
     * Method to feature an article.
     *
     * @param   string  $title  Title
     *
     * @return void
     *
     * @since   4.0.0
     *
     * @throws Exception
     */
    public function featureArticle($title)
    {
        $I = $this;
        $I->amOnPage(ContentListPage::$url);
        $I->waitForElement(ContentListPage::$filterSearch, $I->getConfig('timeout'));
        $I->searchForArticle($title);
        $I->checkAllResults();
        $I->clickToolbarButton('Action');
        $I->wait(2);
        $I->clickToolbarButton('feature');
        $I->wait(2);
        $I->see($title);
    }

    /**
     * Method to set an article accesslevel.
     *
     * @param   string  $title        Title
     * @param   string  $accessLevel  AccessLevel
     *
     * @return void
     *
     * @since   4.0.0
     *
     * @throws Exception
     */
    public function setArticleAccessLevel($title, $accessLevel)
    {
        $I = $this;
        $I->amOnPage(ContentListPage::$url);
        $I->waitForElement(ContentListPage::$filterSearch, $I->getConfig('timeout'));
        $I->searchForItem($title);
        $I->checkAllResults();
        $I->click($title);
        $I->waitForElement(['id' => "jform_access"], $I->getConfig('timeout'));
        $I->selectOption(['id' => "jform_access"], $accessLevel);
        $I->click(ContentListPage::$dropDownToggle);
        $I->clickToolbarButton('Save & Close');
        $I->waitForElement(ContentListPage::$filterSearch, $I->getConfig('timeout'));
        $I->see($accessLevel, ContentListPage::$seeAccessLevel);
    }

    /**
     * Method to unpublish an article.
     *
     * @param   string  $title  Title
     *
     * @return void
     *
     * @since   4.0.0
     * @throws Exception
     */
    public function unPublishArticle($title)
    {
        $I = $this;
        $I->amOnPage(ContentListPage::$url);
        $I->waitForElement(ContentListPage::$filterSearch, $I->getConfig('timeout'));
        $I->searchForArticle($title);
        $I->checkAllResults();
        $I->clickToolbarButton('Action');
        $I->wait(2);

        if ($this->workflowsEnabled) {
            $I->clickToolbarButton('transition', '1');
        } else {
            $I->clickToolbarButton('unpublish');
        }

        $I->filterByCondition($title, "0");
    }

    /**
     * Method to Publish an article.
     *
     * @param   string  $title  Title
     *
     * @return void
     *
     * @since   4.0.0
     * @throws Exception
     */
    public function publishArticle($title)
    {
        $I = $this;
        $I->amOnPage(ContentListPage::$url);
        $I->waitForElement(ContentListPage::$filterSearch, $I->getConfig('timeout'));
        $I->searchForArticle($title);
        $I->checkAllResults();
        $I->clickToolbarButton('Action');
        $I->wait(2);

        if ($this->workflowsEnabled) {
            $I->clickToolbarButton('transition', '2');
        } else {
            $I->clickToolbarButton('publish');
        }

        $I->filterByCondition($title, "1");
    }

    /**
     * Method to trash an article.
     *
     * @param   string  $title  Title
     *
     * @return void
     *
     * @since   4.0.0
     *
     * @throws Exception
     */
    public function trashArticle($title)
    {
        $I = $this;
        $I->amOnPage(ContentListPage::$url);
        $I->waitForElement(ContentListPage::$filterSearch, $I->getConfig('timeout'));
        $I->searchForArticle($title);
        $I->checkAllResults();
        $I->clickToolbarButton('Action');
        $I->wait(2);

        if ($this->workflowsEnabled) {
            $I->clickToolbarButton('transition', '3');
        } else {
            $I->clickToolbarButton('trash');
        }

        $I->filterByCondition($title, "-2");
    }

    /**
     * Method to Delete an article.
     *
     * @param   string  $title  Title
     *
     * @return void
     *
     * @since   4.0.0
     *
     * @throws Exception
     */
    public function deleteArticle($title)
    {
        $I = $this;
        $I->amOnPage(ContentListPage::$url);
        $I->waitForElement(ContentListPage::$filterSearch, $I->getConfig('timeout'));
        $I->filterByCondition($title, "-2");
        $I->searchForArticle($title);
        $I->checkAllResults();
        $I->clickToolbarButton('empty trash');
        $I->wait(2);
        $I->acceptPopup();
    }

    public function searchForArticle($title)
    {
        $I = $this;
        $I->waitForElement(ContentListPage::$articleSearchField, $I->getConfig('timeout'));
        $I->fillField(ContentListPage::$articleSearchField, $title);
        $I->click(ContentListPage::$searchButton);
        $I->see($title);
    }

    public function filterByCondition($title, $condition)
    {
        $I = $this;

        // Make sure that the class js-stools-container-filters is visible.
        // Filter is a toggle button and I never know what happened before.
        $I->executeJS("[].forEach.call(document.querySelectorAll('.js-stools-container-filters'), function (el) {
			el.classList.add('js-stools-container-filters-visible');
		  });");
        $I->selectOption('//*[@id="filter_published"]', $condition);
        $I->wait(2);

        $I->see($title);
    }
}
