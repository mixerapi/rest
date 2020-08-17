<?php
declare(strict_types=1);

namespace MixerApi\Rest\Lib\Parser;

use MixerApi\Rest\Lib\Exception\RunTimeException;
use MixerApi\Rest\Lib\Route\RouteWriter;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeVisitorAbstract;

class RouteScopeVisitor extends NodeVisitorAbstract
{
    /**
     * @var \MixerApi\Rest\Lib\Route\RouteWriter
     */
    private $routeWriter;

    /**
     * @param \MixerApi\Rest\Lib\Route\RouteWriter $routeWriter instance of RouteWriter
     */
    public function __construct(RouteWriter $routeWriter)
    {
        $this->routeWriter = $routeWriter;
    }

    /**
     * @param \PhpParser\Node $node instance of Node
     * @return \PhpParser\Node
     */
    public function enterNode(Node $node): Node
    {
        if ($this->isRouteScope($node)) {
            return $this->scope($node);
        }

        if ($this->isRoutePlugin($node)) {
            return $this->plugin($node);
        }

        return $node;
    }

    /**
     * Modifies the Route::scope
     *
     * @param \PhpParser\Node $node instance of Node
     * @return \PhpParser\Node
     */
    private function scope(Node $node): Node
    {
        if (!isset($node->args)) {
            return $node;
        }

        if (!isset($node->args[0]->value->value)) {
            throw new RunTimeException('Route->scope should have a prefix');
        }

        if ($node->args[0]->value->value !== $this->routeWriter->getPrefix()) {
            return $node;
        }

        $node->args = $this->buildRouteArgs($node->args);

        return $node;
    }

    /**
     * Modifies the Route::plugin
     *
     * @param \PhpParser\Node $node instance of Node
     * @return \PhpParser\Node
     */
    private function plugin(Node $node): Node
    {
        // @phpstan-ignore-next-line
        if (!isset($node->expr->args)) {
            return $node;
        }

        if (!isset($node->expr->args[0]->value->value)) {
            throw new RunTimeException('Route::plugin should have a plugin name defined');
        }

        if ($node->expr->args[0]->value->value !== $this->routeWriter->getPlugin()) {
            return $node;
        }

        if (!isset($node->expr->args[1]->value) || !$node->expr->args[1]->value instanceof Array_) {
            throw new RunTimeException('Route::plugin should have a prefix');
        }

        $matchingPrefixes = array_filter(
            $node->expr->args[1]->value->items,
            function ($value) {
                // @phpstan-ignore-next-line
                return $value->key->value == 'path' && $value->value->value == $this->routeWriter->getPrefix();
            }
        );

        if (count($matchingPrefixes) !== 1) {
            throw new RunTimeException(
                'Route::plugin should have a single matching prefix for ' .
                '`' . $this->routeWriter->getPrefix() . '`'
            );
        }

        $node->expr->args = $this->buildRouteArgs($node->expr->args);

        return $node;
    }

    /**
     * Builds Node arguments array with route resources
     *
     * @param \PhpParser\Node\Arg[] $args node argument list
     * @return \PhpParser\Node\Arg[]
     */
    private function buildRouteArgs(array $args): array
    {
        $return = [];

        foreach ($args as $i => $arg) {
            if (!$arg->value instanceof Closure) {
                continue;
            }

            $stmts = array_merge(
                $arg->value->stmts,
                $this->buildResourceStmtExpressions($arg->value)
            );

            $arg->value->stmts = $stmts;
            $return[$i] = $arg;
        }

        return $return;
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

        foreach ($this->routeWriter->getResources() as $resource) {
            if ($this->hasResource($closure, $resource->getResourceName())) {
                continue;
            }

            $arguments = [new Node\Arg(new String_($resource->getResourceName()))];
            $prefixes = $resource->getPaths($this->routeWriter->getBaseNamespace());

            if (!empty($prefixes)) {
                $pathTemplate = $resource->getPathTemplate($this->routeWriter->getBaseNamespace());
                $arguments[] = new Node\Arg(new Array_([
                    new ArrayItem(
                        new String_($pathTemplate),
                        new String_('path')
                    ),
                    new ArrayItem(
                        new String_(end($prefixes)),
                        new String_('prefix')
                    ),
                ], ['kind' => Array_::KIND_SHORT]));
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

    /**
     * @param \PhpParser\Node $node instance of Node
     * @return bool
     */
    private function isRouteScope(Node $node): bool
    {
        return $node instanceof MethodCall && $node->name->name == 'scope';
    }

    /**
     * @param \PhpParser\Node $node instance of Node
     * @return bool
     */
    private function isRoutePlugin(Node $node): bool
    {
        return isset($node->expr)
            && $node->expr instanceof StaticCall
            && isset($node->expr->name->name)
            && $node->expr->name->name == 'plugin';
    }
}
