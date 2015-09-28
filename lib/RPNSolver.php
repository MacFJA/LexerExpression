<?php

namespace MacFJA\LexerExpression;

/**
 * Class RPNSolver
 *
 * Solve a RPN expression of Doctrine Lexer tokens.
 *
 * @author  MacFJA
 * @license MIT
 * @package MacFJA\LexerExpression
 */
class RPNSolver
{
    /** @var callable The call-back function to get the result of an operator with its operand(s) */
    protected $operatorCallable;

    /**
     *
     * @var RPNOperator[]
     */
    protected $operators = array();

    /**
     * @param RPNOperator $rpnOperator
     */
    public function addOperator($rpnOperator) {
        $this->operators[] = $rpnOperator;
    }

    /**
     * @param RPNOperator[] $rpnOperators
     */
    public function addOperators($rpnOperators) {
        foreach($rpnOperators as $rpnOperator) {
            $this->addOperator($rpnOperator);
        }
    }

    /**
     * Get operators.
     *
     * @param null|int $arity The arity of the operators to retrieve. If null all operator are returned.
     *
     * @return RPNOperator[]
     */
    public function getOperators($arity=null) {
        if (null === $arity) {
            return $this->operators;
        }

        $result = array();
        foreach ($this->operators as $operator) {
            if ($operator->getArity() === $arity) {
                $result[] = $operator;
            }
        }
        return $result;
    }

    /**
     * @param callable $operatorCallable
     */
    public function setOperatorCallable($operatorCallable)
    {
        $this->operatorCallable = $operatorCallable;
    }

    public function solve($rpn) {
        do {
            $rpn = $this->doSolve($rpn);
        } while(count($rpn) > 1);

        return reset($rpn);
    }

    /**
     * Solve the first found operator
     *
     * @param array $rpn The RPN expression
     *
     * @return array The reduced RPN expression
     *
     * @throws MissingOperator
     * @throws MissingOperand
     */
    protected function doSolve($rpn) {
        $tokens = $rpn;

        $collected = array();
        $collect = array_shift($tokens);

        // Loop until an operator
        while(!$this->isOperator($collect)) {
            if (count($tokens) === 0) {
                throw new MissingOperator();
            }

            $collected[] = $collect;
            $collect = array_shift($tokens);
        }

        // Check if the number of collected operand/variable is enough for the found operator
        if (count($collected) < $this->getArity($collect['type'])) {
            throw new MissingOperand(
                vsprintf('The operator "%s" (type %d) need %d operand, only %d found', array(
                    $collect['value'],
                    $collect['type'],
                    $this->getArity($collect['type']),
                    count($collected)
                ))
            );
        }

        // Extract params for collected variable/operand for the found operator
        $params = array_splice($collected, -($this->getArity($collect['type'])));
        // Use the callback function to evaluate the operation
        $collected[] = call_user_func_array($this->operatorCallable, array($collect, $params));

        // The new RPN expression is the previously collected token + the operation + all unread token
        $newRpn = array_merge($collected, $tokens);

        return $newRpn;
    }

    /**
     * Check if the given token is an operator
     *
     * @param array $token
     *
     * @return bool
     */
    protected function isOperator($token) {
        foreach ($this->operators as $operator) {
            if ($operator->getType() === $token['type']) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get the arity of a toke type
     *
     * @param int $type
     *
     * @return int Return the arity of the operator, or 0 if the type is not an operator
     */
    protected function getArity($type) {
        foreach ($this->operators as $operator) {
            if ($operator->getType() === $type) {
                return $operator->getArity();
            }
        }
        return 0;
    }
}