<template>
    <div class="background-waves">
        <div class="background-wave top-wave">
            <svg width="240" height="455" viewBox="0 0 240 455" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M240 0C1.52588e-05 153.367 240 316.936 1.52588e-05 455L1.52588e-05 0H240Z" fill="#FAD307"/>
            </svg>
        </div>
        <div class="background-wave bottom-wave">
            <svg width="479" height="709" viewBox="0 0 479 709" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0 708.471C479 469.86 0 215.378 479 0.575562V708.471H0Z" fill="#FAD307"/>
            </svg>
        </div>
    </div>

    <div class="background-pikachu-face">
        <img src="@/assets/images/pikachu-icon.svg" alt="Pikachu face" />
    </div>
</template>

<style lang="scss" scoped>
    .background-wave {
        line-height: 0;
        max-width: 100%;
        overflow: hidden;
        position: fixed;

        &.bottom-wave {
            bottom: 0;
            right: 0;
        }

        &.top-wave {
            left: 0;
            top: 0;
        }
    }

    .background-pikachu-face {
        bottom: 0;
        line-height: 0;
        position: absolute;
        right: 0;
    }

</style>