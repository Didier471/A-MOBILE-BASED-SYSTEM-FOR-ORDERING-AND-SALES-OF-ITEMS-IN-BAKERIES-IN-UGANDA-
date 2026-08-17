<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Hot Loaf - Login</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-100 flex items-center justify-center">

    <div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-8">

        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">
                Hot Loaf
            </h1>

            <p class="text-gray-500 mt-2">
                Bakery Management System
            </p>
        </div>

        <form id="loginForm" class="space-y-5">

            <div>
                <label class="block text-sm font-medium mb-2">
                    Email
                </label>

                <input
                    type="email"
                    id="email"
                    required
                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-gray-900"
                    placeholder="admin@hotloaf.com"
                >
            </div>

            <div>
                <label class="block text-sm font-medium mb-2">
                    Password
                </label>

                <input
                    type="password"
                    id="password"
                    required
                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-gray-900"
                    placeholder="Enter your password"
                >
            </div>

            <p id="loginError" class="hidden text-sm text-red-600"></p>

            <button
                type="submit"
                class="w-full bg-gray-900 text-white rounded-lg px-4 py-3 font-medium hover:bg-gray-800"
            >
                Login
            </button>

        </form>

    </div>

</body>
</html>