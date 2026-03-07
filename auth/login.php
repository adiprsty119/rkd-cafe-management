<?php session_start(); ?>

<!DOCTYPE html>
<html>

<head>

    <title>Login - RKD Cafe</title>

    <link href="../assets/css/output.css" rel="stylesheet">

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

</head>

<body class="bg-gray-100 flex items-center justify-center h-screen">

    <div class="bg-white p-8 rounded-xl shadow-md w-96">

        <h2 class="text-2xl font-bold mb-6 text-center">
            RKD Cafe Login
        </h2>

        <form action="process_login.php" method="POST">

            <div class="mb-4">
                <label class="block text-sm mb-1">Username</label>

                <input
                    type="text"
                    name="username"
                    required
                    class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-6">
                <label class="block text-sm mb-1">Password</label>

                <input
                    type="password"
                    name="password"
                    required
                    class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <button
                type="submit"
                class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700">

                Login

            </button>

        </form>

    </div>

</body>

</html>