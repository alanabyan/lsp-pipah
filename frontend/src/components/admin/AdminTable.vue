<template>
    <div class="admin-table-wrap">
        <!-- Header -->
        <div class="table-toolbar">
            <div class="toolbar-left">
                <h3 class="table-title">{{ title }}</h3>
                <span class="item-count">{{ data.length }} item</span>
            </div>
            <div class="toolbar-right">
                <div class="search-box">
                    <span class="material-symbols-outlined">search</span>
                    <input v-model="searchQuery" type="text" :placeholder="'Cari ' + title.toLowerCase() + '...'"
                        class="search-input" />
                </div>
                <button v-if="showAdd" class="btn btn--indigo" @click="$emit('add')">
                    <span class="material-symbols-outlined">add</span>
                    Tambah
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="table-scroll">
            <table class="data-table">
                <thead>
                    <tr>
                        <th v-for="col in columns" :key="col.key" :class="col.align ? 'text-' + col.align : ''">
                            {{ col.label }}
                        </th>
                        <th v-if="showActions" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="loading">
                        <td :colspan="columns.length + (showActions ? 1 : 0)" class="loading-cell">
                            <div class="loading-dots">
                                <span></span><span></span><span></span>
                            </div>
                        </td>
                    </tr>
                    <tr v-else-if="filteredData.length === 0">
                        <td :colspan="columns.length + (showActions ? 1 : 0)" class="empty-cell">
                            <span class="material-symbols-outlined">inbox</span>
                            <p>{{ searchQuery ? 'Tidak ada hasil pencarian.' : 'Belum ada data.' }}</p>
                        </td>
                    </tr>
                    <tr v-for="row in filteredData" :key="row.id" class="data-row">
                        <td v-for="col in columns" :key="col.key" :class="col.align ? 'text-' + col.align : ''">
                            <slot :name="'cell-' + col.key" :row="row" :value="row[col.key]">
                                <span>{{ row[col.key] ?? '—' }}</span>
                            </slot>
                        </td>
                        <td v-if="showActions" class="actions-cell">
                            <button v-if="showEdit" class="action-btn action-btn--edit" @click="$emit('edit', row)"
                                title="Edit">
                                <span class="material-symbols-outlined">edit</span>
                            </button>
                            <button v-if="showDelete" class="action-btn action-btn--delete"
                                @click="$emit('delete', row)" title="Hapus">
                                <span class="material-symbols-outlined">delete_outline</span>
                            </button>
                            <slot name="extra-actions" :row="row" />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
export default {
    name: 'AdminTable',
    props: {
        title: { type: String, default: 'Data' },
        columns: { type: Array, required: true },
        data: { type: Array, default: () => [] },
        loading: { type: Boolean, default: false },
        showAdd: { type: Boolean, default: true },
        showEdit: { type: Boolean, default: true },
        showDelete: { type: Boolean, default: true },
        showActions: { type: Boolean, default: true },
        searchKeys: { type: Array, default: () => [] },
    },
    emits: ['add', 'edit', 'delete'],
    data() { return { searchQuery: '' } },
    computed: {
        filteredData() {
            if (!this.searchQuery || this.searchKeys.length === 0) return this.data
            const q = this.searchQuery.toLowerCase()
            return this.data.filter(row =>
                this.searchKeys.some(k => String(row[k] ?? '').toLowerCase().includes(q))
            )
        },
    },
}
</script>

<style scoped>
.admin-table-wrap {
    background: #0f1319;
    border: 1px solid rgba(255, 255, 255, 0.07);
    border-radius: 1rem;
    overflow: hidden;
}

/* Toolbar */
.table-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.06);
    gap: 1rem;
    flex-wrap: wrap;
}

.toolbar-left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.table-title {
    font-family: 'Manrope', sans-serif;
    font-size: 0.95rem;
    font-weight: 700;
    color: #fff;
}

.item-count {
    font-size: 0.72rem;
    color: rgba(255, 255, 255, 0.3);
    background: rgba(255, 255, 255, 0.06);
    padding: 0.15rem 0.6rem;
    border-radius: 9999px;
}

.toolbar-right {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.search-box {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 0.5rem;
    padding: 0.4rem 0.75rem;
}

.search-box .material-symbols-outlined {
    font-size: 0.95rem;
    color: rgba(255, 255, 255, 0.25);
}

.search-input {
    border: none;
    background: none;
    color: #fff;
    font-size: 0.8rem;
    outline: none;
    font-family: inherit;
    width: 14rem;
}

.search-input::placeholder {
    color: rgba(255, 255, 255, 0.2);
}

/* Buttons */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.45rem 1rem;
    border: none;
    border-radius: 0.5rem;
    font-size: 0.78rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.15s;
    font-family: inherit;
}

.btn--indigo {
    background: #6366f1;
    color: #fff;
}

.btn--indigo:hover {
    background: #4f46e5;
}

.btn .material-symbols-outlined {
    font-size: 0.9rem;
}

/* Table */
.table-scroll {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table thead tr {
    border-bottom: 1px solid rgba(255, 255, 255, 0.07);
}

.data-table th {
    padding: 0.75rem 1.25rem;
    font-size: 0.67rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: rgba(255, 255, 255, 0.3);
    text-align: left;
    white-space: nowrap;
}

.data-row {
    border-bottom: 1px solid rgba(255, 255, 255, 0.04);
    transition: background 0.12s;
}

.data-row:hover {
    background: rgba(255, 255, 255, 0.02);
}

.data-row:last-child {
    border-bottom: none;
}

.data-table td {
    padding: 0.875rem 1.25rem;
    font-size: 0.82rem;
    color: rgba(255, 255, 255, 0.75);
    vertical-align: middle;
}

.text-center {
    text-align: center;
}

.text-right {
    text-align: right;
}

/* Actions */
.actions-cell {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    justify-content: center;
}

.action-btn {
    padding: 0.35rem;
    background: none;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 0.4rem;
    cursor: pointer;
    display: flex;
    transition: all 0.15s;
}

.action-btn .material-symbols-outlined {
    font-size: 1rem;
}

.action-btn--edit {
    color: rgba(255, 255, 255, 0.4);
}

.action-btn--edit:hover {
    background: rgba(99, 102, 241, 0.12);
    border-color: rgba(99, 102, 241, 0.3);
    color: #a5b4fc;
}

.action-btn--delete {
    color: rgba(255, 255, 255, 0.3);
}

.action-btn--delete:hover {
    background: rgba(248, 113, 113, 0.1);
    border-color: rgba(248, 113, 113, 0.25);
    color: #f87171;
}

/* States */
.loading-cell,
.empty-cell {
    text-align: center;
    padding: 3rem;
    color: rgba(255, 255, 255, 0.2);
}

.loading-dots {
    display: flex;
    justify-content: center;
    gap: 6px;
}

.loading-dots span {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    animation: bounce 1.2s ease-in-out infinite;
}

.loading-dots span:nth-child(2) {
    animation-delay: 0.2s;
}

.loading-dots span:nth-child(3) {
    animation-delay: 0.4s;
}

@keyframes bounce {

    0%,
    80%,
    100% {
        transform: scale(0.8);
        opacity: .4
    }

    40% {
        transform: scale(1.2);
        opacity: 1
    }
}

.empty-cell .material-symbols-outlined {
    font-size: 2.5rem;
    display: block;
    margin-bottom: 0.5rem;
}

.empty-cell p {
    font-size: 0.82rem;
}
</style>