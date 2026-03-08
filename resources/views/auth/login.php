<?php
session_start();

/* MENCEGAH CACHE LOGIN PAGE */
header("Cache-Control: no-store, no-cache, must-revalidate");

if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

/* CSRF TOKEN */
if (!isset($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="id"
    x-data="{dark: localStorage.theme === 'dark', loading:false, mode:'login'}"
    x-init="$watch('dark', val => {localStorage.theme = val ? 'dark' : 'light'; document.documentElement.classList.toggle('dark', val)})"
    :class="{ 'dark': dark }">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - RKD Cafe</title>

    <!-- Tailwind CSS -->
    <link href="/rkd-cafe/public/assets/css/output.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-5px);
            }

            75% {
                transform: translateX(5px);
            }
        }

        .shake {
            animation: shake .4s;
        }

        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear {
            display: none;
        }

        input[type="password"]::-webkit-credentials-auto-fill-button {
            visibility: hidden;
            display: none !important;
            pointer-events: none;
            position: absolute;
            right: 0;
        }
    </style>

</head>

<body class="min-h-screen flex bg-gray-200 dark:bg-gray-900 transition">

    <div class="flex w-full relative overflow-hidden">

        <!-- IMAGE -->
        <div
            class="hidden lg:flex w-1/2 bg-cover bg-center transition-all duration-700"
            :class="mode === 'login' ? 'order-1' : 'order-2'"
            style="background-image:url('https://images.unsplash.com/photo-1509042239860-f550ce710b93');">

            <div class="w-full h-full bg-black/40 flex items-center justify-center">

                <h1 class="text-white text-4xl font-bold">
                    RKD Cafe
                </h1>

            </div>
        </div>


        <!-- AUTH CARD -->
        <div
            class="flex w-full lg:w-1/2 items-center justify-center p-6 transition-all duration-700"
            :class="mode === 'login' ? 'order-2' : 'order-1'">

            <div class="w-full max-w-md">

                <!-- DARK MODE -->
                <div class="flex justify-end mb-4">

                    <button
                        @click="dark=!dark"
                        title="Toggle dark mode"
                        class="text-gray-600 dark:text-yellow-400 text-xl cursor-pointer">

                        <i :class="dark ? 'fa-solid fa-sun' : 'fa-solid fa-moon'"></i>

                    </button>

                </div>


                <!-- CARD -->
                <div
                    class="backdrop-blur-xl bg-white/70 dark:bg-gray-800/70 border border-white/40 rounded-2xl p-6 sm:p-10 shadow-[0_20px_50px_rgba(0,0,0,0.15)] transition-all duration-500 ease-in-out <?= isset($_SESSION['error']) ? 'shake' : '' ?>"
                    :class="mode === 'register' ? 'translate-x-4 scale-[1.02]' : ''">

                    <!-- LOGIN -->
                    <div x-show="mode === 'login'" x-transition>

                        <h2 class="text-3xl font-bold text-center mb-8 text-gray-700 dark:text-white">
                            Masuk ke Akun
                        </h2>

                        <!-- ERROR -->
                        <div
                            x-data="{show:false,message:''}"
                            x-init="
                            <?php if (isset($_SESSION['error'])): ?>
                            message='<?= $_SESSION['error']; ?>';
                            show=true;
                            setTimeout(()=>show=false,4000);
                            <?php unset($_SESSION['error']);
                            endif; ?>">

                            <div
                                x-show="show"
                                x-transition
                                class="fixed top-6 right-6 bg-red-500 text-white px-6 py-3 rounded-xl shadow-lg flex items-center gap-2 z-50">

                                <i class="fa-solid fa-circle-exclamation"></i>

                                <span x-text="message"></span>

                            </div>

                        </div>

                        <button type="button"
                            class="w-full border rounded-xl py-4 flex items-center justify-center gap-3 text-lg hover:bg-gray-50 dark:bg-gray-600 dark:hover:bg-gray-700 transition cursor-pointer">

                            <img src="https://www.svgrepo.com/show/475656/google-color.svg" class="w-6">

                            <span class="font-medium text-gray-700 dark:text-white">
                                Masuk dengan Google
                            </span>

                        </button>

                        <div class="flex items-center gap-4 my-8">
                            <div class="flex-1 h-px bg-gray-300 dark:bg-gray-600"></div>
                            <span class="text-gray-500 dark:text-gray-300 text-sm">Atau login dengan akun</span>
                            <div class="flex-1 h-px bg-gray-300 dark:bg-gray-600"></div>
                        </div>

                        <form
                            action="/rkd-cafe/app/controllers/AuthController.php"
                            method="POST"
                            @submit.prevent="loading=true; $el.submit()">

                            <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">

                            <!-- USERNAME -->
                            <div class="mb-5">

                                <label class="block mb-1 text-gray-700 dark:text-gray-200">
                                    Username
                                </label>

                                <input
                                    type="text"
                                    id="username"
                                    name="username"
                                    placeholder="username"
                                    autocomplete="username"
                                    autofocus
                                    required
                                    class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">

                            </div>


                            <!-- PASSWORD -->
                            <div class="mb-2 relative" x-data="{show:false}">

                                <label class="block mb-1 text-gray-700 dark:text-gray-200">
                                    Kata Sandi
                                </label>

                                <div class="relative">

                                    <input
                                        :type="show ? 'text' : 'password'"
                                        name="password"
                                        id="password"
                                        autocomplete="current-password"
                                        spellcheck="false"
                                        minlength="8"
                                        placeholder="••••••••"
                                        required
                                        class="w-full border rounded-xl px-4 py-3 pr-12 focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">

                                    <i
                                        @click="show=!show"
                                        title="Tampilkan password"
                                        class="fa-solid absolute right-4 top-4 cursor-pointer text-gray-600 hover:scale-110 transition duration-200 dark:text-white"
                                        :class="show ? 'fa-eye-slash text-blue-500':'fa-eye text-gray-500'">
                                    </i>

                                </div>

                            </div>


                            <!-- REMEMBER -->
                            <div class="flex items-center mb-6 mt-4 pl-1.5">

                                <input type="checkbox" name="remember" class="mr-2 cursor-pointer">

                                <label class="text-sm text-gray-700 dark:text-gray-200">
                                    Ingat saya
                                </label>

                            </div>

                            <div class="mb-6">

                                <a href="#" class="text-blue-500 text-sm hover:underline">
                                    Lupa password?
                                </a>

                            </div>

                            <button
                                type="submit"
                                :disabled="loading"
                                class="w-full bg-green-600 text-white py-3 rounded-xl hover:bg-green-700 flex items-center justify-center gap-2 disabled:opacity-70 cursor-pointer">

                                <span x-show="!loading">Masuk</span>

                                <span x-show="loading" x-cloak class="flex items-center gap-2">

                                    <svg class="animate-spin h-5 w-5 text-white"
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

                                    Memproses...

                                </span>

                            </button>

                        </form>


                        <p class="text-center mt-6 text-gray-600 dark:text-gray-300">

                            Belum punya akun?

                            <button
                                @click="mode='register'"
                                class="text-blue-500 hover:underline cursor-pointer">

                                Registrasi

                            </button>

                        </p>

                    </div>


                    <!-- REGISTER -->
                    <div x-show="mode === 'register'" x-transition>

                        <h2 class="text-3xl font-bold text-center mb-8 text-gray-700 dark:text-white">
                            Buat Akun
                        </h2>

                        <button type="button"
                            class="w-full border rounded-xl py-4 flex items-center justify-center gap-3 text-lg hover:bg-gray-50 dark:bg-gray-600 dark:hover:bg-gray-700 transition cursor-pointer">

                            <img src="https://www.svgrepo.com/show/475656/google-color.svg" class="w-6">

                            <span class="font-medium text-gray-700 dark:text-white">
                                Registrasi dengan Google
                            </span>

                        </button>

                        <div class="flex items-center gap-4 my-8">
                            <div class="flex-1 h-px bg-gray-300"></div>
                            <span class="text-gray-500 dark:text-gray-300 text-sm">Atau registrasi dengan akun</span>
                            <div class="flex-1 h-px bg-gray-300"></div>
                        </div>

                        <form action="/rkd-cafe/app/controllers/AuthController.php" method="POST">

                            <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">

                            <input type="text"
                                name="fullname"
                                placeholder="Nama Lengkap"
                                required
                                autofocus
                                class="w-full border rounded-xl px-4 py-3 mb-4 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">

                            <input type="email"
                                name="email"
                                placeholder="Email"
                                required
                                class="w-full border rounded-xl px-4 py-3 mb-4 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">

                            <input type="text"
                                name="username"
                                placeholder="Username"
                                required
                                class="w-full border rounded-xl px-4 py-3 mb-4 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">

                            <div
                                x-data="{
                                    password:'',
                                    confirm:'',
                                    show:false,

                                    rules:{
                                    length:false,
                                    lower:false,
                                    upper:false,
                                    number:false,
                                    symbol:false
                                    },

                                    check(){

                                    this.rules.length = this.password.length >= 8
                                    this.rules.lower = /[a-z]/.test(this.password)
                                    this.rules.upper = /[A-Z]/.test(this.password)
                                    this.rules.number = /[0-9]/.test(this.password)
                                    this.rules.symbol = /[^A-Za-z0-9]/.test(this.password)

                                    },

                                    valid(){
                                    return Object.values(this.rules).every(Boolean) && this.password === this.confirm
                                    }

                                }"
                                class="mb-4">

                                <!-- PASSWORD INPUT -->
                                <div class="relative">
                                    <input
                                        :type="show ? 'text' : 'password'"
                                        name="password"
                                        x-model="password"
                                        @input="check()"
                                        placeholder="Password"
                                        required
                                        class="w-full border rounded-xl px-4 py-3 mb-1 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">

                                    <i
                                        @click="show=!show"
                                        title="Tampilkan password"
                                        class="fa-solid absolute right-4 top-4 cursor-pointer text-gray-600 hover:scale-110 transition duration-200 dark:text-white"
                                        :class="show ? 'fa-eye-slash text-blue-500':'fa-eye text-gray-500'">
                                    </i>
                                </div>

                                <!-- STRENGTH BAR -->
                                <div class="flex gap-1">

                                    <div class="h-2 flex-1 rounded transition-all duration-300"
                                        :class="strength >=1 ? 'bg-red-500 scale-x-100' : 'bg-gray-200 dark:bg-gray-600 scale-x-90'"></div>

                                    <div class="h-2 flex-1 rounded transition-all duration-300"
                                        :class="strength >=3 ? 'bg-yellow-400 scale-x-100' : 'bg-gray-200 dark:bg-gray-600 scale-x-90'"></div>

                                    <div class="h-2 flex-1 rounded transition-all duration-300"
                                        :class="strength >=5 ? 'bg-green-500 scale-x-100' : 'bg-gray-200 dark:bg-gray-600 scale-x-90'"></div>

                                </div>


                                <!-- RULE CHECKLIST -->
                                <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs mb-4">

                                    <span :class="rules.length ? 'text-green-500' : 'text-gray-400'">
                                        ✓ 8 karakter
                                    </span>

                                    <span :class="rules.lower ? 'text-green-500' : 'text-gray-400'">
                                        ✓ huruf kecil
                                    </span>

                                    <span :class="rules.upper ? 'text-green-500' : 'text-gray-400'">
                                        ✓ huruf besar
                                    </span>

                                    <span :class="rules.number ? 'text-green-500' : 'text-gray-400'">
                                        ✓ angka
                                    </span>

                                    <span :class="rules.symbol ? 'text-green-500' : 'text-gray-400'">
                                        ✓ simbol
                                    </span>

                                </div>


                                <!-- CONFIRM PASSWORD -->
                                <div class="relative">
                                    <input
                                        :type="show ? 'text' : 'password'"
                                        name="confirm_password"
                                        x-model="confirm"
                                        placeholder="Konfirmasi Password"
                                        required
                                        class="w-full border rounded-xl px-4 py-3 mb-1 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
                                    <i
                                        @click="show=!show"
                                        title="Tampilkan password"
                                        class="fa-solid absolute right-4 top-4 cursor-pointer text-gray-600 hover:scale-110 transition duration-200 dark:text-white"
                                        :class="show ? 'fa-eye-slash text-blue-500':'fa-eye text-gray-500'">
                                    </i>

                                </div>
                                <p
                                    x-show="confirm && confirm !== password"
                                    class="text-red-500 text-xs">

                                    Password tidak cocok

                                </p>

                                <p
                                    x-show="confirm && confirm === password"
                                    class="text-green-500 text-xs">

                                    Password cocok ✓

                                </p>

                            </div>

                            <button
                                :disabled="!valid()"
                                class="w-full bg-green-600 text-white py-3 rounded-xl hover:bg-green-700 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">

                                Registrasi

                            </button>

                        </form>


                        <p class="text-center mt-6 text-gray-600 dark:text-gray-300">

                            Sudah mempunyai akun?

                            <button
                                @click="mode='login'"
                                class="text-blue-500 hover:underline cursor-pointer">

                                Masuk

                            </button>

                        </p>

                    </div>

                </div>

            </div>

        </div>

    </div>

</body>

</html>