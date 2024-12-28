<div class="box box-solid">
    <div class="box-header with-border">
        <h3 class="box-title">Group Settings</h3>

        <div class="box-tools">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="box-body no-padding">
        <ul class="nav nav-pills nav-stacked">
            <?php foreach (\PNS\Admin\Extensions\Settings\Models\SettingGroup::all() as $item) : ?>
                <li class="<?php echo request()->get('group_id') == $item->id ? 'active' : ''; ?>"><a href="?group_id=<?php echo $item->id ?>"><i class="fa fa-inbox"></i> <?php echo $item->name ?>
                        <span class="label label-primary pull-right"><?php echo $item->settings->count() ?></span></a></li>
            <?php endforeach; ?>

            <li><a href="#" class="text text-light-blue"  data-toggle="modal" data-target="#group-setting-create-modal"><i class="fa fa-plus"></i> Add Group</a></li>
        </ul>
    </div>
    <!-- /.box-body -->
</div>

<div class="modal fade" id="group-setting-create-modal">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Setting Group</h4>
            </div>
            <form method="POST" action="<?php echo route('admin.settings.create-group') ?>">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Name:</label>
                                <input type="text" class="form-control" style="width: 100%" name="name"></input>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success btn-submit-create-group">Create</button>
                </div>
            </form>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>


<script >
    $(document).ready(function() {
        $('.btn-submit-create-group').click(function() {

        });
    });
</script>