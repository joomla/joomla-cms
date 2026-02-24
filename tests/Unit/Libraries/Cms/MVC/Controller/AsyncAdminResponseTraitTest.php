<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  MVC
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\MVC\Controller;

use Joomla\CMS\MVC\Controller\AsyncAdminResponseTrait;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for \Joomla\CMS\MVC\Controller\AsyncAdminResponseTrait
 *
 * @testdox  The AsyncAdminResponseTrait
 *
 * @since    6.1.0
 */
class AsyncAdminResponseTraitTest extends UnitTestCase
{
    /**
     * @testdox  builds a complete normalized envelope
     *
     * @return  void
     *
     * @since   6.1.0
     */
    public function testBuildEnvelopeWithProvidedData(): void
    {
        $subject = new class {
            use AsyncAdminResponseTrait;

            public function build(array $messages, array $fragments, array $meta): array
            {
                return $this->buildAsyncAdminResponseEnvelope(true, $messages, 'index.php?option=com_content', $fragments, $meta);
            }
        };

        $result = $subject->build(
            [
                'message' => ['Saved'],
                'warning' => 'Needs review',
                'error'   => ['One error'],
            ],
            ['list' => '<table></table>'],
            ['component' => 'com_content', 'view' => 'articles']
        );

        $this->assertTrue($result['success']);
        $this->assertSame('index.php?option=com_content', $result['redirect']);
        $this->assertSame(['list' => '<table></table>'], $result['fragments']);
        $this->assertSame(['component' => 'com_content', 'view' => 'articles'], $result['meta']);
        $this->assertSame(['Saved'], $result['messages']['message']);
        $this->assertSame(['Needs review'], $result['messages']['warning']);
        $this->assertSame(['One error'], $result['messages']['error']);
    }

    /**
     * @testdox  returns empty message buckets when none are provided
     *
     * @return  void
     *
     * @since   6.1.0
     */
    public function testBuildEnvelopeWithDefaultMessages(): void
    {
        $subject = new class {
            use AsyncAdminResponseTrait;

            public function build(): array
            {
                return $this->buildAsyncAdminResponseEnvelope(false);
            }
        };

        $result = $subject->build();

        $this->assertFalse($result['success']);
        $this->assertNull($result['redirect']);
        $this->assertSame([], $result['fragments']);
        $this->assertSame([], $result['meta']);
        $this->assertSame([], $result['messages']['message']);
        $this->assertSame([], $result['messages']['warning']);
        $this->assertSame([], $result['messages']['error']);
    }
}
