<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Company') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('companies.update', $company->id) }}">
                @csrf
                @method('PUT')

                <div style="background:#F5F3FF;border:1px solid #DDD6FE;border-radius:8px;padding:12px 16px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;">
                    <div>
                        <div style="font-size:12px;font-weight:700;color:#6C3DE0;text-transform:uppercase;letter-spacing:0.5px;">Identificador de Sincronización</div>
                        <div style="font-size:13px;color:#4B5563;margin-top:2px;">Utiliza este ID en Nodo ERP o la aplicación de transmisión:</div>
                    </div>
                    <span style="font-family:monospace;font-size:16px;font-weight:800;background:#6C3DE0;color:#fff;padding:4px 12px;border-radius:6px;">
                        ID: {{ $company->id }}
                    </span>
                </div>
            
                <!-- NIT -->
                <div>
                    <x-input-label for="nit" :value="__('NIT')" />
                    <x-text-input id="nit" class="block mt-1 w-full" type="text" name="nit" :value="old('nit', $company->nit)" required />
                    <x-input-error :messages="$errors->get('nit')" class="mt-2" />
                </div>
            
                <!-- Nombre -->
                <div class="mt-4">
                    <x-input-label for="name" :value="__('Nombre')" />
                    <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $company->name)" required />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>
            
                <!-- Teléfono -->
                <div class="mt-4">
                    <x-input-label for="phone" :value="__('Teléfono')" />
                    <x-text-input id="phone" class="block mt-1 w-full" type="text" name="phone" :value="old('phone', $company->phone)" required />
                    <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                </div>
            
                <!-- Dirección -->
                <div class="mt-4">
                    <x-input-label for="address" :value="__('Dirección')" />
                    <x-text-input id="address" class="block mt-1 w-full" type="text" name="address" :value="old('address', $company->address)" required />
                    <x-input-error :messages="$errors->get('address')" class="mt-2" />
                </div>
            
                <!-- Departamento -->
                <div class="mt-4">
                    <x-input-label for="department" :value="__('Departamento')" />
                    <select id="department" name="department" class="block mt-1 w-full rounded-md" required onchange="fetchMunicipalities()">
                        <option value="">{{ __('Seleccione un departamento') }}</option>
                        @foreach (json_decode(File::get(resource_path('json/departments_municipalities.json')), true) as $department => $municipalities)
                            <option value="{{ $department }}" {{ old('department', $company->department) == $department ? 'selected' : '' }}>
                                {{ $department }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('department')" class="mt-2" />
                </div>
            
                <!-- Municipio -->
                <div class="mt-4">
                    <x-input-label for="municipality" :value="__('Municipio')" />
                    <select id="municipality" name="municipality" class="block mt-1 w-full rounded-md" required>
                        <option value="">{{ __('Seleccione un municipio') }}</option>
                        @foreach (json_decode(File::get(resource_path('json/departments_municipalities.json')), true)[$company->department] ?? [] as $municipality)
                            <option value="{{ $municipality }}" {{ old('municipality', $company->municipality) == $municipality ? 'selected' : '' }}>
                                {{ $municipality }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('municipality')" class="mt-2" />
                </div>

                <!-- Arquitectura de Base de Datos -->
                <div class="mt-4" style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:10px;padding:20px;">
                    <div style="font-size:14px;font-weight:700;color:#1E293B;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <svg width="18" height="18" fill="none" stroke="#6C3DE0" stroke-width="2" viewBox="0 0 24 24">
                                <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
                                <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path>
                                <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>
                            </svg>
                            Arquitectura de Base de Datos (Multi-Tenant)
                        </div>
                        <div>
                            @if($company->hasDedicatedDatabase())
                                <span style="background:#ECFDF5;color:#065F46;padding:4px 12px;border-radius:6px;font-size:12px;font-weight:700;border:1px solid #A7F3D0;display:inline-flex;align-items:center;gap:6px;">
                                    <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#10B981;"></span>
                                    Base Dedicada [{{ $company->db_database }}]
                                </span>
                            @else
                                <span style="background:#F1F5F9;color:#475569;padding:4px 12px;border-radius:6px;font-size:12px;font-weight:600;border:1px solid #CBD5E1;display:inline-flex;align-items:center;gap:6px;">
                                    <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#94A3B8;"></span>
                                    Base Compartida Central [distri_app]
                                </span>
                            @endif
                        </div>
                    </div>

                    <div style="margin-top:16px;">
                        <x-input-label for="db_database" :value="__('Nombre de Base de Datos en MySQL')" />
                        <x-text-input id="db_database" class="block mt-1 w-full font-mono text-sm" type="text" name="db_database" :value="old('db_database', $company->db_database)" placeholder="En blanco = Base compartida central (distri_app)" />
                        <x-input-error :messages="$errors->get('db_database')" class="mt-2" />
                        <p style="font-size:12px;color:#64748B;margin-top:4px;">
                            Si se deja en blanco, la empresa almacena y consulta sus datos en la base compartida general (<code>distri_app</code>).
                        </p>
                    </div>

                    {{-- ── CAJA DE AYUDA Y COMANDOS ── --}}
                    <div style="margin-top:16px;background:#FFFFFF;border:1px solid #E2E8F0;border-radius:8px;padding:16px;font-size:12px;color:#334155;">
                        <div style="font-weight:700;color:#0F172A;margin-bottom:8px;display:flex;align-items:center;gap:6px;">
                            <span style="font-size:14px;">🛠️</span> ¿Cómo aislar esta empresa a su propia base de datos física?
                        </div>
                        <p style="margin-bottom:10px;color:#64748B;">
                            Ejecuta cualquiera de los siguientes comandos en la consola/terminal desde la carpeta del proyecto. El sistema creará la base en MySQL con el mismo usuario y contraseña de la principal, copiará todos los clientes, productos y órdenes históricas de esta empresa, y la dejará conectada de forma transparente:
                        </p>

                        <div style="display:flex;flex-direction:column;gap:10px;">
                            <div style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:6px;padding:10px 12px;">
                                <div style="font-weight:600;color:#1E293B;">Opción 1: Con nombre automático (por defecto):</div>
                                <code style="display:block;margin-top:4px;font-family:monospace;background:#EDE9FE;color:#6C3DE0;padding:6px 10px;border-radius:4px;font-weight:700;">
                                    php artisan tenant:isolate {{ $company->id }}
                                </code>
                                <div style="color:#64748B;font-size:11px;margin-top:4px;">
                                    → <strong>Resultado:</strong> Crea la base de datos <code>distri_empresa_{{ $company->id }}</code> y asigna automáticamente este campo.
                                </div>
                            </div>

                            <div style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:6px;padding:10px 12px;">
                                <div style="font-weight:600;color:#1E293B;">Opción 2: Con nombre personalizado (opción --database):</div>
                                <code style="display:block;margin-top:4px;font-family:monospace;background:#EDE9FE;color:#6C3DE0;padding:6px 10px;border-radius:4px;font-weight:700;">
                                    php artisan tenant:isolate {{ $company->id }} --database=distri_{{ Str::slug($company->name, '_') ?: 'empresa' }}
                                </code>
                                <div style="color:#64748B;font-size:11px;margin-top:4px;">
                                    → <strong>Resultado:</strong> Crea la base de datos <code>distri_{{ Str::slug($company->name, '_') ?: 'empresa' }}</code> en MySQL y copia toda la información histórica.
                                </div>
                            </div>

                            <div style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:6px;padding:10px 12px;">
                                <div style="font-weight:600;color:#1E293B;">Actualizaciones de tablas y columnas a futuro:</div>
                                <code style="display:block;margin-top:4px;font-family:monospace;background:#F1F5F9;color:#0F172A;padding:6px 10px;border-radius:4px;font-weight:700;">
                                    php artisan tenants:migrate
                                </code>
                                <div style="color:#64748B;font-size:11px;margin-top:4px;">
                                    → <strong>Resultado:</strong> Corre las migraciones en la base central compartida y en todas las bases dedicadas que existan.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            
                <!-- Botón de guardar -->
                <div class="mt-4">
                    <x-primary-button class="ml-3">
                        {{ __('Guardar cambios') }}
                    </x-primary-button>
                </div>
            </form>
            

        </div>
    </div>

    <script>
        function fetchMunicipalities() {
            var department = document.getElementById('department').value;
            var municipalitySelect = document.getElementById('municipality');

            // Limpia las opciones actuales
            municipalitySelect.innerHTML = '<option value="">Seleccione un municipio</option>';

            if (department) {
                fetch(`{{ route('get-municipalities') }}?department=${department}`)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(municipality => {
                            var option = document.createElement('option');
                            option.value = municipality;
                            option.textContent = municipality;
                            municipalitySelect.appendChild(option);
                        });
                    });
            }
        }
    </script>
</x-app-layout>
