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

use Cawa\StateMachine\Exceptions\InvalidTransition;
use Cawa\StateMachine\Exceptions\MissingTransition;
use Cawa\StateMachine\Exceptions\StateMachineException;

class State
{
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
     * @return State
     */
    public function setStateMachine(StateMachine $stateMachine) : State
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
     * @param string $stateName
     *
     * @return bool
     */
    public function can(string $stateName) : bool
    {
        if (!$this->hasTransition($stateName)) {
            throw new MissingTransition($this, $stateName);
        }

        try {
            return $this->transitions[$stateName]->can();
        } catch (StateMachineException $exception) {
            throw new InvalidTransition($this->transitions[$stateName], $exception);
        }
    }

    /**
     * @return array|Transition[]
     */
    public function getPossibleTransitions() : array
    {
        $return = [];

        foreach ($this->transitions as $transition) {
            try {
                $can = $transition->can();
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
