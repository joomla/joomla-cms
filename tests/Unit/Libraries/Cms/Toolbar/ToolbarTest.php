<?php

/**
 * @package        Joomla.UnitTest
 * @subpackage     Toolbar
 *
 * @copyright      (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Toolbar;

use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarButton;
use Joomla\CMS\Toolbar\ToolbarFactoryInterface;

/**
 * Test class for Toolbar.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Toolbar
 * @since       3.0
 */
class ToolbarTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests the constructor
     *
     * @return  void
     *
     * @since   3.0
     */
    public function testIsConstructable()
    {
        $this->assertInstanceOf(Toolbar::class, $this->createToolbar());
    }

    /**
     * Tests the appendButton method
     *
     * @return  void
     *
     * @since   3.0
     */
    public function testAppendButtonWithToolbarButtonReturnsButton()
    {
        $toolbar = $this->createToolbar();

        $button = $this->createMock(ToolbarButton::class);
        $button->expects($this->once())
            ->method('setParent')
            ->with($toolbar);

        $this->assertEquals($button, $toolbar->appendButton($button));
    }

    /**
     * Tests the appendButton method
     *
     * @return  void
     *
     * @since   3.0
     */
    public function testAppendButtonWithStringsReturnsTrue()
    {
        $toolbar = $this->createToolbar();

        $this->assertTrue($toolbar->appendButton('Separator', 'divider'));
    }

    /**
     * Tests the getItems method
     *
     * @return  void
     *
     * @since   3.0
     */
    public function testGetItemsReturnsArray()
    {
        $toolbar = $this->createToolbar();

        $this->assertTrue(is_array($toolbar->getItems()));
    }

    /**
     * Tests the getItems method
     *
     * @return  void
     *
     * @since   3.0
     */
    public function testGetItemsReturnsButtons()
    {
        $toolbar = $this->createToolbar();
        $button1 = $this->createMock(ToolbarButton::class);
        $button2 = $this->createMock(ToolbarButton::class);

        $toolbar->appendButton($button1);
        $toolbar->appendButton($button2);

        $buttons = $toolbar->getItems();

        $this->assertCount(2, $buttons);
        $this->assertEquals($button1, $buttons[0]);
        $this->assertEquals($button2, $buttons[1]);
    }

    /**
     * Tests the prependButton method
     *
     * @return  void
     *
     * @since   3.0
     */
    public function testPrependButtonWithToolbarButton()
    {
        $toolbar = $this->createToolbar();
        $toolbar->setItems([
            $this->createMock(ToolbarButton::class),
            $this->createMock(ToolbarButton::class),]);

        $button = $this->createMock(ToolbarButton::class);
        $button->expects($this->once())
            ->method('setParent')
            ->with($toolbar);

        $this->assertEquals($button, $toolbar->prependButton($button));
        $this->assertEquals($button, $toolbar->getItems()[0]);
    }

    /**
     * Tests the prependButton method
     *
     * @return  void
     *
     * @since   3.0
     */
    public function testPrependButtonWithStrings()
    {
        $toolbar = $this->createToolbar();
        $toolbar->setItems([
            $this->createMock(ToolbarButton::class),
            $this->createMock(ToolbarButton::class),]);

        $button = ['Separator', 'spacer', 25];

        $this->assertTrue($toolbar->prependButton(...$button));
        $this->assertEquals($button, $toolbar->getItems()[0]);
    }

    /**
     *
     * @return  void
     * @since   4.0.0
     * @throws \Exception
     */
    public function testRenderButton()
    {
        $button         = ['Separator', 'spacer'];
        $renderedButton = 'some-html';
        $buttonMock     = $this->createMock(ToolbarButton::class);
        $buttonMock
            ->expects($this->once())
            ->method('setParent');
        $buttonMock
            ->expects($this->once())
            ->method('render')
            ->with($button)
            ->willReturn($renderedButton);
        $toolbarFactoryMock = $this->createMock(ToolbarFactoryInterface::class);
        $toolbarFactoryMock
            ->expects($this->once())
            ->method('createButton')
            ->willReturn($buttonMock);

        $toolbar = new Toolbar('my-toolbar', $toolbarFactoryMock);

        $this->assertEquals($renderedButton, $toolbar->renderButton($button));
    }

    /**
     * Tests render a button
     *
     * @return  void
     *
     * @since   4.0.0
     * @throws \Exception
     */
    public function testRenderButtonThrowsUnexpectedValueException()
    {
        $this->expectException(\UnexpectedValueException::class);
        $button             = ['Separator', 'spacer'];
        $toolbarFactoryMock = $this->createMock(ToolbarFactoryInterface::class);
        $toolbarFactoryMock
            ->expects($this->once())
            ->method('createButton')
            ->willThrowException(new \InvalidArgumentException());

        $toolbar = new Toolbar('my-toolbar', $toolbarFactoryMock);

        $toolbar->renderButton($button);
    }

    /**
     * Tests Load a button
     *
     * @return  void
     *
     * @since   3.0
     */
    public function testLoadButtonTypeReturnsButton()
    {
        $buttonMock         = $this->createMock(ToolbarButton::class);
        $toolbarFactoryMock = $this->createMock(ToolbarFactoryInterface::class);
        $toolbarFactoryMock
            ->expects($this->once())
            ->method('createButton')
            ->willReturn($buttonMock);

        $toolbar = new Toolbar('my-toolbar', $toolbarFactoryMock);

        $this->assertEquals($buttonMock, $toolbar->loadButtonType('Separator'));
    }

    /**
     * Tests Load a button
     *
     * @return  void
     *
     * @since   3.0
     */
    public function testLoadButtonTypeReturnsFalseForUnknownButtonTypes()
    {
        $toolbarFactoryMock = $this->createMock(ToolbarFactoryInterface::class);
        $toolbarFactoryMock
            ->expects($this->once())
            ->method('createButton')
            ->willThrowException(new \InvalidArgumentException());

        $toolbar = new Toolbar('my-toolbar', $toolbarFactoryMock);

        $this->assertFalse($toolbar->loadButtonType('INVALID'));
    }

    /**
     * Tests the addButtonPath method with an array parameter
     *
     * @return  void
     *
     * @since   3.0
     */
    public function testAddButtonPathWithArray()
    {
        $toolbar = $this->createToolbar();

        $initialValue = $toolbar->getButtonPath();
        $initialCount = count($initialValue);
        $toolbar->addButtonPath(['MyTestPath1', 'MyTestPath2']);
        $newValue = $toolbar->getButtonPath();

        $this->assertEquals('MyTestPath2' . DIRECTORY_SEPARATOR, $newValue[0]);
        $this->assertEquals('MyTestPath1' . DIRECTORY_SEPARATOR, $newValue[1]);

        for ($i = 0; $i < $initialCount; $i++) {
            $this->assertEquals($initialValue[$i], $newValue[$i + 2]);
        }
    }

    /**
     * Tests the addButtonPath method with a string parameter
     *
     * @return  void
     *
     * @since   3.0
     */
    public function testAddButtonPathWithString()
    {
        $toolbar = $this->createToolbar();

        $initialValue = $toolbar->getButtonPath();
        $initialCount = count($initialValue);
        $toolbar->addButtonPath('MyTestPath');
        $newValue = $toolbar->getButtonPath();

        $this->assertEquals('MyTestPath' . DIRECTORY_SEPARATOR, $newValue[0]);

        for ($i = 0; $i < $initialCount; $i++) {
            $this->assertEquals($initialValue[$i], $newValue[$i + 1]);
        }
    }

    /**
     * Helper function to create a toolbar
     *
     * @param   string   $name  Name
     *
     * @return Toolbar
     *
     * @since   4.0.0
     */
    protected function createToolbar($name = 'my-toolbar'): Toolbar
    {
        $toolbarFactoryMock = $this->createMock(ToolbarFactoryInterface::class);

        return new Toolbar($name, $toolbarFactoryMock);
    }
}
