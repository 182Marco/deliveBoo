<template>
    <!-- guest: one login for every account (the role decides where it lands) -->
    <a v-if="!user" href="/login" class="account-login">
        <Icon name="user" :size="18" />
        {{ t("header.login") }}
    </a>

    <details v-else ref="menu" class="account">
        <summary ref="trigger" class="account-trigger" :aria-label="t('account.menu', { name: user.name })">
            <img
                v-if="showPhoto"
                class="account-photo"
                :src="user.avatar_url"
                alt=""
                width="34"
                height="34"
                referrerpolicy="no-referrer"
                @error="onPhotoError"
            />
            <Avatar v-else :seed="user.avatar_seed" :overrides="user.avatar_overrides" :size="34" />
            <span class="account-name">{{ user.name }}</span>
        </summary>
        <div class="account-panel">
            <p class="account-who">
                <span class="account-label">{{ t("account.signedIn") }}</span>
                <span class="account-full">{{ fullName }}</span>
                <span class="account-email">{{ user.email }}</span>
            </p>
            <a class="account-item" href="/account">
                <Icon name="user" :size="18" />
                {{ t("account.settings") }}
            </a>
            <a v-if="areaLink" class="account-item" :href="user.home">
                <Icon :name="areaLink.icon" :size="18" />
                {{ t(areaLink.text) }}
            </a>
            <button type="button" class="account-item" @click="onLogout">
                <Icon name="logout" :size="18" />
                {{ t("account.logout") }}
            </button>
        </div>
    </details>
</template>

<script>
import { accountMenuLogic } from "./AccountMenu.logic.js";

export default accountMenuLogic;
</script>

<style scoped src="./AccountMenu.css"></style>
