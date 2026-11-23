{{-- pagination of the partner area (Paginator::defaultView in AppServiceProvider) --}}
@if ($paginator->hasPages())
    <nav class="ui-pagination" aria-label="{{ __('pagination.label') }}">
        @if ($paginator->onFirstPage())
            <span class="ui-page ui-page--disabled" aria-hidden="true"><x-icon name="chevron-left" size="18" /></span>
        @else
            <a class="ui-page" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('pagination.previous') }}"><x-icon name="chevron-left" size="18" /></a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="ui-page ui-page--disabled" aria-hidden="true">{{ $element }}</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="ui-page ui-page--current" aria-current="page">{{ $page }}</span>
                    @else
                        <a class="ui-page" href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a class="ui-page" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('pagination.next') }}"><x-icon name="chevron-right" size="18" /></a>
        @else
            <span class="ui-page ui-page--disabled" aria-hidden="true"><x-icon name="chevron-right" size="18" /></span>
        @endif
    </nav>
@endif
