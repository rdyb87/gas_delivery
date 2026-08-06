<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Gas Delivery System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
        <div class="p-8 bg-blue-600 text-white text-center">
            <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-truck text-3xl"></i>
            </div>
            <h1 class="text-2xl font-bold">Gas Delivery System</h1>
            <p class="text-blue-100 mt-2">Factory Management Portal</p>
        </div>

        <form method="POST" action="{{ route('login') }}" class="p-8 space-y-6">
            @csrf
            @if($errors->has('username'))
                <div class="p-3 bg-red-50 text-red-700 text-sm rounded-lg flex items-center gap-2">
                    <i class="fas fa-exclamation-circle"></i>{{ $errors->first('username') }}
                </div>
            @endif

            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">Username</label>
                <div class="relative">
                    <i class="fas fa-user absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="username" value="{{ old('username') }}" required placeholder="Enter username" class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>

            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">Password</label>
                <div class="relative">
                    <i class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="password" name="password" required placeholder="Enter your password" class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>

            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" name="remember" class="rounded"> Remember me
            </label>

            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg font-medium hover:bg-blue-700 transition-colors">
                Login
            </button>

            <div class="text-center text-sm text-gray-500">
                <p>Default Admin: admin / admin123</p>
            </div>
        </form>
    </div>
</body>
</html>