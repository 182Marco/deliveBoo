@extends('layouts.app')

@section('title', 'Menu')

@section('content')
<div class="ui-container">
    <a class="adm-back" href="{{ route('admin.restaurants.index') }}"><x-icon name="arrow-left" size="18" /> Your restaurants</a>

    @if (session('deleted'))
        <div class="ui-notice ui-notice--success adm-flash" role="status">
            <x-icon name="check" /> <p><strong>{{ session('deleted') }}</strong> has been removed from the menu.</p>
        </div>
    @endif

    <div class="adm-page-head">
        <div class="adm-page-intro">
            <p class="ui-eyebrow">Menu</p>
            <h1 class="ui-title">Your dishes</h1>
            <p class="ui-muted">What guests see and order on Foodsy.</p>
        </div>
        <a class="ui-btn ui-btn--primary" href="{{ route('admin.plates.create', $slug) }}"><x-icon name="plus" /> New dish</a>
    </div>

    @if ($res_plates->isEmpty())
        <div class="ui-empty">
            <img class="ui-empty-mark" src="{{ asset('images/foodsy-mark.svg') }}" alt="">
            <p class="ui-subtitle">The menu is empty</p>
            <p class="ui-muted">Add your first dish so guests can order it.</p>
        </div>
    @else
        <div class="ui-table-wrap">
            <table class="ui-table">
                <thead>
                    <tr>
                        <th class="ui-th" scope="col">Dish</th>
                        <th class="ui-th" scope="col">Ingredients</th>
                        <th class="ui-th" scope="col">Status</th>
                        <th class="ui-th ui-th--num" scope="col">Price</th>
                        <th class="ui-th ui-th--num" scope="col"><span class="ui-sr-only">Actions</span></th>
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
                                    <span class="ui-badge ui-badge--basil">Available</span>
                                @else
                                    <span class="ui-badge ui-badge--tomato">Unavailable</span>
                                @endif
                            </td>
                            <td class="ui-td ui-td--num ui-td--strong">€{{ number_format($res_plate->price, 2) }}</td>
                            <td class="ui-td ui-td--actions">
                                <a class="ui-btn ui-btn--ghost ui-btn--icon" href="{{ route('admin.plates.show', $res_plate->slug) }}" aria-label="View {{ $res_plate->name }}" title="View"><x-icon name="eye" size="18" /></a>
                                <a class="ui-btn ui-btn--ghost ui-btn--icon" href="{{ route('admin.plates.edit', $res_plate->slug) }}" aria-label="Edit {{ $res_plate->name }}" title="Edit"><x-icon name="edit" size="18" /></a>
                                <form class="delete-post-form adm-inline-form" action="{{ route('admin.plate.delete', $res_plate->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button class="ui-btn ui-btn--ghost ui-btn--icon" type="submit" aria-label="Delete {{ $res_plate->name }}" title="Delete"><x-icon name="trash" size="18" /></button>
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
