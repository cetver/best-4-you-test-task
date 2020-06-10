<?php declare(strict_types=1);

namespace App\DQL;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;;

/**
 * The "StrftimeFunction" class
 * "STRFTIME" "(" StringPrimary "," ArithmeticPrimary ")"
 */
class StrftimeFunction extends FunctionNode
{
    private Node $format;
    private Node $timeString;

    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);

        $this->format = $parser->StringPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->timeString = $parser->ArithmeticPrimary();

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker)
    {
        return "STRFTIME({$this->format->dispatch($sqlWalker)}, {$this->timeString->dispatch($sqlWalker)})";
    }
}