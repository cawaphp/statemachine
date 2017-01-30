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

class Event extends \Cawa\Events\Event
{
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
     * @var State
     */
    private $to;

    /**
     * @return State
     */
    public function getTo() : State
    {
        return $this->to;
    }

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
     * @param string $name
     * @param State $from
     * @param State $to
     * @param object $subject
     */
    public function __construct($name, State $from, State $to, $subject)
    {
        parent::__construct($name);

        $this->from = $from;
        $this->to = $to;
        $this->subject = $subject;
    }
}
