@extends('masterapp.layouts.app')
@section('content')

    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">Settings</h1>
                </div>
                <div class="col-sm-6">
                    <button type="button" class="btn btn-primary btn-block add-new" data-toggle="modal" data-target="#addSettingModal">
                        <i class="fa fa-plus"></i> Add Setting
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            @if(session('success'))
                                <div class="alert alert-success">{{ session('success') }}</div>
                            @endif
                            @if($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            <table id="settingsTable" class="table table-bordered table-hover">
                                <thead>
                                <tr>
                                    <th>Key</th>
                                    <th>Value</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($settings as $setting)
                                    <tr>
                                        <td>{{ $setting->key }}</td>
                                        <td>{{ $setting->value }}</td>
                                        <td>
                                            <div class="action-div">
                                                <button type="button" class="btn btn-sm btn-info action-icon" 
                                                        data-toggle="modal" 
                                                        data-target="#editSettingModal" 
                                                        data-id="{{ $setting->id }}"
                                                        data-key="{{ $setting->key }}"
                                                        data-value="{{ $setting->value }}"
                                                        title="Edit setting">
                                                    <i class="fa fa-edit"></i>
                                                </button>
                                                
                                                <form action="{{ route('masterapp.settings.destroy', $setting->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger action-icon" title="Delete setting">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </div>
    </section>

    <!-- Add Modal -->
    <div class="modal fade" id="addSettingModal" tabindex="-1" role="dialog" aria-labelledby="addSettingModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form action="{{ route('masterapp.settings.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addSettingModalLabel">Add Setting</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="key">Key</label>
                            <input type="text" class="form-control" name="key" required>
                        </div>
                        <div class="form-group">
                            <label for="value">Value</label>
                            <textarea class="form-control" name="value" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editSettingModal" tabindex="-1" role="dialog" aria-labelledby="editSettingModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="editSettingForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editSettingModalLabel">Edit Setting</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="edit_key">Key</label>
                            <input type="text" class="form-control" id="edit_key" name="key" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_value">Value</label>
                            <textarea class="form-control" id="edit_value" name="value" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        $(function () {
             $('#settingsTable').DataTable({
                "pageLength": 10,
                responsive: true,
                autoWidth: false
             });

             $('#editSettingModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var id = button.data('id');
                var key = button.data('key');
                var value = button.data('value');
                var modal = $(this);

                modal.find('#edit_key').val(key);
                modal.find('#edit_value').val(value);
                
                // Update form action
                var updateUrl = "{{ route('masterapp.settings.update', ':id') }}";
                updateUrl = updateUrl.replace(':id', id);
                modal.find('#editSettingForm').attr('action', updateUrl);
             });
        });
    </script>
@endsection
