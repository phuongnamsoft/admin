<?php

namespace PNS\Admin\Grid\Column\Filter;

use PNS\Admin\Grid\Model;

class Ngt extends Equal
{
    /**
     * Add a binding to the query.
     *
     * @param  string  $value
     * @param  Model|null  $model
     */
    public function addBinding($value, Model $model)
    {
        $value = trim($value);
        if ($value === '') {
            return;
        }

        $this->withQuery($model, 'where', ['<=', $value]);
    }
}
