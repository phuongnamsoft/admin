<?php

namespace PNS\Admin\Form\Field;

use PNS\Admin\Form\Field\Textarea;

class CKEditor extends Textarea
{
    protected $view = 'admin::form.ckeditor';

    protected static $js = [
        'vendor/laravel-admin/ckeditor/ckeditor.js',
    ];

    public function render()
    {
        $config = (array) CKEditor::config('config');

        $config = json_encode(array_merge($config, $this->options));

        $this->script = <<<EOT
CKEDITOR.replace('{$this->id}', $config);
EOT;
        return parent::render();
    }
}
