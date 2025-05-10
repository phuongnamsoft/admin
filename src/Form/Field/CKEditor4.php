<?php

namespace PNS\Admin\Form\Field;

use PNS\Admin\Form\Field\Textarea;

class CKEditor4 extends Textarea
{
    protected $view = 'admin::form.ckeditor';

    protected static $js = [
        'https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js',
    ];

    public function render()
    {
        $config = json_encode($this->options);

        $this->script = <<<EOT
CKEDITOR.replace('{$this->id}', $config);
EOT;
        return parent::render();
    }
}
