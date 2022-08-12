<?php

namespace ManeOlawale\Laravel\Termii\Testing;

class Sequence
{
    /**
     * The item list
     * @var array
     */
    protected $items;

    /**
     * The current item list
     * @var array
     */
    protected $currentItems;

    /**
     * Number of times the sequence restarted
     * @var int
     */
    protected $rotation = 0;

    protected function __construct(array $items)
    {
        $this->items = $items;
        $this->currentItems = $items;
    }

    /**
     * Create a new sequence
     *
     * @param array ...$item
     *
     * @return $this
     */
    public static function create(...$item)
    {
        return new static($item);
    }

    /**
     * Get the next item of the sequence
     *
     * @return mixed
     */
    public function next()
    {
        if (empty($this->currentItems)) {
            $this->currentItems = $this->items;
            $this->rotation += 1;
        }

        return array_shift($this->currentItems);
    }

    /**
     * Count the items of the sequence
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Get the number of times the sequence restarted
     *
     * @return int
     */
    public function rotation(): int
    {
        return $this->rotation;
    }
}
