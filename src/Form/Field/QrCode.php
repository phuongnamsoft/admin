<?php

namespace PNS\Admin\Form\Field;

use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class QrCode extends Html
{

    protected $view = 'admin::form.qrcode';

    protected $label = '';

    protected $plain = false;

    public function __construct($column, $label = '')
    {
        $this->label = $label;

        $this->column = $column;

        $this->value = $this->form->model()?->$column;

        $this->addVariables(['label' => $this->label]);
    }

    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    public function render()
    {
        $renderer = new ImageRenderer(
            new RendererStyle(400),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $content = $writer->writeString($this->value);
        $this->html = '<img src="data:image/svg+xml;base64,' . base64_encode($content) . '" />';

        return parent::render();
    }
}
