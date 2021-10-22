<?php

declare(strict_types=1);

namespace Leapt\ImBundle\Twig\Node;

use Twig\Compiler;
use Twig\Node\Node;

/**
 * Represents an img tag node.
 *
 * It looks the HTML width and height attributes, and modifies the src attribute to load a cached image
 * with the proper size
 */
class Imresize extends Node
{
    public function __construct(Node $body, int $lineno = 0, string $tag = 'imresize')
    {
        parent::__construct(['body' => $body], [], $lineno, $tag);
    }

    public function compile(Compiler $compiler): void
    {
        $compiler
            ->addDebugInfo($this)
            ->write("ob_start();\n")
            ->subcompile($this->getNode('body'))
            ->write("echo \$this->env->getExtension('leapt_im')->convert(ob_get_clean());\n");
    }
}
