<?php

define('APP_INIT', true);

require $_SERVER['DOCUMENT_ROOT'] . '/rkd-cafe/middleware/AuthMiddleware.php';
require_once __DIR__ . '/../../../config/database.php';

$pdo = getPDO();
$categories = $pdo->query("SELECT id, name FROM categories WHERE status='active'")->fetchAll();
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Menu</title>

    <!-- Tailwind CSS -->
    <link href="/rkd-cafe/public/assets/css/output.css" rel="stylesheet">

    <!-- FontAwesome -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="bg-gray-100 dark:bg-gray-900 flex items-center justify-center min-h-screen">

    <div
        x-data="menuCreate()"
        class="w-full max-w-lg bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-6">

        <h2 class="text-xl font-semibold mb-4">Add New Menu</h2>

        <!-- ERROR -->
        <div x-show="error"
            class="mb-4 text-sm text-red-500 bg-red-50 px-3 py-2 rounded">
            <span x-text="error"></span>
        </div>

        <div class="space-y-4">

            <!-- NAME -->
            <div>
                <label class="text-sm">Menu Name</label>
                <input
                    type="text"
                    x-model="form.name"
                    class="w-full border rounded-lg px-3 py-2 mt-1 dark:bg-gray-700"
                    placeholder="Example: Cappuccino">
            </div>

            <!-- PRICE -->
            <div>
                <label class="text-sm">Price</label>
                <input
                    type="text"
                    x-model="form.price"
                    @input="formatInputPrice"
                    class="w-full border rounded-lg px-3 py-2 mt-1 dark:bg-gray-700">
            </div>

            <!-- COST -->
            <div>
                <label class="text-sm">Cost</label>
                <input
                    type="number"
                    x-model="form.cost"
                    class="w-full border rounded-lg px-3 py-2 mt-1 dark:bg-gray-700">
            </div>

            <!-- STOCK -->
            <div>
                <label class="text-sm">Stock</label>
                <input
                    type="number"
                    x-model="form.stock"
                    class="w-full border rounded-lg px-3 py-2 mt-1 dark:bg-gray-700">
            </div>

            <!-- CATEGORY -->
            <div>
                <label class="text-sm">Category</label>
                <select
                    x-model="form.category_id"
                    class="w-full border rounded-lg px-3 py-2 mt-1 dark:bg-gray-700">

                    <option value="">Select category</option>

                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>">
                            <?= $cat['name'] ?>
                        </option>
                    <?php endforeach; ?>

                </select>
            </div>

            <!-- STATUS -->
            <div>
                <label class="text-sm">Status</label>
                <select
                    x-model="form.status"
                    class="w-full border rounded-lg px-3 py-2 mt-1 dark:bg-gray-700">

                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <!-- ACTION -->
            <div class="flex justify-end gap-2 pt-4">

                <button
                    @click="goBack()"
                    class="px-4 py-2 rounded-lg bg-gray-200 dark:bg-gray-700 cursor-pointer">
                    Cancel
                </button>

                <button
                    @click="submit()"
                    :disabled="loading"
                    class="px-4 py-2 rounded-lg bg-amber-500 text-white flex items-center gap-2 disabled:opacity-50 cursor-pointer">

                    <i x-show="!loading" class="fa-solid fa-save"></i>
                    <i x-show="loading" class="fa-solid fa-spinner fa-spin"></i>

                    Save
                </button>

            </div>

        </div>
    </div>

    <script>
        function menuCreate() {
            return {

                form: {
                    name: '',
                    price: '',
                    cost: '',
                    stock: '',
                    category_id: '',
                    status: 'active'
                },

                loading: false,
                error: '',

                validate() {
                    if (!this.form.name) return "Nama menu wajib diisi";
                    if (!this.form.price || this.form.price <= 0) return "Harga tidak valid";
                    if (!this.form.category_id) return "Kategori wajib dipilih";
                    return null;
                },

                async submit() {

                    this.error = '';

                    const err = this.validate();
                    if (err) {
                        this.error = err;
                        return;
                    }

                    this.loading = true;

                    try {

                        // 🔥 LETAKKAN DI SINI
                        const cleanPrice = this.form.price
                            .toString()
                            .replace(/\./g, '')
                            .replace(/,/g, '.');

                        const payload = {
                            ...this.form,
                            price: cleanPrice
                        };

                        const res = await fetch('/rkd-cafe/api/menu/menu_store.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(payload)
                        });

                        const json = await res.json();

                        if (!json.success) throw new Error(json.message);

                        window.location.href = '/rkd-cafe/pages/admin/menu_list.php';

                    } catch (e) {
                        this.error = e.message;
                    } finally {
                        this.loading = false;
                    }
                },

                formatInputPrice(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    this.form.price = new Intl.NumberFormat('id-ID').format(value);
                },

                goBack() {
                    window.history.back();
                }
            }
        }
    </script>

</body>

</html>