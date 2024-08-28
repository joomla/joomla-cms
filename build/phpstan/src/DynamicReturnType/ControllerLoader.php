<?php

/**
 * @package     Joomla.Build
 * @subpackage  phpstan
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\PHPStan\DynamicReturnType;

use Joomla\CMS\MVC\Controller\BaseController;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;

class ControllerLoader extends NamespaceBased
{
    public function getClass(): string
    {
        return BaseController::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return \in_array($methodReflection->getName(), ['getModel', 'createModel', 'getView', 'createView']);
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
    {
        $name   = '';
        $prefix = '';

        if (\count($methodCall->getArgs()) === 0) {
            $name = str_replace('Controller', '', $scope->getClassReflection()->getNativeReflection()->getShortName());
        }

        if (\count($methodCall->getArgs()) < 2) {
            $prefix = strpos($scope->getNamespace(), 'Site') ? 'Site' : 'Administrator';
        }

        if (\count($methodCall->getArgs()) > 0) {
            $name = str_replace("'", '', $methodCall->getArgs()[0]->value->getAttribute('rawValue'));
        }

        if (\count($methodCall->getArgs()) > 1) {
            $prefix = str_replace("'", '', $methodCall->getArgs()[1]->value->getAttribute('rawValue'));
        }

        if (!$name || !$prefix) {
            return null;
        }

        // Search in namespaces which belong to the defined prefix
        foreach ($this->findNamespaces($prefix) as $ns => $path) {
            foreach (['Model', 'View'] as $type) {
                if (($methodReflection->getName() !== 'create' . $type && $methodReflection->getName() !== 'get' . $type)
                    || !class_exists($ns . $type . '\\' . $name . $type)) {
                    continue;
                }

                return new ObjectType($ns . $type . '\\' . $name . $type);
            }
        }

        // Search in all namespaces, eg. when an admin model is loaded on site
        foreach ($this->getNamespaces() as $ns => $path) {
            foreach (['Model', 'View'] as $type) {
                if (($methodReflection->getName() !== 'create' . $type && $methodReflection->getName() !== 'get' . $type)
                    || !class_exists($ns . $type . '\\' . $name . $type)) {
                    continue;
                }

                return new ObjectType($ns . $type . '\\' . $name . $type);
            }
        }

        return null;
    }
}
