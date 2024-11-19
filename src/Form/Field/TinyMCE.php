<?php

namespace PNS\Admin\Form\Field;

use PNS\Admin\Form\Field\Textarea;

class TinyMCE extends Textarea
{
    protected $view = 'laravel-admin::form.tinymce';

    protected static $js = [
        'vendor/laravel-admin/tinymce/tinymce.min.js',
    ];

    public function render()
    {
        $this->script = <<<EOT
tinymce.init({
  selector: 'textarea#{$this->id}'
});
EOT;
        return parent::render();
    }
}
