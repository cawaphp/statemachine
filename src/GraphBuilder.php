<?php

namespace Metabor\Statemachine\Graph;

namespace Cawa\StateMachine;

use Fhaculty\Graph\Graph;

/**
 * @author otischlinger
 */
class GraphBuilder
{
    /**
     * @var \SplObjectStorage
     */
    private $layoutCallback;

    /**
     * @var Graph
     */
    private $graph;

    /**
     * @param Graph $graph
     */
    public function __construct(Graph $graph)
    {
        $this->layoutCallback = new \SplObjectStorage();
        $this->graph = $graph;
    }

    /**
     * @param State $state
     *
     * @return \Fhaculty\Graph\Vertex
     */
    public function createStatusVertex(State $state)
    {
        $stateName = $state->getName();
        $vertex = $this->graph->createVertex($stateName, true);

        return $vertex;
    }

    /**
     * @param State $state
     * @param Transition $transition
     *
     * @return string
     */
    protected function getTransitionLabel(State $state, Transition $transition)
    {
        if (sizeof($transition->getConditions()) == 0) {
            return null;
        }

        $labelParts = [];
        foreach ($transition->getConditions() as $condition) {
            $labelParts[] = 'if (' . (new \ReflectionClass($condition))->getShortName() . ')';
        }

        $label = implode(PHP_EOL, $labelParts);

        return $label;
    }

    /**
     * @param State $state
     * @param Transition $transition
     */
    protected function addTransition(State $state, Transition $transition)
    {
        $sourceStateVertex = $this->createStatusVertex($state);
        $targetStateVertex = $this->createStatusVertex(new State($transition->getTo()));
        $edge = $sourceStateVertex->createEdgeTo($targetStateVertex);
        $label = $this->getTransitionLabel($state, $transition);
        if ($label) {
            $edge->setAttribute('graphviz.label', $label);
        }
    }

    /**
     * @param State $state
     */
    public function addState(State $state)
    {
        $this->createStatusVertex($state);
        foreach ($state->getTransitions() as $transition) {
            $this->addTransition($state, $transition);
        }
    }

    /**
     * @param StateMachine $stateMachine
     */
    public function addStateMachine(StateMachine $stateMachine)
    {
        foreach ($stateMachine->getStates() as $state) {
            $this->addState($state);
        }
    }
}
