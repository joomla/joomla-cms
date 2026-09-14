<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Extension
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Plugin\Schemaorg\JobPosting\Extension;

use Joomla\CMS\Event\Plugin\System\Schemaorg\BeforeCompileHeadEvent;
use Joomla\Plugin\Schemaorg\JobPosting\Extension\JobPosting;
use Joomla\Registry\Registry;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for JobPosting plugin
 *
 * @package     Joomla.UnitTest
 * @subpackage  JobPosting
 *
 * @testdox     The JobPosting plugin
 *
 * @since       __DEPLOY_VERSION__
 */
class JobPostingPluginTest extends UnitTestCase
{
    /**
     * Dispatches the schema through the plugin and returns the resulting graph.
     *
     * @param   array  $entry  The JobPosting graph entry to compile
     *
     * @return  array  The compiled graph entry
     *
     * @since   __DEPLOY_VERSION__
     */
    private function compileGraphEntry(array $entry): array
    {
        $plugin = new JobPosting(['name' => 'jobposting', 'type' => 'schemaorg']);

        $schema = new Registry();
        $schema->loadArray(['@graph' => [$entry]]);

        $event = new BeforeCompileHeadEvent('onSchemaBeforeCompileHead', [
            'subject' => $schema,
            'context' => 'com_content.article.1',
        ]);

        $plugin->onSchemaBeforeCompileHead($event);

        return $schema->get('@graph')[0];
    }

    /**
     * @testdox  maps a telecommute job location type to the TELECOMMUTE value expected by search engines
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testTelecommuteIsMappedToUppercaseTelecommute()
    {
        $entry = $this->compileGraphEntry([
            '@type'           => 'JobPosting',
            'title'           => 'Remote Developer',
            'jobLocationType' => 'Telecommute',
        ]);

        $this->assertSame('TELECOMMUTE', $entry['jobLocationType']);
    }

    /**
     * @testdox  maps a hybrid job location type to TELECOMMUTE while keeping the physical job location
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testHybridIsMappedToTelecommuteAndKeepsJobLocation()
    {
        $jobLocation = [
            '@type'   => 'Place',
            'address' => ['@type' => 'PostalAddress', 'addressLocality' => 'Berlin'],
        ];

        $entry = $this->compileGraphEntry([
            '@type'           => 'JobPosting',
            'title'           => 'Hybrid Developer',
            'jobLocationType' => 'Hybrid',
            'jobLocation'     => $jobLocation,
        ]);

        $this->assertSame('TELECOMMUTE', $entry['jobLocationType']);
        $this->assertSame($jobLocation, $entry['jobLocation']);
    }

    /**
     * @testdox  omits the job location type for an on-site job so only the job location is emitted
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testOnsiteJobLocationTypeIsOmitted()
    {
        $jobLocation = [
            '@type'   => 'Place',
            'address' => ['@type' => 'PostalAddress', 'addressLocality' => 'Berlin'],
        ];

        $entry = $this->compileGraphEntry([
            '@type'           => 'JobPosting',
            'title'           => 'Onsite Developer',
            'jobLocationType' => 'Onsite',
            'jobLocation'     => $jobLocation,
        ]);

        $this->assertArrayNotHasKey('jobLocationType', $entry);
        $this->assertSame($jobLocation, $entry['jobLocation']);
    }

    /**
     * @testdox  leaves an entry without a job location type untouched
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testEntryWithoutJobLocationTypeIsUntouched()
    {
        $entry = $this->compileGraphEntry([
            '@type' => 'JobPosting',
            'title' => 'Developer',
        ]);

        $this->assertArrayNotHasKey('jobLocationType', $entry);
        $this->assertSame('Developer', $entry['title']);
    }

    /**
     * @testdox  does not touch graph entries of other schema types
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testOtherSchemaTypesAreUntouched()
    {
        $entry = $this->compileGraphEntry([
            '@type'           => 'Event',
            'jobLocationType' => 'Onsite',
        ]);

        $this->assertSame('Onsite', $entry['jobLocationType']);
    }
}
