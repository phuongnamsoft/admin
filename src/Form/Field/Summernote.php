<?php

namespace PNS\Admin\Form\Field;

use PNS\Admin\Form\Field\Textarea;

class Summernote extends Textarea
{
    protected $view = 'laravel-admin::form.summernote';

    protected static $css = [
        'vendor/laravel-admin/summernote/summernote.css',
    ];

    protected static $js = [
        'vendor/laravel-admin/summernote/summernote.min.js',
    ];

    public function render()
    {
        $name = $this->formatName($this->column);

        $config = (array) self::config('config');

        $config = json_encode(array_merge([
            'height' => 300,
        ], $config));

        $this->script = <<<EOT
        (function(){
            var configs = $config;

            if(configs['imageUploadServer']){
                configs.callbacks = configs.callbacks || {};
                configs.callbacks.onImageUpload = function(images){
                    window.laravelAdminSummernoteImageUploader($('#{$this->id}'),images[0],configs.imageUploadServer,configs.imageUploadName);
                };
            }

            $('#{$this->id}').summernote(configs);

            $('#{$this->id}').on("summernote.change", function (e) {
                var html = $('#{$this->id}').summernote('code');
                $('input[name="{$name}"]').val(html);
            });
        })();
EOT;
        
        return parent::render();
    }
}
