<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — Ayka Originals</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-[#0B132B] flex items-center justify-center">
  <div class="w-full max-w-md mx-4">
    <div class="text-center mb-8">
      <h1 style="font-family:'Syne',sans-serif" class="text-white text-3xl font-bold">Ayka Originals</h1>
      <p class="text-[#C9A96E] text-xs tracking-widest uppercase mt-1">Production Management</p>
    </div>
    <div class="bg-white rounded-2xl p-8">
      <h2 style="font-family:'Syne',sans-serif" class="text-xl font-semibold mb-6 text-[#0B132B]">Sign In</h2>
      @if($errors->any())
        <div class="mb-4 bg-red-50 text-red-700 text-sm px-4 py-3 rounded-lg">{{ $errors->first() }}</div>
      @endif
      <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf
        <div>
          <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Email</label>
          <input type="email" name="email" value="{{ old('email') }}" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm outline-none focus:border-[#0B132B] transition-colors" placeholder="admin@aykaoriginals.com" required>
        </div>
        <div>
          <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Password</label>
          <input type="password" name="password" class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm outline-none focus:border-[#0B132B] transition-colors" required>
        </div>
        <div class="flex items-center justify-between pt-1">
          <label class="flex items-center gap-2 text-sm text-gray-500 cursor-pointer">
            <input type="checkbox" name="remember" class="rounded"> Remember me
          </label>
        </div>
        <button type="submit" class="w-full bg-[#C9A96E] text-[#0B132B] font-semibold py-2.5 rounded-lg hover:bg-[#E8C882] transition-colors text-sm">
          Sign In
        </button>
      </form>
    </div>
  </div>
</body>
</html>
