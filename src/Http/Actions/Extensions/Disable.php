<?php

namespace PNS\Admin\Http\Actions\Extensions;

use PNS\Admin\Admin;
use PNS\Admin\Grid\RowAction;

class Disable extends RowAction
{
    public function title()
    {
        return sprintf('<span class="text-80">%s</span>', trans('admin.disable'));
    }

    public function handle()
    {
        Admin::extension()->enable($this->getKey(), false);

        return $this
            ->response()
            ->success(trans('admin.update_succeeded'))
            ->refresh();
    }
}
