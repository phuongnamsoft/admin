<?php

namespace PNS\Admin\Form\Field;

use Illuminate\Support\Arr;

class Password extends Text
{
    /**
     * {@inheritdoc}
     *
     * Skip validation when updating and the password is left blank (keep current hash).
     */
    public function getValidator(array $input)
    {
        if ($this->form && $this->form->isEditing()) {
            $value = Arr::get($input, $this->column);
            if ($value === null || $value === '') {
                return false;
            }
        }

        return parent::getValidator($input);
    }

    public function render()
    {
        $this->prepend('<i class="fa fa-eye-slash fa-fw toggle-field-password" ></i>')
            ->defaultAttribute('type', 'password');

        return parent::render();
    }
}
