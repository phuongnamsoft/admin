<?php

namespace PNS\Admin\Form\Field;

use PNS\Admin\Form\Field;

class PHPEditor extends CodeMirror
{
    protected static $js = [
        CodeMirror::ASSETS_PATH.'lib/codemirror.js',
        // x-httpd-php mode
        CodeMirror::ASSETS_PATH.'mode/htmlmixed/htmlmixed.js',
        CodeMirror::ASSETS_PATH.'mode/xml/xml.js',
        CodeMirror::ASSETS_PATH.'mode/javascript/javascript.js',
        CodeMirror::ASSETS_PATH.'mode/css/css.js',
        CodeMirror::ASSETS_PATH.'mode/clike/clike.js',
        CodeMirror::ASSETS_PATH.'mode/php/php.js',
    ];
    
    protected $options = [
        'mode' => 'application/x-httpd-php',
        'lineNumbers' => true,
        'matchBrackets' => true,
        'indentUnit' => 4,
        'indentWithTabs' => true,
        'extraKeys'        => [
            'Ctrl-Q' => 'toggleComment',
        ],
    ];
}
