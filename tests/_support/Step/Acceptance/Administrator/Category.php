<?php
namespace Step\Acceptance\Administrator;

use Page\Acceptance\Administrator\CategoryManagerPage;
use Page\Acceptance\Administrator\AdminPage;
use Page\Acceptance\Administrator\ArticleManagerPage;
use Page\Acceptance\Administrator\MenuManagerPage;
use Step\Acceptance\Administrator\Content;

class Category extends \AcceptanceTester
{
    /**
     * @Given There is an article category link
     */
    public function thereIsAnArticleCategoryLink()
    {
        $I = $this;
        $I->amOnPage(CategoryManagerPage::$url);
    }

    /**
     * @When I check available tabs in category
     */
    public function iCheckAvailableTabsInCategory()
    {
        $I = $this;
        $I->clickToolbarButton('New');
        $I->waitForText('Articles: New Category');
    }

    /**
     * @Then I see available tabs :arg1, :arg2, :arg3 and :arg4
     */
    public function iSeeAvailableTabsAnd($tab1, $tab2, $tab3, $tab4)
    {
        $I = $this;
        $I->verifyAvailableTabs([$tab1, $tab2, $tab3, $tab4]);
    }

    /**
     * @When I fill mandatory fields for creating Category
     */
    public function iFillMandatoryFieldsForCreatingCategory(\Behat\Gherkin\Node\TableNode $title)
    {
        $I = $this;
        $I->clickToolbarButton('New');
        $totalRows = count($title->getRows());
        $lastIndex = ($totalRows - 1);

        // iterate over all rows
        foreach ($title->getRows() as $index => $row)
        {
            if ($index !== 0)
            {
                $I->fillField(CategoryManagerPage::$categoryTitleField, $row[0]);

                if ($index == $lastIndex)
                {
                    $I->clickToolbarButton('Save');
                }
                else
                {
                    $I->clickToolbarButton('Save & New');
                }
            }
        }
    }

    /**
     * @When I save the category
     */
    public function iSaveTheCategory()
    {
        $I = $this;
        $I->clickToolbarButton('Save');
    }
    /**
     * @When I search and select category with title :arg1
     */
    public function iSearchAndSelectCategoryWithTitle($Title)
    {
        $I = $this;
        $I->amOnPage(CategoryManagerPage::$url);
        $I->fillField(CategoryManagerPage::$filterSearch, $Title);
        $I->click(CategoryManagerPage::$iconSearch);
        $I->checkAllResults();
        $I->clickToolbarButton('edit');

    }

    /**
     * @When I set the title as a :arg1
     */
    public function iSetTheTitleAsA($title)
    {
        $I = $this;
        $I->fillField(CategoryManagerPage::$categoryTitleField, $title);
    }

    /**
     * @Given I have a category with title :arg1 which needs to be unpublish
     */
    public function iHaveACategoryWithTitleWhichNeedsToBeUnpublish($title)
    {
        $I = $this;
        $I->amOnPage(CategoryManagerPage::$url);
        $I->fillField(CategoryManagerPage::$filterSearch, $title);
        $I->click(CategoryManagerPage::$iconSearch);
        $I->checkAllResults();
    }

    /**
     * @When I unpublish the category
     */
    public function iUnpublishTheCategory()
    {
        $I = $this;
        $I->clickToolbarButton('unpublish');
    }

    /**
     * @Given I have a category with title :arg1 which needs to be trash
     */
    public function iHaveACategoryWithTitleWhichNeedsToBeTrash($title)
    {
        $I = $this;
        $I->amOnPage(CategoryManagerPage::$url);
        $I->fillField(CategoryManagerPage::$filterSearch, $title);
        $I->click(CategoryManagerPage::$iconSearch);
        $I->checkAllResults();
    }

    /**
     * @When I trash the category
     */
    public function iTrashTheCategory()
    {
        $I = $this;
        $I->clickToolbarButton('trash');
    }

    /**
     * @When I create new category without field title
     */
    public function iCreateNewCategoryWithoutFieldTitle()
    {
        $I = $this;
        $I->amOnPage(CategoryManagerPage::$url);
        $I->clickToolbarButton('New');
        $I->waitForText('Articles: New Category');
        $I->clickToolbarButton('Save');
    }

    /**
     * @Then I should see the :arg1
     */
    public function iShouldSeeThe($error)
    {
        $I = $this;
        $I->see($error, CategoryManagerPage::$invalidTitle);
    }

    /**
     * @When I create a new article :arg1 with content as a :arg2
     */
    public function iCreateANewArticleWithContentAsA($title, $content)
    {
        $I = $this;
        $I->fillField(ArticleManagerPage::$articleTitleField, $title);
        $I->click(ArticleManagerPage::$toggleEditor);
        $I->fillField(ArticleManagerPage::$articleContentField, $content);
    }

    /**
     * @When I add the :arg1 menu item in main menu
     */
    public function iAddTheMenuItemInMainMenu($title)
    {
        $I = $this;
        $I->amOnPage(MenuManagerPage::$url);
        $I->fillField(MenuManagerPage::$menuFieldTitle, $title);
    }

    /**
     * @When I Select menu item type as a :arg1
     */
    public function iSelectMenuItemTypeAsA($arg1)
    {
        $I = $this;
        $I->click(MenuManagerPage::$selectMenutype);
    }

    /**
     * @When I select an article :arg1
     */
    public function iSelectAnArticle($arg1)
    {
        $I = $this;
        $I->switchToIFrame("Menu Item Type");

        $I->click(MenuManagerPage::$selectMenuTypeArticle);
        $I->click(MenuManagerPage::$singleArticle);
        $I->switchToIFrame();
        $I->click(MenuManagerPage::$selectArticle);
        $I->switchToIFrame("Menu Item Type");
        $I->click(MenuManagerPage::$chooseArticle);
    }

    /**
     * @When I save the menu item
     */
    public function iSaveTheMenuItem()
    {
        $I = $this;
        $I->clickToolbarButton('Save');
    }

    /**
     * @When I set category as a :arg1
     */
    public function iSetCategoryAsA($Category_2)
    {
        $I = $this;
        $I->selectOptionInChosenById('jform_catid', $Category_2);
    }

    /**
     * @When I select a category :arg1
     */
    public function iSelectAnCategory($Category_2)
    {
        $I = $this;
        $I->selectOptionInChosenById('jform_request_id', $Category_2);
    }
    /**
     * @When I set language as a :arg1
     */
    public function iSetLanguageAsA($english)
    {
        $I = $this;
        $I->selectOptionInChosenById('jform_language', $english);
    }

}