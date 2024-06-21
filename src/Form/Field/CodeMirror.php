<?php

namespace PNS\Admin\Form\Field;

use PNS\Admin\Form\Field;

class CodeMirror extends Field
{
    const ASSETS_PATH = 'vendor/laravel-admin/code-mirror/codemirror-5.40.0/';
    protected $options = [
        'mode'             => 'javascript',
        'lineNumbers'      => true,
        'matchBrackets'    => true,
        'continueComments' => true,
        'extraKeys'        => [
            'Ctrl-Q' => 'toggleComment',
        ],
    ];

    /**
     * {@inheritdoc}
     */
    protected $view = 'laravel-admin::form.code-mirror';

    /**
     * {@inheritdoc}
     */
    protected static $css = [
        CodeMirror::ASSETS_PATH . 'lib/codemirror.css',
    ];

    /**
     * {@inheritdoc}
     */
    protected static $js = [
        CodeMirror::ASSETS_PATH . 'lib/codemirror.js',
        CodeMirror::ASSETS_PATH . 'addon/edit/matchbrackets.js',
        CodeMirror::ASSETS_PATH . 'addon/comment/continuecomment.js',
        CodeMirror::ASSETS_PATH . 'addon/comment/comment.js',
        CodeMirror::ASSETS_PATH . 'mode/javascript/javascript.js',
    ];

    /**
     * Set editor height.
     *
     * @param int $height
     * @return $this
     */
    public function height($height = 10)
    {
        return $this->addVariables(compact('height'));
    }

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        $options = array_merge(
            $this->options,
            self::config('config', [])
        );

        $options = json_encode($options);

        $this->script = <<<EOT
CodeMirror.fromTextArea(document.getElementById("{$this->id}"), $options);
EOT;

        return parent::render();
    }
}
