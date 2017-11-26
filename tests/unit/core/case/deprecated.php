<?php
/**
 * Part of 40dev project.
 *
 * @copyright  Copyright (C) 2017 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

/**
 * The TestDeprecated class.
 *
 * @since  __DEPLOY_VERSION__
 *
 * @deprecated  Should be removed after all test code updated for phpunit >= 6.0.
 */
trait TestCaseDeprecated
{
	/**
	 * Returns a mock object for the specified class.
	 *
	 * This method is a temporary solution to provide backward compatibility for tests that are still using the old
	 * (4.8) getMock() method.
	 * We should update the code and remove this method but for now this is good enough.
	 *
	 * @note  This method is a fork from https://stackoverflow.com/a/46147201 to support older test code.
	 * @todo  Should be remove after all code updated.
	 *
	 * @param string     $originalClassName       Name of the class to mock.
	 * @param array|null $methods                 When provided, only methods whose names are in the array
	 *                                            are replaced with a configurable test double. The behavior
	 *                                            of the other methods is not changed.
	 *                                            Providing null means that no methods will be replaced.
	 * @param array      $arguments               Parameters to pass to the original class' constructor.
	 * @param string     $mockClassName           Class name for the generated test double class.
	 * @param bool       $callOriginalConstructor Can be used to disable the call to the original class' constructor.
	 * @param bool       $callOriginalClone       Can be used to disable the call to the original class' clone constructor.
	 * @param bool       $callAutoload            Can be used to disable __autoload() during the generation of the test double class.
	 * @param bool       $cloneArguments
	 * @param bool       $callOriginalMethods
	 * @param object     $proxyTarget
	 *
	 * @return \PHPUnit_Framework_MockObject_MockObject
	 *
	 * @throws \Exception
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @deprecated  Use createMock() or getMockBuilder() instead.
	 */
	public function getMock($originalClassName, $methods = array(), array $arguments = array(), $mockClassName = '', $callOriginalConstructor = true, $callOriginalClone = true, $callAutoload = true, $cloneArguments = false, $callOriginalMethods = false, $proxyTarget = null)
	{
		$builder = $this->getMockBuilder($originalClassName);

		if (is_array($methods)) {
			$builder->setMethods($methods);
		}

		if (is_array($arguments)) {
			$builder->setConstructorArgs($arguments);
		}

		$callOriginalConstructor ? $builder->enableOriginalConstructor() : $builder->disableOriginalConstructor();
		$callOriginalClone ? $builder->enableOriginalClone() : $builder->disableOriginalClone();
		$callAutoload ? $builder->enableAutoload() : $builder->disableAutoload();
		$cloneArguments ? $builder->enableOriginalClone() : $builder->disableOriginalClone();
		$callOriginalMethods ? $builder->enableProxyingToOriginalMethods() : $builder->disableProxyingToOriginalMethods();

		if ($mockClassName) {
			$builder->setMockClassName($mockClassName);
		}

		if ($proxyTarget) {
			$builder->setProxyTarget($proxyTarget);
		}

		$mockObject = $builder->getMock();

		return $mockObject;
	}

	/**
	 * Returns a mock object for the specified abstract class with all abstract
	 * methods of the class mocked. Concrete methods are not mocked by default.
	 * To mock concrete methods, use the 7th parameter ($mockedMethods).
	 *
	 * @param string $originalClassName
	 * @param array  $arguments
	 * @param string $mockClassName
	 * @param bool   $callOriginalConstructor
	 * @param bool   $callOriginalClone
	 * @param bool   $callAutoload
	 * @param array  $mockedMethods
	 * @param bool   $cloneArguments
	 *
	 * @return PHPUnit_Framework_MockObject_MockObject
	 *
	 * @throws Exception
	 *
	 * @deprecated  Use $this->getMockObjectGenerator()->getMockForAbstractClass() instead.
	 */
	public function getMockForAbstractClass($originalClassName, array $arguments = [], $mockClassName = '', $callOriginalConstructor = true, $callOriginalClone = true, $callAutoload = true, $mockedMethods = [], $cloneArguments = false)
	{
		return parent::getMockForAbstractClass(...func_get_args());
	}

	public static function assertTag($matcher, $actual, $message = '', $isHtml = true)
	{
		$dom     = \PHPUnit\Util\Xml::load($actual, $isHtml);
		$tags    = TestDomhelper::findNodes($dom, $matcher, $isHtml);
		$matched = count($tags) > 0 && $tags[0] instanceof DOMNode;

		self::assertTrue($matched, $message);
	}

	public static function assertNotTag($matcher, $actual, $message = '', $ishtml = true) {
		$dom = \PHPUnit\Util\Xml::load($actual, $ishtml);
		$tags = TestDomhelper::findNodes($dom, $matcher, $ishtml);
		$matched = count($tags) > 0 && $tags[0] instanceof DOMNode;
		self::assertFalse($matched, $message);
	}
}
