<?php

namespace PNS\Admin\Form\Field;

use PNS\Admin\Form\Field\Textarea;

class CKEditor5 extends Textarea
{
    protected $view = 'admin::form.ckeditor5';

    protected static $css = [
        'https://cdn.ckeditor.com/ckeditor5/43.3.1/ckeditor5.css',
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
