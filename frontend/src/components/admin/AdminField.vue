<template>
    <div class="form-group" :class="{ 'form-group--row': row }">
        <label v-if="label" class="form-label">
            {{ label }} <span v-if="required" class="req">*</span>
        </label>

        <textarea v-if="type === 'textarea'" :value="modelValue" :placeholder="placeholder" :rows="rows"
            class="form-control" @input="$emit('update:modelValue', $event.target.value)"></textarea>

        <select v-else-if="type === 'select'" :value="modelValue" class="form-control"
            @change="$emit('update:modelValue', $event.target.value)">
            <option value="">— Pilih —</option>
            <option v-for="opt in options" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
        </select>

        <input v-else-if="type === 'file'" type="file" :accept="accept" class="form-control form-control--file"
            @change="$emit('update:modelValue', $event.target.files[0])" />

        <input v-else :type="type" :value="modelValue" :placeholder="placeholder" :min="min" :step="step"
            class="form-control" @input="$emit('update:modelValue', $event.target.value)" />

        <p v-if="error" class="field-error">{{ error }}</p>
        <p v-if="hint" class="field-hint">{{ hint }}</p>
    </div>
</template>

<script>
export default {
    name: 'AdminField',
    props: {
        label: { type: String },
        modelValue: { default: '' },
        type: { type: String, default: 'text' },
        placeholder: { type: String, default: '' },
        required: { type: Boolean, default: false },
        row: { type: Boolean, default: false },
        options: { type: Array, default: () => [] },
        rows: { type: Number, default: 3 },
        accept: { type: String, default: 'image/*' },
        error: { type: String, default: '' },
        hint: { type: String, default: '' },
        min: { type: [String, Number], default: undefined },
        step: { type: [String, Number], default: undefined },
    },
    emits: ['update:modelValue'],
}
</script>

<style scoped>
.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.form-group--row {
    display: grid;
    grid-template-columns: 8rem 1fr;
    align-items: center;
    gap: 0.5rem;
}

.form-label {
    font-size: 0.72rem;
    font-weight: 700;
    color: rgba(255, 255, 255, 0.4);
    text-transform: uppercase;
    letter-spacing: 0.07em;
}

.req {
    color: #f87171;
}

.form-control {
    padding: 0.6rem 0.875rem;
    background: rgba(255, 255, 255, 0.04);
    border: 1.5px solid rgba(255, 255, 255, 0.08);
    border-radius: 0.5rem;
    color: #fff;
    font-size: 0.875rem;
    outline: none;
    font-family: inherit;
    transition: border 0.15s;
    width: 100%;
}

.form-control:focus {
    border-color: #6366f1;
    background: rgba(99, 102, 241, 0.04);
}

.form-control::placeholder {
    color: rgba(255, 255, 255, 0.2);
}

select.form-control option {
    background: #1a1f2e;
}

.form-control--file {
    padding: 0.5rem 0.875rem;
    cursor: pointer;
    color: rgba(255, 255, 255, 0.4);
    border-style: dashed;
}

textarea.form-control {
    resize: vertical;
    min-height: 5rem;
}

.field-error {
    font-size: 0.68rem;
    color: #f87171;
    font-weight: 600;
}

.field-hint {
    font-size: 0.68rem;
    color: rgba(255, 255, 255, 0.25);
}
</style>