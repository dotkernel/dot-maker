<?php

declare(strict_types=1);

namespace Dot\Maker;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class MethodVisitor extends NodeVisitorAbstract
{
    protected string $name = '';
    protected string $sourceCode = '';
    protected int $startPos = 0;
    protected int $endPos = 0;

    public function __construct(string $sourceCode, string $name)
    {
        $this->sourceCode = $sourceCode;
        $this->name       = $name;
    }

    public function leaveNode(Node $node): void
    {
        if ($node instanceof Node\Stmt\ClassMethod && $node->name->name === $this->name) {
            var_dump($node->name->name, $node->getStartFilePos(), $node->getEndFilePos());
            $this->startPos = $node->getStartFilePos();
            $this->endPos = $node->getEndFilePos();
        }
    }

    public function getBody(): string
    {
        var_dump([$this->startPos, $this->endPos]);exit;
        return substr($this->sourceCode, $this->startPos, $this->endPos);
    }
}
