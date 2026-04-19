{{--
  Invoice list: pager only (summary text lives in card footer .dataTables_info).
  Fixes Livewire default bootstrap view hiding numbered links behind d-none / d-sm-flex
  when Bootstrap responsive utilities do not apply as expected (BS4 + AdminLTE).
--}}
@php
if (! isset($scrollTo)) {
    $scrollTo = 'body';
}

$scrollIntoViewJsSnippet = ($scrollTo !== false)
    ? <<<JS
       (\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView()
    JS
    : '';
@endphp

<div>
    {{-- Laravel hides links when hasPages() is false (single page). Show disabled ‹ 1 › like DataTables. --}}
    @if ($paginator->hasPages())
        <nav class="d-flex align-items-center justify-content-end flex-wrap" aria-label="Pagination">
            <ul class="pagination mb-0">
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                        <span class="page-link" aria-hidden="true">&lsaquo;</span>
                    </li>
                @else
                    <li class="page-item">
                        <button type="button" dusk="previousPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}" class="page-link" wire:click="previousPage('{{ $paginator->getPageName() }}')" @if($scrollIntoViewJsSnippet !== '') x-on:click="{{ $scrollIntoViewJsSnippet }}" @endif wire:loading.attr="disabled" aria-label="@lang('pagination.previous')">&lsaquo;</button>
                    </li>
                @endif

                @foreach ($elements as $element)
                    @if (is_string($element))
                        <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ $element }}</span></li>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <li class="page-item active" wire:key="paginator-{{ $paginator->getPageName() }}-page-{{ $page }}" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                            @else
                                <li class="page-item" wire:key="paginator-{{ $paginator->getPageName() }}-page-{{ $page }}"><button type="button" class="page-link" wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')" @if($scrollIntoViewJsSnippet !== '') x-on:click="{{ $scrollIntoViewJsSnippet }}" @endif>{{ $page }}</button></li>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                @if ($paginator->hasMorePages())
                    <li class="page-item">
                        <button type="button" dusk="nextPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}" class="page-link" wire:click="nextPage('{{ $paginator->getPageName() }}')" @if($scrollIntoViewJsSnippet !== '') x-on:click="{{ $scrollIntoViewJsSnippet }}" @endif wire:loading.attr="disabled" aria-label="@lang('pagination.next')">&rsaquo;</button>
                    </li>
                @else
                    <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                        <span class="page-link" aria-hidden="true">&rsaquo;</span>
                    </li>
                @endif
            </ul>
        </nav>
    @elseif ($paginator->total() > 0)
        <nav class="d-flex align-items-center justify-content-end flex-wrap" aria-label="Pagination">
            <ul class="pagination mb-0">
                <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                    <span class="page-link">&lsaquo;</span>
                </li>
                <li class="page-item active" aria-current="page">
                    <span class="page-link">{{ $paginator->currentPage() }}</span>
                </li>
                <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                    <span class="page-link">&rsaquo;</span>
                </li>
            </ul>
        </nav>
    @endif
</div>
