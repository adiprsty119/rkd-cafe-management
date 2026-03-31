<?php

define('APP_INIT', true);

require_once __DIR__ . '/../../../app/bootstrap.php';
require_once __DIR__ . '/../../../app/helpers/auth_helper.php';

function isSecure()
{
    return (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
        ($_SERVER['SERVER_PORT'] == 443) ||
        (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
    );
}

/* SESSION CONFIG */
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => isSecure(),
    'httponly' => true,
    'samesite' => 'Strict'
]);

ini_set('session.use_strict_mode', 1);

/* START SESSION */
session_start();

if (empty($_SESSION['initiated'])) {
    session_regenerate_id(true);
    $_SESSION['initiated'] = true;
}

/* SECURITY HEADER */
if (basename($_SERVER['PHP_SELF']) === 'register.php') {
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Pragma: no-cache");
}

if (isSecure()) {
    header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
}

/* CSP */
$csp = "default-src 'self'; ";
$csp .= "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://unpkg.com https://cdnjs.cloudflare.com; ";
$csp .= "style-src 'self' https://cdnjs.cloudflare.com 'unsafe-inline'; ";
$csp .= "img-src 'self' https://images.unsplash.com data:; ";
$csp .= "font-src 'self' https://cdnjs.cloudflare.com; ";
$csp .= "connect-src 'self' https://nominatim.openstreetmap.org; ";
$csp .= "frame-ancestors 'self'; ";
$csp .= "form-action 'self'; ";
$csp .= "base-uri 'self';";

header("Content-Security-Policy: $csp");
header("Permissions-Policy: geolocation=(), camera=(), microphone=()");
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("X-XSS-Protection: 1; mode=block");

guestOnly();

/* CSRF */
if (
    empty($_SESSION['csrf_token']) ||
    empty($_SESSION['csrf_token_expiry']) ||
    $_SESSION['csrf_token_expiry'] < time()
) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $_SESSION['csrf_token_expiry'] = time() + 900;
    $_SESSION['csrf_user_agent'] = $_SERVER['HTTP_USER_AGENT'];
}
?>

<!DOCTYPE html>
<html lang="id"
    x-data="{ dark: localStorage.theme === 'dark' }"
    x-init="$watch('dark', val => {
        localStorage.theme = val ? 'dark' : 'light';
        document.documentElement.classList.toggle('dark', val)
    })"
    :class="{ 'dark': dark }">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - RKD Cafe</title>

    <!-- Tailwind CSS -->
    <link href="/rkd-cafe/public/assets/css/output.css" rel="stylesheet">

    <!-- Vanilla CSS -->
    <link href="/rkd-cafe/public/assets/css/auth.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- x-cloak -->
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="min-h-screen flex bg-gradient-to-br from-gray-200 via-gray-100 to-gray-300 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900">

    <div
        x-data="registerFlow"
        x-init="init()"
        @keydown.enter.prevent=" handleEnter($event)"
        class="flex w-full min-h-screen">

        <!-- IMAGE -->
        <div class="hidden lg:flex w-1/2 bg-cover bg-center"
            style="background-image:url('https://images.unsplash.com/photo-1509042239860-f550ce710b93');">
            <div class="w-full h-full bg-black/40 flex items-center justify-center">
                <h1 class="text-white text-4xl font-bold">RKD Cafe</h1>
            </div>
        </div>

        <!-- FORM -->
        <div class="flex w-full lg:w-1/2 items-center justify-center p-6">

            <div class="w-full max-w-md">

                <!-- DARK MODE -->
                <div class="flex justify-end mb-4">
                    <button @click="dark=!dark"
                        class="text-gray-500 dark:text-yellow-400 text-lg hover:rotate-180 transition duration-500 cursor-pointer">
                        <i :class="dark ? 'fa-solid fa-sun' : 'fa-solid fa-moon'"></i>
                    </button>
                </div>

                <!-- PROGRESS -->
                <div class="relative mb-8">

                    <!-- LINE -->
                    <div class="absolute top-4 left-0 right-0 h-[2px] bg-gray-200 dark:bg-gray-700"></div>

                    <div class="flex justify-between relative z-10">

                        <template x-for="(label, i) in steps" :key="i">

                            <div class="flex flex-col items-center w-full">

                                <div class="w-9 h-9 flex items-center justify-center rounded-full text-xs font-bold transition-all duration-300"
                                    :class="step > i ? 'bg-green-500 text-white' :
                                    step === i ? 'bg-blue-600 text-white scale-110 shadow-lg' :
                                    'bg-gray-300 text-gray-600'">

                                    <span x-text="i+1"></span>

                                </div>

                                <span class="mt-2 text-[11px] text-center"
                                    :class="step === i ? 'text-blue-600 font-semibold' : 'text-gray-500 dark:text-gray-400'"
                                    x-text="label">
                                </span>

                            </div>

                        </template>

                    </div>

                </div>

                <!-- CARD -->
                <form x-ref="form" method="POST" action="/register" autocomplete="off" @submit.prevent="submitForm">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-xl border border-gray-100 dark:border-gray-700">

                        <!-- HEADER -->
                        <div class="mb-6 space-y-2">

                            <!-- TOP ROW -->
                            <div class="flex items-center justify-between">

                                <!-- LEFT: ICON + TITLE -->
                                <div class="flex items-center gap-3">

                                    <!-- ICON BADGE -->
                                    <div class="w-10 h-10 flex items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-md">

                                        <i class="fa-solid"
                                            :class="{
                                                'fa-store': step === 0,
                                                'fa-user': step === 1,
                                                'fa-lock': step === 2,
                                                'fa-users': step === 3
                                            }">
                                        </i>

                                    </div>

                                    <!-- TITLE -->
                                    <div>
                                        <h2 class="text-lg font-semibold text-gray-800 dark:text-white leading-tight"
                                            x-text="steps[step] === 'Usaha' ? 'Identitas Usaha' : steps[step] === 'Owner' ? 'Identitas Owner' : steps[step] === 'Akun' ? 'Akun Owner' : 'Tambah Kasir (Opsional)'">
                                        </h2>

                                        <!-- STEP INFO -->
                                        <p class="text-xs text-gray-400 mt-0.5">
                                            Step <span x-text="step + 1"></span> dari <span x-text="steps.length"></span>
                                        </p>
                                    </div>

                                </div>

                                <!-- RIGHT: MINI BADGE -->
                                <span class="text-[11px] px-2.5 py-1 rounded-full bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-300 font-medium">

                                    <span x-text="steps[step]"></span>

                                </span>

                            </div>

                            <!-- DESCRIPTION -->
                            <p class="text-sm text-gray-500 dark:text-gray-400 pl-[52px]">
                                Isi data dengan benar untuk melanjutkan proses
                            </p>

                        </div>

                        <!-- STAGE CONTENT -->
                        <div class="min-h-[260px]">

                            <!-- STAGE 1 -->
                            <div x-show="step === 0" data-step="0" x-cloak class="space-y-4">

                                <div class="grid grid-cols-2 gap-4">

                                    <div class="col-span-2">
                                        <label class="label">Nama Usaha</label>
                                        <input x-model="form.business_name" x-ref="businessName" name="business[name]" class="input-modern">
                                    </div>

                                    <div
                                        x-data="phoneField()"
                                        x-modelable="modelValue"
                                        x-model="form.business_phone">

                                        <label class="label">No HP</label>

                                        <div class="flex items-center border rounded-xl overflow-hidden focus-within:ring-2 focus-within:ring-blue-500">

                                            <!-- PREFIX -->
                                            <span class="px-3 text-gray-500 text-sm border-r">
                                                +62
                                            </span>

                                            <!-- INPUT -->
                                            <input
                                                type="tel"
                                                x-model="modelValue"
                                                @input="touched = true"
                                                @blur="touched = true"
                                                name="business[phone]"
                                                placeholder="812xxxxxxx"
                                                class="w-full px-3 py-3 outline-none text-sm bg-transparent dark:text-white">

                                        </div>

                                        <!-- ERROR MESSAGE -->
                                        <p x-show="touched && !valid" x-cloak
                                            class="text-red-500 text-xs mt-1 pl-1">
                                            Nomor tidak valid (contoh: 812xxxxxxx)
                                        </p>

                                    </div>

                                    <div>
                                        <label class="label">Kategori</label>

                                        <select
                                            x-model="form.business_category"
                                            name="business[category]"
                                            class="input-modern cursor-pointer">

                                            <option value="">-- Pilih Kategori --</option>

                                            <option value="cafe">Cafe</option>
                                            <option value="resto">Restoran</option>
                                            <option value="coffee_shop">Coffee Shop</option>
                                            <option value="bakery">Bakery</option>
                                            <option value="bar">Bar</option>
                                            <option value="other">Lainnya</option>

                                        </select>

                                        <input
                                            x-show="form.business_category === 'other'"
                                            x-model="form.business_category_other"
                                            name="business[category_other]"
                                            placeholder="Masukkan kategori usaha"
                                            class="input-modern mt-2">
                                    </div>

                                    <div class="col-span-2">
                                        <label class="label">Alamat</label>

                                        <div class="relative">

                                            <input
                                                type="text"
                                                x-model="form.business_address"
                                                @input.debounce.500ms="searchAddress($event.target.value)"
                                                name="business[address]"
                                                class="input-modern"
                                                placeholder="Ketik alamat usaha...">

                                            <!-- LOADING -->
                                            <div x-show="isSearchingAddress"
                                                class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400">
                                                mencari...
                                            </div>

                                            <!-- DROPDOWN -->
                                            <div
                                                x-show="addressSuggestions.length"
                                                x-cloak
                                                class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-800 border rounded-xl shadow-lg max-h-60 overflow-auto">

                                                <template x-for="item in addressSuggestions" :key="item.label">

                                                    <div
                                                        @click="selectAddress(item)"
                                                        class="px-4 py-2 text-sm hover:bg-blue-50 dark:hover:bg-gray-700 cursor-pointer border-b last:border-none">

                                                        <span x-text="item.label"></span>

                                                    </div>

                                                </template>

                                            </div>

                                        </div>

                                        <input type="hidden" name="business[lat]" :value="form.latitude">
                                        <input type="hidden" name="business[lng]" :value="form.longitude">

                                        <div x-show="!isSearchingAddress && addressSuggestions.length === 0 && form.business_address.length >= 3"
                                            class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-800 border rounded-xl shadow-lg p-3 text-sm text-gray-400">
                                            Alamat tidak ditemukan
                                        </div>
                                    </div>

                                </div>

                            </div>

                            <!-- STAGE 2 -->
                            <div x-show="step === 1" data-step="1" x-cloak class="space-y-4">

                                <div class="grid grid-cols-2 gap-4">

                                    <div class="col-span-2">
                                        <label class="label">Nama Owner</label>
                                        <input x-model="form.owner_name" x-ref="ownerName" name="owner[name]" class="input-modern">
                                    </div>

                                    <div>
                                        <label class="label">Email</label>

                                        <input
                                            x-model="form.owner_email"
                                            @input="
                                                form.owner_email = form.owner_email.trim();
                                                generateEmailSuggestion()"
                                            @blur="form.owner_email = form.owner_email.toLowerCase().trim()"
                                            @keydown.space.prevent
                                            @keydown.tab="
                                                if (emailSuggestion) {
                                                    $event.preventDefault();
                                                    applyEmailSuggestion();
                                                }"
                                            :class="{
                                                    'border-red-500 focus:ring-red-500': form.owner_email && !isValidEmail(form.owner_email),
                                                    'border-green-500 focus:ring-green-500': isValidEmail(form.owner_email)
                                                }"
                                            name="owner[email]"
                                            class="input-modern">

                                        <!-- EMAIL SUGGESTION -->
                                        <p x-show="emailSuggestion && form.owner_email.includes('@')"
                                            x-cloak
                                            class="text-xs mt-1 text-gray-400 flex items-center gap-1">

                                            <span>Tekan</span>
                                            <kbd class="px-1.5 py-0.5 bg-gray-200 dark:bg-gray-700 rounded text-[10px]">Tab</kbd>
                                            <span>→</span>

                                            <span class="text-gray-600 dark:text-gray-300">
                                                @<span x-text="emailSuggestion"></span>
                                            </span>
                                        </p>

                                        <!-- ERROR -->
                                        <p x-show="form.owner_email && !isValidEmail(form.owner_email)"
                                            x-cloak
                                            class="text-red-500 text-xs mt-1">
                                            Format email tidak valid
                                        </p>

                                        <!-- SUCCESS (opsional, tapi bagus UX) -->
                                        <p x-show="isValidEmail(form.owner_email)"
                                            x-cloak
                                            class="text-green-500 text-xs mt-1">
                                            Email valid
                                        </p>
                                    </div>

                                    <div
                                        x-data="phoneField()"
                                        x-modelable="modelValue"
                                        x-model="form.owner_phone">

                                        <label class="label">No HP</label>

                                        <div class="relative flex items-center border rounded-xl overflow-hidden focus-within:ring-2 focus-within:ring-blue-500">

                                            <!-- PREFIX -->
                                            <span class="px-3 text-gray-500 text-sm border-r">
                                                +62
                                            </span>

                                            <!-- INPUT -->
                                            <input
                                                type="tel"
                                                x-model="modelValue"
                                                @input="touched = true"
                                                @blur="touched = true"
                                                name="owner[phone]"
                                                placeholder="812xxxxxxx"
                                                class="w-full px-3 py-3 outline-none text-sm bg-transparent dark:text-white">

                                        </div>

                                        <p x-show="touched && !valid" x-cloak
                                            class="text-red-500 text-xs mt-1 pl-10">
                                            Nomor tidak valid
                                        </p>

                                    </div>

                                </div>

                            </div>

                            <!-- STAGE 3 -->
                            <div x-show="step === 2" data-step="2" x-cloak class="space-y-6">

                                <!-- USERNAME -->
                                <div class="space-y-1">
                                    <label class="label">Username</label>

                                    <div class="relative">
                                        <input
                                            x-model="form.username"
                                            x-ref="username"
                                            name="owner[username]"
                                            class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition">

                                        <i class="fa-solid fa-user absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                                    </div>
                                </div>

                                <!-- PASSWORD -->
                                <div class="space-y-2">

                                    <label class="label">Password</label>

                                    <div class="relative">

                                        <input
                                            :type="showPassword ? 'text' : 'password'"
                                            x-model="form.password"
                                            @input="form.password = form.password.trim()"
                                            name="owner[password]"
                                            :class="{
                                            'border-red-500 focus:ring-red-500': form.password && !isValidPassword(form.password),
                                            'border-green-500 focus:ring-green-500': isValidPassword(form.password)
                                        }"
                                            class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-3 pr-10 text-sm focus:ring-2 outline-none transition">

                                        <!-- TOGGLE -->
                                        <button type="button"
                                            @click="showPassword=!showPassword"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-blue-500 transition">

                                            <i class="fa-solid"
                                                :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                                        </button>
                                    </div>

                                    <!-- STRENGTH BAR -->
                                    <div x-show="form.password" x-cloak class="space-y-1">

                                        <div class="flex items-center justify-between text-xs">
                                            <span class="text-gray-500">Kekuatan Password</span>
                                            <span :class="{
                                            'text-red-500': passwordScore() <= 2,
                                            'text-yellow-500': passwordScore() === 3,
                                            'text-blue-500': passwordScore() === 4,
                                            'text-green-500': passwordScore() === 5
                                        }"
                                                x-text="passwordStrengthLabel()"></span>
                                        </div>

                                        <div class="w-full h-2 bg-gray-200 dark:bg-gray-600 rounded-full overflow-hidden">
                                            <div
                                                class="h-full transition-all duration-300"
                                                :class="passwordStrengthColor()"
                                                :style="`width: ${(passwordScore() / 5) * 100}%`">
                                            </div>
                                        </div>

                                    </div>

                                    <!-- INLINE PASSWORD CHECKLIST -->
                                    <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs mt-2">

                                        <span class="flex items-center gap-1"
                                            :class="hasMinLength() ? 'text-green-500' : 'text-gray-400'">
                                            <i class="fa-solid text-[10px]"
                                                :class="hasMinLength() ? 'fa-circle-check' : 'fa-circle'"></i>
                                            8+ karakter
                                        </span>

                                        <span class="flex items-center gap-1"
                                            :class="hasLowercase() ? 'text-green-500' : 'text-gray-400'">
                                            <i class="fa-solid text-[10px]"
                                                :class="hasLowercase() ? 'fa-circle-check' : 'fa-circle'"></i>
                                            a-z
                                        </span>

                                        <span class="flex items-center gap-1"
                                            :class="hasUppercase() ? 'text-green-500' : 'text-gray-400'">
                                            <i class="fa-solid text-[10px]"
                                                :class="hasUppercase() ? 'fa-circle-check' : 'fa-circle'"></i>
                                            A-Z
                                        </span>

                                        <span class="flex items-center gap-1"
                                            :class="hasNumber() ? 'text-green-500' : 'text-gray-400'">
                                            <i class="fa-solid text-[10px]"
                                                :class="hasNumber() ? 'fa-circle-check' : 'fa-circle'"></i>
                                            0-9
                                        </span>

                                        <span class="flex items-center gap-1"
                                            :class="hasSymbol() ? 'text-green-500' : 'text-gray-400'">
                                            <i class="fa-solid text-[10px]"
                                                :class="hasSymbol() ? 'fa-circle-check' : 'fa-circle'"></i>
                                            simbol
                                        </span>

                                    </div>

                                    <!-- FEEDBACK -->
                                    <p x-show="form.password && !isValidPassword(form.password)"
                                        x-cloak
                                        class="text-red-500 text-xs">
                                        Password belum memenuhi semua kriteria
                                    </p>

                                    <p x-show="isValidPassword(form.password)"
                                        x-cloak
                                        class="text-green-500 text-xs">
                                        Password kuat
                                    </p>
                                </div>

                                <!-- CONFIRM PASSWORD -->
                                <div class="space-y-1">

                                    <label class="label">Konfirmasi Password</label>

                                    <div class="relative">

                                        <input
                                            type="password"
                                            x-model="form.confirmPassword"
                                            @input="form.confirmPassword = form.confirmPassword.trim()"
                                            name="owner[confirmPassword]"
                                            :class="{
                                            'border-red-500 focus:ring-red-500': form.confirmPassword && !isPasswordMatch(),
                                            'border-green-500 focus:ring-green-500': isPasswordMatch()
                                        }"
                                            class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-3 pr-10 text-sm focus:ring-2 outline-none transition">

                                        <!-- ICON STATUS -->
                                        <i class="fa-solid absolute right-3 top-1/2 -translate-y-1/2 text-sm"
                                            :class="isPasswordMatch() ? 'fa-check text-green-500' : 'fa-xmark text-red-400'">
                                        </i>
                                    </div>

                                    <!-- FEEDBACK -->
                                    <p x-show="form.confirm && !isPasswordMatch()"
                                        x-cloak
                                        class="text-red-500 text-xs">
                                        Password tidak cocok
                                    </p>

                                    <p x-show="isPasswordMatch() && form.confirm"
                                        x-cloak
                                        class="text-green-500 text-xs">
                                        Password cocok
                                    </p>

                                </div>

                            </div>

                            <!-- STAGE 4 -->
                            <div x-show="step === 3" data-step="3" x-cloak class="space-y-5">

                                <!-- KASIR FORM -->
                                <template x-if="form.cashiers.length === 1">
                                    <div class="rounded-2xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 p-5 shadow-sm space-y-4">

                                        <!-- HEADER -->
                                        <div class="flex items-center justify-between">
                                            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200 flex items-center gap-2">
                                                <i class="fa-solid fa-user text-blue-500"></i>
                                                Data Kasir
                                            </h3>

                                            <button
                                                type="button"
                                                @click="form.cashiers = []"
                                                class="text-red-500 hover:text-red-600 text-xs flex items-center gap-1 cursor-pointer">
                                                <i class="fa-solid fa-trash"></i>
                                                Hapus
                                            </button>
                                        </div>

                                        <!-- GRID -->
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                                            <!-- NAMA -->
                                            <div class="space-y-1">
                                                <label class="text-xs text-gray-500">Nama Kasir</label>
                                                <input
                                                    x-model="form.cashiers[0].name"
                                                    name="cashiers[0][name]"
                                                    class="input-modern">
                                            </div>

                                            <!-- USERNAME -->
                                            <div class="space-y-1">
                                                <label class="text-xs text-gray-500">Username</label>
                                                <input
                                                    x-model="form.cashiers[0].username"
                                                    name="cashiers[0][username]"
                                                    class="input-modern">
                                            </div>

                                            <!-- PASSWORD -->
                                            <div class="space-y-2">
                                                <label class="text-xs text-gray-500">Password</label>

                                                <input
                                                    type="password"
                                                    x-model="form.cashiers[0].password"
                                                    name="cashiers[0][password]"
                                                    :class="{
                                                    'border-red-500': form.cashiers[0].password && !isValidPassword(form.cashiers[0].password),
                                                    'border-green-500': isValidPassword(form.cashiers[0].password)
                                                }"
                                                    class="input-modern">

                                                <!-- INLINE CHECKLIST -->
                                                <div class="flex flex-wrap gap-3 text-xs">

                                                    <span :class="hasMinLengthKasir() ? 'text-green-500' : 'text-gray-400'">8+</span>
                                                    <span :class="hasLowercaseKasir() ? 'text-green-500' : 'text-gray-400'">a-z</span>
                                                    <span :class="hasUppercaseKasir() ? 'text-green-500' : 'text-gray-400'">A-Z</span>
                                                    <span :class="hasNumberKasir() ? 'text-green-500' : 'text-gray-400'">0-9</span>
                                                    <span :class="hasSymbolKasir() ? 'text-green-500' : 'text-gray-400'">@#!</span>

                                                </div>
                                            </div>

                                            <!-- CONFIRM -->
                                            <div class="space-y-1">
                                                <label class="text-xs text-gray-500">Konfirmasi Password</label>

                                                <input
                                                    type="password"
                                                    x-model="form.cashiers[0].confirmPassword"
                                                    name="cashiers[0][confirmPassword]"
                                                    :class="{
                                                    'border-red-500': form.cashiers[0].confirmPassword && !isPasswordMatchKasir(),
                                                    'border-green-500': isPasswordMatchKasir()
                                                }"
                                                    class="input-modern">

                                                <p x-show="form.cashiers[0].confirmPassword && !isPasswordMatchKasir()"
                                                    class="text-red-500 text-xs">
                                                    Password tidak cocok
                                                </p>
                                            </div>

                                        </div>

                                    </div>

                                </template>

                                <!-- EMPTY STATE -->
                                <div x-show="form.cashiers.length === 0"
                                    class="text-center text-sm text-gray-400 py-6 border border-dashed rounded-xl">

                                    Tambahkan 1 kasir (opsional)
                                </div>

                                <!-- BUTTON (HANYA MUNCUL JIKA BELUM ADA) -->
                                <button
                                    type="button"
                                    x-show="form.cashiers.length === 0"
                                    @click="addKasir"
                                    class="w-full flex items-center justify-center gap-2 py-3 rounded-xl border border-dashed border-blue-400 text-blue-600 hover:bg-blue-50 dark:hover:bg-gray-700 transition text-sm font-medium cursor-pointer">

                                    <i class="fa-solid fa-plus"></i>
                                    Tambah Kasir
                                </button>

                            </div>

                        </div>

                        <!-- NAVIGATION -->
                        <div class="flex justify-between items-center mt-6">

                            <button
                                type="button"
                                x-show="step > 0"
                                @click="step--"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition shadow-sm cursor-pointer">

                                <i class="fa-solid fa-arrow-left text-xs"></i>

                                <span>Kembali</span>

                            </button>

                            <button
                                type="button"
                                @click="nextStep"
                                :disabled="isLoading || !canProceed()"
                                class="inline-flex items-center justify-center gap-2 px-6 py-2 rounded-xl text-sm font-semibold bg-green-600 hover:bg-green-700 text-white shadow-lg transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed active:scale-95 cursor-pointer">

                                <!-- TEXT -->
                                <span x-show="!isLoading"
                                    x-text="step === 3 ? 'Selesai' : 'Lanjut'">
                                </span>

                                <!-- LOADING -->
                                <span x-show="isLoading" x-cloak class="flex items-center gap-2">

                                    <svg class="animate-spin h-4 w-4 text-white"
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

                        </div>

                    </div>

                </form>

                <!-- LOGIN LINK -->
                <p class="text-center mt-6 text-gray-600 dark:text-gray-300 text-sm">
                    Sudah punya akun?
                    <button
                        onclick="window.location.href='/rkd-cafe/resources/views/auth/login.php'"
                        class="text-blue-500 hover:underline cursor-pointer">
                        Masuk
                    </button>
                </p>

            </div>

        </div>

    </div>

    <?php require '../../components/toast.php'; ?>

    <?php if (isset($_SESSION['toast'])): ?>
        <script>
            window.toastData = {
                type: "<?= $_SESSION['toast']['type'] ?>",
                message: "<?= htmlspecialchars($_SESSION['toast']['message']) ?>"
            };
        </script>
    <?php unset($_SESSION['toast']);
    endif; ?>

    <script src="/rkd-cafe/public/assets/js/toast.js?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'] . '/rkd-cafe/public/assets/js/toast.js') ?>"></script>
    <script src="/rkd-cafe/public/assets/js/register.js?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'] . '/rkd-cafe/public/assets/js/register.js') ?>"></script>

</body>

</html>