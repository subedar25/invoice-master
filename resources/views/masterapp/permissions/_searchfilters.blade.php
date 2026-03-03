<div class="filter-wrapper" id="filterWrapper" style="display: none;">
    <a href="#" class="close-filter-btn" id="toggleFilterclear" title="Clear Filters & Close">
        &times;
    </a>
    <form id="filterForm">
        <div class="row align-items-end">
            <div class="col-md-3">
                <label class="font-weight-bold">Filter By Module</label>
                <select id="filter_module_ui" class="form-control filter-input">
                    <option value="">All</option>
                    @if(isset($modules))
                        @foreach($modules as $module)
                            <option value="{{ $module->name }}">{{ $module->name }}</option>
                        @endforeach
                    @endif
                </select>
                <input type="hidden" id="filter_module" value="">
            </div>
            <div class="col-md-3">
                <button type="button" class="btn btn-primary" id="applyFilterBtn">
                    <i class="fa fa-filter"></i> Apply Filter
                </button>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-12 text-right">
                <button type="button" class="btn btn-link btn-sm text-secondary" id="clearFilterBtn">Clear All Filters</button>
            </div>
        </div>
    </form>
</div>
