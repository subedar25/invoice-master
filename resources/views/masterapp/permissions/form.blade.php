<div class="form-group">
    <label for="name">Permission Name <span class="text-danger">*</span></label>
    <input type="text" class="form-control" id="name" name="name" value="{{ $permission->name ?? '' }}" required>
</div>

<div class="form-group">
    <label for="module_id">Assign to Module</label>
    <select name="module_id" id="module_id" class="form-control" required>
        <option value="">-- Select a Module --</option>
        @if(isset($modules))
            @foreach($modules as $id => $name)
                <option value="{{ $id }}" {{ isset($permission) && ($permission->module_id == $id) ? 'selected' : '' }}>{{ $name }}</option>
            @endforeach
        @endif
    </select>
</div>

<div class="form-group">
    <label for="display_name">Display Name <span class="text-danger">*</span></label>
    <input type="text" class="form-control" id="display_name" name="display_name" value="{{ $permission->display_name ?? '' }}" required>
</div>


<div class="mb-3">
    <label for="is_active" class="form-label">Active Status</label>
    <select id="is_active" name="is_active" class="form-control">
        <option value="1" @if(!isset($permission) || (isset($permission) && $permission->is_active)) selected @endif>Active</option>
        <option value="0" @if(isset($permission) && !$permission->is_active) selected @endif>Inactive</option>
    </select>
</div>
