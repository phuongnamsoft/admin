<?php

namespace PNS\Admin\Grid\Actions;

use PNS\Admin\Form;
use PNS\Admin\Grid\RowAction;

class QuickEdit extends RowAction
{
    /**
     * @return array|null|string
     */
    public function title()
    {
        if ($this->title) {
            return $this->title;
        }

        return '<i class="feather icon-edit"></i> '.__('admin.quick_edit').' &nbsp;&nbsp;';
    }

    public function render()
    {
        [$width, $height] = $this->parent->option('dialog_form_area');

        $title = trans('admin.edit');

        Form::dialog($title)
            ->click(".{$this->getElementClass()}")
            ->dimensions($width, $height)
            ->forceRefresh()
            ->success('PNS.reload()');

        $this->setHtmlAttribute([
            'data-url' => $this->parent->getEditUrl($this->getKey()),
        ]);

        return parent::render();
    }

    public function makeSelector()
    {
        return 'quick-edit';
    }
}
