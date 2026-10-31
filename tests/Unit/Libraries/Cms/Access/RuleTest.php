<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Access
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Access;

use Joomla\CMS\Access\Rule;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for \Joomla\CMS\Access\Rule.
 *
 * @since    1.7.0
 */
class RuleTest extends UnitTestCase
{
    /**
     * Tests the \Joomla\CMS\Access\Rule::__construct and \Joomla\CMS\Access\Rule::__toString methods.
     *
     * @return  void
     *
     * @since   1.7.0
     */
    public function testIsConstructableWithString()
    {
        $ruleData = [
            -42 => 1,
            2   => 1,
            3   => 0,
        ];

        // Test constructor with array.
        $rule = new Rule(json_encode($ruleData));

        $this->assertSame($ruleData, $rule->getData());
    }

    /**
     * Tests the \Joomla\CMS\Access\Rule::__construct and \Joomla\CMS\Access\Rule::__toString methods.
     *
     * @return  void
     *
     * @since   1.7.0
     */
    public function testIsConstructableWithArray()
    {
        $ruleData = [
            -42 => 1,
            2   => 1,
            3   => 0,
        ];

        // Test constructor with array.
        $rule = new Rule($ruleData);

        $this->assertSame($ruleData, $rule->getData());
    }

    /**
     * Tests the \Joomla\CMS\Access\Rule::mergeIdentity method.
     *
     * @return  void
     *
     * @since   1.7.0
     */
    public function testMergeIdentity()
    {
        // Construct a rule with no identities.
        $rule = new Rule('');

        // Add the identity with allow.
        $rule->mergeIdentity(-42, true);
        $this->assertSame('{"-42":1}', (string) $rule);

        // Read the identity, but deny.
        $rule->mergeIdentity(-42, false);
        $this->assertSame('{"-42":0}', (string) $rule);

        // Read the identity with allow (checking deny wins).
        $rule->mergeIdentity(-42, true);
        $this->assertSame('{"-42":0}', (string) $rule);
    }

    /**
     * Tests the \Joomla\CMS\Access\Rule::mergeIdentities method.
     *
     * @return  void
     *
     * @since   1.7.0
     */
    public function testMergeIdentities()
    {
        $ruleData = [
            -42 => 1,
            2   => 1,
            3   => 0,
        ];

        // Construct a rule with no identities.
        $rule = new Rule('');

        $rule->mergeIdentities($ruleData);
        $this->assertSame(json_encode($ruleData), (string) $rule);

        $rule2 = new Rule($ruleData);
        $rule->mergeIdentities($rule2);
        $this->assertSame(json_encode($ruleData), (string) $rule);

        $this->assertSame((string) $rule2, (string) $rule);

        // Merge a new set, flipping some bits.
        $ruleData2 = [
            -42 => 0,
            2   => 1,
            3   => 1,
            4   => 1,
        ];

        // Ident 3 should remain false, 4 should be added.
        $expectedResult = [
            -42 => 0,
            2   => 1,
            3   => 0,
            4   => 1,
        ];
        $rule->mergeIdentities($ruleData2);
        $this->assertSame(json_encode($expectedResult), (string) $rule);
    }

    /**
     * Tests the \Joomla\CMS\Access\Rule::allow method.
     *
     * @return  void
     *
     * @since   1.7.0
     */
    public function testAllow()
    {
        // Simple allow and deny test.
        $ruleData = [
            -42 => 0,
            2   => 1,
        ];
        $rule = new Rule($ruleData);

        // This one should be denied.
        $this->assertFalse($rule->allow(-42));
        $this->assertSame(null, $rule->allow(null));
        $this->assertTrue($rule->allow(2));
        $this->assertFalse($rule->allow([-42, 2]));
    }

    /**
     * @return void
     * @since   4.0.0
     */
    public function testToString()
    {
        $ruleData = [
            -42 => 0,
            2   => 1,
        ];

        $rule = new Rule($ruleData);

        $this->assertSame(json_encode($ruleData), (string) $rule);
    }
}
