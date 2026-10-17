<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Database;

use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Locale-aware collation and ORDER BY handling for supported database drivers (MySQL/MariaDB, PostgreSQL).
 *
 * @since  __DEPLOY_VERSION__
 */
final class LocaleCollation
{
    /**
     * @param   DatabaseInterface  $db  Active database connection.
     */
    public function __construct(private readonly DatabaseInterface $db)
    {
    }

    /**
     * Collation spec for ORDER BY … COLLATE for the given language tag.
     * MySQL/MariaDB: utf8mb4_* collation name. PostgreSQL: quoted locale (e.g. "da_DK.UTF-8").
     *
     * @param   string  $languageTag  BCP 47 tag (e.g. en-GB, da-DK).
     *
     * @return  string|null  Collation spec, or null if not supported for this server type.
     */
    public function getCollationForLanguage(string $languageTag): ?string
    {
        $serverType = strtolower($this->db->getServerType());
        $lang       = strtolower(substr($languageTag, 0, 2));

        if ($serverType === 'mysql') {
            $collationByLang = [
                'cs' => 'utf8mb4_czech_ci',
                'da' => 'utf8mb4_danish_ci',
                'el' => 'utf8mb4_greek_ci',
                'es' => 'utf8mb4_spanish_ci',
                'et' => 'utf8mb4_estonian_ci',
                'hr' => 'utf8mb4_croatian_ci',
                'hu' => 'utf8mb4_hungarian_ci',
                'lv' => 'utf8mb4_latvian_ci',
                'lt' => 'utf8mb4_lithuanian_ci',
                'pl' => 'utf8mb4_polish_ci',
                'ro' => 'utf8mb4_romanian_ci',
                'sk' => 'utf8mb4_slovak_ci',
                'sl' => 'utf8mb4_slovenian_ci',
                'tr' => 'utf8mb4_turkish_ci',
                'zh' => 'utf8mb4_chinese_ci',
            ];

            return $collationByLang[$lang] ?? 'utf8mb4_unicode_ci';
        }

        if ($serverType === 'pgsql' || $serverType === 'postgresql') {
            $locale = str_replace('-', '_', $languageTag);

            return '"' . $locale . '"';
        }

        return null;
    }

    /**
     * Build ORDER BY expression with locale collation when the column is listed as a text column.
     *
     * @param   string  $orderCol                 Order column (e.g. a.title).
     * @param   string  $orderDirn                Direction (ASC or DESC).
     * @param   array   $textColumnsForCollation  Column keys that should use locale collation.
     * @param   string  $languageTag              Active language tag.
     *
     * @return  string  Expression for use in QueryInterface::order().
     */
    public function getOrderByExpression(
        string $orderCol,
        string $orderDirn,
        array $textColumnsForCollation,
        string $languageTag
    ): string {
        $collation = $this->getCollationForLanguage($languageTag);

        if ($collation !== null && \in_array($orderCol, $textColumnsForCollation, true)) {
            return '(' . $this->db->escape($orderCol) . ' COLLATE ' . $collation . ') ' . $this->db->escape($orderDirn);
        }

        return $this->db->escape($orderCol) . ' ' . $this->db->escape($orderDirn);
    }

    /**
     * Apply locale collation to recognized text columns in a comma-separated ORDER BY.
     *
     * @param   string  $ordering       Composite ORDER BY (comma-separated segments).
     * @param   array   $textColumns    Columns that should use locale collation.
     * @param   string  $languageTag    Active language tag.
     *
     * @return  string
     */
    public function applyToCompositeOrdering(string $ordering, array $textColumns, string $languageTag): string
    {
        $parts   = array_map('trim', explode(',', $ordering));
        $updated = [];

        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }

            if (stripos($part, 'collate') !== false) {
                $updated[] = $part;
                continue;
            }

            $replaced = false;

            foreach ($textColumns as $col) {
                $pattern = '/^' . preg_quote($col, '/') . '(?:\s+(ASC|DESC))?$/i';

                if (preg_match($pattern, $part, $matches)) {
                    $dirn      = isset($matches[1]) && $matches[1] ? strtoupper($matches[1]) : 'ASC';
                    $updated[] = $this->getOrderByExpression($col, $dirn, $textColumns, $languageTag);
                    $replaced  = true;
                    break;
                }
            }

            if (!$replaced) {
                $updated[] = $part;
            }
        }

        return implode(', ', $updated);
    }

    /**
     * Ensure a PostgreSQL collation exists for the given language tag.
     *
     * @param   string  $languageTag  Language tag (e.g. en-GB, da-DK).
     *
     * @return  boolean  True if collation exists or was created; false on failure (non-blocking).
     */
    public function ensureCollation(string $languageTag): bool
    {
        $serverType = strtolower($this->db->getServerType());

        if ($serverType !== 'pgsql' && $serverType !== 'postgresql') {
            return true;
        }

        $collationName = str_replace('-', '_', $languageTag);

        if ($this->postgresCollationExists($collationName)) {
            return true;
        }

        return $this->createPostgresCollation($collationName);
    }

    /**
     * @param   string  $collationName  Collation name (e.g. en_GB).
     */
    private function postgresCollationExists(string $collationName): bool
    {
        $query = 'SELECT 1 FROM pg_collation WHERE collname = ' . $this->db->quote($collationName)
            . ' AND (collencoding = -1 OR collencoding = (SELECT encoding FROM pg_database WHERE datname = current_database()))'
            . ' LIMIT 1';
        $this->db->setQuery($query);

        try {
            return (bool) $this->db->loadResult();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param   string  $collationName  Collation name (e.g. en_GB).
     */
    private function createPostgresCollation(string $collationName): bool
    {
        $localesToTry = [$collationName . '.UTF-8', $collationName . '.utf8', $collationName];

        foreach ($localesToTry as $locale) {
            try {
                $safeName   = $this->db->quoteName($collationName);
                $safeLocale = $this->db->quote($locale);
                $sql        = 'CREATE COLLATION ' . $safeName . ' (LOCALE = ' . $safeLocale . ', PROVIDER = libc)';
                $this->db->setQuery($sql);
                $this->db->execute();

                return true;
            } catch (\Exception $e) {
                $code = $e->getCode();
                $msg  = $e->getMessage();

                if ($code === '42P07' || strpos($msg, 'already exists') !== false) {
                    return true;
                }

                Log::add(
                    'PostgreSQL collation ' . $collationName . ' could not be created: ' . $msg,
                    Log::DEBUG,
                    'locale'
                );
            }
        }

        return false;
    }
}
