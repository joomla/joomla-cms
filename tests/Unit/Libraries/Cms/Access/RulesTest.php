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
use Joomla\CMS\Access\Rules;
use Joomla\CMS\Object\CMSObject;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for \Joomla\CMS\Access\Rules.
 *
 * @since       1.7.0
 */
class RulesTest extends UnitTestCase
{
    /**
     *
     * @return void
     * @since   4.0.0
     */
    public function testIsConstructableWithInputString()
    {
        $ruleIdentities = [
            -42 => 1,
            2   => 1,
            3   => 0,
        ];

        $input = [
            'edit' => $ruleIdentities,
        ];

        // Test input as string.
        $rules = new Rules(json_encode($input));

        $editRule = $rules->getData()['edit'];
        $this->assertInstanceOf(Rule::class, $editRule);
        $this->assertEquals($ruleIdentities, $editRule->getData());
    }

    /**
     *
     * @return void
     * @since   4.0.0
     */
    public function testIsConstructableWithArray()
    {
        $ruleIdentities = [
            -42 => 1,
            2   => 1,
            3   => 0,
        ];

        $input = [
            'edit' => $ruleIdentities,
        ];

        $rules = new Rules($input);

        $editRule = $rules->getData()['edit'];
        $this->assertInstanceOf(Rule::class, $editRule);
        $this->assertEquals($ruleIdentities, $editRule->getData());
    }

    /**
     * Tests the \Joomla\CMS\Access\Rules constructor
     *
     * @return  void
     *
     * @since   1.7.0
     */
    public function testIsConstructableWithObject()
    {
        $ruleIdentities = [
            -42 => 1,
            2   => 1,
            3   => 0,
        ];

        $input = [
            'edit' => $ruleIdentities,
        ];

        $rules = new Rules((object) $input);

        $editRule = $rules->getData()['edit'];
        $this->assertInstanceOf(Rule::class, $editRule);
        $this->assertEquals($ruleIdentities, $editRule->getData());
    }

    /**
     * Tests the \Joomla\CMS\Access\Rules::mergeAction method.
     *
     * @return  void
     *
     * @since   1.7.0
     */
    public function testMergeAction()
    {
        $ruleIdentities = [
            -42 => 1,
            2   => 1,
            3   => 0,
        ];

        // Construct and empty \Joomla\CMS\Access\Rules.
        $rules = new Rules();
        $rules->mergeAction('edit', $ruleIdentities);

        $editRule = $rules->getData()['edit'];
        $this->assertInstanceOf(Rule::class, $editRule);
        $this->assertEquals($ruleIdentities, $editRule->getData());

        // Merge a new set, flipping some bits.
        // Ident 3 should remain false, 4 should be added.
        $newRuleIdentities = [
            -42 => 0,
            2   => 1,
            3   => 1,
            4   => 1,
        ];
        $rules->mergeAction('edit', $newRuleIdentities);

        $editRule = $rules->getData()['edit'];
        $this->assertEquals(
            [
                -42 => 0,
                2   => 1,
                3   => 0,
                4   => 1,
            ],
            $editRule->getData()
        );
    }

    /**
     * Tests the \Joomla\CMS\Access\Rules::merge method.
     *
     * @return  void
     *
     * @since   1.7.0
     */
    public function testMerge()
    {
        $ruleData1 = [
            'edit' => [
                -42 => 1,
            ],
            'delete' => [
                -42 => 0,
            ],
        ];

        $ruleData2 = [
            'create' => [
                2 => 1,
            ],
            'delete' => [
                2 => 0,
            ],
        ];

        $expectedResult = [
            'edit' => [
                -42 => 1,
            ],
            'delete' => [
                -42 => 0,
                2   => 0,
            ],
            'create' => [
                2 => 1,
            ],
        ];

        $rules1 = new Rules($ruleData1);
        $rules2 = new Rules($ruleData2);
        $rules1->merge($rules2);

        $editRule   = $rules1->getData()['edit'];
        $deleteRule = $rules1->getData()['delete'];
        $createRule = $rules1->getData()['create'];

        $this->assertInstanceOf(Rule::class, $editRule);
        $this->assertInstanceOf(Rule::class, $deleteRule);
        $this->assertInstanceOf(Rule::class, $createRule);
        $this->assertEquals($expectedResult['edit'], $editRule->getData());
        $this->assertEquals($expectedResult['delete'], $deleteRule->getData());
        $this->assertEquals($expectedResult['create'], $createRule->getData());
    }

    /**
     * Tests the \Joomla\CMS\Access\Rules::merge method
     *
     * @return  void
     *
     * @since   1.7.0
     */
    public function testMergeRulesNull()
    {
        $ruleData = [
            'edit' => [
                -42 => 1,
            ],
            'delete' => [
                -42 => 0,
            ],
        ];

        $rules1 = new Rules($ruleData);
        $rules2 = new Rules('');
        $rules2->merge($rules1);

        $editRule   = $rules1->getData()['edit'];
        $deleteRule = $rules1->getData()['delete'];

        $this->assertInstanceOf(Rule::class, $editRule);
        $this->assertInstanceOf(Rule::class, $deleteRule);
        $this->assertEquals($ruleData['edit'], $editRule->getData());
        $this->assertEquals($ruleData['delete'], $deleteRule->getData());
    }

    /**
     * Tests the \Joomla\CMS\Access\Rules::merge method
     *
     * @return  void
     *
     * @since   1.7.0
     */
    public function testMergeRules()
    {
        $ruleData1 = [
            'edit' => [
                -42 => 1,
            ],
            'delete' => [
                -42 => 0,
            ],
        ];

        $ruleData2 = [
            'create' => [
                2 => 1,
            ],
            'delete' => [
                2 => 0,
            ],
        ];

        $expectedResult = [
            'edit' => [
                -42 => 1,
            ],
            'delete' => [
                -42 => 0,
                2   => 0,
            ],
            'create' => [
                2 => 1,
            ],
        ];

        $rules1 = new Rules($ruleData1);
        $rules1->merge($ruleData2);

        $editRule   = $rules1->getData()['edit'];
        $deleteRule = $rules1->getData()['delete'];
        $createRule = $rules1->getData()['create'];

        $this->assertInstanceOf(Rule::class, $editRule);
        $this->assertInstanceOf(Rule::class, $deleteRule);
        $this->assertInstanceOf(Rule::class, $createRule);
        $this->assertEquals($expectedResult['edit'], $editRule->getData());
        $this->assertEquals($expectedResult['delete'], $deleteRule->getData());
        $this->assertEquals($expectedResult['create'], $createRule->getData());
    }

    /**
     * Tests the \Joomla\CMS\Access\Rules::allow method.
     *
     * @return  void
     *
     * @since   1.7.0
     */
    public function testAllow()
    {
        $ruleData = [
            'edit' => [
                -42 => 1,
            ],
            'delete' => [
                -42 => 0,
                2   => 1,
            ],
        ];

        $rules = new Rules($ruleData);

        // Explicit allow.
        $this->assertTrue($rules->allow('edit', -42));
        $this->assertTrue($rules->allow('edit', '-42'));
        $this->assertNull($rules->allow('edit', 999));
        $this->assertFalse($rules->allow('delete', -42));
        $this->assertTrue($rules->allow('edit', [-42, 999]));
        $this->assertFalse($rules->allow('delete', [-42, 2]));
        $this->assertNull($rules->allow('unknown', [-42, 2]));
    }

    /**
     * Tests the \Joomla\CMS\Access\Rules::getAllowed method.
     *
     * @return  void
     *
     * @since   1.7.0
     */
    public function testGetAllowed()
    {
        $ruleData = [
            'create' => [
                -42 => 1,
            ],
            'edit' => [
                -42 => 1,
            ],
            'delete' => [
                -42 => 0,
                2   => 1,
            ],
        ];

        $rules   = new Rules($ruleData);
        $allowed = $rules->getAllowed(-42);

        $this->assertInstanceOf(CMSObject::class, $allowed);
        $this->assertTrue($allowed->get('create'));
        $this->assertTrue($allowed->get('edit'));
        $this->assertNull($allowed->get('delete'));
    }

    /**
     *
     * @return void
     * @since   4.0.0
     */
    public function testToString()
    {
        $ruleData = [
            'create' => [
                -42 => 1,
            ],
            'edit' => [
                -42 => 1,
            ],
            'delete' => [
                -42 => 0,
                2   => 1,
            ],
        ];

        $rules = new Rules($ruleData);

        $this->assertEquals(json_encode($ruleData), (string) $rules);
    }
}
