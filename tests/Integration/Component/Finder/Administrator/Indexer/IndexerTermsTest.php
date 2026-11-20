<?php

/**
 * @package     Joomla.IntegrationTest
 * @subpackage  com_finder
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Integration\Component\Finder\Administrator\Indexer;

use Joomla\Component\Finder\Administrator\Indexer\Indexer;
use Joomla\Tests\Integration\DBTestInterface;
use Joomla\Tests\Integration\DBTestTrait;
use Joomla\Tests\Integration\IntegrationTestCase;

/**
 * Tests how the indexer resolves the ids of terms. Whether two spellings are the same term
 * is decided by the collation of the term column, so the indexer has to follow the database.
 *
 * @since  __DEPLOY_VERSION__
 */
class IndexerTermsTest extends IntegrationTestCase implements DBTestInterface
{
    use DBTestTrait;

    /**
     * Retrieve a list of schemas to load for this testcase
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getSchemasToLoad(): array
    {
        return [];
    }

    /**
     * Removes the terms table after each test
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function tearDown(): void
    {
        $db = $this->getDBDriver();
        $db->setQuery('DROP TABLE IF EXISTS ' . $db->quoteName('#__finder_terms'))->execute();

        parent::tearDown();
    }

    /**
     * An accent-insensitive collation treats both spellings as one term, so both get the same id
     * and creating them in the same batch doesn't fail with a duplicate key.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testAccentInsensitiveCollationMapsSpellingsToOneTerm(): void
    {
        if (!$this->isMysql()) {
            $this->markTestSkipped('The term column of PostgreSQL uses the deterministic default collation.');
        }

        $this->createTermsTable('utf8mb4_unicode_ci');

        $ids = $this->resolveTermIds(['korišćenja', 'koriscenja', 'usluga']);

        $this->assertSame($ids['korišćenja'], $ids['koriscenja']);
        $this->assertNotSame($ids['korišćenja'], $ids['usluga']);
        $this->assertSame(2, $this->countTerms());

        // A later item with another spelling gets the existing term
        $ids2 = $this->resolveTermIds(['koriscenja', 'KORIŠĆENJA']);

        $this->assertSame($ids['korišćenja'], $ids2['koriscenja']);
        $this->assertSame($ids['korišćenja'], $ids2['KORIŠĆENJA']);
        $this->assertSame(2, $this->countTerms());
    }

    /**
     * A binary collation (MySQL) or the default collation (PostgreSQL) keeps the spellings apart.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testAccentSensitiveCollationKeepsSpellingsApart(): void
    {
        $this->createTermsTable('utf8mb4_bin');

        $ids = $this->resolveTermIds(['korišćenja', 'koriscenja', 'usluga']);

        $this->assertCount(3, array_unique($ids));
        $this->assertSame(3, $this->countTerms());

        // Existing terms are found again
        $this->assertSame($ids, $this->resolveTermIds(['korišćenja', 'koriscenja', 'usluga']));
        $this->assertSame(3, $this->countTerms());
    }

    /**
     * Numeric terms, which PHP turns into integer array keys, and terms with quotes are resolved.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testSpecialTerms(): void
    {
        $this->createTermsTable('utf8mb4_bin');

        $ids = $this->resolveTermIds(['2026', "o'neil", 'back\\slash']);

        $this->assertCount(3, $ids);
        $this->assertArrayHasKey(2026, $ids);
        $this->assertArrayHasKey("o'neil", $ids);
        $this->assertArrayHasKey('back\\slash', $ids);
    }

    /**
     * Calls the private resolveTermIds() method of the indexer
     *
     * @param   string[]  $terms  The terms
     *
     * @return  array  The term ids, keyed by term
     *
     * @since   __DEPLOY_VERSION__
     */
    private function resolveTermIds(array $terms): array
    {
        $indexer = (new \ReflectionClass(Indexer::class))->newInstanceWithoutConstructor();
        (new \ReflectionProperty(Indexer::class, 'db'))->setValue($indexer, $this->getDBDriver());

        // The aggregated data per term: stem, common, phrase, weight and the counts per context
        $aggregate = [];

        foreach ($terms as $term) {
            $aggregate[$term] = [$term, 0, 0, 1.0, ['title' => 1]];
        }

        $ids = (new \ReflectionMethod(Indexer::class, 'resolveTermIds'))->invoke($indexer, 'sr-YU', $aggregate);

        return array_map('intval', $ids);
    }

    /**
     * Creates the terms table like the installation does, with the given collation for MySQL
     *
     * @param   string  $collation  The collation of the term column on MySQL
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function createTermsTable(string $collation): void
    {
        $db = $this->getDBDriver();
        $db->setQuery('DROP TABLE IF EXISTS ' . $db->quoteName('#__finder_terms'))->execute();

        if ($this->isMysql()) {
            $db->setQuery('CREATE TABLE ' . $db->quoteName('#__finder_terms') . ' (
                `term_id` int unsigned NOT NULL AUTO_INCREMENT,
                `term` varchar(75) CHARACTER SET utf8mb4 COLLATE ' . $collation . ' NOT NULL,
                `stem` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT \'\',
                `common` tinyint unsigned NOT NULL DEFAULT 0,
                `phrase` tinyint unsigned NOT NULL DEFAULT 0,
                `weight` float unsigned NOT NULL DEFAULT 0,
                `soundex` varchar(75) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT \'\',
                `links` int NOT NULL DEFAULT 0,
                `language` char(7) NOT NULL DEFAULT \'\',
                PRIMARY KEY (`term_id`),
                UNIQUE KEY `idx_term_language` (`term`, `language`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci')->execute();

            return;
        }

        // The indexer uses SOUNDEX(), which the installation adds to PostgreSQL as a function
        preg_match(
            '/CREATE OR REPLACE FUNCTION soundex\(.*?\$\$;/s',
            file_get_contents(JPATH_ROOT . '/installation/sql/postgresql/extensions.sql'),
            $matches
        );
        $db->setQuery($matches[0])->execute();

        $db->setQuery('CREATE TABLE ' . $db->quoteName('#__finder_terms') . ' (
            "term_id" serial NOT NULL,
            "term" varchar(75) NOT NULL,
            "stem" varchar(75) DEFAULT \'\' NOT NULL,
            "common" smallint DEFAULT 0 NOT NULL,
            "phrase" smallint DEFAULT 0 NOT NULL,
            "weight" numeric(8,2) DEFAULT 0 NOT NULL,
            "soundex" varchar(75) DEFAULT \'\' NOT NULL,
            "links" integer DEFAULT 0 NOT NULL,
            "language" varchar(7) DEFAULT \'\' NOT NULL,
            PRIMARY KEY ("term_id"),
            CONSTRAINT ' . $db->quoteName('#__finder_terms_idx_term_language') . ' UNIQUE ("term", "language")
        )')->execute();
    }

    /**
     * Counts the rows in the terms table
     *
     * @return  integer
     *
     * @since   __DEPLOY_VERSION__
     */
    private function countTerms(): int
    {
        $db = $this->getDBDriver();

        return (int) $db->setQuery('SELECT COUNT(*) FROM ' . $db->quoteName('#__finder_terms'))->loadResult();
    }

    /**
     * Whether the tests run on MySQL or MariaDB
     *
     * @return  boolean
     *
     * @since   __DEPLOY_VERSION__
     */
    private function isMysql(): bool
    {
        return strtolower($this->getDBDriver()->getServerType()) === 'mysql';
    }
}
