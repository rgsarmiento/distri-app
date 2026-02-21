<x-app-layout>
    <x-slot name="header">Empresas</x-slot>

    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;padding:20px 24px;border-bottom:1px solid #F3F4F8;">
            <div>
                <div style="font-size:15px;font-weight:700;color:#1E1B2E;">Listado de Empresas</div>
                <div style="font-size:12px;color:#9CA3AF;margin-top:2px;">
                    Configuración de empresas y parámetros de alertas
                </div>
            </div>
            <a href="{{ route('companies.create') }}" class="btn-primary">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
                Nueva Empresa
            </a>
        </div>

        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>NIT</th>
                        <th>Teléfono</th>
                        <th>Ubicación</th>
                        <th style="text-align:center;">Días de Alerta</th>
                        <th style="text-align:center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($companies as $company)
                    <tr>
                        <td>
                            <div style="font-weight:700;color:#6C3DE0;">{{ $company->name }}</div>
                        </td>
                        <td style="color:#6B7280;">{{ $company->nit }}</td>
                        <td>{{ $company->phone }}</td>
                        <td style="font-size:12px;">{{ $company->municipality }}, {{ $company->department }}</td>
                        <td style="text-align:center;">
                            <span style="background:#FEF3C7;color:#92400E;padding:3px 10px;border-radius:6px;font-weight:700;font-size:12px;">
                                {{ $company->alert_days }} días
                            </span>
                        </td>
                        <td style="text-align:center;">
                            <div style="display:flex;gap:6px;justify-content:center;">
                                <a href="{{ route('companies.edit', $company->id) }}" class="btn-edit" style="padding:6px 12px;font-size:12px;">Editar</a>
                                <form action="{{ route('companies.destroy', $company->id) }}" method="POST" onsubmit="return confirm('¿Eliminar empresa? Esto borrará todos sus clientes y pedidos asociados.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-danger" style="padding:6px 12px;font-size:12px;">Eliminar</button>
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
