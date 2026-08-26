<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Html;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\Helpers\Content;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;
use Joomla\Component\Content\Site\Model\ArticlesModel;
use Joomla\Registry\Registry;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for Content helper.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Html
 * @since       6.1.0
 */
class ContentTest extends UnitTestCase
{
    /**
     * @var  CMSApplicationInterface|null
     */
    private $application;

    /**
     * @return  void
     *
     * @since   6.1.0
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->application = Factory::$application;
    }

    /**
     * @return  void
     *
     * @since   6.1.0
     */
    protected function tearDown(): void
    {
        Factory::$application = $this->application;

        parent::tearDown();
    }

    /**
     * @return  void
     *
     * @since   6.1.0
     */
    public function testMonthsPreservesParamsRegistry(): void
    {
        $params = new Registry(['orderby_sec' => 'created']);
        $state  = new Registry([
            'category.id'      => 7,
            'filter.published' => 1,
        ]);
        $state->set('params', $params);
        $modelState = [];
        $model      = $this->createMock(ArticlesModel::class);
        $model->method('setState')->willReturnCallback(function ($key, $value) use (&$modelState) {
            $modelState[$key] = $value;
        });
        $model->method('countItemsByMonth')->willReturnCallback(function () use (&$modelState) {
            $modelState['params']->get('orderby_sec');

            return [];
        });

        $mvcFactory = $this->createStub(MVCFactoryInterface::class);
        $mvcFactory->method('createModel')->willReturn($model);

        $component = $this->createStub(ContentComponent::class);
        $component->method('getMVCFactory')->willReturn($mvcFactory);

        $application = $this->createStub(CMSApplicationInterface::class);
        $application->method('bootComponent')->willReturn($component);
        Factory::$application = $application;

        $this->assertSame([], Content::months($state));
        $this->assertSame($params, $modelState['params']);
        $this->assertSame(1, $modelState['filter.published']);
    }
}
