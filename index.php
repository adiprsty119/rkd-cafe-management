<?php require 'middleware/auth.php'; ?>

<!DOCTYPE html>
<html lang="en"
    x-data="{ dark: localStorage.theme === 'dark', loading:false }"
    x-init="$watch('dark', val => {localStorage.theme = val ? 'dark' : 'light'; document.documentElement.classList.toggle('dark', val)})"
    :class="{ 'dark': dark }">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>RKD Cafe Management</title>

    <!-- Tailwind CSS -->
    <link href="assets/css/output.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

</head>

<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-white transition-colors duration-300">

    <div class="flex h-screen">

        <!-- SIDEBAR -->
        <aside class="w-64 bg-white text-gray-800 flex flex-col dark:bg-gray-800">
            <?php require 'components/sidebar.php'; ?>
        </aside>

        <!-- MAIN CONTENT -->
        <div class="flex-1 flex flex-col">

            <!-- NAVBAR -->
            <div class="p-4 border-t border-gray-700">
                <?php require 'components/navbar.php'; ?>
            </div>

            <!-- DASHBOARD CONTENT -->
            <main class="p-6 space-y-6 overflow-y-auto">

                <!-- STATISTIC CARDS -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-14">

                    <div class="bg-white p-6 rounded-xl shadow-2xl cursor-pointer dark:bg-gray-800">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-gray-500 text-sm">Total Sales</p>
                                <h2 class="text-2xl font-bold">Rp 8.2 jt</h2>
                            </div>
                            <i class="fa-solid fa-money-bill-wave text-green-500 text-2xl"></i>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-2xl cursor-pointer dark:bg-gray-800">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-gray-500 text-sm">Orders Today</p>
                                <h2 class="text-2xl font-bold">124</h2>
                            </div>
                            <i class="fa-solid fa-receipt text-blue-500 text-2xl"></i>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-2xl cursor-pointer dark:bg-gray-800">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-gray-500 text-sm">Menu Items</p>
                                <h2 class="text-2xl font-bold">36</h2>
                            </div>
                            <i class="fa-solid fa-utensils text-yellow-500 text-2xl"></i>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-2xl cursor-pointer dark:bg-gray-800">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-gray-500 text-sm">Customers</p>
                                <h2 class="text-2xl font-bold">58</h2>
                            </div>
                            <i class="fa-solid fa-user-group text-purple-500 text-2xl"></i>
                        </div>
                    </div>

                </div>


                <!-- RECENT ORDERS TABLE -->
                <div class="bg-white rounded-xl shadow-xl dark:bg-gray-700">

                    <div class="p-4 border-b font-semibold dark:bg-gray-800">
                        Recent Orders
                    </div>

                    <table class="w-full text-sm">

                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="p-3 text-left">Order ID</th>
                                <th class="p-3 text-left">Customer</th>
                                <th class="p-3 text-left">Menu</th>
                                <th class="p-3 text-left">Total</th>
                                <th class="p-3 text-left">Status</th>
                            </tr>
                        </thead>

                        <tbody>

                            <tr class="border-t">
                                <td class="p-3">#ORD001</td>
                                <td class="p-3">Andi</td>
                                <td class="p-3">Latte + Croissant</td>
                                <td class="p-3">Rp 45.000</td>
                                <td class="p-3">
                                    <span class="bg-green-100 text-green-600 px-2 py-1 rounded text-xs">
                                        Paid
                                    </span>
                                </td>
                            </tr>

                            <tr class="border-t">
                                <td class="p-3">#ORD002</td>
                                <td class="p-3">Budi</td>
                                <td class="p-3">Espresso</td>
                                <td class="p-3">Rp 20.000</td>
                                <td class="p-3">
                                    <span class="bg-yellow-100 text-yellow-600 px-2 py-1 rounded text-xs">
                                        Pending
                                    </span>
                                </td>
                            </tr>

                        </tbody>

                    </table>

                </div>

            </main>

        </div>

    </div>

</body>

</html>