<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $query = User::with(['company', 'role']);

        if ($user->role_id === 3) {
            $query->where('company_id', $user->company_id);
        } elseif ($user->role_id !== 1) {
            abort(403);
        }

        $users = $query->get();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $user = auth()->user();
        if ($user->role_id === 3) {
            $companies = Company::where('id', $user->company_id)->get();
            $roles = Role::where('id', 2)->get(); // Solo Distribuidor
        } else {
            $companies = Company::all();
            $roles = Role::all();
        }
        return view('users.create', compact('companies', 'roles', 'user'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $validated = $request->validate([
            'name' => 'required|unique:users,name|max:255',
            'email' => 'required|unique:users,email|email',
            'password' => 'required|min:8|confirmed',
            'company_id' => 'required|exists:companies,id',
            'role_id' => 'required|exists:roles,id',
        ]);

        // Seguridad para Supervisor
        if ($user->role_id === 3) {
            $validated['company_id'] = $user->company_id;
            $validated['role_id'] = 2; // Siempre Distribuidor
        }

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'company_id' => $validated['company_id'],
            'role_id' => $validated['role_id'],
        ]);

        return redirect()->route('users.index')->with('success', 'Usuario creado con éxito');
    }

    public function show($id)
    {
        $user = User::with(['company', 'role'])->findOrFail($id);
        $authUser = auth()->user();
        if ($authUser->role_id === 3 && $user->company_id !== $authUser->company_id) abort(403);
        
        return view('users.show', compact('user'));
    }

    public function edit($id)
    {
        $userToEdit = User::findOrFail($id);
        $authUser = auth()->user();
        
        if ($authUser->role_id === 3) {
            if ($userToEdit->company_id !== $authUser->company_id) abort(403);
            $companies = Company::where('id', $authUser->company_id)->get();
            $roles = Role::where('id', 2)->get();
        } else {
            $companies = Company::all();
            $roles = Role::all();
        }
        
        return view('users.edit', ['user' => $userToEdit, 'companies' => $companies, 'roles' => $roles]);
    }

    public function update(Request $request, $id)
    {
        $userToUpdate = User::findOrFail($id);
        $authUser = auth()->user();
        
        if ($authUser->role_id === 3 && $userToUpdate->company_id !== $authUser->company_id) abort(403);

        $validated = $request->validate([
            'name' => 'required|max:255|unique:users,name,' . $id,
            'email' => 'required|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|min:8|confirmed',
            'company_id' => 'required|exists:companies,id',
            'role_id' => 'required|exists:roles,id',
        ]);

        if ($authUser->role_id === 3) {
            $validated['company_id'] = $authUser->company_id;
            $validated['role_id'] = 2;
        }

        $dataToUpdate = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'company_id' => $validated['company_id'],
            'role_id' => $validated['role_id'],
        ];

        if (!empty($validated['password'])) {
            $dataToUpdate['password'] = Hash::make($validated['password']);
        }

        $userToUpdate->update($dataToUpdate);

        return redirect()->route('users.index')->with('success', 'Usuario actualizado con éxito');
    }

    public function destroy($id)
    {
        $userToDelete = User::findOrFail($id);
        $authUser = auth()->user();
        
        if ($authUser->role_id === 3 && $userToDelete->company_id !== $authUser->company_id) abort(403);
        
        $userToDelete->delete();
        return redirect()->route('users.index')->with('success', 'Usuario eliminado con éxito');
    }
}
