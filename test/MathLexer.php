<?php

class MathLexer extends \Doctrine\Common\Lexer\AbstractLexer
{
    const T_PLUS              = 1;
    const T_MINUS             = 2;
    const T_MULTIPLY          = 3;
    const T_DIVIDE            = 4;
    const T_CLOSE_PARENTHESIS = 5;
    const T_OPEN_PARENTHESIS  = 6;
    const T_VALUE             = 7;

    protected function getCatchablePatterns()
    {
        return array(
            '[0-9][\.0-9]*',
            '[\(\)\/+*-]'
        );
    }

    protected function getNonCatchablePatterns()
    {
        return array(
            '\s+'
        );
    }

    protected function getType(&$value)
    {
        switch ($value) {
            case '(':
                return self::T_OPEN_PARENTHESIS;
            case ')':
                return self::T_CLOSE_PARENTHESIS;
            case '+':
                return self::T_PLUS;
            case '-':
                return self::T_MINUS;
            case '/':
                return self::T_DIVIDE;
            case '*':
                return self::T_MULTIPLY;
            default:
                return self::T_VALUE;
        }
    }
}