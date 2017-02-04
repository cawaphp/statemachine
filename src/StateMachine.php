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

use Cawa\Events\InstanceDispatcherTrait;
use Cawa\StateMachine\Exceptions\MissingState;
use Cawa\StateMachine\Exceptions\StateMachineException;
use Fhaculty\Graph\Graph;
use Graphp\GraphViz\GraphViz;

class StateMachine
{
    use InstanceDispatcherTrait;

    /**
     * @param string $initialState
     * @param object $subject
     */
    public function __construct(string $initialState, $subject)
    {
        $this->initialState = $initialState;
        $this->subject = $subject;
    }

    /**
     * @var string
     */
    private $initialState;

    /**
     * @var object
     */
    private $subject;

    /**
     * @return object
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @var array|State[]
     */
    private $states = [];

    /**
     * @param string $state
     *
     * @return bool
     */
    public function hasState(string $state) : bool
    {
        return isset($this->states[$state]);
    }

    /**
     * @return array|State[]
     */
    public function getStates()
    {
        return $this->states;
    }

    /**
     * @param State $state
     *
     * @return $this|self
     */
    public function addState(State $state)
    {
        $state->setStateMachine($this);
        $this->states[$state->getName()] = $state;

        return $this;
    }

    /**
     * @var State
     */
    private $currentState;

    /**
     * @return State
     */
    public function getCurrentState() : State
    {
        if (!$this->currentState) {
            $this->currentState = $this->states[$this->initialState];
        }

        return $this->currentState;
    }

    /**
     * @var StateMachineException
     */
    private $lastException;

    /**
     * @return StateMachineException
     */
    public function getLastException() : StateMachineException
    {
        return $this->lastException;
    }

    /**
     * @param string $stateName
     * @param bool $soft
     *
     * @return bool
     */
    public function apply(string $stateName, bool $soft = false) : bool
    {
        if (!$this->hasState($stateName)) {
            throw new MissingState($this->getCurrentState(), $stateName);
        }

        $from = $this->getCurrentState();
        self::instanceDispatcher()->emit(new Event(
            'state.before',
            $from,
            $this->states[$stateName],
            $this->subject
        ));

        try {
            if (!$this->getCurrentState()->can($stateName)) {
                return false;
            }
        } catch (StateMachineException $exception) {
            $this->lastException = $exception;

            if ($soft) {
                return false;
            } else {
                throw $exception;
            }
        }

        $this->currentState = $this->states[$stateName];

        self::instanceDispatcher()->emit(new Event(
            'state.after',
            $from,
            $this->states[$stateName],
            $this->subject
        ));

        return true;
    }

    /**
     * @return string[]
     */
    public function getPossibleTransitions() : array
    {
        $return = [];
        foreach ($this->getCurrentState()->getPossibleTransitions() as $transition) {
            $return[] = $transition->getTo();
        }

        return $return;
    }

    /**
     * @return string
     */
    public function getGraph() : string
    {
        $graph = new Graph();
        $builder = new GraphBuilder($graph);
        $builder->addStateMachine($this);

        $viz = new GraphViz();
        return $viz->createScript($graph);
    }
}
