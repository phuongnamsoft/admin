<?php

namespace PNS\Admin\Form\Field;

class Password extends Text
{
    public function render()
    {
        $this->prepend('<i class="fa fa-eye-slash fa-fw toggle-field-password" ></i>')
            ->defaultAttribute('type', 'password');

        return parent::render();
    }
}
