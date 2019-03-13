<?php

namespace Leapt\ImBundle\Twig\Node;

use Twig\Compiler;
use Twig\Node\Node;

/**
 * Represents a img tag node
 *
 * It looks the HTML width and height attributes, and modifies the src attribute to load a cached image
 * with the proper size
 *
 * @codeCoverageIgnore
 */
class Imresize extends Node
{
    public function __construct(Node $body, $lineno, $tag = 'imresize')
    {
        parent::__construct(array('body' => $body), array(), $lineno, $tag);
    }

    public function compile(Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->write("ob_start();\n")
            ->subcompile($this->getNode('body'))
            ->write("echo \$this->env->getExtension('leapt_im')->convert(ob_get_clean());\n");
    }
}
