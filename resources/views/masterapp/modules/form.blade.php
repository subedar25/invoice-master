<div class="mb-3">
    <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
    <input type="text" class="form-control" id="name" name="name" value="{{ $module->name ?? '' }}" required>
    <!-- The invalid-feedback div will be added by JavaScript if there's an error -->
</div>

<div class="mb-3">
    <label for="slug" class="form-label">Slug</label>
    <input type="text" class="form-control" id="slug" name="slug" value="{{ $module->slug ?? '' }}" required>
    <!-- The invalid-feedback div will be added by JavaScript if there's an error -->
</div>

<div class="mb-3">
    <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
    <select class="form-control" id="type" name="type" required>
        @php($moduleType = old('type', $module->type ?? 'public'))
        <option value="system" {{ $moduleType === 'system' ? 'selected' : '' }}>System</option>
        <option value="public" {{ $moduleType === 'public' ? 'selected' : '' }}>Public</option>
    </select>
</div>

<div class="mb-3">
    <label for="is_active" class="form-label">Active <span class="text-danger">*</span></label>
    @php($moduleIsActive = (string) old('is_active', isset($module) ? (int) ($module->is_active ?? 1) : 1))
    <select class="form-control" id="is_active" name="is_active" required>
        <option value="1" {{ $moduleIsActive === '1' ? 'selected' : '' }}>Active</option>
        <option value="0" {{ $moduleIsActive === '0' ? 'selected' : '' }}>Inactive</option>
    </select>
</div>
