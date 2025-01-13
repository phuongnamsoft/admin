<?php

namespace PNS\Admin\Widgets;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Arr;

class DataTableAjax extends Widget implements Renderable
{
    /**
     * @var string
     */
    protected $view = 'admin::widgets.datatable-ajax';

    const ACTION_KEY = '__actions__';

    /**
     * @var array
     */
    protected $headers = [];

    /**
     * @var array
     */
    protected $styles = ['table-bordered', 'table-hover', 'table-striped'];

    /**
     * @var string
     */
    protected $ajaxSrc = null;

    protected $perPage = 25;

    /**
     * Table constructor.
     *
     * @param array $headers
     * @param array $rows
     * @param array $style
     * @param array $options
     */
    public function __construct($headers, $ajaxSrc)
    {
        $this->headers = $headers;
        $this->ajaxSrc = $ajaxSrc;
    }

    public function setStyles(array $styles)
    {
        $this->styles = $styles;
        return $this;
    }

    public function render()
    {
        $this->class('table dataTable ' . implode(' ', $this->styles));

        $headers = [];

        foreach($this->headers as $key => $val) {
            $headers[] = $val;
            $columns[] = [
                'data' => $key,
                'name' => $key,
                'searchable' => $key !== self::ACTION_KEY,
                'orderable' => $key !== self::ACTION_KEY,
            ];
        }

        $vars = [
            'headers' => $headers,
            'columns' => $columns,
            'attributes' => $this->formatAttributes(),
            'ajaxSrc' =>$this->ajaxSrc
        ];

        return view($this->view, $vars)->render();
    }
}
