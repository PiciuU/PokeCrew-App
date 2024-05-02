<template>
    <RouterView />
</template>

<script setup>
    import { onMounted } from 'vue';
    import { RouterView } from 'vue-router';

    import { useI18n } from "vue-i18n";

    const i18n = useI18n({ useScope: "global" });

    onMounted(() => {
        if (typeof window !== 'undefined') {
            i18n.locale.value = navigator.language;
        }
    });

</script>