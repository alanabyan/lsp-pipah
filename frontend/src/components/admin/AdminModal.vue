<template>
    <transition name="modal">
        <div v-if="show" class="modal-overlay" @click.self="$emit('close')">
            <div class="modal" :style="{ maxWidth: width }">
                <div class="modal-header">
                    <h3 class="modal-title">{{ title }}</h3>
                    <button class="modal-close" @click="$emit('close')">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <div class="modal-body">
                    <slot />
                </div>
                <div class="modal-footer">
                    <button class="btn btn--ghost" @click="$emit('close')">Batal</button>
                    <button class="btn btn--indigo" :disabled="loading" @click="$emit('submit')">
                        <span v-if="loading" class="material-symbols-outlined spin">progress_activity</span>
                        <span v-else class="material-symbols-outlined">{{ submitIcon }}</span>
                        {{ loading ? 'Menyimpan...' : submitLabel }}
                    </button>
                </div>
            </div>
        </div>
    </transition>
</template>

<script>
export default {
    name: 'AdminModal',
    props: {
        show: { type: Boolean, default: false },
        title: { type: String, default: 'Form' },
        loading: { type: Boolean, default: false },
        submitLabel: { type: String, default: 'Simpan' },
        submitIcon: { type: String, default: 'save' },
        width: { type: String, default: '28rem' },
    },
    emits: ['close', 'submit'],
}
</script>

<style scoped>
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 200;
    padding: 1rem;
}

.modal {
    background: #0f1319;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 1rem;
    width: 100%;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.07);
    flex-shrink: 0;
}

.modal-title {
    font-family: 'Manrope', sans-serif;
    font-size: 1rem;
    font-weight: 700;
    color: #fff;
}

.modal-close {
    background: none;
    border: none;
    cursor: pointer;
    color: rgba(255, 255, 255, 0.4);
    display: flex;
    padding: 0.25rem;
    border-radius: 0.375rem;
    transition: all 0.15s;
}

.modal-close:hover {
    color: #fff;
    background: rgba(255, 255, 255, 0.06);
}

.modal-close .material-symbols-outlined {
    font-size: 1.1rem;
}

.modal-body {
    padding: 1.5rem;
    overflow-y: auto;
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.modal-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid rgba(255, 255, 255, 0.07);
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    flex-shrink: 0;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.55rem 1.1rem;
    border: none;
    border-radius: 0.625rem;
    font-size: 0.82rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.15s;
    font-family: inherit;
}

.btn--indigo {
    background: #6366f1;
    color: #fff;
}

.btn--indigo:hover:not(:disabled) {
    background: #4f46e5;
}

.btn--indigo:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.btn--ghost {
    background: rgba(255, 255, 255, 0.05);
    color: rgba(255, 255, 255, 0.6);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.btn--ghost:hover {
    background: rgba(255, 255, 255, 0.08);
}

.btn .material-symbols-outlined {
    font-size: 1rem;
}

.spin {
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.modal-enter-active,
.modal-leave-active {
    transition: all 0.25s;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.modal-enter-from .modal,
.modal-leave-to .modal {
    transform: scale(0.96) translateY(8px);
}
</style>