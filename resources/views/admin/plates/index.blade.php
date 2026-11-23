@extends('layouts.app')

@section('title', __('partner.common.menu'))

@section('content')
<div class="ui-container">
    <a class="adm-back" href="{{ route('admin.restaurants.index') }}"><x-icon name="arrow-left" size="18" /> {{ __('partner.common.yourRestaurants') }}</a>

    @if (session('deleted'))
        <div class="ui-notice ui-notice--success adm-flash" role="status">
            <x-icon name="check" /> <p>{!! __('partner.plates.removed', ['name' => '<strong>' . e(session('deleted')) . '</strong>']) !!}</p>
        </div>
    @endif

    <div class="adm-page-head">
        <div class="adm-page-intro">
            <p class="ui-eyebrow">{{ __('partner.common.menu') }}</p>
            <h1 class="ui-title">{{ __('partner.plates.heading') }}</h1>
            <p class="ui-muted">{{ __('partner.plates.lead') }}</p>
        </div>
        <a class="ui-btn ui-btn--primary" href="{{ route('admin.plates.create', $slug) }}"><x-icon name="plus" /> {{ __('partner.plates.new') }}</a>
    </div>

    @if ($res_plates->isEmpty())
        <div class="ui-empty">
            <img class="ui-empty-mark" src="{{ asset('images/foodsy-mark.svg') }}" alt="">
            <p class="ui-subtitle">{{ __('partner.plates.emptyTitle') }}</p>
            <p class="ui-muted">{{ __('partner.plates.emptyText') }}</p>
        </div>
    @else
        <div class="ui-table-wrap">
            <table class="ui-table">
                <thead>
                    <tr>
                        <th class="ui-th" scope="col">{{ __('partner.plates.dish') }}</th>
                        <th class="ui-th" scope="col">{{ __('partner.common.ingredients') }}</th>
                        <th class="ui-th" scope="col">{{ __('partner.common.status') }}</th>
                        <th class="ui-th ui-th--num" scope="col">{{ __('partner.common.price') }}</th>
                        <th class="ui-th ui-th--num" scope="col"><span class="ui-sr-only">{{ __('partner.common.actions') }}</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($res_plates as $res_plate)
                        <tr>
                            <td class="ui-td">
                                <p class="adm-cell-title">{{ str_replace('_', ' ', $res_plate->name) }}</p>
                                <p class="adm-cell-sub">{{ $res_plate->description }}</p>
                            </td>
                            <td class="ui-td"><p class="adm-cell-sub">{{ $res_plate->ingredients }}</p></td>
                            <td class="ui-td">
                                @if ($res_plate->visible == 1)
                                    <span class="ui-badge ui-badge--basil">{{ __('partner.common.available') }}</span>
                                @else
                                    <span class="ui-badge ui-badge--tomato">{{ __('partner.common.unavailable') }}</span>
                                @endif
                            </td>
                            <td class="ui-td ui-td--num ui-td--strong">€{{ number_format($res_plate->price, 2) }}</td>
                            <td class="ui-td ui-td--actions">
                                <a class="ui-btn ui-btn--ghost ui-btn--icon" href="{{ route('admin.plates.show', $res_plate->slug) }}" aria-label="{{ __('partner.common.viewItem', ['name' => $res_plate->name]) }}" title="{{ __('partner.common.view') }}"><x-icon name="eye" size="18" /></a>
                                <a class="ui-btn ui-btn--ghost ui-btn--icon" href="{{ route('admin.plates.edit', $res_plate->slug) }}" aria-label="{{ __('partner.common.editItem', ['name' => $res_plate->name]) }}" title="{{ __('partner.common.edit') }}"><x-icon name="edit" size="18" /></a>
                                <form class="delete-post-form adm-inline-form" action="{{ route('admin.plate.delete', $res_plate->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button class="ui-btn ui-btn--ghost ui-btn--icon" type="submit" aria-label="{{ __('partner.common.deleteItem', ['name' => $res_plate->name]) }}" title="{{ __('partner.common.delete') }}"><x-icon name="trash" size="18" /></button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{ $res_plates->links() }}
</div>
@include('layouts/deleteConfirm')
@endsection
