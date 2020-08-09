<?php
declare(strict_types=1);

namespace MixerApi\Rest\Lib\Parser;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeVisitorAbstract;

class RouteScopeVisitor extends NodeVisitorAbstract
{
    /**
     * @var \MixerApi\Rest\Lib\Route\RouteDecorator[]
     */
    private $resources;

    /**
     * @param \MixerApi\Rest\Lib\Route\RouteDecorator[] $resources array of RouteDecorators
     */
    public function __construct(array $resources)
    {
        $this->resources = $resources;
    }

    /**
     * @param \PhpParser\Node $node instance of Node
     * @return \PhpParser\Node
     */
    public function enterNode(Node $node): Node
    {
        if (!$node instanceof MethodCall || $node->name->name != 'scope') {
            return $node;
        }

        if ($node->args[0]->value instanceof String_ && $node->args[0]->value->value == '/') {
            return $this->modify($node);
        }

        return $node;
    }

    /**
     * Modifies the Route::scope
     *
     * @param \PhpParser\Node $node instance of Node
     * @return \PhpParser\Node
     */
    private function modify(Node $node): Node
    {
        if (!isset($node->args)) {
            return $node;
        }

        foreach ($node->args as $i => $arg) {
            /** @var \PhpParser\Node\Arg $arg */
            if (!$arg->value instanceof Closure) {
                continue;
            }

            $stmts = array_merge(
                $arg->value->stmts,
                $this->buildResourceStmtExpressions($arg->value)
            );

            $arg->value->stmts = $stmts;
            $node->args[$i] = $arg;
        }

        return $node;
    }

    /**
     * Returns an array of statements
     *
     * @param \PhpParser\Node\Expr\Closure $closure instance of Closure
     * @return \PhpParser\Node\Stmt\Expression[]
     */
    private function buildResourceStmtExpressions(Closure $closure): array
    {
        $statements = [];

        foreach ($this->resources as $resource) {
            if ($this->hasResource($closure, $resource->getController())) {
                continue;
            }

            $arguments = [new Node\Arg(new String_($resource->getController()))];

            if (!empty($resource->getRoute()->defaults['prefix'])) {
                $pieces = explode('/', $resource->getTemplate());
                array_pop($pieces);

                $arguments[] = new Node\Arg(new Array_([
                    new ArrayItem(
                        new String_(implode('/', $pieces)),
                        new String_('path')
                    ),
                    new ArrayItem(
                        new String_($resource->getRoute()->defaults['prefix']),
                        new String_('prefix')
                    ),
                ]));
            }

            $methodCall = new MethodCall(
                new Node\Expr\Variable('builder'),
                'resources',
                $arguments
            );
            $statements[] = new Expression($methodCall);
        }

        return $statements;
    }

    /**
     * Check if the route resource already exists
     *
     * @param \PhpParser\Node\Expr\Closure $closure instance of Closure
     * @param string $argument the resource name (such as Actors for ActorsController)
     * @return bool
     */
    private function hasResource(Closure $closure, string $argument): bool
    {
        foreach ($closure->stmts as $stmt) {
            if (!$stmt instanceof Expression || !$stmt->expr instanceof MethodCall) {
                continue;
            }
            if ($stmt->expr->name->name != 'resources') {
                continue;
            }

            $results = array_filter($stmt->expr->args, function ($arg) use ($argument) {
                if (!$arg->value instanceof String_) {
                    return false;
                }

                return $arg->value->value == $argument;
            });

            if (count($results) >= 1) {
                return true;
            }
        }

        return false;
    }
}
