{{-- Sortable column header of the Tables tab: a link that keeps every filter, flips the direction
     on the active column and resets the page. aria-sort tells screen readers the current order. --}}
@props(['key', 'label', 'sort', 'dir', 'num' => false])
@php($active = $sort === $key)
@php($next = $active && $dir === 'desc' ? 'asc' : 'desc')
<th @class(['ui-th', 'ui-th--num' => $num]) scope="col" @if ($active) aria-sort="{{ $dir === 'asc' ? 'ascending' : 'descending' }}" @endif>
    <a
        @class(['plat-sort', 'plat-sort--active' => $active])
        href="{{ request()->fullUrlWithQuery(['sort' => $key, 'dir' => $next, 'page' => null]) }}"
        title="{{ __('platform.tables.sortBy', ['column' => $label]) }}"
    >
        {{ $label }}
        <span class="plat-sort-arrow" aria-hidden="true">{{ $active ? ($dir === 'asc' ? '↑' : '↓') : '↕' }}</span>
    </a>
</th>
