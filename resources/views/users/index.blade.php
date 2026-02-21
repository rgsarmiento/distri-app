<x-app-layout>
    <x-slot name="header">Gestión de Usuarios</x-slot>

    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;padding:20px 24px;border-bottom:1px solid #F3F4F8;">
            <div>
                <div style="font-size:15px;font-weight:700;color:#1E1B2E;">Usuarios del Sistema</div>
                <div style="font-size:12px;color:#9CA3AF;margin-top:2px;">
                    Administra los accesos de Administradores, Supervisores y Distribuidores
                </div>
            </div>
            <a href="{{ route('users.create') }}" class="btn-primary">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
                Nuevo Usuario
            </a>
        </div>

        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Empresa</th>
                        <th>Email</th>
                        <th style="text-align:center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:12px;">
                                <div class="user-avatar-sm" style="width:34px;height:34px;font-size:13px;">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div style="font-weight:600;color:#1E1B2E;">{{ $user->name }}</div>
                            </div>
                        </td>
                        <td>
                            @php
                                $roleColor = match($user->role_id) {
                                    1 => ['bg' => '#EDE9FF', 'text' => '#6C3DE0'],
                                    3 => ['bg' => '#E0E7FF', 'text' => '#4338CA'],
                                    default => ['bg' => '#F3F4F6', 'text' => '#4B5563'],
                                };
                            @endphp
                            <span style="background: {{ $roleColor['bg'] }}; color: {{ $roleColor['text'] }}; padding:3px 10px; border-radius:6px; font-size:11.5px; font-weight:700; text-transform:uppercase;">
                                {{ $user->role->name ?? 'N/A' }}
                            </span>
                        </td>
                        <td>
                            <div style="font-size:13px;color:#374151;">{{ $user->company->name ?? 'Sistema' }}</div>
                        </td>
                        <td style="color:#6B7280;font-size:13px;">{{ $user->email }}</td>
                        <td style="text-align:center;">
                            <div style="display:flex;gap:6px;justify-content:center;">
                                <a href="{{ route('users.edit', $user->id) }}" class="btn-edit" style="padding:6px 14px;font-size:12px;">Editar</a>
                                <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este usuario?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-danger" style="padding:6px 14px;font-size:12px;">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
