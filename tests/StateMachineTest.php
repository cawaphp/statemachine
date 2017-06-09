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

/**
 * Сáша frameworks tests.
 *
 * @author tchiotludo <http://github.com/tchiotludo>
 */

namespace CawaTest\StateMachine;

use Cawa\StateMachine\Condition;
use Cawa\StateMachine\Event;
use Cawa\StateMachine\Exceptions\StateMachineException;
use Cawa\StateMachine\State;
use Cawa\StateMachine\StateMachine;
use Cawa\StateMachine\Transition;
use PHPUnit_Framework_TestCase as TestCase;

class StateMachineTest extends TestCase
{
    const STATUS_PENDING = 'PENDING';
    const STATUS_TOCONFIRM = 'TOCONFIRM';
    const STATUS_CONFIRMED = 'CONFIRMED';
    const STATUS_READY = 'READY';
    const STATUS_PROCESSED = 'PROCESSED';
    const STATUS_SENT = 'SENT';
    const STATUS_CANCELED = 'CANCELED';
    const STATUS_RETURN = 'RETURN';

    /**
     * @param string $initialState
     *
     * @return StateMachine
     */
    private function getStateMachine($initialState = self::STATUS_PENDING) : StateMachine
    {
        $stateMachine = new StateMachine($initialState, new \stdClass());

        $stateMachine->addState((new State(self::STATUS_PENDING))
            ->addTransition(new Transition(self::STATUS_TOCONFIRM))
            ->addTransition(new Transition(self::STATUS_CONFIRMED))
        );

        $stateMachine->addState((new State(self::STATUS_TOCONFIRM))
            ->addTransition(new Transition(self::STATUS_CONFIRMED))
            ->addTransition(new Transition(self::STATUS_CANCELED))
        );

        $stateMachine->addState((new State(self::STATUS_CONFIRMED))
            ->addTransition(new Transition(self::STATUS_READY))
        );

        $returnTrue = new class() extends Condition {
            public function __invoke($subject) : bool
            {
                return true;
            }
        };

        $returnFalse = new class() extends Condition {
            public function __invoke($subject) : bool
            {
                return false;
            }
        };

        $stateMachine->addState((new State(self::STATUS_READY))
            ->addTransition((new Transition(self::STATUS_CONFIRMED))
                ->addCondition($returnFalse)
            )
            ->addTransition((new Transition(self::STATUS_PROCESSED))
                ->addCondition($returnTrue)
            )
            ->addTransition(new Transition(self::STATUS_RETURN))
        );

        $stateMachine
            ->addState((new State(self::STATUS_PROCESSED))
                ->addTransition(new Transition(self::STATUS_SENT))
            );

        $stateMachine
            ->addState((new State(self::STATUS_SENT))
                ->addCondition($returnFalse)
            );

        $stateMachine
            ->addState(new State(self::STATUS_CANCELED))
            ->addState(new State(self::STATUS_RETURN));

        return $stateMachine;
    }

    /**
     * @param string $initial
     * @param string $to
     *
     * @dataProvider allowedTransitionProvider
     */
    public function testAllowedTransition(string $initial, string $to)
    {
        $stateMachine = $this->getStateMachine($initial);

        $stateMachine->apply($to);

        $this->assertEquals($stateMachine->getCurrentState()->getName(), $to);
    }

    /**
     * @param string $initial
     * @param string $to
     *
     * @dataProvider allowedTransitionProvider
     */
    public function testEvents(string $initial, string $to)
    {
        $stateMachine = $this->getStateMachine($initial);

        $stateMachine->instanceDispatcher()->addListener(
            'state.before',
            function (Event $event) use ($stateMachine, $initial, $to) {
                $this->assertInstanceOf(\stdClass::class, $event->getSubject());
                $this->assertEquals($event->getFrom()->getName(), $initial);
                $this->assertEquals($event->getTo()->getName(), $to);
                $this->assertEquals($stateMachine->getCurrentState()->getName(), $initial);
            }
        );

        $stateMachine->instanceDispatcher()->addListener(
            'state.after',
            function (Event $event) use ($stateMachine, $initial, $to) {
                $this->assertEquals($event->getFrom()->getName(), $initial);
                $this->assertEquals($event->getTo()->getName(), $to);
                $this->assertEquals($stateMachine->getCurrentState()->getName(), $to);
            }
        );

        $stateMachine->apply($to);
    }

    /**
     * @return array
     */
    public function allowedTransitionProvider()
    {
        return [
            [
                self::STATUS_PENDING,
                self::STATUS_TOCONFIRM,
            ],
            [
                self::STATUS_CONFIRMED,
                self::STATUS_READY,
            ],
            [
                self::STATUS_READY,
                self::STATUS_RETURN,
            ],
            [
                self::STATUS_READY,
                self::STATUS_PROCESSED,
            ],
        ];
    }

    /**
     * @param string $initial
     * @param string $to
     *
     * @dataProvider prohibitedTransitionProvider
     */
    public function testProhibitedTransition(string $initial, string $to)
    {
        $this->expectException(StateMachineException::class);
        $stateMachine = $this->getStateMachine($initial);
        $stateMachine->apply($to);
    }

    /**
     * @param string $initial
     * @param string $to
     *
     * @dataProvider prohibitedTransitionProvider
     */
    public function testSoftProhibitedTransition(string $initial, string $to)
    {
        $stateMachine = $this->getStateMachine($initial);
        $stateMachine->apply($to, true);

        $this->assertInstanceOf(StateMachineException::class, $stateMachine->getLastException());
    }

    /**
     * @return array
     */
    public function prohibitedTransitionProvider()
    {
        return [
            [
                self::STATUS_PROCESSED,
                self::STATUS_READY,
            ],
            [
                self::STATUS_CONFIRMED,
                self::STATUS_PENDING,
            ],
            [
                self::STATUS_CONFIRMED,
                self::STATUS_TOCONFIRM,
            ],
            [
                self::STATUS_READY,
                self::STATUS_CONFIRMED,
            ],
            [
                self::STATUS_PROCESSED,
                self::STATUS_SENT,
            ],
        ];
    }

    /**
     *
     */
    public function testMissingState()
    {
        $this->expectException(StateMachineException::class);
        $stateMachine = $this->getStateMachine(self::STATUS_READY);
        $stateMachine->apply('INVALID');
    }

    /**
     *
     */
    public function testGraph()
    {
        $stateMachine = $this->getStateMachine(self::STATUS_READY);
        $this->assertContains('digraph', $stateMachine->getGraph());
    }

    /**
     * @param string $initial
     * @param array $to
     *
     * @dataProvider possibleTransitionsProviders
     */
    public function testPossibleTransitions(string $initial, array $to)
    {
        $stateMachine = $this->getStateMachine($initial);
        $transitions = [];
        foreach ($stateMachine->getCurrentState()->getPossibleTransitions() as $transition) {
            $transitions[] = $transition->getTo();
        }

        $this->assertEquals($to, $transitions);
        $this->assertEquals($to, $stateMachine->getPossibleTransitions());
    }

    /**
     * @return array
     */
    public function possibleTransitionsProviders()
    {
        return [
            [
                self::STATUS_PENDING,
                [self::STATUS_TOCONFIRM, self::STATUS_CONFIRMED],
            ],
            [
                self::STATUS_READY,
                [self::STATUS_PROCESSED, self::STATUS_RETURN],
            ],
            [
                self::STATUS_PROCESSED,
                [],
            ],
        ];
    }

    /**
     * @param string $initial
     * @param array $to
     *
     * @dataProvider possibleTransitionsWithoutConditions
     */
    public function testPossibleTransitionsWithoutConditions(string $initial, array $to)
    {
        $stateMachine = $this->getStateMachine($initial);
        $transitions = [];
        foreach ($stateMachine->getCurrentState()->getPossibleTransitions(false) as $transition) {
            $transitions[] = $transition->getTo();
        }

        $this->assertEquals($to, $transitions);
        $this->assertEquals($to, $stateMachine->getPossibleTransitions(false));
    }

    /**
     * @return array
     */
    public function possibleTransitionsWithoutConditions()
    {
        return [
            [
                self::STATUS_READY,
                [self::STATUS_CONFIRMED, self::STATUS_PROCESSED, self::STATUS_RETURN],
            ],
        ];
    }
}
