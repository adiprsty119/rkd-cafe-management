function usersPage() {
    return {
        users: [],
        search: '',
        statusFilter: '',

        loading: false,

        _approvingMap: {},
        _deletingMap: {},

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

            this._approvingMap[id] = true

            const controller = new AbortController()
            const timeout = setTimeout(() => controller.abort(), 10000)

            try {
                const res = await fetch('/auth?action=approve', {
                    method: 'POST',
                    body: new URLSearchParams({
                        request_id: id,
                        csrf_token: window.csrfToken
                    }),
                    signal: controller.signal
                })

                const text = await res.text()

                let data
                try {
                    data = JSON.parse(text)
                } catch {
                    throw new Error('Response tidak valid')
                }

                if (!res.ok || data.error) {
                    throw new Error(data.error || 'Gagal approve')
                }

                // Optimistic update
                this.users = this.users.map(u =>
                    u.request_id === id
                        ? { ...u, request_status: 'approved', status: 'active' }
                        : u
                )

                this.toast('success', 'User berhasil di-approve')

            } catch (err) {
                this.toast('error', err.name === 'AbortError'
                    ? 'Request timeout'
                    : err.message)
            } finally {
                clearTimeout(timeout)
                delete this._approvingMap[id]
            }
        },

        /* =========================
           DELETE
        ========================= */
        async deleteUser(id) {
            if (!id || this._deletingMap[id]) return
            if (!confirm('Yakin hapus user?')) return

            this._deletingMap[id] = true

            const controller = new AbortController()
            const timeout = setTimeout(() => controller.abort(), 10000)

            try {
                const res = await fetch('/auth?action=deleteUser', {
                    method: 'POST',
                    body: new URLSearchParams({
                        user_id: id,
                        csrf_token: window.csrfToken
                    }),
                    signal: controller.signal
                })

                const text = await res.text()

                let data
                try {
                    data = JSON.parse(text)
                } catch {
                    throw new Error('Response tidak valid')
                }

                if (!res.ok || data.error) {
                    throw new Error(data.error || 'Gagal hapus user')
                }

                // Optimistic delete
                this.users = this.users.filter(u => u.id !== id)

                this.toast('success', 'User berhasil dihapus')

            } catch (err) {
                this.toast('error', err.name === 'AbortError'
                    ? 'Request timeout'
                    : err.message)
            } finally {
                clearTimeout(timeout)
                delete this._deletingMap[id]
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