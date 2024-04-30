<template>
    <main>
        <BackgroundElements />

        <div class="wrapper">
            <div class="nav">
                <img class="nav__logo" src="@/assets/images/logo.svg" alt="PokeCrew logo" />
            </div>

            <div class="gallery">
                <img class="gallery__image gallery__image--left" src="@/assets/images/gallery/01.jpg" alt="PokeCrew group in the picture" />
                <img class="gallery__image gallery__image--mid" src="@/assets/images/gallery/02.jpg" alt="PokeCrew group in the picture" />
                <img class="gallery__image gallery__image--right" src="@/assets/images/gallery/03.jpg" alt="PokeCrew group in the picture" />
            </div>

            <div class="content">
                <p class="content__text content__text--left">
                    {{ $t("home.upload-message-start") }} <span>@PokeCrew</span> {{ $t("home.upload-message-end")}}
                </p>
                <div class="content__uploader">
                    <img src="@/assets/images/pokeball.png" alt="Pokeball upload button" @click="uploadInputRef.click()" />
                    <input ref="uploadInputRef" @change="uploadImage" type="file" accept=".png, .avif, .gif, .jpg, .jpeg, .webp, .heic" multiple hidden>
                </div>
                <p class="content__text content__text--right">
                    {{ $t("home.tag-message") }} <a href="https://www.instagram.com/pokecrewpl" aria-label="Instagram" target="_blank">Instagram</a>
                </p>
            </div>

            <div class="instagram-qr-code">
                <a href="https://www.instagram.com/pokecrewpl" aria-label="Instagram" target="_blank">
                    <img src="@/assets/images/instagram-qrcode.png" alt="Instagram QR Code"/>
                </a>
            </div>
        </div>

        <div class="watermark">
            <p>
                Developed by <a href="https://dream-speak.pl" target="_blank">DreamSpeak</a> | Designed by kacpulol
            </p>
        </div>
    </main>
    <Uploader v-if="uploader.isUploading" :files="uploader.filesToUpload" @close="clearUpload"/>
</template>

<script setup>
    import { ref, reactive } from 'vue';

    import BackgroundElements from '@/components/BackgroundElements.vue';
    import Uploader from '@/components/Uploader.vue';

    const uploadInputRef = ref(null);

    const uploader = reactive({
        isUploading: false,
        filesToUpload: null,
    });

    const uploadImage = (e) => {
        uploader.isUploading = true;
        uploader.filesToUpload = e.target.files;
    };

    const clearUpload = () => {
        uploader.isUploading = false;
        uploadInputRef.value.value = null;
    };
</script>

<style lang="scss" scoped>

    .wrapper {
        gap: 20px;
    }

    .nav {
        &__logo {
            height: 100%;
            width: 100%;
        }
    }

    .gallery {
        height: 45vw;
        max-height: 400px;
        width: 100%;

        &__image {
            aspect-ratio: 16/10;
            border-radius: 5px;
            box-shadow: 0 0 15px rgba(0, 0, 0, .3);
            position: absolute;

            &--left {
                bottom: 10px;
                filter: blur(1px);
                left: 0;
                max-width: 48%;
                z-index: 20;
            }

            &--mid {
                left: 0;
                margin-left: auto;
                margin-right: auto;
                max-width: 70%;
                right: 0;
                top: 0;
                z-index: 30;
            }

            &--right {
                bottom: 0;
                filter: blur(1px);
                max-width: 48%;
                right: 0;
                z-index: 10;
            }
        }
    }

    .content {
        width: 100%;

        &__text {
            font-size: 1.4rem;
            font-weight: bold;

            &--left {
                text-align: left;
            }

            &--right {
                text-align: right;
            }

            span, a {
                color: $--color-primary;
                font-weight: bold;
            }
        }

        &__uploader {
            display: flex;
            justify-content: center;
            margin: 10px 0px;

            img {
                cursor: pointer;
                filter: drop-shadow(2px 4px 6px black);
                height: 75px;
                transition: all .25s ease-in-out;
                width: 75px;

                &:hover {
                    transform: scale(1.1);
                }
            }
        }
    }

    .watermark {
        bottom: 0;
        font-size: 0.9rem;
        padding: 5px;
        position: absolute;

        a {
            color: inherit;
            text-decoration: none;
        }
    }
</style>