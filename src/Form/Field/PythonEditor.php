<?php

namespace PNS\Admin\Form\Field;

use PNS\Admin\Form\Field;

class PythonEditor extends CodeMirror
{
    /**
     * {@inheritdoc}
     */
    protected static $js = [
        CodeMirror::ASSETS_PATH . 'lib/codemirror.js',
        CodeMirror::ASSETS_PATH . 'addon/edit/matchbrackets.js',
        CodeMirror::ASSETS_PATH . 'mode/python/python.js',
    ];

    protected $version = 3;

    protected $options = [
        'mode' => [
            'name' => 'python',
            'version' => 3,
            'singleLineStringErrors' => false,
        ],
        'lineNumbers' => true,
        'matchBrackets' => true,
        'indentUnit' => 4,
    ];

    /**
     * Set python version.
     *
     * @param int $version
     * @return $this
     */
    public function version($version = 3)
    {
        $this->version = $version;

        return $this;
    }
}
