@php($id = 'datatable-' . uniqid())
<table id="{{ $id }}" {!! $attributes !!} style="width:100%">
<thead>
    <tr>
        @foreach($headers as $header)
            <th>{{ $header }}</th>
        @endforeach
    </tr>
    </thead>
    <tbody>
    </tbody>

</table>

<script>
    $(function () {
        const datatable = $('#{{ $id }}').DataTable(
            {
                "lengthMenu": [[25, 50, 100, 250], [25, 50, 100, 250]],
                'paging': true,
                'lengthChange': true,
                'searching': true,
                'ordering': true,
                'info': true,
                'autoWidth': false,
                "processing": true,
                "serverSide": true,
                "searchDelay": 3000,
                "columns": JSON.parse('<?php echo json_encode($columns)?>'),
                "ajax": {
                    url: "<?php echo $ajaxSrc?>",
                    dataFilter: function(reps) {
                        reps = JSON.parse(reps);

                        return JSON.stringify({
                            ...reps,
                            data: reps.data.map((item) => {
                                // console.log(item);
                                return item;
                            }) 
                        });
                    }, 
                    data: function (d) {
                        // console.log(d);
                        let orderBy = null;
                        let search = null; 

                        const columns = d.columns ? d.columns : [];
                        if (d.order && d.order[0]) {
                            if (columns[d.order[0].column]) {
                                orderBy = {
                                    name: columns[d.order[0].column].name,
                                    dir: d.order[0].dir ? d.order[0].dir : 'desc'
                                };
                            }
                        }

                        if (d.search && d.search.value) {
                            search = d.search.value;
                        }

                        return {
                            ...d,
                            orderBy,
                            search
                        };
                    }
                }

            }
        );

        datatable.on( 'draw', function () {
            $('.grid-editable-dt').editable({name: $(this).data('name')});
        });
    });


</script>
