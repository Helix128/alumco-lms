<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Models\Estamento;
use App\Models\Sede;
use App\Notifications\SetupPasswordNotification;
use Spatie\Permission\Models\Role;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserManagement extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $showDrawer = false;
    public $editingUser = null;

    // Form fields
    public $name = '';
    public $email = '';
    public $rut = '';
    public $role = '';
    public $estamento_id = '';
    public $sede_id = '';
    public $sexo = '';
    public $fecha_nacimiento = '';
    public $firma_digital;
    public $firma_digital_url;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openCreate()
    {
        $this->resetForm();
        $this->resetValidation();
        $this->editingUser = null;
        $this->showDrawer = true;
    }

    public function edit(User $user)
    {
        $this->abortIfUnauthorizedToManage($user);
        
        $this->editingUser = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->rut = $user->rut;
        $this->role = $user->roles->first()?->name;
        $this->estamento_id = $user->estamento_id;
        $this->sede_id = $user->sede_id;
        $this->sexo = $user->sexo;
        $this->fecha_nacimiento = $user->fecha_nacimiento;
        $this->firma_digital_url = $user->firma_digital ? Storage::url($user->firma_digital) : null;
        $this->firma_digital = null;

        $this->showDrawer = true;
    }

    public function save()
    {
        $rules = $this->editingUser ? $this->updateRules() : $this->storeRules();
        $this->validate($rules);

        if ($this->editingUser) {
            $this->abortIfUnauthorizedToManage($this->editingUser);
        }

        // Protección de Jerarquía: No puedes asignar un rol igual o superior al tuyo (salvo si eres Dev)
        if (!auth()->user()->isDesarrollador()) {
            $roleToAssignRank = ($this->role === 'Desarrollador') ? 3 : (($this->role === 'Administrador') ? 2 : 1);
            if ($roleToAssignRank >= auth()->user()->getHierarchyRank()) {
                abort(403, 'No tienes permisos para asignar el rol: ' . $this->role);
            }
        }

        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'rut' => $this->rut ?: null,
            'estamento_id' => $this->estamento_id ?: null,
            'sede_id' => $this->sede_id,
            'sexo' => $this->sexo ?: null,
            'fecha_nacimiento' => $this->fecha_nacimiento ?: null,
        ];

        if ($this->firma_digital) {
            if ($this->editingUser && $this->editingUser->firma_digital) {
                Storage::disk('public')->delete($this->editingUser->firma_digital);
            }
            $data['firma_digital'] = $this->firma_digital->store('firmas', 'public');
        }

        if ($this->editingUser) {
            $this->editingUser->update($data);
            $this->editingUser->syncRoles([$this->role]);
            session()->flash('success', 'Usuario actualizado exitosamente.');
        } else {
            $data['password'] = Hash::make(Str::random(64));
            $data['activo'] = true;
            
            $user = User::create($data);
            $user->assignRole($this->role ?: 'Trabajador');
            $user->notify(new SetupPasswordNotification());
            
            session()->flash('success', 'Usuario creado. Se envió un correo para que configure su contraseña.');
        }

        $this->showDrawer = false;
        $this->resetForm();
    }

    public function toggleStatus(User $user)
    {
        $this->abortIfUnauthorizedToManage($user);

        $user->activo = !$user->activo;
        $user->save();

        session()->flash('success', 'Estado del usuario actualizado.');
    }

    public function resetPassword(User $user)
    {
        $this->abortIfUnauthorizedToManage($user);

        $status = Password::sendResetLink(['email' => $user->email]);

        if ($status !== Password::RESET_LINK_SENT) {
            session()->flash('error', 'No se pudo enviar el correo de recuperación.');
        } else {
            session()->flash('success', 'Correo de recuperación enviado a ' . $user->email);
        }
    }

    public function deleteUser(User $user)
    {
        $this->abortIfUnauthorizedToManage($user);

        $user->delete();

        session()->flash('success', 'Usuario eliminado.');
    }

    public function render()
    {
        $query = User::with(['estamento', 'sede', 'roles']);

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%")
                  ->orWhere('rut', 'like', "%{$this->search}%");
            });
        }

        $rolesQuery = Role::query();
        if (!auth()->user()->isDesarrollador()) {
            $rolesQuery->whereNotIn('name', ['Desarrollador', 'Administrador']);
        }
        $roles = $rolesQuery->get();

        return view('livewire.admin.user-management', [
            'usuarios' => $query->paginate(15),
            'estamentos' => Estamento::all(),
            'sedes' => Sede::all(),
            'roles' => $roles,
        ]);
    }

    private function resetForm()
    {
        $this->name = '';
        $this->email = '';
        $this->rut = '';
        $this->role = '';
        $this->estamento_id = '';
        $this->sede_id = '';
        $this->sexo = '';
        $this->fecha_nacimiento = '';
        $this->firma_digital = null;
        $this->firma_digital_url = null;
        $this->editingUser = null;
    }

    private function storeRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'rut' => ['nullable', 'string', 'max:12', 'unique:users,rut', 'regex:/^(\d{1,2}\.?\d{3}\.?\d{3}-[\dkK])$/'],
            'estamento_id' => 'nullable|exists:estamentos,id',
            'sede_id' => 'required|exists:sedes,id',
            'role' => 'required|exists:roles,name',
            'firma_digital' => 'nullable|image|max:1024',
            'fecha_nacimiento' => 'nullable|date',
            'sexo' => 'nullable|in:F,M,Otro',
        ];
    }

    private function updateRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($this->editingUser->id)],
            'rut' => ['nullable', 'string', 'max:12', Rule::unique('users', 'rut')->ignore($this->editingUser->id), 'regex:/^(\d{1,2}\.?\d{3}\.?\d{3}-[\dkK])$/'],
            'estamento_id' => 'nullable|exists:estamentos,id',
            'sede_id' => 'required|exists:sedes,id',
            'role' => 'required|exists:roles,name',
            'firma_digital' => 'nullable|image|max:1024',
            'fecha_nacimiento' => 'nullable|date',
            'sexo' => 'nullable|in:F,M,Otro',
        ];
    }

    private function abortIfUnauthorizedToManage(User $user): void
    {
        if (!auth()->user()->canManageUser($user)) {
            abort(403, 'No tienes permisos suficientes para realizar esta acción.');
        }
    }
}
