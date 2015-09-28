<?php

namespace MacFJA\LexerExpression;

/**
 * Class RPNOperator
 *
 * Representation of a Shunting Yard algorithm operator.
 * Hold information about Token identifier (token type) and operator precedence (its priority) and associativity.
 *
 * @author  MacFJA
 * @license MIT
 * @package MacFJA\LexerExpression
 */
class ShuntingYardOperator
{
    const ASSOCIATIVITY_LEFT = 0;
    const ASSOCIATIVITY_RIGHT = 0;

    /** @var int The token type identifier */
    protected $type;
    /**
     * The precedence of the operator.
     * The higher is the integer value , the higher is the precedence of the token.
     *   Example, for basic mathematics, "*" is higher than "+"
     *
     * @var int
     */
    protected $precedence;
    /**
     * The associativity of the operator.
     *   Example, for basic mathematics, "-" is left, and "^" (power) is right
     * @var int
     */
    protected $associativity = self::ASSOCIATIVITY_LEFT;

    /**
     * ShuntingYardOperator constructor.
     * @param int $type
     * @param int $precedence
     * @param int $associativity
     */
    public function __construct($type, $precedence, $associativity)
    {
        $this->type = $type;
        $this->precedence = $precedence;
        $this->associativity = $associativity;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param int $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getPrecedence()
    {
        return $this->precedence;
    }

    /**
     * @param int $precedence
     */
    public function setPrecedence($precedence)
    {
        $this->precedence = $precedence;
    }

    /**
     * @return int
     */
    public function getAssociativity()
    {
        return $this->associativity;
    }

    /**
     * @param int $associativity
     */
    public function setAssociativity($associativity)
    {
        $this->associativity = $associativity;
    }

    /**
     * Check if an operator has a lower precedence than an other
     *
     * @param ShuntingYardOperator $operator
     *
     * @return bool
     */
    public function hasLowerPrecedence(ShuntingYardOperator $operator)
    {
        return (self::ASSOCIATIVITY_LEFT === $this->associativity && $this->precedence <= $operator->precedence)
            || (self::ASSOCIATIVITY_RIGHT === $this->associativity && $this->precedence < $operator->precedence);
    }
}