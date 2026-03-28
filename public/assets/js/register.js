function registerFlow() {
    return {
        step: 0,
        showPassword: false,

        steps: ['Usaha', 'Owner', 'Akun', 'Kasir'],

        form: {
            business_name: '',
            business_phone: '',

            business_category: '',
            business_category_other: '',
            business_address: '',
            latitude: '',
            longitude: '',
            addressSuggestions: [],
            isSearchingAddress: false,

            owner_name: '',
            owner_email: '',
            owner_phone: '',

            username: '',
            password: '',
            confirm: '',

            cashiers: []
        },

        isLoading: false,

        canProceed() {
            switch (this.step) {

                case 0:
                    return this.form.business_name.trim() !== '' &&
                        this.form.business_phone.length >= 9 &&
                        this.form.business_category !== '' &&
                        (this.form.business_category !== 'other' || this.form.business_category_other.trim() !== '') &&
                        this.form.business_address.trim() !== '' &&
                        this.form.latitude !== '' &&  
                        this.form.longitude !== '' 

                case 1:
                    return this.form.owner_name.trim() !== '' &&
                        this.isValidEmail(this.form.owner_email) &&
                        this.isValidPhone(this.form.owner_phone)

                case 2:
                    return this.form.username.trim() !== '' &&
                        this.isValidPassword(this.form.password) &&
                        this.isPasswordMatch()

                case 3:
                    if (this.form.cashiers.length === 0) return true
                    const k = this.form.cashiers[0]

                    return k.name.trim() !== '' &&
                        k.username.trim() !== '' &&
                        this.isValidPassword(k.password) &&
                        k.password === k.confirm

                default:
                    return false
            }
        },

        handleEnter(e) {
            if (e.target.tagName === 'TEXTAREA') return
            if (!this.canProceed()) return
            if (this.step === 3) return
            if (this.step === 1 && this.emailSuggestion) return

            const currentStepEl = this.$el.querySelector(`[data-step="${this.step}"]`)
            if (!currentStepEl) return

            const inputs = currentStepEl.querySelectorAll('input, select, textarea')
            const last = inputs[inputs.length - 1]

            if (e.target === last) {
                this.nextStep()
            }
        },

        nextStep() {
            if (!this.canProceed()) return

            if (this.step < 3) {
                this.step++
                return
            }

            this.submitForm()
        },

        async submitForm() {
            this.isLoading = true

            const controller = new AbortController()
            const timeout = setTimeout(() => controller.abort(), 5000)

            try {
                const formEl = this.$el.querySelector('form')
                const formData = new FormData(formEl)

                const res = await fetch('/register', {
                    method: 'POST',
                    body: formData,
                    signal: controller.signal
                })

                clearTimeout(timeout)

                if (!res.ok) throw new Error('Gagal submit')

                window.location.href = '/login'

            } catch (err) {
                console.error(err)
                alert('Timeout / error, coba lagi')
                this.isLoading = false
            }
        },

        isValidPhone(phone) {
            return /^8[1-9][0-9]{7,11}$/.test(phone)
        },

        isValidEmail(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)
        },

        emailSuggestion: '',

        generateEmailSuggestion() {
            const email = this.form.owner_email

            if (!email.includes('@')) {
                this.emailSuggestion = ''
                return
            }

            if ((email.match(/@/g) || []).length > 1) {
                this.emailSuggestion = ''
                return
            }

            const [name, domain = ''] = email.split('@')
            const domains = ['gmail.com', 'yahoo.com', 'outlook.com']
            const match = domains.find(d => d.startsWith(domain.toLowerCase()))
            this.emailSuggestion = match || ''
        },

        applyEmailSuggestion() {
            if (!this.emailSuggestion) return

            const [name] = this.form.owner_email.split('@')
            this.form.owner_email = name + '@' + this.emailSuggestion
            this.emailSuggestion = ''
        },

        init() {
            const focusMap = {
                0: 'businessName',
                1: 'ownerName',
                2: 'username'
            }

            this.$watch('step', (val) => {
                this.$nextTick(() => {
                    this.$refs[focusMap[val]]?.focus()
                })
            })

            this.$watch('form.business_address', (val) => {
                // reset hanya jika user mengetik manual
                if (this.addressSuggestions.length > 0) {
                    this.form.latitude = ''
                    this.form.longitude = ''
                }
            })
        },

        async searchAddress(query) {
            if (!query || query.length < 3) {
                this.addressSuggestions = []
                return
            }

            this.isSearchingAddress = true

            try {
                const res = await fetch(
                    `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(query)}&format=json&addressdetails=1&limit=5`
                )

                const data = await res.json()

                this.addressSuggestions = data.map(item => ({
                    label: item.display_name,
                    lat: item.lat,
                    lng: item.lon
                }))

            } catch (err) {
                console.error('Nominatim error:', err)
            } finally {
                this.isSearchingAddress = false
            }
        },

        selectAddress(item) {
            this.form.business_address = item.label
            this.form.latitude = item.lat
            this.form.longitude = item.lng

            this.addressSuggestions = []
        },

        isValidPassword(password) {
            return /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).{8,}$/.test(password)
        },

        isPasswordMatch() {
            return this.form.password &&
                this.form.confirm &&
                this.form.password === this.form.confirm
        },

        passwordScore() {
            let score = 0
            const p = this.form.password

            if (p.length >= 8) score++
            if (/[a-z]/.test(p)) score++
            if (/[A-Z]/.test(p)) score++
            if (/\d/.test(p)) score++
            if (/[@$!%*?&]/.test(p)) score++

            return score
        },

        passwordStrengthLabel() {
            const score = this.passwordScore()

            if (score <= 2) return 'Weak'
            if (score === 3) return 'Medium'
            if (score === 4) return 'Strong'
            if (score === 5) return 'Very Strong'
        },

        passwordStrengthColor() {
            const score = this.passwordScore()

            if (score <= 2) return 'bg-red-500'
            if (score === 3) return 'bg-yellow-500'
            if (score === 4) return 'bg-blue-500'
            if (score === 5) return 'bg-green-500'
        },

        isPasswordMatchKasir() {
            if (this.form.cashiers.length === 0) return true
            const k = this.form.cashiers[0]
            if (!k) return false
            return k.password && k.confirm && k.password === k.confirm
        },

        hasMinLengthKasir() {
            const k = this.form.cashiers[0]
            return k && k.password && k.password.length >= 8
        },

        hasLowercaseKasir() {
            const k = this.form.cashiers[0]
            return k && /[a-z]/.test(k.password || '')
        },

        hasUppercaseKasir() {
            const k = this.form.cashiers[0]
            return k && /[A-Z]/.test(k.password || '')
        },

        hasNumberKasir() {
            const k = this.form.cashiers[0]
            return k && /\d/.test(k.password || '')
        },

        hasSymbolKasir() {
            const k = this.form.cashiers[0]
            return k && /[@$!%*?&]/.test(k.password || '')
        },

        addKasir() {
            if (this.form.cashiers.length >= 1) return

            this.form.cashiers.push({
                name: '',
                username: '',
                password: '',
                confirm: ''
            })

            this.$nextTick(() => {
                const input = this.$el.querySelector('[data-step="3"] input')
                input?.focus()
            })
        }
    }
}

function phoneField() {
    return {
        modelValue: '',
        valid: false,
        touched: false,

        init() {
            this.$watch('modelValue', (val) => {
                this.format(val)
            })
        },

        format(val) {
            val = (val || '').toString()
            val = val.replace(/\D/g, '')

            if (val.startsWith('0')) {
                val = val.substring(1)
            }

            if (val.length > 13) {
                val = val.substring(0, 13)
            }

            this.valid = /^8[1-9][0-9]{7,11}$/.test(val)
            this.modelValue = val
        }
    }
}

document.addEventListener('alpine:init', () => {
    Alpine.data('registerFlow', registerFlow)
    Alpine.data('phoneField', phoneField)
})