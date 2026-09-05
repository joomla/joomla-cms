<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Application
 *
 * @copyright  (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Application;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Filter\InputFilter;
use Joomla\Input\Input as FrameworkInput;
use Joomla\Registry\Registry;
use Joomla\Tests\Unit\UnitTestCase;

class CMSApplicationTest extends UnitTestCase
{
    /**
     * @testdox  An array request cannot replace a scalar user state
     *
     * @return void
     */
    public function testArrayRequestCannotReplaceScalarState()
    {
        $app = $this->createApplication(['limit' => ['5']]);

        $this->assertSame(20, $app->getUserStateFromRequest('global.list.limit', 'limit', 20, 'uint'));
        $this->assertNull($app->getUserState('global.list.limit'));
    }

    /**
     * @testdox  A previously stored array is replaced by the scalar default
     *
     * @return void
     */
    public function testStoredArrayIsReplacedByScalarDefault()
    {
        $app = $this->createApplication([]);
        $app->setUserState('global.list.limit', [5]);

        $this->assertSame(20, $app->getUserStateFromRequest('global.list.limit', 'limit', 20, 'uint'));
        $this->assertSame(20, $app->getUserState('global.list.limit'));
    }

    /**
     * @testdox  A scalar request is stored normally
     *
     * @return void
     */
    public function testScalarRequestIsStored()
    {
        $app = $this->createApplication(['limit' => '5']);

        $this->assertSame(5, $app->getUserStateFromRequest('global.list.limit', 'limit', 20, 'uint'));
        $this->assertSame(5, $app->getUserState('global.list.limit'));
    }

    /**
     * @testdox  An array default continues to allow recursively filtered arrays
     *
     * @return void
     */
    public function testArrayDefaultAllowsArrayRequest()
    {
        $app = $this->createApplication(['limit' => ['5']]);

        $this->assertSame([5], $app->getUserStateFromRequest('component.list.limit', 'limit', [], 'uint'));
        $this->assertSame([5], $app->getUserState('component.list.limit'));
    }

    /**
     * @testdox  Ordinary scalar and explicitly declared collection state remains compatible
     *
     * @dataProvider compatibleStateProvider
     *
     * @return void
     */
    public function testCompatibleStateRequests(
        array $input,
        array $savedState,
        $default,
        $type,
        $expectedValue,
        $expectedState
    ) {
        $app = $this->createApplication($input);

        foreach ($savedState as $key => $value) {
            $app->setUserState($key, $value);
        }

        $value = $type === null
            ? $app->getUserStateFromRequest('component.value', 'value', $default)
            : $app->getUserStateFromRequest('component.value', 'value', $default, $type);

        $this->assertSame($expectedValue, $value);
        $this->assertSame($expectedState, $app->getUserState('component.value'));
    }

    /**
     * Representative core caller shapes and additional supported collection semantics.
     *
     * @return array
     */
    public static function compatibleStateProvider(): array
    {
        return [
            'missing request uses scalar default without storing it' => [
                [], [], 20, 'uint', 20, null,
            ],
            'missing request preserves saved scalar' => [
                [], ['component.value' => 40], 20, 'uint', 40, 40,
            ],
            'zero replaces a saved integer' => [
                ['value' => '0'], ['component.value' => 20], 20, 'uint', 0, 0,
            ],
            'empty string replaces saved text' => [
                ['value' => ''], ['component.value' => 'title'], '', 'string', '', '',
            ],
            'command with a null default' => [
                ['value' => 'com_content'], [], null, 'cmd', 'com_content', 'com_content',
            ],
            'false replaces saved boolean' => [
                ['value' => '0'], ['component.value' => true], true, 'bool', false, false,
            ],
            'array default retains recursive integer filtering' => [
                ['value' => ['5', '10']], [], [], 'uint', [5, 10], [5, 10],
            ],
            'array filter retains nested list ordering' => [
                ['value' => ['multi_ordering' => ['a.title ASC']]], [], [], 'array',
                ['multi_ordering' => ['a.title ASC']], ['multi_ordering' => ['a.title ASC']],
            ],
            'array filter permits a non-array default' => [
                ['value' => ['title', 'id']], [], null, 'array', ['title', 'id'], ['title', 'id'],
            ],
            'raw filter retains an intentional collection' => [
                ['value' => ['ordering' => 'title']], [], null, 'raw', ['ordering' => 'title'], ['ordering' => 'title'],
            ],
            'omitted filter retains an intentional collection' => [
                ['value' => ['ordering' => 'title']], [], null, null, ['ordering' => 'title'], ['ordering' => 'title'],
            ],
            'array filter is case insensitive' => [
                ['value' => ['title']], [], null, 'ARRAY', ['title'], ['title'],
            ],
            'missing request preserves a declared saved collection' => [
                [], ['component.value' => [5, 10]], [], 'uint', [5, 10], [5, 10],
            ],
            'missing request preserves collection with omitted filter' => [
                [], ['component.value' => ['ordering' => 'title']], null, null,
                ['ordering' => 'title'], ['ordering' => 'title'],
            ],
        ];
    }

    /**
     * Helper function to create a CMSApplication with isolated user state
     *
     * @param   array  $input  Request input
     *
     * @return CMSApplication
     */
    private function createApplication(array $input): CMSApplication
    {
        $filter       = InputFilter::getInstance();
        $serverInput  = new FrameworkInput([
            'HTTPS'       => '',
            'HTTP_HOST'   => 'localhost',
            'PHP_SELF'    => '/index.php',
            'REQUEST_URI' => '/',
        ]);
        $requestInput = $this->createMock('Joomla\\CMS\\Input\\Input');
        $requestInput->method('__get')->willReturnCallback(
            static fn ($name) => $name === 'server' ? $serverInput : null
        );
        $requestInput->method('get')->willReturnCallback(
            static function ($name, $default, $type) use ($filter, $input) {
                return \array_key_exists($name, $input) ? $filter->clean($input[$name], $type) : $default;
            }
        );

        return new class ($requestInput, new Registry(['uri.request' => 'http://localhost/'])) extends CMSApplication {
            private $userState = [];

            protected function doExecute()
            {
            }

            public function getUserState($key, $default = null)
            {
                return \array_key_exists($key, $this->userState) ? $this->userState[$key] : $default;
            }

            public function setUserState($key, $value)
            {
                $previous              = $this->getUserState($key);
                $this->userState[$key] = $value;

                return $previous;
            }
        };
    }
}
