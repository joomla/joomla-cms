<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\Controller;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Trait providing normalized JSON envelope payloads for async admin actions.
 *
 * @since  6.1.0
 */
trait AsyncAdminResponseTrait
{
    /**
     * Build a normalized async admin response envelope.
     *
     * @param   boolean      $success    Indicates whether the request was successful.
     * @param   array        $messages   Message map keyed by message type.
     * @param   string|null  $redirect   Optional redirect URL.
     * @param   array        $fragments  Optional rendered HTML fragments.
     * @param   array        $meta       Optional metadata for the client.
     *
        * @return  array
        *
        * @since   6.1.0
     */
    protected function buildAsyncAdminResponseEnvelope(
        bool $success,
        array $messages = [],
        ?string $redirect = null,
        array $fragments = [],
        array $meta = []
    ): array {
        return [
            'success'   => $success,
            'messages'  => $this->normalizeAsyncAdminMessages($messages),
            'redirect'  => $redirect,
            'fragments' => $fragments,
            'meta'      => $meta,
        ];
    }

    /**
     * Normalize message arrays to a stable schema.
     *
        * @param   array  $messages  Message map keyed by message type.
        *
        * @return  array
        *
        * @since   6.1.0
     */
    private function normalizeAsyncAdminMessages(array $messages): array
    {
        $normalized = [
            'message' => [],
            'warning' => [],
            'error'   => [],
        ];

        foreach ($normalized as $type => $_) {
            if (!isset($messages[$type])) {
                continue;
            }

            if (\is_array($messages[$type])) {
                $normalized[$type] = array_values($messages[$type]);

                continue;
            }

            $normalized[$type] = [$messages[$type]];
        }

        return $normalized;
    }
}
