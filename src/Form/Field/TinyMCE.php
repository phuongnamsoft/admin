<?php

namespace PNS\Admin\Form\Field;

use PNS\Admin\Form\Field\Textarea;

class TinyMCE extends Textarea
{
    protected $view = 'admin::form.tinymce';

    protected static $js = [
        'vendor/laravel-admin/tinymce/tinymce.min.js',
    ];

    public function render()
    {
        $this->script = <<<EOT
(() => {
    const currentInstance = tinymce.get('{$this->id}');
    if (currentInstance) {
        currentInstance.remove();
    }

    tinymce.init({
        selector: 'textarea#{$this->id}'
    });
})();
EOT;
        return parent::render();
    }
}