<?php

/**
 * @package     Joomla.Build
 * @subpackage  phpstan
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\PHPStan\DynamicReturnType;

use PHPStan\Type\DynamicMethodReturnTypeExtension;

abstract class NamespaceBased implements DynamicMethodReturnTypeExtension
{
    private array $namespaces = [];

    /**
     * Returns a list of namespaces.
     */
    protected function getNamespaces(): array
    {
        if (!$this->namespaces) {
            $this->namespaces = require \dirname(__DIR__, 4) . '/administrator/cache/autoload_psr4.php';
        }

        return $this->namespaces;
    }

    /**
     * Searches namespaces for the given name case insensitive.
     */
    protected function findNamespaces(string $name): array
    {
        $result = [];

        foreach ($this->getNamespaces() as $ns => $path) {
            if (!stripos($ns, $name)) {
                continue;
            }

            $result[$ns] = $path;
        }

        return $result;
    }

    /**
     * Searches a namespace for the given name case insensitive.
     */
    protected function findNamespace(string $name): string
    {
        foreach ($this->getNamespaces() as $ns => $path) {
            if (!stripos($ns, $name)) {
                continue;
            }

            return $ns;
        }

        return '';
    }
}
