<?php

/**
 * @package     Joomla.Build
 * @subpackage  phpstan
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\PHPStan\DynamicReturnType;

use Joomla\CMS\MVC\View\AbstractView;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;

class ViewModelLoader extends NamespaceBased
{
    public function getClass(): string
    {
        return AbstractView::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getName() === 'getModel';
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
    {
        $name   = '';
        $prefix = '';

        if (\count($methodCall->getArgs()) === 0) {
            $name = end(explode('\\', $scope->getNamespace()));
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
            if (!class_exists($ns . 'Model\\' . $name . 'Model')) {
                continue;
            }

            return new ObjectType($ns . 'Model\\' . $name . 'Model');
        }

        // Search in all namespaces, eg. when an admin model is loaded on site
        foreach ($this->getNamespaces() as $ns => $path) {
            if (!class_exists($ns . 'Model\\' . $name . 'Model')) {
                continue;
            }

            return new ObjectType($ns . 'Model\\' . $name . 'Model');
        }

        return null;
    }
}
