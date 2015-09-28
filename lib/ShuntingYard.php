<?php

namespace MacFJA\LexerExpression;

use Doctrine\Common\Lexer\AbstractLexer;

/**
 * Class ShuntingYard
 *
 * A Shunting Yard Algorithm that work with Doctrine Lexer.
 *
 * @author  MacFJA
 * @license MIT
 * @package MacFJA\LexerExpression
 */
class ShuntingYard
{
    /** @var AbstractLexer  */
    protected $lexer;
    /** @var ShuntingYardOperator[] List of token types */
    protected $operators = array();
    /** @var int The token type for opening group */
    protected $openParenthesis;
    /** @var int The token type for closing group */
    protected $closeParenthesis;
    /** @var int[] List of token types to ignore */
    protected $ignoreTokens = array();
    /** @var int The token type for function argument separator */
    protected $argumentSeparator;
    /** @var array List of token, order to be the RPN expression */
    protected $rpn = array();
    /** @var array The temporary token (operator) stack */
    protected $operatorsStack = array();

    /**
     * Set the Doctrine Lexer to use.
     *
     * @param AbstractLexer $lexer
     */
    public function setLexer($lexer)
    {
        $this->lexer = $lexer;
    }

    /**
     * Set the list of operator
     *
     * @param ShuntingYardOperator[] $operators
     */
    public function setOperators($operators)
    {
        $this->operators = $operators;
    }

    /**
     * Add an operator in operator list
     *
     * @param ShuntingYardOperator $operator
     */
    public function addOperator($operator) {
        $this->operators[] = $operator;
    }

    /**
     * Set the token type of an open parenthesis (starting group token)
     *
     * @param int $openParenthesis
     */
    public function setOpenParenthesis($openParenthesis)
    {
        $this->openParenthesis = $openParenthesis;
    }

    /**
     * Set the token type of a close parenthesis (ending group token)
     *
     * @param int $closeParenthesis
     */
    public function setCloseParenthesis($closeParenthesis)
    {
        $this->closeParenthesis = $closeParenthesis;
    }

    /**
     * List of token types to ignore while building the RPN expression
     *
     * @param \int[] $ignoreTokens
     */
    public function setIgnoreTokens($ignoreTokens)
    {
        $this->ignoreTokens = $ignoreTokens;
    }

    /**
     * Set the token type of the function argument separator
     *
     * @param int $argumentSeparator
     */
    public function setArgumentSeparator($argumentSeparator)
    {
        $this->argumentSeparator = $argumentSeparator;
    }

    /**
     * Parse the current Lexer and return a RPN expression
     *
     * @return array
     *
     * @throws MissingParenthesisException If a group is closed, but no opened, or opened and never closed.
     */
    public function parse() {
        $this->lexer->resetPosition();
        $this->lexer->resetPeek();

        while(($token = $this->lexer->peek())) {
            /* The token definition of Doctrine\Lexer
             *
             * $token = array(
             *     'value' => string,
             *     'type'  => int,
             *     'position' => int,
             * );
             */
            $type = $token['type'];

            if (in_array($type, $this->ignoreTokens, true)) {
                // -- Ignored token
                // Skip this token
                continue;
            }

            if ($type !== $this->argumentSeparator && !$this->isParenthesis($type) && !$this->isAnOperator($type)) {
                // -- Variable/Operand
                // Just add it to the RPN expression
                $this->rpn[] = $token;
            } elseif ($type === $this->openParenthesis) {
                // -- Open parenthesis
                // just add the token in the stack
                $this->operatorsStack[] = $token;
            } elseif ($type === $this->closeParenthesis) {
                // -- Close parenthesis
                // Pop the stack in the RPN expression until an open parenthesis is found.
                // The close and the open parenthesis are not added to the RPN expression.
                $stackToken = array_pop($this->operatorsStack);
                while($stackToken['type'] !== $this->openParenthesis && count($this->operatorsStack) > 0) {
                    $this->rpn[] = $stackToken;
                    $stackToken = array_pop($this->operatorsStack);
                }
                // If the last popped token is not an open parenthesis, then it is missing!
                if ($stackToken['type'] !== $this->openParenthesis) {
                    throw new MissingParenthesisException('A group is closed but never opened');
                }
            } elseif ($type === $this->argumentSeparator) {
                // -- Argument separator
                // Pop the stack in the RPN expression until an open parenthesis is found.
                // The open parenthesis is not added to the RPN expression and not removed from the stack.
                $stackToken = array_pop($this->operatorsStack);
                while($stackToken['type'] !== $this->openParenthesis && count($this->operatorsStack) > 0) {
                    $this->rpn[] = $stackToken;
                    $stackToken = array_pop($this->operatorsStack);
                }
                // If the last popped token is not an open parenthesis, then it is missing!
                if ($stackToken['type'] !== $this->openParenthesis) {
                    throw new MissingParenthesisException('A function argument separator have been found, but not function start');
                }
                // Re-add the open parenthesis to the stack
                $this->operatorsStack[] = $stackToken;
            } else/*if (in_array($type, $this->operators))*/ {
                // -- Operator

                // Check the precedence of the newly found operator against others
                while(
                    // If there are operators to compare with
                    count($this->operatorsStack) &&
                    // Get the last operator
                    ($stackToken = end($this->operatorsStack)) &&
                    /* If the last operator is a parenthesis (open)
                     * then we are at the end of our comparison
                     * (not more operators in the current group)
                     */
                    !$this->isParenthesis($stackToken['type']) &&
                    // If the new operator has lower precedence than the last operator
                    $this->getOperator($type)->hasLowerPrecedence($this->getOperator($stackToken['type']))
                ) {
                    $this->rpn[] = array_pop($this->operatorsStack);
                }

                $this->operatorsStack[] = $token;
            }
        }
        // Un-stack all left operators
        while (count($this->operatorsStack) > 0) {
            $operator = array_pop($this->operatorsStack);

            if ($operator['type'] === $this->openParenthesis) {
                throw new MissingParenthesisException('A group is opened but never closed');
            }

            $this->rpn[] = $operator;
        }

        return $this->rpn;
    }

    /**
     * An utils function to check if an token is an parenthesis.
     * (Made to reduce code)
     *
     * @param int $tokenType The token type to check
     *
     * @return bool
     */
    protected function isParenthesis($tokenType) {
        return $tokenType === $this->closeParenthesis || $tokenType === $this->openParenthesis;
    }

    /**
     * Check if a token type is an operator type
     *
     * @param int $tokenType
     *
     * @return bool
     */
    protected function isAnOperator($tokenType)
    {
        foreach ($this->operators as $operator) {
            if ($tokenType === $operator->getType()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get the operator object for a token type
     *
     * @param int $tokenType
     *
     * @return ShuntingYardOperator|null
     */
    protected function getOperator($tokenType)
    {
        foreach ($this->operators as $operator) {
            if ($tokenType === $operator->getType()) {
                return $operator;
            }
        }
        return null;
    }
}