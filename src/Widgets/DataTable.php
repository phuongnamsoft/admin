<?php

namespace PNS\Admin\Widgets;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Arr;

class DataTable extends Widget implements Renderable
{
    /**
     * @var string
     */
    protected $view = 'admin::widgets.datatable';

    /**
     * @var array
     */
    protected $headers = [];

    /**
     * @var array
     */
    protected $rows = [];

    /**
     * @var array
     */
    protected $style = [];

    protected $options = [];

    /**
     * Table constructor.
     *
     * @param array $headers
     * @param array $rows
     * @param array $style
     * @param array $options
     */
    public function __construct($headers = [], $rows = [], $style = [], $options = [])
    {
        $this->setHeaders($headers);
        $this->setRows($rows);
        $this->setStyle($style);
        $this->setOptions($options);
        $this->class('table dataTable ' . implode(' ', $this->style));
    }

    /**
     * Set table headers.
     *
     * @param array $headers
     *
     * @return $this
     */
    public function setHeaders($headers = [])
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * Set table rows.
     *
     * @param array $rows
     *
     * @return $this
     */
    public function setRows($rows = [])
    {
        if (Arr::isAssoc($rows)) {
            foreach ($rows as $key => $item) {
                $this->rows[] = [$key, $item];
            }

            return $this;
        }

        $this->rows = $rows;

        return $this;
    }

    /**
     * Set table style.
     *
     * @param array $style
     *
     * @return $this
     */
    public function setStyle($style = [])
    {
        $this->style = $style;

        return $this;
    }

    /**
     * Set table options.
     *
     * @param array $options
     *
     * @return $this
     */
    public function setOptions($options = [])
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Render the table.
     *
     * @return mixed|string
     *
     * @throws \Throwable
     */
    public function render()
    {
        $vars = [
            'headers' => $this->headers,
            'rows' => $this->rows,
            'style' => $this->style,
            'attributes' => $this->formatAttributes(),
            'options' => json_encode($this->options),
        ];

        return view($this->view, $vars)->render();
    }
}
