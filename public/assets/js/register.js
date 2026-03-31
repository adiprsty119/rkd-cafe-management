function registerFlow() {
    return {
        step: 0,
        showPassword: false,

        steps: ['Usaha', 'Owner', 'Akun', 'Kasir'],

        addressSuggestions: [],
        isSearchingAddress: false,

        form: {
            business_name: '',
            business_phone: '',

            business_category: '',
            business_category_other: '',
            business_address: '',
            latitude: '',
            longitude: '',

            owner_name: '',
            owner_email: '',
            owner_phone: '',

            username: '',
            password: '',
            confirmPassword: '',

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
                        this.form.business_address.trim() !== ''

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
                        k.password === k.confirmPassword

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
            console.log("=== NEXT STEP CLICKED ===")
            const canProceed = this.canProceed()
            console.log("STEP:", this.step)
            console.log("CAN PROCEED:", canProceed)

            // 🔍 DETAIL DEBUG PER STEP
            if (this.step === 0) {
                console.log("STEP 0 DATA:", {
                    business_name: this.form.business_name,
                    business_phone: this.form.business_phone,
                    business_category: this.form.business_category,
                    business_category_other: this.form.business_category_other,
                    business_address: this.form.business_address,
                    latitude: this.form.latitude,
                    longitude: this.form.longitude
                })
            }

            if (this.step === 1) {
                console.log("STEP 1 DATA:", {
                    owner_name: this.form.owner_name,
                    owner_email: this.form.owner_email,
                    owner_phone: this.form.owner_phone,
                    valid_email: this.isValidEmail(this.form.owner_email),
                    valid_phone: this.isValidPhone(this.form.owner_phone)
                })
            }

            if (this.step === 2) {
                console.log("STEP 2 DATA:", {
                    username: this.form.username,
                    password: this.form.password,
                    confirmPassword: this.form.confirmPassword,
                    password_valid: this.isValidPassword(this.form.password),
                    password_match: this.isPasswordMatch()
                })
            }

            if (this.step === 3) {
                console.log("STEP 3 DATA:", {
                    cashiers: this.form.cashiers
                })
            }

            // ❌ STOP JIKA VALIDASI GAGAL
            if (!canProceed) {
                console.warn("❌ VALIDATION FAILED - STOP HERE")
                return
            }

            // 🔄 PINDAH STEP
            if (this.step < 3) {
                console.log("➡️ GO TO NEXT STEP:", this.step + 1)
                this.step++
                return
            }

            // 🚀 SUBMIT
            console.log("🚀 SUBMIT FORM TRIGGERED")
            this.submitForm()
        },

        async submitForm() {
            console.log("📡 SUBMIT STARTED")

            this.isLoading = true

            const controller = new AbortController()
            const timeout = setTimeout(() => controller.abort(), 15000)

            try {
                const formEl = this.$refs.form
                const formData = new FormData(formEl)
                const BASE_URL = '/rkd-cafe'

                const res = await fetch(`${BASE_URL}/app/controllers/AuthController.php?action=register`, {
                    method: 'POST',
                    body: formData,
                    signal: controller.signal
                })

                clearTimeout(timeout)

                const data = await res.json()

                if (!res.ok || !data.success) {
                    throw new Error(data.message || 'Registrasi gagal')
                }

                if (data.success) {
                    sessionStorage.setItem("toast", JSON.stringify({
                        type: "success",
                        message: data.message
                    }));

                    window.location.href = `${BASE_URL}/resources/views/auth/login.php`;
                }
            } catch (err) {
                console.error(err)

                window.toastData = {
                    type: "error",
                    message: err.message || "Terjadi kesalahan"
                };

                window.dispatchEvent(new CustomEvent("toast", {
                    detail: window.toastData
                }));

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
            return this.form.password.trim() &&
                this.form.confirmPassword.trim() &&
                this.form.password.trim() === this.form.confirmPassword.trim()
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
            return k.password && k.confirmPassword && k.password === k.confirmPassword
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
                confirmPassword: ''
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