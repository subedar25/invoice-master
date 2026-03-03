@extends('masterapp.layouts.app')
@section('content')
<select id="master_type" class="form-control select2 mb-3">
  <option value="department">Department</option>
  <option value="status">Status</option>
  <option value="publication">Publication</option>
</select>

<table id="master-table" class="table table-bordered table-striped nowrap" width="100%">
  <thead>
    <tr>
      <th>Name</th>
      <th>Code</th>
      <th>Parent</th>
      <th>Sort</th>
      <th>Status</th>
      <th>Action</th>
    </tr>
  </thead>
</table>


<script>

let masterTable;

function initMasterTable(type) {

  if ($.fn.DataTable.isDataTable('#master-table')) {
    masterTable.destroy();
    $('#master-table').empty();
  }

  masterTable = $('#master-table').DataTable({
    processing: true,
    serverSide: true,
    responsive: false,
    scrollX: true,
    pageLength: 10,
    lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
    order: [[3, 'asc']], // sort_order default

    ajax: {
      url: '/master-data',
      type: 'GET',
      data: function (d) {
        d.type = type;
      }
    },

    columns: [
      { data: 'name', name: 'name' },
      { data: 'code', name: 'code' },
      {
        data: 'parent_name',
        name: 'parent_name',
        defaultContent: '-'
      },
      { data: 'sort_order', name: 'sort_order' },
      {
        data: 'is_active',
        name: 'is_active',
        orderable: false,
        searchable: false,
        render: function (data, type, row) {
          let checked = data ? 'checked' : '';
          return `
            <input type="checkbox"
                   class="toggle-status"
                   data-id="${row.id}"
                   ${checked}
            />
          `;
        }
      },
      {
        data: 'action',
        orderable: false,
        searchable: false,
        className: 'text-center',
        width: '120px'
      }
    ],

    columnDefs: [
      {
        targets: [2],
        visible: type === 'department'
      }
    ],

    drawCallback: function () {
      bindStatusToggle();
    }
  });
}

</script>
@endsection
