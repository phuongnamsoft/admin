<?php

namespace PNS\Admin\Form\Field;

use PNS\Admin\Form\Field;

class JsonEditor extends Field
{
    protected $view = 'admin::json-editor';

    protected static $css = [
        'vendor/laravel-admin/json-editor/jsoneditor-6.2.1/dist/jsoneditor.min.css',
    ];

    protected static $js = [
        'vendor/laravel-admin/json-editor/jsoneditor-6.2.1/dist/jsoneditor.min.js',
    ];

    public function render()
    {
        $json = old($this->column, $this->value());

        if (empty($json)) {
            $json = '{}';
        }

        if (!is_string($json)) {
            $json = json_encode($json);
        } else {
            $json = json_encode(json_decode($json));
        }
        $this->value = $json;

        $options = @json_encode(config('admin.extensions.json-editor.config'));

        if (empty($options)) {
            $options = "{}";
        }

        $this->script = <<<EOT
// create the editor
var container = document.getElementById("{$this->id}");
var options = {$options};
window['editor_{$this->id}'] = new JSONEditor(container, options);

// set json
var json = {$json};
window['editor_{$this->id}'].set(json);

// get json
$('button[type="submit"]').click(function() {
var json = window['editor_{$this->id}'].get()
$('input[id={$this->id}_input]').val(JSON.stringify(json))
});
EOT;

        return parent::render();
    }
}
