<template>
    <main>
        <BackgroundElements />

        <div class="wrapper">
            <div class="nav">
                <img class="nav__logo" width="304" height="73" src="@/assets/images/logo.svg" alt="PokeCrew logo" />
            </div>

            <div class="gallery">
                <transition name="fade">
                    <img :key="`left-${currentSetIndex}`" class="gallery__image gallery__image--left" width="350" height="220" loading="lazy" :src="loadImageFromSet(sets[currentSetIndex], 'left')" alt="PokeCrew group in the picture" />
                </transition>
                <transition name="fade">
                    <img :key="`mid-${currentSetIndex}`" class="gallery__image gallery__image--mid" width="500" height="320" loading="lazy" :src="loadImageFromSet(sets[currentSetIndex], 'mid')" alt="PokeCrew group in the picture" />
                </transition>
                <transition name="fade">
                    <img :key="`right-${currentSetIndex}`" class="gallery__image gallery__image--right" width="350" height="220" loading="lazy" :src="loadImageFromSet(sets[currentSetIndex], 'right')" alt="PokeCrew group in the picture" />
                </transition>
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
                    <img width="190" height="227" src="@/assets/images/instagram-qrcode.png" alt="Instagram QR Code"/>
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
    import { ref, reactive, onMounted, onUnmounted } from 'vue';

    import BackgroundElements from '@/components/BackgroundElements.vue';
    import Uploader from '@/components/Uploader.vue';

    const sets = ['01', '02', '03'];
    let currentSetIndex = ref(Math.floor(Math.random() * sets.length));
    let intervalFuncId;
    const cachedSets = [];

    const uploadInputRef = ref(null);

    const uploader = reactive({
        isUploading: false,
        filesToUpload: null,
    });

    onMounted(() => {
        intervalFuncId = setInterval(changeImageSet, 15000);
    })

    onUnmounted(() => {
        clearInterval(intervalFuncId);
    })

    const changeImageSet = () => {
        currentSetIndex.value = (currentSetIndex.value + 1) % sets.length;
    }

    const loadImageFromSet = (fileset, filename) => {
        let set = findImageSetById(fileset);
        if (!set) {
            set = { id: fileset };
            cachedSets.push(set);
        }

        if (!set[filename]) {
            set[filename] = new URL(`${import.meta.env.BASE_URL}images/sets/${fileset}/${filename}.jpg`, import.meta.url).href;
        }

        return set[filename];
    };

    const findImageSetById = (id) => {
        return cachedSets.find(set => set.id === id);
    }

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
            border-radius: 5px;
            box-shadow: 0 0 15px rgba(0, 0, 0, .3);
            height: auto;
            position: absolute;
            width: 100%;

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

            img {
                height: auto;
                width: 100%;
            }
        }
    }

    .fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>