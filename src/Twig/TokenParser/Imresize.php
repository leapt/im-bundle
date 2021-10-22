<?php

declare(strict_types=1);

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
 */
class Imresize extends AbstractTokenParser
{
    public function parse(Token $token): ImresizeNode
    {
        $lineno = $token->getLine();

        $this->parser->getStream()->expect(Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse([$this, 'decideImresizeEnd'], true);
        $this->parser->getStream()->expect(Token::BLOCK_END_TYPE);

        return new ImresizeNode($body, $lineno, $this->getTag());
    }

    public function decideImresizeEnd(Token $token): bool
    {
        return $token->test('endimresize');
    }

    public function getTag(): string
    {
        return 'imresize';
    }
}
