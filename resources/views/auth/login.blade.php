<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ONG Alunco</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        Alunco: {
                            blue: '#205099',
                            green: '#AFDD83',
                            gray: '#5E5E5E',
                        }
                    },
                    fontFamily: {
                        sans: ['Roboto', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 font-sans text-Alunco-gray h-screen flex flex-col justify-center items-center">

    <div class="w-full max-w-md bg-white shadow-xl rounded-xl p-8 border border-gray-100">
        <!-- Logo u ONG Marca Placeholder -->
        <div class="flex justify-center mb-6">
            <!-- Si tienes el logo, puedes sustituir esto por img src asset('images/logo/alumco-full.svg') -->
            <div class="h-16 w-16 bg-Alunco-blue rounded-full flex items-center justify-center text-white font-bold text-2xl">
                OA
            </div>
        </div>

        <h1 class="text-2xl font-bold text-center text-Alunco-blue mb-8">ONG Alunco - Portal de Capacitaciones</h1>

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <!-- Email Address -->
            <div>
                <label for="email" class="block text-sm font-semibold text-gray-700 mb-1">Correo Electrónico</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-Alunco-blue focus:border-Alunco-blue outline-none transition-colors @error('email') border-red-500 @enderror">
                
                @error('email')
                    <p class="text-sm text-red-600 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-sm font-semibold text-gray-700 mb-1">Contraseña</label>
                <input id="password" type="password" name="password" required autocomplete="current-password"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-Alunco-blue focus:border-Alunco-blue outline-none transition-colors @error('password') border-red-500 @enderror">
                
                @error('password')
                    <p class="text-sm text-red-600 mt-1 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between">
                <label for="remember_me" class="inline-flex items-center cursor-pointer">
                    <input id="remember_me" type="checkbox" name="remember" class="w-4 h-4 text-Alunco-blue border-gray-300 rounded focus:ring-Alunco-blue accent-Alunco-blue">
                    <span class="ml-2 text-sm text-gray-600">Recuérdame</span>
                </label>
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 px-4 rounded-lg shadow transition-colors focus:ring-4 focus:ring-blue-300 outline-none">
                    Ingresar
                </button>
            </div>
        </form>
    </div>

    <!-- Marca de agua en el fondo si se desea, alineado con el diseño LMS -->
    <div class="mt-8 text-center text-sm text-gray-400">
        &copy; {{ date('Y') }} ONG Alunco. Todos los derechos reservados.
    </div>

</body>
</html>
