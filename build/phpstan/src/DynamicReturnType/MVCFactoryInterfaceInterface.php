<?php

/**
 * @package     Joomla.Build
 * @subpackage  phpstan
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\PHPStan\DynamicReturnType;

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;

class MVCFactoryInterfaceInterface extends NamespaceBased
{
    public function getClass(): string
    {
        return MVCFactoryInterface::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return \in_array($methodReflection->getName(), ['createController', 'createModel', 'createView', 'createTable']);
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
    {
        if (\count($methodCall->getArgs()) === 0) {
            return null;
        }

        $name   = str_replace("'", '', $methodCall->getArgs()[0]->value->getAttribute('rawValue'));
        $prefix = str_replace("'", '', $methodCall->getArgs()[1]->value->getAttribute('rawValue'));

        foreach ($this->findNamespaces($prefix) as $ns => $path) {
            foreach (['Controller', 'Model', 'View', 'Table'] as $type) {
                if ($methodReflection->getName() !== 'create' . $type || !class_exists($ns . $type . '\\' . $name . $type)) {
                    continue;
                }

                return new ObjectType($ns . $type . '\\' . $name . $type);
            }
        }

        return null;
    }
}
