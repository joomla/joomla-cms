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

class ModelBootComponent extends NamespaceBased
{
    public function getClass(): string
    {
        return BaseDatabaseModel::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getName() === 'bootComponent';
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): ?Type
    {
        if (\count($methodCall->getArgs()) === 0) {
            return null;
        }

        $name = str_replace("'", '', $methodCall->getArgs()[0]->value->getAttribute('rawValue'));

        if ($namespace = $this->findNamespace('\\Component\\' . $name . '\\Administrator')) {
            return new ObjectType($namespace . 'Extension\\' . $name . 'Component');
        }

        return null;
    }
}
