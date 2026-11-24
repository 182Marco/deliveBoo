@extends('layouts.app')

@section('title', __('partner.reviews.title').' · '.$restaurant->name)

@php
    $r = fn (string $key, array $replace = []) => __("partner.reviews.{$key}", $replace);
    $max = max(1, max($distribution));
@endphp

@section('content')
<div class="ui-container">
    <a class="adm-back" href="{{ route('admin.restaurants.show', $restaurant->id) }}"><x-icon name="arrow-left" size="18" /> {{ $restaurant->name }}</a>

    <div class="adm-page-head">
        <div class="adm-page-intro">
            <p class="ui-eyebrow">{{ $restaurant->name }}</p>
            <h1 class="ui-title">{{ $r('title') }}</h1>
            <p class="ui-muted">{{ $r('lead') }}</p>
        </div>
    </div>

    @if (session('reviewReplied'))
        <div class="ui-notice ui-notice--success adm-flash" role="status">{{ $r('replied') }}</div>
    @elseif (session('reviewReplyDeleted'))
        <div class="ui-notice ui-notice--success adm-flash" role="status">{{ $r('replyDeleted') }}</div>
    @endif

    {{-- summary: stored average and count (counted reviews), how many reviews per star --}}
    <section class="adm-review-summary" aria-labelledby="summary-title">
        <h2 id="summary-title" class="ui-sr-only">{{ $r('rating') }}</h2>
        <div class="adm-review-score">
            @if ($restaurant->rating_count > 0)
                <span class="adm-review-average">{{ number_format($restaurant->rating_avg, 1) }}</span>
                <x-stars :value="$restaurant->rating_avg" :size="20" />
                <span class="adm-meta">{{ trans_choice('partner.reviews.count', $restaurant->rating_count, ['count' => $restaurant->rating_count]) }}</span>
            @else
                <span class="adm-review-average">–</span>
                <span class="adm-meta">{{ $r('none') }}</span>
            @endif
        </div>
        <ul class="adm-dist">
            @foreach ($distribution as $n => $count)
                <li class="adm-dist-row">
                    <span class="adm-dist-label">{{ $n }} <x-icon name="star" size="14" filled /></span>
                    <span class="adm-dist-track" aria-hidden="true"><span class="adm-dist-fill" style="transform: scaleX({{ round($count / $max, 4) }})"></span></span>
                    <span class="adm-dist-count">{{ $count }}</span>
                </li>
            @endforeach
        </ul>
        <p class="adm-review-note">
            <strong>{{ trans_choice('partner.reviews.unanswered', $unansweredCount, ['count' => $unansweredCount]) }}</strong>
            <span class="adm-meta">{{ $r('howCounted') }}</span>
        </p>
    </section>

    <form class="adm-review-filters" method="GET" action="{{ route('admin.reviews.index', $restaurant->slug) }}">
        <div class="ui-field">
            <label class="ui-label" for="f-stars">{{ $r('filterStars') }}</label>
            <select class="ui-input" id="f-stars" name="stars">
                <option value="">{{ $r('allStars') }}</option>
                @foreach (range(5, 1) as $n)
                    <option value="{{ $n }}" @selected($stars === $n)>{{ trans_choice('partner.reviews.starsCount', $n, ['count' => $n]) }}</option>
                @endforeach
            </select>
        </div>
        <label class="ui-check">
            <input class="ui-check-input" type="checkbox" name="unanswered" value="1" @checked($unanswered)>
            {{ $r('onlyUnanswered') }}
        </label>
        <button type="submit" class="ui-btn ui-btn--outline">{{ $r('filter') }}</button>
        @if ($stars || $unanswered)
            <a class="ui-btn ui-btn--ghost" href="{{ route('admin.reviews.index', $restaurant->slug) }}">{{ $r('clear') }}</a>
        @endif
    </form>

    @if ($reviews->isEmpty())
        <div class="ui-empty">
            <img class="ui-empty-mark" src="{{ asset('images/foodsy-mark.svg') }}" alt="">
            <p class="ui-subtitle">{{ $r('emptyTitle') }}</p>
            <p class="ui-muted">{{ $r('emptyText') }}</p>
        </div>
    @else
        <ul class="adm-reviews">
            @foreach ($reviews as $review)
                @php($editing = (int) old('review_id') === $review->id)
                <li id="review-{{ $review->id }}" class="adm-review">
                    <div class="adm-review-top">
                        <div class="adm-review-who">
                            <h2 class="adm-review-author">{{ $review->user ? $review->user->name.' '.mb_substr((string) $review->user->last_name, 0, 1).'.' : $r('anonymous') }}</h2>
                            <p class="adm-meta">
                                {{ $review->created_at?->translatedFormat('j M Y') }}
                                @if ($review->order_id)
                                    · <a class="ui-link" href="{{ route('admin.orders.show', $review->order_id) }}">{{ $r('order', ['id' => $review->order_id]) }}</a>
                                @endif
                            </p>
                        </div>
                        <x-stars :value="$review->rating" :size="18" />
                    </div>

                    @if ($review->isHidden())
                        <p><span class="ui-badge ui-badge--tomato">{{ $r('hiddenBadge') }}</span></p>
                    @elseif (! $review->is_current)
                        <p><span class="ui-badge">{{ $r('notCounted') }}</span></p>
                    @endif

                    @if ($review->comment)
                        <p class="adm-review-text">{{ $review->comment }}</p>
                    @else
                        <p class="adm-meta">{{ $r('starsOnly') }}</p>
                    @endif

                    @if ($review->reply)
                        <div class="adm-reply">
                            <p class="adm-reply-label">{{ $r('yourReply') }} · {{ $review->replied_at?->translatedFormat('j M Y') }}</p>
                            <p class="adm-reply-text">{{ $review->reply }}</p>
                        </div>
                    @endif

                    @if (! $review->isHidden() && ($review->comment || $review->reply))
                        <details class="adm-reply-form" @if ($editing) open @endif>
                            <summary class="ui-btn ui-btn--outline ui-btn--sm adm-reply-toggle">
                                <x-icon :name="$review->reply ? 'edit' : 'mail'" size="16" /> {{ $review->reply ? $r('editReply') : $r('reply') }}
                            </summary>
                            <form class="adm-form adm-reply-panel" method="POST" action="{{ route('admin.reviews.reply', $review->id) }}">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="review_id" value="{{ $review->id }}">
                                <div class="ui-field">
                                    <label class="ui-label" for="reply-{{ $review->id }}">{{ $r('replyLabel') }}</label>
                                    <textarea id="reply-{{ $review->id }}" name="reply" rows="4" maxlength="{{ config('reviews.comment_max') }}" required
                                        @class(['ui-input', 'ui-textarea', 'ui-input--error' => $editing && $errors->has('reply')])
                                        @if ($editing && $errors->has('reply')) aria-invalid="true" aria-describedby="reply-{{ $review->id }}-error" @endif
                                    >{{ $editing ? old('reply') : $review->reply }}</textarea>
                                    <p class="ui-hint">{{ $r('replyHint') }}</p>
                                    @if ($editing && $errors->has('reply'))
                                        <p id="reply-{{ $review->id }}-error" class="ui-field-error" role="alert">{{ $errors->first('reply') }}</p>
                                    @endif
                                </div>
                                <div class="adm-form-foot">
                                    <button type="submit" class="ui-btn ui-btn--dark ui-btn--sm">{{ $r('publishReply') }}</button>
                                    @if ($review->reply)
                                        <button type="submit" form="delete-reply-{{ $review->id }}" class="ui-btn ui-btn--ghost ui-btn--sm"><x-icon name="trash" size="16" /> {{ $r('deleteReply') }}</button>
                                    @endif
                                </div>
                            </form>
                        </details>
                    @endif

                    @if ($review->reply)
                        {{-- submitted by "Delete reply" inside the reply form (forms cannot be nested) --}}
                        <form id="delete-reply-{{ $review->id }}" method="POST" action="{{ route('admin.reviews.reply.delete', $review->id) }}" hidden>
                            @csrf
                            @method('DELETE')
                        </form>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif

    {{ $reviews->links() }}
</div>
@endsection
