<x-app-layout>
    <x-slot name="header">Clientes</x-slot>

    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;padding:20px 24px;border-bottom:1px solid #F3F4F8;">
            <div>
                <div style="font-size:15px;font-weight:700;color:#1E1B2E;">Directorio de Clientes</div>
                <div style="font-size:12px;color:#9CA3AF;margin-top:2px;">
                    Gestiona la información de contacto y pedidos de tus clientes
                </div>
            </div>
            <a href="{{ route('customer-details.create') }}" class="btn-primary">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
                Nuevo Cliente
            </a>
        </div>

        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nombre / Razón Social</th>
                        <th>Identificación</th>
                        <th>Teléfono</th>
                        <th>Ciudad</th>
                        @if(Auth::user()->role_id === 1)
                        <th>Empresa</th>
                        @endif
                        <th style="text-align:center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customerDetails as $customer)
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div style="width:32px;height:32px;border-radius:8px;background:#F8F7FF;color:#6C3DE0;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;">
                                    {{ strtoupper(substr($customer->full_name, 0, 1)) }}
                                </div>
                                <div style="font-weight:600;color:#1E1B2E;">{{ $customer->full_name }}</div>
                            </div>
                        </td>
                        <td style="color:#6B7280;font-family:monospace;">{{ $customer->identification }}</td>
                        <td>{{ $customer->phone }}</td>
                        <td>{{ $customer->municipality }}</td>
                        @if(Auth::user()->role_id === 1)
                        <td>
                            <span style="font-size:12px;color:#6C3DE0;background:#EDE9FF;padding:2px 8px;border-radius:4px;font-weight:600;">
                                {{ $customer->company->name ?? 'N/A' }}
                            </span>
                        </td>
                        @endif
                        <td style="text-align:center;">
                            <div style="display:flex;gap:6px;justify-content:center;">
                                <a href="{{ route('customer-details.show', $customer->id) }}" class="btn-secondary" style="padding:6px 10px;font-size:12px;">Ver</a>
                                <a href="{{ route('customer-details.edit', $customer->id) }}" class="btn-edit" style="padding:6px 10px;font-size:12px;">Editar</a>
                                <form action="{{ route('customer-details.destroy', $customer->id) }}" method="POST" onsubmit="return confirm('¿Eliminar cliente?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-danger" style="padding:6px 10px;font-size:12px;">Eliminar</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align:center;padding:48px;color:#9CA3AF;">No se encontraron clientes.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="padding:16px 24px;border-top:1px solid #F3F4F8;">
            {{ $customerDetails->links() }}
        </div>
    </div>
</x-app-layout>
