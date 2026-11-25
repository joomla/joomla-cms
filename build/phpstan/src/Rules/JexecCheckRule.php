<?php

/**
 * @package     Joomla.Build
 * @subpackage  phpstan
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\LogicalOr;
use PhpParser\Node\Expr\Exit_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt;
use PHPStan\Analyser\Scope;
use PHPStan\Node\FileNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * Ensures that every PHP file starts, after the namespace, use and declare
 * statements, with the \defined('_JEXEC') or die; check.
 *
 * Entry point files which define the _JEXEC constant themselves are skipped.
 *
 * @implements Rule<FileNode>
 */
class JexecCheckRule implements Rule
{
    /**
     * The exact code of the check.
     */
    private const CHECK = "\\defined('_JEXEC') or die;";

    public function getNodeType(): string
    {
        return FileNode::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $statements = $node->getNodes();

        // Files with a namespace have their statements inside the namespace node
        foreach ($statements as $statement) {
            if ($statement instanceof Stmt\Namespace_) {
                $statements = $statement->stmts;
                break;
            }
        }

        if ($this->definesJexec($statements)) {
            return [];
        }

        $first = null;

        foreach ($statements as $statement) {
            if (
                $statement instanceof Stmt\Declare_
                || $statement instanceof Stmt\Namespace_
                || $statement instanceof Stmt\Use_
                || $statement instanceof Stmt\GroupUse
                || $statement instanceof Stmt\Nop
                || $statement instanceof Stmt\InlineHTML
            ) {
                continue;
            }

            $first = $statement;
            break;
        }

        if ($first !== null && $this->isJexecCheck($first)) {
            $lines = file($scope->getFile(), FILE_IGNORE_NEW_LINES);
            $code  = implode("\n", \array_slice($lines, $first->getStartLine() - 1, $first->getEndLine() - $first->getStartLine() + 1));

            if ($code === self::CHECK) {
                return [];
            }

            return [
                RuleErrorBuilder::message(\sprintf('The _JEXEC check must be written exactly as %s', self::CHECK))
                    ->identifier('joomla.jexecCheck')
                    ->line($first->getStartLine())
                    ->build(),
            ];
        }

        return [
            RuleErrorBuilder::message("File must contain \\defined('_JEXEC') or die; directly after the namespace and use statements.")
                ->identifier('joomla.jexecCheck')
                ->line($first !== null ? $first->getStartLine() : 1)
                ->tip('Add \defined(\'_JEXEC\') or die; to protect the file from direct access.')
                ->build(),
        ];
    }

    /**
     * Checks if the statement is \defined('_JEXEC') or die;
     */
    private function isJexecCheck(Stmt $statement): bool
    {
        if (
            !$statement instanceof Stmt\Expression
            || (!$statement->expr instanceof LogicalOr && !$statement->expr instanceof BooleanOr)
        ) {
            return false;
        }

        return $this->isFunctionCallWithJexec($statement->expr->left, 'defined')
            && $statement->expr->right instanceof Exit_;
    }

    /**
     * Checks if one of the top level statements is a define('_JEXEC', ...) call, which is the case for entry points.
     *
     * @param  Stmt[]  $statements
     */
    private function definesJexec(array $statements): bool
    {
        foreach ($statements as $statement) {
            if ($statement instanceof Stmt\Expression && $this->isFunctionCallWithJexec($statement->expr, 'define')) {
                return true;
            }
        }

        return false;
    }

    private function isFunctionCallWithJexec(Node $expr, string $function): bool
    {
        if (!$expr instanceof FuncCall || !$expr->name instanceof Name || $expr->name->toLowerString() !== $function) {
            return false;
        }

        $args = $expr->getArgs();

        return \count($args) > 0 && $args[0]->value instanceof String_ && $args[0]->value->value === '_JEXEC';
    }
}
