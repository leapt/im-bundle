<?php

namespace Leapt\ImBundle\Twig\Node;

use Twig_Compiler;
use Twig_Node;

/**
 * Represents a img tag node
 *
 * It looks the HTML width and height attributes, and modifies the src attribute to load a cached image
 * with the proper size
 *
 * @codeCoverageIgnore
 */
class Imresize extends Twig_Node
{
    /**
     * @param Twig_Node          $body   @see Twig_NodeInterface::__construct
     * @param array              $lineno @see Twig_NodeInterface::__construct
     * @param string             $tag    @see Twig_NodeInterface::__construct
     */
    public function __construct(Twig_Node $body, $lineno, $tag = 'imresize')
    {
        parent::__construct(array('body' => $body), array(), $lineno, $tag);
    }

    /**
     * Compiles the node to PHP.
     *
     * @param Twig_Compiler $compiler A Twig_Compiler instance
     */
    public function compile(Twig_Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)
            ->write("ob_start();\n")
            ->subcompile($this->getNode('body'))
            ->write("echo \$this->env->getExtension('leapt_im')->convert(ob_get_clean());\n");
    }
}
