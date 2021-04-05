<?php
/**
 * @package     Joomla.Tests
 * @subpackage  Acceptance.tests
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Step\Acceptance\Administrator\Field as FieldStep;

/**
 * Tests for com_content Field list view.
 *
 * @since   4.0.0
 */
class ContentFieldListCest
{
	/**
	 * ContentFieldListCest constructor.
	 *
	 * @since   4.0.0
	 */
	public function __construct()
	{
		$this->fieldTitle = 'Field title';
	}

	/**
	 * Before constructor.
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
	 * Field constructor.
	 *
	 * @param   mixed  AcceptanceTester  $I  I
	 * @param   mixed  $scenario             Scenario
	 *
	 * @return void
	 *
	 * @since    4.0.0
	 *
	 * @throws Exception
	 */
	public function Field(AcceptanceTester $I, $scenario)
	{
		$I = new FieldStep($scenario);
		$I->createField('text', $this->fieldTitle);
		$I->trashField($this->fieldTitle, 'Field trashed');
		$I->deleteField($this->fieldTitle, 'Field deleted');
	}
}
