<?php

namespace PNS\Admin\Controllers;

use PNS\Admin\Auth\Database\Extension;
use PNS\Admin\Form;
use PNS\Admin\Grid;
use PNS\Admin\Show;

class ExtensionController extends AdminController
{
    /**
     * {@inheritdoc}
     */
    protected function title()
    {
        return trans('admin.extensions');
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $extensionModel = (string) config('admin.database.extensions_model');

        $grid = new Grid(new $extensionModel());

        $grid->column('id', 'ID')->sortable();
        $grid->column('slug', trans('admin.slug'));
        $grid->column('name', trans('admin.name'));

        $grid->column('enabled', 'Enabled')->display(function ($enabled, $col) {
            if ($this->install_status === Extension::INSTALL_STATUS_INSTALLED) {
                return $col->switch(
                    [
                        'on'  => ['value' => 1, 'text' => 'ON', 'color' => 'success'],
                        'off' => ['value' => 0, 'text' => 'OFF', 'color' => 'danger'],
                    ]
                );
            }

            return '<span class="label label-warning">Not installed</span>';
        });

        $grid->column('created_at', trans('admin.created_at'));
        $grid->column('updated_at', trans('admin.updated_at'));

        $grid->actions(function (Grid\Displayers\Actions $actions) {
            if ($actions->row->slug == 'administrator') {
                $actions->disableDelete();
            }

            if ($actions->row->canInstall()) {
                $actions->prepend('<a href="'.route('admin.auth.extensions.install', ['id' => $actions->row->id]).'"><i class="fa fa-download"></i></a>');
            }
        });

        $grid->tools(function (Grid\Tools $tools) {
            $tools->batch(function (Grid\Tools\BatchActions $actions) {
                $actions->disableDelete();
            });
        });

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        $extensionModel = config('admin.database.extensions_model');

        $show = new Show($extensionModel::findOrFail($id));

        $show->field('id', 'ID');
        $show->field('slug', trans('admin.slug'));
        $show->field('name', trans('admin.name'));
        $show->field('permissions', trans('admin.permissions'))->as(function ($permission) {
            return $permission->pluck('name');
        })->label();
        $show->field('created_at', trans('admin.created_at'));
        $show->field('updated_at', trans('admin.updated_at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    public function form()
    {
        $extensionModel = config('admin.database.extensions_model');

        $form = new Form(new $extensionModel());

        $form->display('id', 'ID');

        $form->text('slug', trans('admin.slug'))->rules('required');
        $form->text('name', trans('admin.name'))->rules('required');

        $form->json('config', 'Config');

        $form->switch('enabled', 'Enabled');

        $form->display('created_at', trans('admin.created_at'));
        $form->display('updated_at', trans('admin.updated_at'));

        return $form;
    }

    public function install($id)
    {
        $extensionModel = config('admin.database.extensions_model');

        /** @var Extension $extension */
        $extension = $extensionModel::findOrFail($id);

        if ($extension->install_status == Extension::INSTALL_STATUS_INSTALLED) {
            admin_error("Extension [{$extension->name}] already installed");
            return back()->with('error', 'Extension already installed');
        }

        if (!$extension->canInstall()) {
            admin_error("Extension [{$extension->name}] can not be installed");
            return back()->with('error', 'Extension can not be installed');
        }

        if (!$extension->install()) {
            admin_error("Extension [{$extension->name}] install failed");
            return back()->with('error', 'Extension install failed');
        }

        admin_success("Extension [{$extension->name}] installed successfully");
        return back();
    }
}
