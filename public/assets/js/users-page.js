function usersPage() {
    return {
        users: [],
        search: '',
        statusFilter: '',

        loading: false,

        _approvingMap: {},
        _deletingMap: {},
        _togglingMap: {},

        selectedUsers: [],
        _bulkLoading: false,

        /* =========================
           INIT
        ========================= */
        async init() {
            await this.fetchUsers()
        },

        /* =========================
           FETCH USERS
        ========================= */
        async fetchUsers() {
            this.loading = true

            try {
                const res = await fetch('/rkd-cafe/app/controllers/AuthController.php?action=getUsers', {
                    credentials: 'same-origin'
                })

                if (!res.ok) {
                    throw new Error(`HTTP ${res.status}`)
                }

                const text = await res.text()

                let data
                try {
                    data = JSON.parse(text)
                } catch {
                    throw new Error('Response tidak valid')
                }

                if (data.error) {
                    throw new Error(data.error)
                }

                this.users = (Array.isArray(data) ? data : []).map(u => ({
                    id: u.id ?? null,
                    name: u.name?.trim() || 'Unknown User',
                    email: u.email?.trim() || '-',
                    status: u.status?.trim().toLowerCase() || 'inactive',
                    login_method: (u.login_method ?? '').toLowerCase(),
                    foto: u.foto ?? null,
                    created_at: u.created_at ?? null,
                    request_id: u.request_id ?? null,
                    request_status: u.request_status?.trim().toLowerCase() || null
                }))

            } catch (err) {
                this.toast('error', err.message || 'Gagal mengambil data')
            } finally {
                this.loading = false
            }
        },

        /* =========================
           FILTER USERS
        ========================= */
        filtered() {
            const search = (this.search || '').toLowerCase().trim()

            return this.users.filter(u => {
                const name = (u.name ?? '').toLowerCase()
                const email = (u.email ?? '').toLowerCase()
                const status = (u.status ?? 'inactive').toLowerCase()
                const request = (u.request_status ?? '').toLowerCase()

                const matchSearch =
                    !search ||
                    name.includes(search) ||
                    email.includes(search)

                let matchStatus = true

                if (this.statusFilter) {
                    if (this.statusFilter === 'request_pending') {
                        matchStatus = request === 'pending'
                    } else {
                        matchStatus = status === this.statusFilter
                    }
                }

                return matchSearch && matchStatus
            })
        },

        /* =========================
           STATUS CLASS
        ========================= */
        statusClass(status) {
            status = (status || 'inactive').toLowerCase()

            return {
                'bg-green-100 text-green-700': status === 'active',
                'bg-gray-200 text-gray-600': status === 'inactive',
                'bg-yellow-100 text-yellow-700': status === 'pending',
                'bg-red-100 text-red-600': status === 'blocked'
            }
        },

        /* =========================
           APPROVE
        ========================= */
        async approve(id) {
            if (!id || this._approvingMap[id]) return

            this._approvingMap = {
                ...this._approvingMap,
                [id]: true
            }

            const controller = new AbortController()
            const timeout = setTimeout(() => controller.abort(), 10000)

            try {
                const res = await fetch('/rkd-cafe/api/admin/approve_request.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        request_id: id,
                        csrf_token: window.csrfToken
                    }),
                    signal: controller.signal
                })

                const data = await res.json().catch(() => null)

                if (!res.ok || !data || data.error) {
                    throw new Error(data?.error || 'Gagal approve')
                }

                // ✅ OPTIMISTIC UPDATE
                this.users = this.users.map(u =>
                    u.request_id === id
                        ? { ...u, request_status: 'approved', status: 'active' }
                        : u
                )

                this.toast('success', data.message)

            } catch (err) {
                this.toast(
                    'error',
                    err.name === 'AbortError'
                        ? 'Request timeout'
                        : err.message
                )
            } finally {
                clearTimeout(timeout)

                const { [id]: _, ...rest } = this._approvingMap
                this._approvingMap = rest
            }
        },

        /* =========================
           DELETE
        ========================= */
        async toggleStatus(user) {
            if (!user?.id || this._togglingMap[user.id]) return

            const newStatus = user.status === 'active' ? 'inactive' : 'active'

            if (!confirm(`Ubah status menjadi ${newStatus}?`)) return

            // ✅ reactive set
            this._togglingMap = {
                ...this._togglingMap,
                [user.id]: true
            }

            const controller = new AbortController()
            const timeout = setTimeout(() => controller.abort(), 10000)

            try {
                const res = await fetch('/rkd-cafe/api/admin/toggle_user_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        user_id: user.id,
                        status: newStatus,
                        csrf_token: window.csrfToken
                    }),
                    signal: controller.signal
                })

                const data = await res.json().catch(() => null)

                if (!res.ok || !data || data.error) {
                    throw new Error(data?.error || 'Gagal update status')
                }

                // ✅ update UI langsung
                this.users = this.users.map(u =>
                    u.id === user.id
                        ? { ...u, status: newStatus }
                        : u
                )

                this.toast('success', data.message)

            } catch (err) {
                this.toast(
                    'error',
                    err.name === 'AbortError'
                        ? 'Request timeout'
                        : err.message
                )
            } finally {
                clearTimeout(timeout)

                const { [user.id]: _, ...rest } = this._togglingMap
                this._togglingMap = rest
            }
        },

        /* =========================
           TOGGLE BULK ACTIONS
        ========================= */
        toggleAll(e) {
            if (e.target.checked) {
                this.selectedUsers = this.filtered().map(u => u.id)
            } else {
                this.selectedUsers = []
            }
        },

        /* =========================
           BULK UPDATE
        ========================= */
        async bulkUpdate(status) {
            if (!this.selectedUsers.length || this._bulkLoading) return

            if (!confirm(`Ubah ${this.selectedUsers.length} user menjadi ${status}?`)) return

            this._bulkLoading = true

            const controller = new AbortController()
            const timeout = setTimeout(() => controller.abort(), 10000)

            try {
                const res = await fetch('/rkd-cafe/api/admin/bulk_user_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        user_ids: JSON.stringify(this.selectedUsers),
                        status: status,
                        csrf_token: window.csrfToken
                    }),
                    signal: controller.signal
                })

                const data = await res.json().catch(() => null)

                if (!res.ok || !data || data.error) {
                    throw new Error(data?.error || 'Gagal bulk update')
                }

                // ✅ update UI
                this.users = this.users.map(u =>
                    this.selectedUsers.includes(u.id)
                        ? { ...u, status }
                        : u
                )

                this.toast('success', data.message || 'Bulk update berhasil')

                // reset selection
                this.selectedUsers = []

                this.$nextTick(() => {
                    this.selectedUsers = []
                    
                    if (this.$refs.selectAll) {
                        this.$refs.selectAll.checked = false
                    }
                })

            } catch (err) {
                this.toast(
                    'error',
                    err.name === 'AbortError'
                        ? 'Request timeout'
                        : err.message
                )
            } finally {
                clearTimeout(timeout)
                this._bulkLoading = false
            }
        },

        /* =========================
           TOAST
        ========================= */
        toast(type, message) {
            window.dispatchEvent(new CustomEvent("toast", {
                detail: { type, message }
            }))
        }
    }
}