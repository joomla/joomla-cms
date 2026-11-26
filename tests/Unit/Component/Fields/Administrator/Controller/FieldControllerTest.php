<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Fields
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Component\Fields\Administrator\Controller;

use Joomla\Component\Fields\Administrator\Controller\FieldController;
use Joomla\Component\Fields\Administrator\Controller\GroupController;
use Joomla\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Test class for the save permission check of the field and field group controllers
 *
 * @package     Joomla.UnitTest
 * @subpackage  Fields
 * @since       6.2.1
 */
class FieldControllerTest extends UnitTestCase
{
    /**
     * Data provider for testAllowSave
     *
     * @return  array
     *
     * @since   6.2.1
     */
    public static function allowSaveProvider(): array
    {
        $cases = [
            'same context new record'          => [['id' => 0, 'context' => 'com_content.article'], true],
            'same context existing record'     => [['id' => 5, 'context' => 'com_content.article'], true],
            'no posted context'                => [['id' => 0], true],
            'other context new record'         => [['id' => 0, 'context' => 'com_users.user'], false],
            'other context existing record'    => [['id' => 5, 'context' => 'com_users.user'], false],
            'component name without section'   => [['id' => 0, 'context' => 'com_users'], false],
            'component as prefix of the other' => [['id' => 0, 'context' => 'com_content_x.article'], false],
            'empty context'                    => [['id' => 0, 'context' => ''], false],
            'array as context'                 => [['id' => 0, 'context' => ['com_content.article']], false],
        ];

        $data = [];

        foreach ([FieldController::class, GroupController::class] as $class) {
            foreach ($cases as $name => $case) {
                $data[substr(strrchr($class, '\\'), 1) . ': ' . $name] = [$class, ...$case];
            }
        }

        return $data;
    }

    /**
     * Tests that a posted context of another component is rejected
     *
     * @param   string  $class     The controller class
     * @param   array   $data      The posted form data
     * @param   bool    $expected  The expected result
     *
     * @return  void
     *
     * @since   6.2.1
     */
    #[TestDox('allowSave rejects a posted context of another component: $_dataName')]
    #[DataProvider('allowSaveProvider')]
    public function testAllowSave(string $class, array $data, bool $expected): void
    {
        $controller = $this->createController($class);

        // The request context is com_content.article
        (new \ReflectionProperty($class, 'component'))->setValue($controller, 'com_content');

        $this->assertSame($expected, $controller->runAllowSave($data));
    }

    /**
     * Creates the controller without the constructor, the add and edit checks grant access
     *
     * @param   string  $class  The controller class
     *
     * @return  object
     *
     * @since   6.2.1
     */
    private function createController(string $class): object
    {
        if ($class === FieldController::class) {
            return new class () extends FieldController {
                public function __construct()
                {
                }

                protected function allowAdd($data = [])
                {
                    return true;
                }

                protected function allowEdit($data = [], $key = 'id')
                {
                    return true;
                }

                public function runAllowSave($data)
                {
                    return $this->allowSave($data);
                }
            };
        }

        return new class () extends GroupController {
            public function __construct()
            {
            }

            protected function allowAdd($data = [])
            {
                return true;
            }

            protected function allowEdit($data = [], $key = 'parent_id')
            {
                return true;
            }

            public function runAllowSave($data)
            {
                return $this->allowSave($data);
            }
        };
    }
}
