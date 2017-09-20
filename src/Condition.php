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

abstract class Condition
{
    /**
     * @var Transition
     */
    private $transition;

    /**
     * @return Transition
     */
    public function getTransition() : Transition
    {
        return $this->transition;
    }

    /**
     * @param Transition $transition
     *
     * @return Condition
     */
    public function setTransition(Transition $transition) : Condition
    {
        $this->transition = $transition;

        return $this;
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
     * @param object $subject
     *
     * @return bool
     */
    abstract public function __invoke($subject) : bool;
}
