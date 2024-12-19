<?php

/**
 * @package     Joomla.Build
 * @subpackage  phpstan
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\PHPStan\DynamicReturnType;

use Joomla\CMS\Extension\ExtensionManagerInterface as CMSExtensionManagerInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\Plugin\CMSPlugin;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;

class ExtensionManagerInterface extends NamespaceBased
{
    public function getClass(): string
    {
        return CMSExtensionManagerInterface::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return \in_array($methodReflection->getName(), ['bootComponent', 'bootPlugin']);
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
    {
        if (\count($methodCall->getArgs()) === 0) {
            return null;
        }

        $name = str_replace(["'", 'com_'], ['', ''], $methodCall->getArgs()[0]->value->getAttribute('rawValue'));
        $type = \count($methodCall->getArgs()) > 1 ? str_replace("'", '', $methodCall->getArgs()[1]->value->getAttribute('rawValue')) : '';

        // Component class
        if (!$type && $namespace = $this->findNamespace('\\Component\\' . $name . '\\Administrator')) {
            $class = $namespace . 'Extension\\' . ucfirst($name) . 'Component';
            if (!class_exists($class)) {
                // Try to determine the real name
                $class = $namespace . 'Extension\\' . substr($namespace, strpos($namespace, 'Component\\') + 10, \strlen($name)) . 'Component';
            }

            if (!class_exists($class)) {
                $class = MVCComponent::class;
            }

            return new ObjectType($class);
        }

        // Plugin class
        if ($type && $namespace = $this->findNamespace('\\Plugin\\' . $type . '\\' . $name)) {
            $class = $namespace . 'Extension\\' . $name;
            if (!class_exists($class)) {
                $class = CMSPlugin::class;
            }

            return new ObjectType($class);
        }

        return null;
    }
}
