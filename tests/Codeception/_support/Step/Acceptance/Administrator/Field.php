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
use Page\Acceptance\Administrator\FieldListPage;

/**
 * Acceptance Step object class contains suits for Content Manager.
 *
 * @package  Step\Acceptance\Administrator
 *
 * @since    4.0.0
 */
class Field extends Admin
{
	/**
	 * Method to create a Field.
	 *
	 * @param   string  $type   Type of the Field
	 * @param   string  $title  Title of the Field
	 *
	 * @return void
	 *
	 * @since   4.0.0
	 *
	 * @throws Exception
	 */
	public function createField($type, $title)
	{
		$I = $this;
		$I->amOnPage(FieldListPage::$url);
		$I->clickToolbarButton('New');
		$I->fillField(FieldListPage::$titleField, $title);
		$I->selectOption(FieldListPage::$fieldType, $type);
		$I->clickToolbarButton('Save & Close');
		$I->assertSuccessMessage(FieldListPage::$successMessage);
	}

	/**
	 * Method to see success message.
	 *
	 * @param   string   $message  Message
	 *
	 * @return void
	 *
	 * @since   4.0.0
	 *
	 * @throws Exception
	 */
	public function assertSuccessMessage($message)
	{
		$I = $this;
		$I->waitForText($message, $I->getConfig('timeout'), FieldListPage::$systemMessageContainer);
		$I->see($message, FieldListPage::$systemMessageContainer);
	}

	/**
	 * Method to trash a Field.
	 *
	 * @param   string   $title    Field Title
	 * @param   string   $message  Message
	 *
	 * @return void
	 *
	 * @since   4.0.0
	 *
	 * @throws Exception
	 */
	public function trashField($title, $message)
	{
		$I = $this;
		$I->amOnPage(FieldListPage::$url);
		$I->waitForElement(FieldListPage::$searchField, $I->getConfig('timeout'));
		$I->fillField(FieldListPage::$searchField, $title);
		$I->Click(FieldListPage::$filterSearch);
		$I->checkAllResults();
		$I->clickToolbarButton('Action');
		$I->wait(2);
		$I->click('Trash');
		$I->assertSuccessMessage($message);
	}

	/**
	 * Method to delete a Field.
	 *
	 * @param   string   $title    Field Title
	 * @param   string   $message  Message
	 *
	 * @return void
	 *
	 * @since   4.0.0
	 *
	 * @throws Exception
	 */
	public function deleteField($title, $message)
	{
		$I = $this;
		$I->amOnPage(FieldListPage::$url);
		$I->waitForElement(FieldListPage::$searchField, $I->getConfig('timeout'));

		// Make sure that the class js-stools-container-filters is visible. 
		// Filter is a toggle button and I never know what happened before.
		$I->executeJS("[].forEach.call(document.querySelectorAll('.js-stools-container-filters'), function (el) {
			el.classList.add('js-stools-container-filters-visible');
		  });");
		$I->selectOption('//*[@id="filter_state"]', "-2");
		$I->wait(2);

		$I->fillField(FieldListPage::$searchField, $title);
		$I->Click(FieldListPage::$filterSearch);
		$I->checkAllResults();
		$I->wait(2);
		$I->clickToolbarButton('Empty trash');
		$I->assertSuccessMessage($message);
	}
}
