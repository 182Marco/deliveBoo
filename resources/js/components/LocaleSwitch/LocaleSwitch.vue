<template>
    <!-- same classes as the Blade <x-locale-switch>: the styles live once, in resources/css/ui.css -->
    <div class="ui-lang" @keydown.esc="close(true)" @focusout="onFocusOut">
        <button
            ref="toggle"
            type="button"
            :class="open ? 'ui-lang-trigger ui-lang-trigger--open' : 'ui-lang-trigger'"
            aria-controls="locale-menu"
            :aria-expanded="open ? 'true' : 'false'"
            :aria-label="t('locale.current', { name: current.name })"
            @click="toggle"
        >
            <Flag :code="current.code" />
            <span>{{ current.short }}</span>
            <Icon
                name="chevron-down"
                :size="14"
                :class="open ? 'ui-lang-chevron ui-lang-chevron--open' : 'ui-lang-chevron'"
            />
        </button>

        <div v-if="open" id="locale-menu" class="ui-lang-panel">
            <p class="ui-lang-title">{{ t("locale.label") }}</p>
            <ul class="ui-lang-list" @keydown.down.prevent="move(1)" @keydown.up.prevent="move(-1)">
                <li v-for="item in locales" :key="item.code">
                    <button
                        ref="options"
                        type="button"
                        :class="item.code === current.code ? 'ui-lang-option ui-lang-option--current' : 'ui-lang-option'"
                        :lang="item.code"
                        :aria-current="item.code === current.code ? 'true' : null"
                        @click="choose(item.code)"
                    >
                        <Flag :code="item.code" :size="24" />
                        <span>{{ item.name }}</span>
                        <span class="ui-lang-short">{{ item.short }}</span>
                        <Icon v-if="item.code === current.code" name="check" :size="16" class="ui-lang-check" />
                    </button>
                </li>
            </ul>
        </div>
    </div>
</template>

<script>
import { localeSwitchLogic } from "./LocaleSwitch.logic.js";

export default localeSwitchLogic;
</script>
