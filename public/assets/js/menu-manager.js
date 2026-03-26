function menuManager() {
    return {

        /* ==========================
           STATE
        ========================== */
        search: '',
        category: '',
        status: '',
        usage: '',
        minPrice: '',
        maxPrice: '',

        data: [],
        filtered: [],
        categories: [],

        loading: false,
        deletingId: null,

        confirmModal: false,
        selectedItem: null,
        forceMode: false,

        previewItem: null,
        previewModal: false,

        quickAddModal: false,
        quickForm: {
            name: '',
            price: '',
            category_id: ''
        },

        /* ==========================
           HELPERS
        ========================== */
        toast(type, message) {
            window.dispatchEvent(new CustomEvent('toast', {
                detail: { type, message }
            }));
        },

        async api(url, payload = {}) {
            const res = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            const json = await res.json();

            if (!res.ok) {
                throw new Error(json.message || 'Request failed');
            }

            return json;
        },

        debug(...args) {
            if (window.APP_DEBUG) console.log(...args);
        },

        /* ==========================
           INIT
        ========================== */
        init() {
            this.fetchMenu();
            this.fetchCategories();

            let timer;

            this.$watch('search', () => {
                clearTimeout(timer);
                timer = setTimeout(() => this.applyFilter(), 300);
            });

            this.$watch('category', () => this.applyFilter());
            this.$watch('status', () => this.applyFilter());
            this.$watch('usage', () => this.applyFilter());
            this.$watch('minPrice', () => this.applyFilter());
            this.$watch('maxPrice', () => this.applyFilter());
        },

        /* ==========================
           FETCH
        ========================== */
        async fetchMenu() {
            this.loading = true;

            try {
                const res = await fetch('/rkd-cafe/api/menu/menu.php');
                if (!res.ok) throw new Error();

                this.data = await res.json();

                // 🔥 inject UI state
                this.data = this.data.map(i => ({
                    ...i,
                    selected: false,
                    _highlight: false,
                    _removing: false
                }));

                this.applyFilter();

            } catch (e) {
                this.toast('error', 'Failed to load menu');
                this.debug(e);
            } finally {
                this.loading = false;
            }
        },

        async fetchCategories() {
            try {
                const res = await fetch('/rkd-cafe/api/categories.php');
                if (!res.ok) throw new Error();

                this.categories = await res.json();

            } catch (e) {
                this.debug(e);
            }
        },

        /* ==========================
           FILTER (ADVANCED 🔥)
        ========================== */
        applyFilter() {
            const s = this.search.toLowerCase();
            const min = Number(this.minPrice || 0);
            const max = Number(this.maxPrice || Infinity);

            this.filtered = this.data.filter(item => {

                const name = item.name?.toLowerCase() || '';
                const category = item.category?.toLowerCase() || '';
                const status = item.status?.toLowerCase() || '';

                return (
                    name.includes(s) &&
                    (!this.category || category === this.category.toLowerCase()) &&
                    (!this.status || status === this.status.toLowerCase()) &&
                    (!this.usage || (this.usage === 'used' ? item.used : !item.used)) &&
                    (item.price >= min && item.price <= max)
                );
            });
        },

        /* ==========================
           FORMAT
        ========================== */
        formatRupiah(val) {
            return 'Rp ' + Number(val || 0).toLocaleString('id-ID');
        },

        /* ==========================
           NAVIGATION
        ========================== */
        navigate(url, message = '') {
            window.dispatchEvent(new CustomEvent('app:navigate', {
                detail: { url, message }
            }));
        },

        edit(item) {
            this.navigate(`/rkd-cafe/pages/menu/edit.php?id=${item.id}`);
        },

        view(item) {
            this.navigate(`/rkd-cafe/pages/menu/view.php?id=${item.id}`);
        },

        quickView(item) {
            this.previewItem = item;
            this.previewModal = true;
        },

        quickAdd() {
            this.previewItem = {
                name: '',
                price: '',
                category: ''
            };
            this.previewModal = true;
        },

        quickAdd() {
            this.quickAddModal = true;
        },

        /* ==========================
           BULK ACTION 🔥
        ========================== */
        toggleAll(e) {
            this.filtered.forEach(i => i.selected = e.target.checked);
        },

        get selectedItems() {
            return this.data.filter(i => i.selected);
        },

        bulkDelete() {
            if (!this.selectedItems.length) return;
            this.selectedItems.forEach(i => this.confirmDelete(i));
        },

        bulkEnable() {
            this.selectedItems.forEach(i => this.enableItem(i));
        },

        /* ==========================
           DELETE FLOW
        ========================== */
        confirmDelete(item) {
            this.selectedItem = item;
            this.confirmModal = true;
            this.forceMode = false;
        },

        handleDeleteUI(id, type) {
            const item = this.data.find(i => i.id === id);

            if (type === 'soft' || type === 'force') {
                if (item) {
                    item._removing = true;

                    setTimeout(() => {
                        this.data = this.data.filter(i => i.id !== id);
                        this.applyFilter();
                    }, 300);
                }
            }

            if (type === 'disabled') {
                if (item) {
                    item.status = 'inactive';
                    item._highlight = true;
                    setTimeout(() => item._highlight = false, 1000);
                }
            }
        },

        async executeDelete(force = false) {
            if (!this.selectedItem || this.deletingId) return;

            const id = this.selectedItem.id;
            this.deletingId = id;
            this.loading = true;

            try {
                const json = await this.api('/rkd-cafe/api/menu/menu_delete.php', {
                    id,
                    force
                });

                if (json.requires_confirmation) {
                    this.forceMode = true;
                    this.deletingId = null;
                    return;
                }

                if (!json.success) {
                    throw new Error(json.message);
                }

                this.handleDeleteUI(id, json.type);

                this.toast('success', json.message);

                this.confirmModal = false;
                this.selectedItem = null;
                this.forceMode = false;

            } catch (e) {
                this.toast('error', e.message);
                this.debug(e);
            } finally {
                this.deletingId = null;
                this.loading = false;
            }
        },

        /* ==========================
           ENABLE FLOW
        ========================== */
        async enableItem(item) {
            try {
                const json = await this.api('/rkd-cafe/api/menu/menu_enable.php', {
                    id: item.id
                });

                item.status = 'active';

                item._highlight = true;
                setTimeout(() => item._highlight = false, 1000);

                this.toast('success', json.message);

            } catch (e) {
                this.toast('error', e.message);
                this.debug(e);
            }
        },

        /* ==========================
           DUPLICATE LAST ITEM
        ========================== */
        async duplicateLast() {
            if (!this.data.length) return;

            try {
                const json = await this.api('/rkd-cafe/api/menu/menu_duplicate.php', {
                    id: this.data[0].id
                });

                this.toast('success', json.message);

                this.fetchMenu();

            } catch (e) {
                this.toast('error', e.message);
            }
        },

        /* ==========================
           STORE QUICK MENU
        ========================== */
        async storeQuickMenu() {
            try {
                const json = await this.api('/rkd-cafe/api/menu/menu_store.php', this.previewItem);

                this.toast('success', json.message);

                this.previewModal = false;

                // reload data
                this.fetchMenu();

            } catch (e) {
                this.toast('error', e.message);
            }
        },

        /* ==========================
           SUBMIT QUICK ADD BUTTON
        ========================== */
        async submitQuickAdd() {

            if (!this.quickForm.name || !this.quickForm.price || !this.quickForm.category_id) {
                this.toast('error', 'Lengkapi data');
                return;
            }

            try {
                const json = await this.api('/rkd-cafe/api/menu/menu_store.php', this.quickForm);

                this.toast('success', json.message);

                this.quickAddModal = false;

                // refresh data
                this.fetchMenu();

                // reset form
                this.quickForm = {
                    name: '',
                    price: '',
                    category_id: ''
                };

            } catch (e) {
                this.toast('error', e.message);
            }
        }

    }
}