<?php
namespace enshrined\svgSanitize\Tests;

use enshrined\svgSanitize\ElementReference\Subject;
use PHPUnit\Framework\TestCase;

/**
 * Class SubjectTest
 */
class SubjectTest extends TestCase
{
    protected $nestingLimit = 15;

    /**
     * <first>
     *   <!-- 0 -->
     * </first>
     *
     * @test
     */
    public function oneLevelCountsUseIsCorrect()
    {
        $first = new \DOMElement('first');
        $firstSubject = new Subject($first, $this->nestingLimit);
        self::assertSame(0, $firstSubject->countUse(false));
        self::assertSame(0, $firstSubject->countUse(true));
    }

    /**
     * <first>
     *   <second /> <!-- 1 -->
     *   <second /> <!-- 2 -->
     * </first>
     *
     * @test
     */
    public function twoLevelsCountUseIsCorrect()
    {
        $first = new \DOMElement('first');
        $second = new \DOMElement('second');
        $firstSubject = new Subject($first, $this->nestingLimit);
        $secondSubject = new Subject($second, $this->nestingLimit);
        $firstSubject->addUse($secondSubject);
        $firstSubject->addUse($secondSubject);
        self::assertSame(2, $firstSubject->countUse(false));
        self::assertSame(2, $firstSubject->countUse(true));
    }

    /**
     * <first>
     *   <second>    <!-- accumulated=false: 0; accumulated=true: 1 -->
     *     <third /> <!-- accumulated=false: 1; accumulated=true: 2 -->
     *     <third /> <!-- accumulated=false: 2; accumulated=true: 3 -->
     *     <third /> <!-- accumulated=false: 3; accumulated=true: 4 -->
     *   </second>
     *   <second>    <!-- accumulated=false: 3; accumulated=true: 5 -->
     *     <third /> <!-- accumulated=false: 4; accumulated=true: 6 -->
     *     <third /> <!-- accumulated=false: 5; accumulated=true: 7 -->
     *     <third /> <!-- accumulated=false: 6; accumulated=true: 8 -->
     *   </second>
     * </first>
     *
     * @test
     */
    public function threeLevelsCountUseIsCorrect()
    {
        $first = new \DOMElement('first');
        $second = new \DOMElement('second');
        $third = new \DOMElement('third');
        $firstSubject = new Subject($first, $this->nestingLimit);
        $secondSubject = new Subject($second, $this->nestingLimit);
        $thirdSubject = new Subject($third, $this->nestingLimit);
        $firstSubject->addUse($secondSubject);
        $firstSubject->addUse($secondSubject);
        $secondSubject->addUse($thirdSubject);
        $secondSubject->addUse($thirdSubject);
        $secondSubject->addUse($thirdSubject);
        self::assertSame(6, $firstSubject->countUse(false));
        self::assertSame(8, $firstSubject->countUse(true));
    }

    /**
     * <first>
     * </first>
     *
     * @test
     */
    public function oneLevelCountsUsedInIsCorrect()
    {
        $first = new \DOMElement('first');
        $firstSubject = new Subject($first, $this->nestingLimit);
        self::assertSame(0, $firstSubject->countUsedIn());
    }

    /**
     * <first>
     *   <second /> <!-- 1 -->
     *   <second /> <!-- 2 -->
     * </first>
     *
     * @test
     */
    public function twoLevelsCountUsedInIsCorrect()
    {
        $first = new \DOMElement('first');
        $second = new \DOMElement('second');
        $firstSubject = new Subject($first, $this->nestingLimit);
        $secondSubject = new Subject($second, $this->nestingLimit);
        $secondSubject->addUsedIn($firstSubject);
        $secondSubject->addUsedIn($firstSubject);
        self::assertSame(2, $secondSubject->countUsedIn());
    }

    /**
     * <first>
     *   <second>
     *     <third /> <!-- 1 -->
     *     <third /> <!-- 2 -->
     *     <third /> <!-- 3 -->
     *   </second>
     *   <second>
     *     <third /> <!-- 4 -->
     *     <third /> <!-- 5 -->
     *     <third /> <!-- 6 -->
     *   </second>
     * </first>
     *
     * @test
     */
    public function threeLevelsCountUsedInIsCorrect()
    {
        $first = new \DOMElement('first');
        $second = new \DOMElement('second');
        $third = new \DOMElement('third');
        $firstSubject = new Subject($first, $this->nestingLimit);
        $secondSubject = new Subject($second, $this->nestingLimit);
        $thirdSubject = new Subject($third, $this->nestingLimit);
        $thirdSubject->addUsedIn($secondSubject);
        $thirdSubject->addUsedIn($secondSubject);
        $thirdSubject->addUsedIn($secondSubject);
        $secondSubject->addUsedIn($firstSubject);
        $secondSubject->addUsedIn($firstSubject);
        self::assertSame(6, $thirdSubject->countUsedIn());
    }
}
