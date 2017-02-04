<?php

/*
 * This file is part of the Сáша framework.
 *
 * (c) tchiotludo <http://github.com/tchiotludo>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare (strict_types = 1);

namespace Cawa\StateMachine;

/**
 * @mixin State|Transition
 */
trait ConditionsTrait
{
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
        if ($this instanceof Transition) {
            $condition->setTransition($this);
        }

        $this->conditions[] = $condition;

        return $this;
    }
}
