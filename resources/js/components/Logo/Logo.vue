<template>
    <!-- same classes as the Blade <x-logo>: the styles live once, in resources/css/ui.css -->
    <span class="ui-logo">
        <img class="ui-logo-mark" :src="src" alt="" width="36" height="36" />
        <span class="ui-logo-word" :class="{ 'ui-logo-word--inverse': inverse }">Foodsy</span>
    </span>
</template>

<script>
import { logoLogic } from "./Logo.logic.js";

export default logoLogic;
</script>
