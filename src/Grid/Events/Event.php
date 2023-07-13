<?php

namespace PNS\Admin\Grid\Events;

use PNS\Admin\Grid;

abstract class Event
{
    /**
     * @var Grid
     */
    public $grid;

    public $payload = [];

    public function __construct(array $payload = [])
    {
        $this->payload = $payload;
    }

    public function setGrid(Grid $grid)
    {
        $this->grid = $grid;
    }
}
