<button type="submit"
    class="group relative w-full overflow-hidden rounded-xl bg-Alumco-blue px-6 py-4 text-lg font-bold text-white shadow-lg shadow-Alumco-blue/20 transition-all duration-200 hover:shadow-xl hover:shadow-Alumco-blue/30 active:scale-[0.98] focus:outline-none focus-visible:ring-2 focus-visible:ring-Alumco-blue focus-visible:ring-offset-2">
    <div class="absolute inset-0 bg-gradient-to-tr from-black/10 to-transparent opacity-0 transition-opacity group-hover:opacity-100"></div>
    <span class="relative flex items-center justify-center gap-2">
        {{ $slot }}
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5 transition-transform group-hover:translate-x-1">
            <path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.638L10.23 5.29a.75.75 0 1 1 1.04-1.08l5.5 5.25a.75.75 0 0 1 0 1.08l-5.5 5.25a.75.75 0 1 1-1.04-1.08l4.158-3.96H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd" />
        </svg>
    </span>
</button>
