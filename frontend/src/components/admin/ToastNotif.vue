<template>
    <transition name="toast">
        <div v-if="toast.show" :class="['toast', 'toast--' + toast.type]">
            <span class="material-symbols-outlined">{{ toast.type === 'success' ? 'check_circle' : 'error' }}</span>
            {{ toast.message }}
        </div>
    </transition>
</template>
<script>
export default {
    name: 'ToastNotif',
    props: { toast: { type: Object, default: () => ({ show: false, message: '', type: 'success' }) } },
}
</script>
<style scoped>
.toast {
    position: fixed;
    bottom: 2rem;
    left: 50%;
    transform: translateX(-50%);
    padding: 0.75rem 1.5rem;
    border-radius: 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    font-weight: 600;
    z-index: 500;
    white-space: nowrap;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
    font-family: 'Plus Jakarta Sans', sans-serif;
}

.toast--success {
    background: #064e3b;
    color: #fff;
}

.toast--error {
    background: #7f1d1d;
    color: #fff;
}

.toast .material-symbols-outlined {
    font-size: 1rem;
}

.toast-enter-active,
.toast-leave-active {
    transition: all 0.3s;
}

.toast-enter-from,
.toast-leave-to {
    opacity: 0;
    transform: translateX(-50%) translateY(1rem);
}
</style>