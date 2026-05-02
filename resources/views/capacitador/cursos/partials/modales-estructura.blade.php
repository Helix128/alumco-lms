<!-- Modal Estructura: Nueva/Editar Sección -->
<div id="seccion-modal" class="fixed inset-0 bg-Alumco-gray/40 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden" aria-hidden="true">
    <div class="bg-white w-full max-w-md rounded-3xl shadow-xl overflow-hidden">
        <form id="seccion-form" method="POST" action="">
            @csrf
            <input type="hidden" id="seccion-method" name="_method" value="POST">
            <div class="p-8">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-12 h-12 rounded-full bg-Alumco-blue/10 text-Alumco-blue flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 15.75h16.5m-16.5-7.5h16.5m-16.5-3.75h16.5" /></svg>
                    </div>
                    <div>
                        <h3 id="seccion-modal-title" class="text-xl font-display font-black text-Alumco-blue">Nueva Sección</h3>
                        <p class="text-[10px] font-bold text-Alumco-gray/65 uppercase tracking-widest mt-1">Organiza tus módulos</p>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-black text-Alumco-blue/65 uppercase tracking-widest">Título de la sección</label>
                    <input type="text" id="seccion-titulo" name="titulo" required placeholder="Ej: Introducción, Etapa 1..."
                           class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3.5 text-Alumco-gray font-medium focus:ring-4 focus:ring-Alumco-blue/10 focus:border-Alumco-blue outline-none transition-all">
                </div>
            </div>

            <div class="p-6 border-t border-gray-50 bg-gray-50/30 flex items-center justify-end gap-3">
                <button type="button" onclick="closeSeccionModal()" class="px-6 py-2.5 text-sm font-bold text-Alumco-gray/65 hover:text-Alumco-coral transition-colors">Cancelar</button>
                <button type="submit" class="bg-Alumco-blue hover:bg-Alumco-blue/90 text-white font-display font-black text-xs uppercase tracking-[0.2em] py-4 px-8 rounded-xl shadow-lg shadow-Alumco-blue/20 transition-all active:scale-95">
                    Guardar Sección
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Duplicar Curso (Ya existía, lo movemos aquí para consistencia si es necesario) -->
<div id="duplicate-modal-backdrop" class="fixed inset-0 bg-Alumco-gray/40 backdrop-blur-sm z-50 opacity-0 pointer-events-none transition-opacity duration-300" aria-hidden="true"></div>
<div id="duplicate-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 opacity-0 pointer-events-none transition-all duration-300 scale-95" aria-hidden="true">
    <div class="bg-white w-full max-w-md rounded-3xl shadow-xl overflow-hidden">
        <form id="duplicate-form" method="POST" action="">
            @csrf
            <div class="p-8">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-12 h-12 rounded-full bg-Alumco-blue/10 text-Alumco-blue flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-display font-black text-Alumco-blue">Duplicar Curso</h3>
                        <p class="text-[10px] font-bold text-Alumco-gray/65 uppercase tracking-widest mt-1">Crear nueva versión</p>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-black text-Alumco-blue/65 uppercase tracking-widest">Título de la nueva versión</label>
                    <input type="text" id="duplicate-title" name="titulo" required
                           class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3.5 text-Alumco-gray font-medium focus:ring-4 focus:ring-Alumco-blue/10 focus:border-Alumco-blue outline-none transition-all">
                </div>
            </div>

            <div class="p-6 border-t border-gray-50 bg-gray-50/30 flex items-center justify-end gap-3">
                <button type="button" onclick="closeDuplicateModal()" class="px-6 py-2.5 text-sm font-bold text-Alumco-gray/65 hover:text-Alumco-coral transition-colors">Cancelar</button>
                <button type="submit" class="bg-Alumco-blue hover:bg-Alumco-blue/90 text-white font-display font-black text-xs uppercase tracking-[0.2em] py-4 px-8 rounded-xl shadow-lg shadow-Alumco-blue/20 transition-all active:scale-95">
                    Crear copia
                </button>
            </div>
        </form>
    </div>
</div>
