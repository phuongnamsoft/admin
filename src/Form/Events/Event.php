<?php

namespace PNS\Admin\Form\Events;

use PNS\Admin\Form;

abstract class Event
{
    /**
     * @var Form
     */
    public $form;

    public $payload = [];

    public function __construct(Form $form, array $payload = [])
    {
        $this->form = $form;
        $this->payload = $payload;
    }
}
