<template>
    <div class="search">
        <button
            ref="toggle"
            type="button"
            class="search-toggle"
            aria-label="Search restaurants"
            aria-controls="restaurant-search"
            :aria-expanded="open ? 'true' : 'false'"
            @click="openSearch"
        >
            <Icon name="search" :size="20" />
        </button>

        <Transition name="search-fade">
            <div v-if="open" class="search-backdrop" aria-hidden="true" @click="close()"></div>
        </Transition>

        <Transition name="search-drop">
            <div v-if="open" id="restaurant-search" class="search-panel" role="search" @keydown.esc="close()">
                <div class="search-field">
                    <div class="ui-input search-box">
                        <Icon name="search" :size="20" class="search-lens" />
                        <label for="restaurant-search-input" class="ui-sr-only">Search by restaurant</label>
                        <input
                            id="restaurant-search-input"
                            ref="input"
                            v-model="query"
                            class="search-input"
                            type="text"
                            inputmode="search"
                            enterkeyhint="go"
                            placeholder="Search by restaurant"
                            autocomplete="off"
                            spellcheck="false"
                            maxlength="60"
                            role="combobox"
                            aria-autocomplete="list"
                            aria-controls="restaurant-search-list"
                            :aria-expanded="showDropdown && results.length ? 'true' : 'false'"
                            :aria-activedescendant="activeId"
                            @input="onInput"
                            @keydown.down.prevent="move(1)"
                            @keydown.up.prevent="move(-1)"
                            @keydown.enter.prevent="chooseActive"
                        />
                        <span v-if="status === 'loading'" class="search-spinner" aria-hidden="true"></span>
                        <button type="button" class="search-close" aria-label="Close search" @click="close()">
                            <Icon name="close" :size="18" />
                        </button>
                    </div>

                    <Transition name="search-drop">
                        <div v-if="showDropdown" class="search-dropdown">
                            <div class="search-head">
                                <span class="ui-eyebrow">Restaurants</span>
                                <span class="search-count">{{ countLabel }}</span>
                            </div>

                            <ul
                                id="restaurant-search-list"
                                class="search-list"
                                role="listbox"
                                aria-label="Restaurants"
                            >
                                <li
                                    v-for="(rest, i) in results"
                                    :id="optionId(i)"
                                    :key="`search__id${rest.id}`"
                                    role="option"
                                    :aria-selected="i === active ? 'true' : 'false'"
                                    :class="i === active ? 'search-option search-option--active' : 'search-option'"
                                    @mousedown.prevent
                                    @mouseenter="active = i"
                                    @click="choose(rest)"
                                >
                                    <FoodImage class="search-thumb" :src="rest.img" aria-hidden="true" />
                                    <span class="search-text">
                                        <span class="search-name">
                                            <template v-for="(part, p) in highlight(rest.name, answeredQuery)" :key="p">
                                                <mark v-if="part.match" class="search-mark">{{ part.text }}</mark>
                                                <template v-else>{{ part.text }}</template>
                                            </template>
                                        </span>
                                        <span class="search-meta">{{ meta(rest) }}</span>
                                    </span>
                                    <span :class="rest.average ? 'search-rating' : 'search-rating search-rating--new'">
                                        <Icon v-if="rest.average" name="star" :size="14" filled class="search-star" />
                                        <span v-if="rest.average" class="ui-sr-only">Rated</span>
                                        {{ rating(rest.average) }}
                                    </span>
                                    <Icon
                                        name="arrow-right"
                                        :size="18"
                                        :class="i === active ? 'search-go search-go--on' : 'search-go'"
                                    />
                                </li>
                            </ul>

                            <div v-if="showSkeleton" class="search-skeleton" aria-hidden="true">
                                <span v-for="n in 3" :key="`sk${n}`" class="search-skeleton-row">
                                    <span class="search-skeleton-thumb"></span>
                                    <span class="search-skeleton-lines">
                                        <span class="search-skeleton-line"></span>
                                        <span class="search-skeleton-line search-skeleton-line--short"></span>
                                    </span>
                                </span>
                            </div>

                            <div v-else-if="status === 'done' && !results.length" class="search-empty">
                                <Icon name="utensils" :size="22" class="search-empty-icon" />
                                <p class="search-empty-title">No restaurants match “{{ answeredQuery }}”</p>
                                <p class="search-empty-text">Check the spelling or try a shorter name.</p>
                            </div>

                            <div v-else-if="status === 'error'" class="search-empty">
                                <p class="search-empty-title">Search is unavailable right now</p>
                                <p class="search-empty-text">Please try again in a moment.</p>
                            </div>

                            <p v-if="results.length" class="search-hint" aria-hidden="true">
                                <kbd class="search-key">↑</kbd><kbd class="search-key">↓</kbd> to move
                                <kbd class="search-key">Enter</kbd> to open
                                <kbd class="search-key">Esc</kbd> to close
                            </p>
                        </div>
                    </Transition>
                </div>
                <p class="ui-sr-only" aria-live="polite">{{ liveMessage }}</p>
            </div>
        </Transition>
    </div>
</template>

<script>
import { restaurantSearchLogic } from "./RestaurantSearch.logic.js";

export default restaurantSearchLogic;
</script>

<style scoped src="./RestaurantSearch.css"></style>
