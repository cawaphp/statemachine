<?php

/*
 * This file is part of the Сáша framework.
 *
 * (c) tchiotludo <http://github.com/tchiotludo>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Cawa\StateMachine;

use Cawa\StateMachine\Exceptions\InvalidCondition;
use Cawa\StateMachine\Exceptions\InvalidTransition;
use Cawa\StateMachine\Exceptions\MissingTransition;
use Cawa\StateMachine\Exceptions\StateMachineException;

class State
{
    use ConditionsTrait;

    /**
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @var string
     */
    private $name;

    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * @var string
     */
    private $label;

    /**
     * @return string
     */
    public function getLabel() : ?string
    {
        return $this->label;
    }

    /**
     * @param string $label
     *
     * @return self|$this
     */
    public function setLabel(string $label = null) : self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @var StateMachine
     */
    private $stateMachine;

    /**
     * @return StateMachine
     */
    public function getStateMachine() : StateMachine
    {
        return $this->stateMachine;
    }

    /**
     * @param StateMachine $stateMachine
     *
     * @return self|$this
     */
    public function setStateMachine(StateMachine $stateMachine) : self
    {
        $this->stateMachine = $stateMachine;

        return $this;
    }

    /**
     * @var array|Transition[]
     */
    private $transitions = [];

    /**
     * @return array|Transition[]
     */
    public function getTransitions()
    {
        return $this->transitions;
    }

    /**
     * @param string $stateName
     *
     * @return bool
     */
    public function hasTransition(string $stateName) : bool
    {
        return isset($this->transitions[$stateName]);
    }

    /**
     * @param Transition $transition
     *
     * @return $this|self
     */
    public function addTransition(Transition $transition) : self
    {
        $transition->setFrom($this);

        $this->transitions[$transition->getTo()] = $transition;

        return $this;
    }

    /**
     * @param State $state
     */
    private function analyseStateConditions(State $state)
    {
        $subject = $this->getStateMachine()->getSubject();

        foreach ($state->getConditions() as $condition) {
            $clone = clone $condition;
            $clone->setTransition((new Transition($state->getName(), [$clone]))
                   ->setFrom($this->getStateMachine()->getCurrentState())
               );

            if (!$clone($subject)) {
                throw new InvalidCondition($clone);
            }
        }
    }

    /**
     * @param string $stateName
     *
     * @return bool
     */
    public function can(string $stateName) : bool
    {
        if (!$this->hasTransition($stateName)) {
            throw new MissingTransition($this, $stateName);
        }

        $this->analyseStateConditions($this->stateMachine->getStates()[$stateName]);

        try {
            return $this->transitions[$stateName]->can();
        } catch (StateMachineException $exception) {
            throw new InvalidTransition($this->transitions[$stateName], $exception);
        }
    }

    /**
     * @param bool $analyseConditions
     *
     * @return array|Transition[]
     */
    public function getPossibleTransitions(bool $analyseConditions = true) : array
    {
        $return = [];

        foreach ($this->transitions as $transition) {
            if (!isset($this->stateMachine->getStates()[$transition->getTo()])) {
                continue;
            }

            try {
                if ($analyseConditions) {
                    $can = $transition->can();
                    $toState = $this->stateMachine->getStates()[$transition->getTo()];
                    $this->analyseStateConditions($toState);
                } else {
                    $can = true;
                }
            } catch (StateMachineException $exception) {
                $can = false;
            }

            if ($can) {
                $return[] = $transition;
            }
        }

        return $return;
    }
}
