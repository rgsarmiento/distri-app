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
                <div class="mt-4" style="background:#F9FAFB;border:1px solid #E5E7EB;border-radius:8px;padding:16px;">
                    <div style="font-size:13px;font-weight:700;color:#374151;display:flex;align-items:center;gap:6px;">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
                            <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path>
                            <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>
                        </svg>
                        Arquitectura de Base de Datos
                    </div>
                    <div style="margin-top:10px;">
                        <x-input-label for="db_database" :value="__('Nombre de Base de Datos Dedicada')" />
                        <x-text-input id="db_database" class="block mt-1 w-full font-mono text-sm" type="text" name="db_database" :value="old('db_database', $company->db_database)" placeholder="En blanco = Base compartida central (distri_app)" />
                        <x-input-error :messages="$errors->get('db_database')" class="mt-2" />
                        <p style="font-size:11px;color:#6B7280;margin-top:6px;">
                            Estado actual: 
                            @if($company->hasDedicatedDatabase())
                                <span style="color:#059669;font-weight:700;">Base Dedicada [{{ $company->db_database }}]</span>
                            @else
                                <span style="color:#6B7280;font-weight:600;">Base Compartida [distri_app]</span>
                            @endif
                            <br>
                            Para aislar automáticamente esta empresa copiando todos sus datos históricos, puedes ejecutar en terminal:
                            <code style="background:#EDE9FE;color:#6C3DE0;padding:2px 6px;border-radius:4px;">php artisan tenant:isolate {{ $company->id }}</code>
                        </p>
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
