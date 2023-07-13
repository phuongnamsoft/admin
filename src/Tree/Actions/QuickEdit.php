<?php

namespace PNS\Admin\Tree\Actions;

use PNS\Admin\Form;
use PNS\Admin\Tree\RowAction;

class QuickEdit extends RowAction
{
    protected $dialogFormDimensions = ['700px', '670px'];

    public function html()
    {
        [$width, $height] = $this->dialogFormDimensions;

        Form::dialog(trans('admin.edit'))
            ->click('.tree-quick-edit')
            ->success('PNS.reload()')
            ->dimensions($width, $height);

        return <<<HTML
<a href="javascript:void(0);" data-url="{$this->resource()}/{$this->getKey()}/edit" class="tree-quick-edit"><i class="feather icon-edit"></i>&nbsp;</a>
HTML;
    }
}
