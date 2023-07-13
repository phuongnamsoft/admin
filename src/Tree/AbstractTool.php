<?php

namespace PNS\Admin\Tree;

use PNS\Admin\Actions\Action;
use PNS\Admin\Tree;

abstract class AbstractTool extends Action
{
    /**
     * @var Tree
     */
    protected $parent;

    /**
     * @var string
     */
    protected $style = 'btn btn-sm btn-primary';

    /**
     * @param  Tree  $parent
     * @return void
     */
    public function setParent(Tree $parent)
    {
        $this->parent = $parent;
    }

    /**
     * @return void
     */
    public function setupHtmlAttributes()
    {
        $this->addHtmlClass($this->style);

        parent::setupHtmlAttributes();
    }
}
