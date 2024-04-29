<template>
    <div class="modal-overlay"></div>
    <div class="modal">
        <div class="card" ref="cardRef">
            <img class="card__image card__image--main" src="@/assets/images/pikachu.png" alt="Pikachu" />
            <img class="card__image card__image--mask" src="@/assets/images/pikachu-mask.png" alt="Layer mask for Pikachu hands" />
            <div class="card__uploader" :class="{
                'card__uploader--fail': uploader.status == UPLOADER_STATUS.Fail,
                'card__uploader--success': uploader.status == UPLOADER_STATUS.Success
            }">
                <div class="uploader__header">
                    <p v-if="uploader.status == UPLOADER_STATUS.Uploading"> {{ uploader.filesAmount == 1 ? '1 photo' : uploader.filesAmount + ' photos' }}</p>
                    <p v-else-if="uploader.status == UPLOADER_STATUS.Fail">Failed</p>
                    <p v-else-if="uploader.status == UPLOADER_STATUS.Success">Success</p>
                </div>
                <div class="uploader__body">
                    <p v-if="uploader.status == UPLOADER_STATUS.Uploading">Uploading...</p>
                    <p v-else-if="uploader.status == UPLOADER_STATUS.Fail">Uh-oh! Upload didn't make it...</p>
                    <p v-else-if="uploader.status == UPLOADER_STATUS.Success">Hooray! Thank you for your upload!</p>
                </div>
                <div class="uploader__footer">
                    <div class="progress-bar">
                        <div class="fill" ref="progressBarFillRef"></div>
                    </div>
                    <button @click="closeModal">
                        {{ uploader.status === UPLOADER_STATUS.Uploading ? 'Cancel' : 'Close' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
    import { onMounted, onUnmounted, reactive, ref } from 'vue'
    import axios from 'axios'

    onMounted(() => {
        document.body.classList.add('disableScroll')

        uploadFiles(uploader.files);
    });

    onUnmounted(() => document.body.classList.remove('disableScroll'));

    const emit = defineEmits(['close']);

    const props = defineProps({
        files: { type: Object, required: true }
    });

    const abortController = new AbortController();

    const UPLOADER_STATUS = {
        Uploading: 1,
        Success: 2,
        Fail: 0,
    }

    const cardRef = ref(null);
    const progressBarFillRef = ref(null);

    const uploader = reactive({
        files: props.files,
        filesAmount: 0,
        status: UPLOADER_STATUS.Uploading
    })

    const uploadFiles = async (files) => {
        const formData = new FormData();

        uploader.filesAmount = files.length;

        for (const file of files) {
            formData.append('images[]', file);
        }

        axios.post('/upload', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            },
            onUploadProgress,
            signal: abortController.signal
        })
        .then(() => {
            uploader.status = UPLOADER_STATUS.Success;
        })
        .catch(() => {
            uploader.status = UPLOADER_STATUS.Fail;
        });
    };

    const onUploadProgress = (progressEvent) => {
        let percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total)
        progressBarFillRef.value.style.width = percentCompleted + '%';
    };

    const closeModal = () => {
        abortController.abort();
        cardRef.value.classList.add('reverse-animation');

        cardRef.value.addEventListener('animationend', () => {
            emit('close');
        }, { once: true })
    };
</script>

<style lang="scss" scoped>
    .modal-overlay {
        backdrop-filter: blur(5px);
        background-color: rgba(1, 1, 1, 0.75);
        height: 100%;
        left: 0;
        pointer-events: auto;
        position: fixed;
        top: 0;
        width: 100%;
        z-index: 1000;
    }

    .modal {
        align-items: center;
        display: flex;
        flex-direction: column;
        height: 100%;
        justify-content: center;
        overflow: hidden;
        padding: 20px 40px;
        position: fixed;
        width: 100%;
        z-index: 2000;
    }

    .card {
        align-items: center;
        animation: scale .25s ease-in 0s 1;
        display: flex;
        justify-content: center;
        max-width: 80vh;
        position: relative;

        &__image {
            max-width: 600px;
            min-width: 300px;
            width: 100%;

            &--main {
                filter: drop-shadow(2px 4px 6px black);
                z-index: 0;
            }

            &--mask {
                position: absolute;
                z-index: 10;
            }
        }

        &__uploader {
            background: #fff;
            border-radius: 15px;
            bottom: 0;
            box-shadow: 0 0 15px rgba(0, 0, 0, .3);
            display: flex;
            flex-direction: column;
            height: 30%;
            justify-content: center;
            max-height: 200px;
            min-height: 100px;
            position: absolute;
            padding: 5px 15px 10px 15px;
            width: 100%;

            .uploader {
                &__header {
                    p {
                        text-align: right;
                    }
                }

                &__body {
                    margin-bottom: 5px;

                    p {
                        white-space: nowrap;
                        overflow: hidden;
                        text-overflow: ellipsis;
                    }
                }

                &__footer {
                    display: flex;
                    gap: 20px;

                    .progress-bar {
                        background: transparent;
                        border: 2px solid $--color-text-muted-border;
                        border-radius: 10px;
                        height: 25px;
                        overflow: hidden;
                        width: 100%;

                        .fill {
                            background: $--color-background;
                            height: 100%;
                            width: 0%;
                        }
                    }

                    button {
                        background: transparent;
                        border: 2px solid $--color-text-muted-border;
                        border-radius: 10px;
                        color: $--color-text-muted;
                        cursor: pointer;
                        font-weight: bold;
                        outline: none;
                        width: 100px;
                        z-index: 20;
                    }
                }
            }

            &--fail {
                .uploader {
                    &__header p {
                        color: $--color-error;
                    }

                    &__footer .progress-bar .fill {
                        background: $--color-error;
                        width: 100%;
                    }
                }
            }

            &--success {
                .uploader {
                    &__header p {
                        color: $--color-success;
                    }

                    &__footer .progress-bar .fill {
                        background: $--color-success;
                        width: 100%;
                    }
                }
            }
        }
    }

    .reverse-animation {
        animation: scale-reverse .25s ease-out 0s 1;
    }

    @keyframes scale {
        from {
            transform: scale(0);
        }
        to {
            transform: scale(1);
        }
    }

    @keyframes scale-reverse {
        from {
            transform: scale(1);
        }
        to {
            transform: scale(0);
        }
    }
</style>