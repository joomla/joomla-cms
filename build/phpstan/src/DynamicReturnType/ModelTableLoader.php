<?php

/**
 * @package     Joomla.Build
 * @subpackage  phpstan
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\PHPStan\DynamicReturnType;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;

class ModelTableLoader extends NamespaceBased
{
    public function getClass(): string
    {
        return BaseDatabaseModel::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getName() === 'getTable';
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
    {
        $name   = '';
        $prefix = '';

        if (\count($methodCall->getArgs()) === 0) {
            $name = end(explode('\\', $scope->getClassReflection()->getNativeReflection()->getShortName()));

            // Some models have form as part of their name
            $name = str_replace(['Model', 'form'], ['', ''], $name);
        }

        if (\count($methodCall->getArgs()) < 2) {
            $prefix = 'Administrator';
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

        foreach ($this->findNamespaces($prefix) as $ns => $path) {
            if (!class_exists($ns . 'Table\\' . $name . 'Table')) {
                continue;
            }

            return new ObjectType($ns . 'Table\\' . $name . 'Table');
        }

        return null;
    }
}
