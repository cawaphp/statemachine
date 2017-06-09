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

namespace Cawa\StateMachine\Exceptions;

use Cawa\StateMachine\Condition;

class InvalidCondition extends StateMachineException
{
    /**
     * @param Condition $condition
     * @param \Exception $previous
     */
    public function __construct(Condition $condition, \Exception $previous = null)
    {
        parent::__construct(sprintf(
            "Invalid condition '%s' on transition from '%s' to '%s'",
            (new \ReflectionClass($condition))->getShortName(),
            $condition->getTransition()->getFrom()->getName(),
            $condition->getTransition()->getTo()
        ), 0, $previous);
    }
}
