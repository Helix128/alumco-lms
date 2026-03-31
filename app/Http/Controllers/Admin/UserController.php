<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Estamento;
use App\Models\Sede;
use App\Notifications\SetupPasswordNotification;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['estamento', 'sede']);

        // Búsqueda simple
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $usuarios = $query->paginate(15)->withQueryString();
        
        $estamentos = Estamento::all();
        $sedes = Sede::all();

        return view('admin.usuarios.index', compact('usuarios', 'estamentos', 'sedes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->storeRules());

        $validated['password'] = Hash::make(Str::random(64));
        $validated['activo'] = true;

        $user = User::create($validated);

        $user->notify(new SetupPasswordNotification());

        return redirect()->route('admin.usuarios.index')->with('success', 'Usuario creado. Se envió un correo para que configure su contraseña.');
    }

    public function update(Request $request, User $user)
    {
        $this->abortIfUnauthorizedForDeveloper($user);

        $validated = $request->validate($this->updateRules($user));

        $user->update($validated);

        return redirect()->route('admin.usuarios.index')->with('success', 'Usuario actualizado exitosamente.');
    }

    public function toggleStatus(User $user)
    {
        if ($response = $this->redirectIfSelfAction($user, 'No puedes cambiar tu propio estado.')) {
            return $response;
        }

        $this->abortIfUnauthorizedForDeveloper($user);

        $user->activo = !$user->activo;
        $user->save();

        return redirect()->back()->with('success', 'Estado del usuario actualizado.');
    }

    public function destroy(User $user)
    {
        if ($response = $this->redirectIfSelfAction($user, 'No puedes eliminarte a ti mismo.')) {
            return $response;
        }

        $this->abortIfUnauthorizedForDeveloper($user);

        $user->delete();

        return redirect()->back()->with('success', 'Usuario eliminado (soft-delete).');
    }

    public function resetPassword(User $user)
    {
        $this->abortIfUnauthorizedForDeveloper($user);

        $status = Password::sendResetLink(['email' => $user->email]);

        if ($status !== Password::RESET_LINK_SENT) {
            return redirect()->back()->with('error', 'No se pudo enviar el correo de recuperación.');
        }

        return redirect()->back()->with('success', 'Correo de recuperación enviado a ' . $user->email);
    }

    private function storeRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'estamento_id' => 'required|exists:estamentos,id',
            'sede_id' => 'required|exists:sedes,id',
            'fecha_nacimiento' => 'nullable|date',
            'sexo' => 'nullable|in:F,M,Otro',
        ];
    }

    private function updateRules(User $user): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'estamento_id' => 'required|exists:estamentos,id',
            'sede_id' => 'required|exists:sedes,id',
            'fecha_nacimiento' => 'nullable|date',
            'sexo' => 'nullable|in:F,M,Otro',
        ];
    }

    private function abortIfUnauthorizedForDeveloper(User $user): void
    {
        if ($user->isDesarrollador() && !auth()->user()->isDesarrollador()) {
            abort(403, 'No puedes gestionar un usuario de nivel superior.');
        }
    }

    private function redirectIfSelfAction(User $user, string $message): ?RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', $message);
        }

        return null;
    }
}
