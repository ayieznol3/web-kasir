<?php
// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: index.php?page=dashboard');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Aplikasi Kasir</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#6366f1',
                        dark: '#1e293b'
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="min-h-screen bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center p-4">

    <div class="w-full max-w-md">
        
        <!-- Logo & Judul -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-primary rounded-2xl shadow-lg mb-4">
                <i class="fas fa-store text-3xl text-white"></i>
            </div>
            <h1 class="text-3xl font-bold text-dark">Kasir App</h1>
            <p class="text-gray-500 mt-2">Silakan login untuk melanjutkan</p>
        </div>

        <!-- Card Form -->
        <div class="bg-white rounded-2xl shadow-lg p-8">
            
            <!-- Pesan Error -->
            <?php if (isset($_SESSION['error'])): ?>
            <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg flex items-start gap-3">
                <i class="fas fa-exclamation-circle mt-0.5"></i>
                <span><?= $_SESSION['error'] ?></span>
            </div>
            <?php unset($_SESSION['error']); endif; ?>

            <?php if (isset($_SESSION['sukses'])): ?>
            <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg flex items-start gap-3">
                <i class="fas fa-check-circle mt-0.5"></i>
                <span><?= $_SESSION['sukses'] ?></span>
            </div>
            <?php unset($_SESSION['sukses']); endif; ?>

            <!-- Form -->
            <form action="proses/login_proses.php" method="POST" class="space-y-5">
                
                <!-- Username -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-user text-gray-400 mr-1"></i> Username
                    </label>
                    <input 
                        type="text" 
                        name="username" 
                        required 
                        autofocus
                        placeholder="Masukkan username"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary transition outline-none"
                    >
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-lock text-gray-400 mr-1"></i> Password
                    </label>
                    <div class="relative">
                        <input 
                            type="password" 
                            name="password" 
                            id="password"
                            required 
                            placeholder="Masukkan password"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary transition outline-none pr-12"
                        >
                        <button type="button" onclick="togglePassword()" class="absolute right-3 top-3 text-gray-400 hover:text-gray-600">
                            <i id="eye-icon" class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center">
                    <input type="checkbox" id="remember" class="w-4 h-4 text-primary rounded border-gray-300 focus:ring-primary">
                    <label for="remember" class="ml-2 text-sm text-gray-600">Ingat saya</label>
                </div>

                <!-- Submit -->
                <button type="submit" name="login" class="w-full bg-primary text-white py-3 rounded-xl font-semibold hover:bg-indigo-700 transition duration-200 shadow-lg shadow-indigo-200">
                    <i class="fas fa-sign-in-alt mr-2"></i> Login
                </button>

            </form>

            <!-- Info Login (untuk testing) -->
            <div class="mt-6 p-4 bg-gray-50 rounded-xl border border-gray-200">
                <p class="text-xs text-gray-500 font-semibold mb-2">ℹ️ Info Login Testing:</p>
                <div class="text-xs text-gray-500 space-y-1">
                    <p><strong>Admin:</strong> admin / password</p>
                    <p><strong>Kasir:</strong> kasir1 / password</p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <p class="text-center text-xs text-gray-400 mt-6">
            &copy; <?= date('Y') ?> Aplikasi Kasir v1.0
        </p>

    </div>

    <script>
        // Toggle password visibility
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('eye-icon');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Enter key to submit
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                document.querySelector('form').submit();
            }
        });
    </script>

</body>
</html>