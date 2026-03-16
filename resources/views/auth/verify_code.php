<?php
session_start();

require_once __DIR__ . '/../../../config/database.php';

$pdo = getPDO(); // ← TAMBAHKAN BARIS INI

if (!isset($_SESSION['reset_user_id'])) {

    header("Location: /rkd-cafe/resources/views/auth/forgot_password.php");
    exit;
}

$stmt = $pdo->prepare("
    SELECT id
    FROM password_resets
    WHERE user_id = ?
    AND expires_at > NOW()
    LIMIT 1
");

$stmt->execute([$_SESSION['reset_user_id']]);

if (!$stmt->fetch()) {

    header("Location: /rkd-cafe/resources/views/auth/forgot_password.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id"
    x-data="otpApp()"
    x-init="init()"
    :class="{ 'dark': dark }">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Verifikasi Kode - RKD Cafe</title>

    <link href="/rkd-cafe/public/assets/css/output.css" rel="stylesheet">

    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

</head>


<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-200 via-gray-100 to-gray-300 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 transition">


    <div class="w-full max-w-md px-6">

        <!-- CARD -->

        <div class="backdrop-blur-xl bg-white/80 dark:bg-gray-800/70 border border-gray-200 dark:border-gray-700 rounded-2xl p-8 shadow-xl">

            <h2 class="text-3xl font-bold text-center text-gray-800 dark:text-white mb-2">
                Verifikasi Kode
            </h2>

            <p class="text-center text-gray-500 dark:text-gray-400 text-sm mb-8">
                Masukkan kode verifikasi yang telah dikirim ke email Anda
            </p>


            <!-- OTP INPUT -->

            <div class="flex justify-center gap-3 mb-6"
                @paste="handlePaste">

                <template x-for="(digit, index) in digits" :key="index">

                    <input
                        type="text"
                        inputmode="numeric"
                        pattern="[0-9]*"
                        maxlength="1"
                        x-model="digits[index]"
                        @input="next(index)"
                        @keydown.backspace="prev(index)"
                        :autocomplete="index === 0 ? 'one-time-code' : 'off'"
                        class="w-12 h-14 text-center text-xl font-bold border rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">

                </template>

            </div>


            <!-- VERIFY BUTTON -->

            <button
                @click="verify()"
                :disabled="loading"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl font-semibold flex items-center justify-center gap-2 transition">

                <span x-show="!loading">Verifikasi</span>

                <span x-show="loading" class="flex items-center gap-2">

                    <svg class="animate-spin h-5 w-5"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24">

                        <circle class="opacity-25"
                            cx="12"
                            cy="12"
                            r="10"
                            stroke="currentColor"
                            stroke-width="4"></circle>

                        <path class="opacity-75"
                            fill="currentColor"
                            d="M4 12a8 8 0 018-8v8z"></path>

                    </svg>

                    Memverifikasi...

                </span>

            </button>


            <!-- ERROR MESSAGE -->

            <p
                x-show="error"
                x-text="error"
                class="text-red-500 text-sm text-center mt-4">
            </p>


            <!-- RESEND -->

            <div class="text-center mt-6 text-sm text-gray-500 dark:text-gray-400">

                <span x-show="timer > 0">
                    Kirim ulang kode dalam <span x-text="timer"></span>s
                </span>

                <button
                    x-show="timer === 0"
                    @click="resend()"
                    class="text-blue-500 hover:underline">

                    Kirim ulang kode

                </button>

            </div>

        </div>

    </div>



    <script>
        /* global AbortController */
        function otpApp() {

            return {

                dark: localStorage.theme === 'dark',

                digits: ['', '', '', '', '', ''],

                loading: false,

                error: '',

                timer: 30,


                init() {

                    document.documentElement.classList.toggle('dark', this.dark)

                    this.countdown()

                },


                next(i) {


                    let inputs = this.$el.querySelectorAll("input")

                    if (this.digits[i] && i < inputs.length - 1) {
                        inputs[i + 1].focus()
                    }

                    if (i === 5 && this.getCode().length === 6) {
                        this.verify()
                    }

                },


                prev(i) {

                    let inputs = this.$el.querySelectorAll("input")

                    if (!this.digits[i] && i > 0) {
                        inputs[i - 1].focus()
                    }

                },


                getCode() {

                    return this.digits.join('')

                },


                async verify() {

                    this.loading = true
                    this.error = ''

                    let code = this.getCode()

                    if (code.length !== 6) {
                        this.error = "Kode OTP harus 6 digit"
                        this.loading = false
                        return
                    }

                    try {

                        let res = await fetch(
                            "/rkd-cafe/app/controllers/AuthController.php?action=verifyCode", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/x-www-form-urlencoded"
                                },
                                body: "verification_code=" + code,
                                signal: controller.signal
                            })

                        clearTimeout(timeout)

                        if (!res.ok) {
                            throw new Error("HTTP " + res.status)
                        }

                        let data = await res.json()

                        if (data.redirect_url) {
                            window.location = data.redirect_url
                        } else {
                            this.error = data.message
                        }

                    } catch (e) {

                        this.error = "Terjadi kesalahan server"

                    }

                    this.loading = false
                },

                handlePaste(e) {

                    let paste = e.clipboardData.getData('text').trim()

                    if (/^\d{6}$/.test(paste)) {

                        this.digits = paste.split('')
                        this.verify()

                    }

                },


                countdown() {

                    if (this.interval) clearInterval(this.interval)

                    this.interval = setInterval(() => {

                        if (this.timer > 0) {
                            this.timer--
                        } else {
                            clearInterval(this.interval)
                        }

                    }, 1000)

                },


                async resend() {

                    let res = await fetch(
                        "/rkd-cafe/app/controllers/AuthController.php?action=resendOtp", {
                            method: "POST"
                        }
                    )

                    let data = await res.json()

                    if (data.success) {

                        this.timer = 30
                        this.countdown()

                    } else {

                        this.error = data.message

                    }

                }

            }

        }
    </script>

</body>

</html>