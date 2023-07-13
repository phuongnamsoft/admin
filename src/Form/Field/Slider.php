<?php

namespace PNS\Admin\Form\Field;

use PNS\Admin\Form\Field;

class Slider extends Field
{
    protected $options = [
        'type'     => 'single',
        'prettify' => false,
        'hasGrid'  => true,
    ];
}
