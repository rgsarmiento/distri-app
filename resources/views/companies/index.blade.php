<x-app-layout>
    <x-slot name="header">Empresas</x-slot>

    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;padding:20px 24px;border-bottom:1px solid #F3F4F8;">
            <div>
                <div style="font-size:15px;font-weight:700;color:#1E1B2E;">Listado de Empresas</div>
                <div style="font-size:12px;color:#9CA3AF;margin-top:2px;">
                    Configuración de empresas y parámetros de alertas (utilice el <strong>ID</strong> para vincular la app de transmisión con Nodo ERP)
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
                        <th style="text-align:center;width:90px;">ID</th>
                        <th>Nombre</th>
                        <th>NIT</th>
                        <th>Teléfono</th>
                        <th>Ubicación</th>
                        <th style="text-align:center;">Base de Datos</th>
                        <th style="text-align:center;">Días de Alerta</th>
                        <th style="text-align:center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($companies as $company)
                    <tr>
                        <td style="text-align:center;">
                            <span style="font-family:monospace;font-weight:800;font-size:13px;background:#EDE9FE;color:#6C3DE0;padding:4px 10px;border-radius:6px;border:1px solid #DDD6FE;display:inline-block;" title="ID de empresa para conectar con Nodo ERP / API">
                                {{ $company->id }}
                            </span>
                        </td>
                        <td>
                            <div style="font-weight:700;color:#1E1B2E;">{{ $company->name }}</div>
                        </td>
                        <td style="color:#6B7280;">{{ $company->nit }}</td>
                        <td>{{ $company->phone }}</td>
                        <td style="font-size:12px;">{{ $company->municipality }}, {{ $company->department }}</td>
                        <td style="text-align:center;">
                            @if($company->hasDedicatedDatabase())
                                <span style="background:#ECFDF5;color:#065F46;padding:4px 10px;border-radius:6px;font-size:11px;font-weight:700;border:1px solid #A7F3D0;display:inline-flex;align-items:center;gap:5px;" title="Base de Datos Independiente: {{ $company->db_database }}">
                                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
                                        <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path>
                                        <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>
                                    </svg>
                                    Dedicada ({{ $company->db_database }})
                                </span>
                            @else
                                <span style="background:#F3F4F6;color:#6B7280;padding:4px 10px;border-radius:6px;font-size:11px;font-weight:600;border:1px solid #E5E7EB;display:inline-flex;align-items:center;gap:5px;" title="Base de datos central compartida (distri_app)">
                                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                                        <line x1="8" y1="21" x2="16" y2="21"></line>
                                        <line x1="12" y1="17" x2="12" y2="21"></line>
                                    </svg>
                                    Compartida
                                </span>
                            @endif
                        </td>
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
