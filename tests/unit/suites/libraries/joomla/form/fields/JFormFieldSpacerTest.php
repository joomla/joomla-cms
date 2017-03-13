<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

JFormHelper::loadFieldClass('spacer');

/**
 * Test class for JForm.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 * @since       11.1
 */
class JFormFieldSpacerTest extends TestCase
{
	/**
	 * Backup of the SERVER superglobal
	 *
	 * @var    array
	 * @since  3.1
	 */
	protected $backupServer;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->saveFactoryState();

		JFactory::$application = $this->getMockCmsApp();

		$this->backupServer = $_SERVER;

		$_SERVER['HTTP_HOST'] = 'example.com';
		$_SERVER['SCRIPT_NAME'] = '';
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	protected function tearDown()
	{
		$_SERVER = $this->backupServer;

		$this->restoreFactoryState();

		parent::tearDown();
	}

	/**
	 * Test the getInput method.
	 *
	 * @return void
	 */
	public function testGetInput()
	{
		$form = new JForm('form1');

		$this->assertThat(
			$form->load('<form><field name="spacer" type="spacer" /></form>'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' XML string should load successfully.'
		);

		$field = new JFormFieldSpacer($form);

		$this->assertThat(
			$field->setup($form->getXml()->field, 'value'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true.'
		);

		$this->assertThat(
			strlen($field->input),
			$this->greaterThan(0),
			'Line:' . __LINE__ . ' The getInput method should return something without error.'
		);
	}

	/**
	 * Test the getLabel method.
	 *
	 * @return void
	 */
	public function testGetLabel()
	{
		$form = new JForm('form1');

		$this->assertThat(
			$form->load('<form><field name="spacer" type="spacer" description="spacer" /></form>'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' XML string should load successfully.'
		);

		$field = new JFormFieldSpacer($form);

		$this->assertThat(
			$field->setup($form->getXml()->field, 'value'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true.'
		);

		$equals = '<span class="spacer"><span class="before"></span><span>' .
			'<label id="spacer-lbl" class="hasTooltip" title="&lt;strong&gt;spacer&lt;/strong&gt;">spacer</label></span>' .
			'<span class="after"></span></span>';

		$this->assertEquals(
			$equals,
			$field->label,
			'Line:' . __LINE__ . ' The getLabel method should return something without error.'
		);

		$this->assertThat(
			$form->load('<form><field name="spacer" type="spacer" class="text" /></form>'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' XML string should load successfully.'
		);

		$field = new JFormFieldSpacer($form);

		$this->assertThat(
			$field->setup($form->getXml()->field, 'value'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true.'
		);

		$equals = '<span class="spacer"><span class="before"></span><span class="text">' .
			'<label id="spacer-lbl" class="">spacer</label></span><span class="after"></span></span>';

		$this->assertEquals(
			$equals,
			$field->label,
			'Line:' . __LINE__ . ' The getLabel method should return something without error.'
		);

		$this->assertThat(
			$form->load('<form><field name="spacer" type="spacer" class="text" label="MyLabel" /></form>'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' XML string should load successfully.'
		);

		$field = new JFormFieldSpacer($form);

		$this->assertThat(
			$field->setup($form->getXml()->field, 'value'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true.'
		);

		$equals = '<span class="spacer"><span class="before"></span><span class="text">' .
			'<label id="spacer-lbl" class="">MyLabel</label></span><span class="after"></span></span>';

		$this->assertEquals(
			$field->label,
			$equals,
			'Line:' . __LINE__ . ' The getLabel method should return something without error.'
		);

		$this->assertThat(
			$form->load('<form><field name="spacer" type="spacer" hr="true" /></form>'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' XML string should load successfully.'
		);

		$field = new JFormFieldSpacer($form);

		$this->assertThat(
			$field->setup($form->getXml()->field, 'value'),
			$this->isTrue(),
			'Line:' . __LINE__ . ' The setup method should return true.'
		);

		$expected = '<span class="spacer"><span class="before"></span><span><hr /></span>' .
			'<span class="after"></span></span>';

		$this->assertEquals(
			$expected,
			$field->label,
			'Line:' . __LINE__ . ' The getLabel method should return something without error.'
		);
	}

	/**
	 * Test the getTitle method.
	 *
	 * @return void
	 */
	public function testGetTitle()
	{
		$this->testGetLabel();
	}
}
