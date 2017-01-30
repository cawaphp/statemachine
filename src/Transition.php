<?php

/*
 * This file is part of the Сáша framework.
 *
 * (c) tchiotludo <http://github.com/tchiotludo>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare (strict_types=1);

namespace Cawa\StateMachine;

use Cawa\StateMachine\Exceptions\InvalidCondition;

class Transition
{
    /**
     * @param string $to
     * @param Condition[] $conditions
     */
    public function __construct(string $to, array $conditions = [])
    {
        $this->to = $to;
        $this->conditions = $conditions;
    }

    /**
     * @var string
     */
    private $to;

    /**
     * @return string
     */
    public function getTo() : string
    {
        return $this->to;
    }

    /**
     * @var State
     */
    private $from;

    /**
     * @return State
     */
    public function getFrom() : State
    {
        return $this->from;
    }

    /**
     * @param State $from
     *
     * @return Transition
     */
    public function setFrom(State $from) : Transition
    {
        $this->from = $from;

        return $this;
    }

    /**
     * @var array|Condition[]
     */
    private $conditions = [];

    /**
     * @return array|Condition[]
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * @param Condition $condition
     *
     * @return $this|self
     */
    public function addCondition(Condition $condition) : self
    {
        $condition->setTransition($this);

        $this->conditions[] = $condition;

        return $this;
    }

    /**
     * @return bool
     */
    public function can() : bool
    {
        $subject = $this->getFrom()->getStateMachine()->getSubject();

        foreach ($this->conditions as $condition) {
            if (!$condition($subject)) {
                throw new InvalidCondition($condition);
            }
        }

        return true;
    }
}
