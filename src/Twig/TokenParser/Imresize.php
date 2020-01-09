<?php

namespace Leapt\ImBundle\Twig\TokenParser;

use Leapt\ImBundle\Twig\Node\Imresize as ImresizeNode;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

/**
 * Create and use image cache regarding the HTML width and height attributes.
 *
 * <pre>
 * {% imresize %}
 *      <div>
 *          <img src="/some/img.jpg" width="100" />
 *          <img src="{{ asset('some/img.jpg'}}" height="100" />
 *      </div>
 * {% endimresize %}
 * </pre>
 *
 * @codeCoverageIgnore
 */
class Imresize extends AbstractTokenParser
{
    /**
     * Parses a token and returns a node.
     *
     * @param Token $token A Token instance
     *
     * @return Node A Node instance
     */
    public function parse(Token $token)
    {
        $lineno = $token->getLine();

        $this->parser->getStream()->expect(Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse([$this, 'decideImresizeEnd'], true);
        $this->parser->getStream()->expect(Token::BLOCK_END_TYPE);

        return new ImresizeNode($body, $lineno, $this->getTag());
    }

    /**
     * @return bool
     */
    public function decideImresizeEnd(Token $token)
    {
        return $token->test('endimresize');
    }

    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string The tag name
     */
    public function getTag()
    {
        return 'imresize';
    }
}
