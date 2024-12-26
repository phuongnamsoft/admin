<?php

namespace PNS\Admin\Extensions\Settings\Controllers;

use PNS\Admin\Layout\Content;
use Illuminate\Http\Request;
use PNS\Admin\Form;
use PNS\Admin\Layout\Row;
use PNS\Admin\Layout\Column;
use PNS\Admin\Grid;
use PNS\Admin\Extensions\Settings\Models\Setting;
use PNS\Admin\Extensions\Settings\Models\SettingGroup;
use Illuminate\Support\Facades\Storage;
use Illuminate\Routing\Controller;

class SettingController extends Controller
{

    protected $model = Setting::class;

    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Settings';

    /**
     * Make a grid builder.
     *
     * @return Content
     */
    public function index()
    {
        $grid = new Grid(new Setting);
        $grid->model()
            ->orderBy('sort', 'asc')
            ->orderBy('id', 'desc');

        if (request()->has('group_id')) {
            $grid->model()->where('group_id', (int) request()->get('group_id'));
        } else {
            $grid->model()->where('group_id', 1);
        }

        $grid->paginate(50);
        // $grid->filter(function (Grid\Filter $filter) {
        //     $filter->equal('group_id', 'Group')->select(SettingGroup::getListKV());
        // });
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableView();
            //            $actions->disableEdit();
        });

        $grid->column('id', __('ID'))->sortable();
        $grid->column('label', __('Label'));
        $grid->column('name', __('Name'))->sortable()->myEditable();

        $grid->column('value', __('Value'))->display(function ($value, $col) {
            /** @var Setting $this */
            if ($this->cast_type == Setting::CAST_TYPE_BOOLEAN) {
                return $col->switchToggle([
                    '1' => ['value' => 1, 'text' => 'open', 'color' => 'success'],
                    '0' => ['value' => 0, 'text' => 'close', 'color' => 'danger'],
                ]);
            } else if ($this->cast_type == Setting::CAST_TYPE_IMAGE) {
                return '<img style="width: 50px;" src=' . Storage::disk('upload')->url($value) . '/>';
            } else {
                return $col->myEditable('textarea');
            }
        });

        $grid->column('group_id', __('Group'))->myEditable('select', SettingGroup::getListKV());

        $grid->column('cast_type', __('Cast Type'))->myEditable('select', Setting::CASTS);

        $grid->column('sort', __('Sort'))->myEditable();

        $grid->column('status')->switchToggle([
            '1' => ['value' => 1, 'text' => 'open', 'color' => 'success'],
            '0' => ['value' => 0, 'text' => 'close', 'color' => 'danger'],
        ])->sortable();

        $grid->column('created_at', __('Created at'))->sortable();
        $grid->column('updated_at', __('Updated at'))->hide()->sortable();

        return (new Content)->title($this->title)
            // ->description('Danh sách')
            ->row(function (Row $row) use ($grid) {

                $row->column(2, function (Column $column) {
                    $column->append(view('laravel-admin-settings::partials.menu'));
                });

                $row->column(10, function (Column $column) use ($grid) {
                    $column->append($grid);
                });
            });
    }

    public function delete(Request $request, $id)
    {
        try {
            Setting::deleteById($id);
            return ['status' => 1, 'message' => 'Delete item #' . $id . ' success!'];
        } catch (\Throwable $t) {
            return ['status' => 0, 'message' => 'Delete item #' . $id . ' failed!'];
        }
    }

    public function editInline(Request $request, $id)
    {
        try {
            $post = $request->except(['_token']);
            Setting::updateById([$post['name'] => $post['value']], $id);
            return ['status' => 1, 'message' => 'Change ' . $post['name'] . ' success'];
        } catch (\Throwable $t) {
            return ['status' => 0, 'message' => 'Edit error!'];
        }
    }

    public function edit($id)
    {
        return (new Content)->title('Settings')
            ->body($this->form($id)->edit($id));
    }

    public function create()
    {
        return (new Content)->title('Settings')
            ->body($this->form());
    }

    public function processData($id = NULL)
    {
        if ($id !== NULL) {
            return $this->form($id)->update($id);
        } else {
            return $this->form()->store();
        }
    }

    protected function form($id = null)
    {
        $form = new Form(new Setting);

        $form->tools(function (Form\Tools $tool) {
            $tool->disableView();
            $tool->disableDelete();
        });

        $item = Setting::find($id);

        $form->text('label', __('Label'));
        $form->text('name', __('Name'))->rules('required');

        if ($item && $item->cast_type == Setting::CAST_TYPE_IMAGE) {
            $form->image('value', __('Value'))->rules('image');
        } elseif ($item && ($item->cast_type == Setting::CAST_TYPE_JSON || $item->cast_type == Setting::CAST_TYPE_JSON_ASSOC)) {
            $form->json('value', __('Value'));
        } elseif ($item && $item->cast_type == Setting::CAST_TYPE_HTML) {
            $form->ckeditor('value', __('Value'));
        } elseif ($item && $item->cast_type == Setting::CAST_TYPE_CSS) {
            $form->css('value', __('Value'));
        } elseif ($item && $item->cast_type == Setting::CAST_TYPE_JS) {
            $form->js('value', __('Value'));
        } else {
            $form->textarea('value', __('Value'));
        }

        $form->select('group_id', __('Group'))->options(SettingGroup::getListKV())->rules('required');

        $form->select('cast_type', __('Cast Type'))->options(Setting::CASTS)->rules('required');

        $form->number('sort', __('Sort'))->default(0);

        $form->radio('status', __('Status'))->options([0 => ' Inactive', 1 => ' Active'])->rules('required');

        return $form;
    }
}
