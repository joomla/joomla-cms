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
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Test class for JobPosting plugin
 *
 * @package     Joomla.UnitTest
 * @subpackage  JobPosting
 *
 * @since       6.2.0
 */
#[TestDox('The JobPosting plugin')]
class JobPostingPluginTest extends UnitTestCase
{
    /**
     * Dispatches the schema through the plugin and returns the resulting graph.
     *
     * @param   array  $entry  The JobPosting graph entry to compile
     *
     * @return  array  The compiled graph entry
     *
     * @since   6.2.0
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
     * @return  void
     *
     * @since   6.2.0
     */
    #[TestDox('maps a telecommute job location type to the TELECOMMUTE value expected by search engines')]
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
     * @return  void
     *
     * @since   6.2.0
     */
    #[TestDox('maps a hybrid job location type to TELECOMMUTE while keeping the physical job location')]
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
     * @return  void
     *
     * @since   6.2.0
     */
    #[TestDox('omits the job location type for an on-site job so only the job location is emitted')]
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
     * @return  void
     *
     * @since   6.2.0
     */
    #[TestDox('leaves an entry without a job location type untouched')]
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
     * @return  void
     *
     * @since   6.2.0
     */
    #[TestDox('does not touch graph entries of other schema types')]
    public function testOtherSchemaTypesAreUntouched()
    {
        $entry = $this->compileGraphEntry([
            '@type'           => 'Event',
            'jobLocationType' => 'Onsite',
        ]);

        $this->assertSame('Onsite', $entry['jobLocationType']);
    }
}
