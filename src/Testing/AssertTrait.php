<?php

/*
 * This file is part of the Termii Client.
 *
 * (c) Ilesanmi Olawale Adedoun Twitter: @mane_olawale
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ManeOlawale\Laravel\Termii\Testing;

use Closure;
use PHPUnit\Framework\Assert as PHPUnit;

/**
 * Assertion functions
 */
trait AssertTrait
{
    /**
     * Assert sent requests
     * @param string $alias
     */
    public function assertSent(string $alias)
    {
        PHPUnit::assertTrue(
            $this->fake()->responses($alias)->count() > 0,
            sprintf('Expected request to be sent to {%s} at least once.', $this->fake()->getPath($alias))
        );
    }

    /**
     * Assert sent requests
     * @param string $alias
     */
    public function assertNotSent(string $alias)
    {
        PHPUnit::assertTrue(
            $this->fake()->responses($alias)->count() < 1,
            sprintf('Expected no request to be sent to {%s}.', $this->fake()->getPath($alias))
        );
    }

    /**
     * Assert number of sent requests
     * @param string $alias
     */
    public function assertSentTimes(string $alias, int $times = 1)
    {
        $count = $this->fake()->responses($alias)->count();

        PHPUnit::assertSame(
            $times,
            $count,
            sprintf(
                'The expected {%s} endpoint received request {%s} times instead of {%s} times.',
                $this->fake()->getPath($alias),
                $times,
                $count
            )
        );
    }

    /**
     * Assert successful requests
     * @param string $alias
     */
    public function assertSentSuccessful(string $alias)
    {
        $count = $this->fake()->responses($alias)->where('successful', true)->count();

        PHPUnit::assertTrue(
            $count > 0,
            sprintf(
                'The expected {%s} endpoint should return success response at least once.',
                $this->fake()->getPath($alias)
            )
        );
    }

    /**
     * Assert successful requests times
     * @param string $alias
     */
    public function assertSentSuccessfulTimes(string $alias, int $times)
    {
        $count = $this->fake()->responses($alias)->where('successful', true)->count();

        PHPUnit::assertSame(
            $times,
            $count,
            sprintf(
                'The expected {%s} endpoint return success response {%s} times instead of {%s} times.',
                $this->fake()->getPath($alias),
                $times,
                $count
            )
        );
    }

    /**
     * Assert failed requests
     * @param string $alias
     */
    public function assertSentFailed(string $alias)
    {
        $count = $this->fake()->responses($alias)->where('successful', false)->count();

        PHPUnit::assertTrue(
            $count > 0,
            sprintf(
                'The expected {%s} endpoint should return success response at least once.',
                $this->fake()->getPath($alias)
            )
        );
    }

    /**
     * Assert failed requests times
     * @param string $alias
     */
    public function assertSentFailedTimes(string $alias, int $times = 1)
    {
        $count = $this->fake()->responses($alias)->where('successful', false)->count();

        PHPUnit::assertSame(
            $times,
            $count,
            sprintf(
                'The expected {%s} endpoint return failed response {%s} times instead of {%s} times.',
                $this->fake()->getPath($alias),
                $times,
                $count
            )
        );
    }

    /**
     * Assert sent requests with a closure of sequence of closures
     * @param string $alias
     * @param \Closure|Sequence $callable
     */
    public function assert(string $alias, $callable)
    {
        if ($this->fake()->responses($alias)->count()) {
            if ($callable instanceof Closure) {
                $callable($this->fake()->responses($alias)->first());
            }

            if ($callable instanceof Sequence) {
                $this->fake()->responses($alias)
                    ->take($callable->count())
                    ->each(function ($item) use ($callable) {
                        $closure = $callable->next();
                        $closure($item);
                    });
            }
        }
    }
}
