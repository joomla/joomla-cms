<?php

use \Pages\Joomla\AdminLoginPage;
use \Pages\Joomla\ArticleManagerPage;

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = null)
 *
 * @SuppressWarnings(PHPMD)
 */
class AcceptanceTester extends \Codeception\Actor
{
	/* @todo The namespace should come from the configuration (ENV) */
	private $namespace = '\\Pages\\Joomla';

	/** @var AdminLoginPage|ArticleManagerPage */
	private $currentPage = null;

	use _generated\AcceptanceTesterActions;
	use \Codeception\Util\Shared\Asserts;

	/**
	 * @Given the article :arg1 does not exist
	 * @Then I don't see the article :arg1 in the list
	 */
	public function theArticleTitledDoesNotExist($arg1)
	{
		$this->dontSeeLink($arg1);
	}

	/**
	 * @When I click :arg1 in the toolbar
	 */
	public function iClickInTheToolbar($arg1)
	{
		$this->currentPage = $this->currentPage->toolbar()->click($arg1);
	}

	/**
	 * @When I fill in
	 */
	public function iFillIn(\Behat\Gherkin\Node\TableNode $table)
	{
		$rows = $table->getRows();
		array_shift($rows);

		foreach ($rows as $row)
		{
			$this->currentPage->set($row[0], $row[1]);
		}
	}

	/**
	 * @Then /^I am on the (\w+) page$/
	 */
	public function iAmOnThePage($arg1)
	{
		$pageClass = $this->namespace . '\\' . $arg1 . 'Page';

		$this->assertInstanceOf($pageClass, $this->currentPage, '');
		$this->assertTrue($this->currentPage->isCurrent());
	}

	/**
	 * @Then I see the message :arg1
	 */
	public function iSeeTheMessage($arg1)
	{
		$this->assertContains($arg1, $this->currentPage->message());
	}

	/**
	 * @Given the article :arg1 exists
	 * @Then I see the article :arg1 in the list
	 */
	public function iSeeTheArticleInTheList($arg1)
	{
		$this->seeLink(($arg1));
	}

	/**
	 * @Given I am logged in as administrator
	 */
	public function iAmLoggedInAsAdministrator()
	{
		$this->currentPage = new AdminLoginPage($this);
		$this->amOnPage($this->currentPage->url());

		/* @todo The credentials should come from configuration (ENV) */
		$this->currentPage = $this->currentPage->login('admin', 'password');

		$this->assertInstanceOf($this->namespace . '\\' . 'ControlPanelPage', $this->currentPage, '');
	}

	/**
	 * @Given /^I go to the (\w+) page$/
	 */
	public function iGoToThePage($arg1)
	{
		$pageClass = $this->namespace . '\\' . $arg1 . 'Page';
		$this->currentPage = new $pageClass($this);
		$this->amOnPage($this->currentPage->url());

		$this->assertInstanceOf($pageClass, $this->currentPage, '');
		$this->assertTrue($this->currentPage->isCurrent());
	}

	/**
	 * @When I select the article :arg1
	 */
	public function iSelectTheArticle($arg1)
	{
		$this->currentPage = $this->currentPage->selectItem($arg1);
	}
}
