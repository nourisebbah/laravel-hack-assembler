<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hack Assembler</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white min-h-screen flex items-center justify-center px-4">
    <div class="max-w-5xl w-full">
        <h1 class="text-3xl font-bold mb-6 text-center">Hack Assembler</h1>

        <div class="grid md:grid-cols-2 gap-4">
          
            <form action="{{ route('assemble') }}" method="POST" class="bg-gray-800 p-6 rounded-2xl shadow-xl space-y-4">
                @csrf

                <label for="hackCode" class="block text-lg font-medium">Enter Hack Assembly Code:</label>

              
                @error('hackCode')
                    <div class="bg-red-600 text-white p-2 rounded font-semibold">
                        {{ $message }}
                    </div>
                @enderror

                <textarea 
                    id="hackCode" 
                    name="hackCode" 
                    rows="15"
                    class="w-full p-3 rounded-lg bg-gray-700 text-white resize-y outline-none focus:ring-2 focus:ring-blue-500 @error('hackCode') border-2 border-red-500 @enderror"
                    placeholder="Write your Hack assembly here line by line"
                >{{ old('hackCode') }}</textarea>

                <button 
                    type="submit" 
                    class="w-full bg-blue-600 hover:bg-blue-700 font-semibold py-2 rounded-lg transition">
                    Assemble Code
                </button>
            </form>

           
            @if(session('binary'))
                <div class="bg-gray-800 p-6 rounded-2xl shadow-xl">
                    <h2 class="text-xl font-semibold mb-2">Binary Output:</h2>
                    <pre class="bg-gray-700 p-4 rounded-lg text-green-400 h-full overflow-auto whitespace-pre-wrap">{{ session('binary') }}</pre>
                </div>
            @endif
        </div>
    </div>
</body>
</html>
