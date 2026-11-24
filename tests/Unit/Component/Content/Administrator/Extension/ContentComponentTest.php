<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  com_content
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Component\Content\Administrator\Extension;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Factory;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Tests article custom-field section resolution.
 *
 * @since  __DEPLOY_VERSION__
 */
class ContentComponentTest extends UnitTestCase
{
    /**
     * Tests that the articles section maps to the article section in the administrator only.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testArticlesSectionMapsToArticleOnlyInAdministrator(): void
    {
        $oldApplication = Factory::$application;

        try {
            $application = $this->createMock(CMSApplicationInterface::class);
            $application->method('isClient')->willReturnCallback(static fn ($client) => $client === 'administrator');
            Factory::$application = $application;

            $component = new ContentComponent($this->createStub(ComponentDispatcherFactoryInterface::class));
            $this->assertSame('article', $component->validateSection('articles'));
            $this->assertSame('article', $component->validateSection('article'));

            $siteApplication = $this->createMock(CMSApplicationInterface::class);
            $siteApplication->method('isClient')->willReturnCallback(static fn ($client) => $client === 'site');
            Factory::$application = $siteApplication;
            $this->assertNull($component->validateSection('articles'));
        } finally {
            Factory::$application = $oldApplication;
        }
    }
}
