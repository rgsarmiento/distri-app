<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CustomerDetail;
use Illuminate\Http\Request;

class CustomerDetailController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->role_id === 3) {
            // Supervisor: solo clientes de su empresa
            $customerDetails = CustomerDetail::with('company')
                ->where('company_id', $user->company_id)
                ->paginate(10);
        } elseif ($user->role_id === 1) {
            // Admin: todos
            $customerDetails = CustomerDetail::with('company')->paginate(10);
        } else {
            // Distribuidor: los clientes de su empresa también (para poder crear órdenes)
            $customerDetails = CustomerDetail::with('company')
                ->where('company_id', $user->company_id)
                ->paginate(10);
        }

        return view('customer-details.index', compact('customerDetails'));
    }

    public function create()
    {
        $companies = Company::all();
        return view('customer-details.create', compact('companies'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'identification' => 'required|unique:customer_details',
            'full_name' => 'required',
            'email' => 'required|email|unique:customer_details',
            'phone' => 'required',
            'address' => 'required',
            'company_id' => auth()->user()->role_id === 1 ? 'required|exists:companies,id' : 'nullable',
        ]);

        if (auth()->user()->role_id !== 1) {
            $validatedData['company_id'] = auth()->user()->company_id;
        }

        CustomerDetail::create($validatedData);

        $this->flashNotification('success', 'Cliente Creado', 'El cliente ha sido creado exitosamente.');
        return redirect()->route('customer-details.index');
    }

    public function show($id)
    {
        $user = auth()->user();
        $customerDetail = CustomerDetail::with(['company'])->findOrFail($id);
        
        if ($user->role_id !== 1 && $customerDetail->company_id !== $user->company_id) abort(403);
        
        return view('customer-details.show', compact('customerDetail'));
    }

    public function edit($id)
    {
        $user = auth()->user();
        $customerDetail = CustomerDetail::findOrFail($id);
        
        if ($user->role_id !== 1 && $customerDetail->company_id !== $user->company_id) abort(403);

        $companies = $user->role_id === 1 ? Company::all() : Company::where('id', $user->company_id)->get();
        return view('customer-details.edit', compact('customerDetail', 'companies'));
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $customerDetail = CustomerDetail::findOrFail($id);
        
        if ($user->role_id !== 1 && $customerDetail->company_id !== $user->company_id) abort(403);

        $validated = $request->validate([
            'identification' => 'required|unique:customer_details,identification,' . $customerDetail->id,
            'full_name' => 'required',
            'email' => 'required|email|unique:customer_details,email,' . $customerDetail->id,
            'phone' => 'required',
            'address' => 'required',
            'company_id' => 'required|exists:companies,id',
        ]);

        if ($user->role_id !== 1) $validated['company_id'] = $user->company_id;

        $customerDetail->update($validated);

        $this->flashNotification('success', 'Cliente Actualizado', 'El cliente ha sido actualizado exitosamente.');
        return redirect()->route('customer-details.index');
    }

    public function destroy($id)
    {
        $user = auth()->user();
        $customerDetail = CustomerDetail::withCount('orders')->findOrFail($id);

        if ($user->role_id !== 1 && $customerDetail->company_id !== $user->company_id) abort(403);

        if ($user->role_id === 3 && $customerDetail->orders_count > 0) {
            $this->flashNotification('error', 'Error', 'No puedes eliminar un cliente que ya tiene pedidos registrados.');
            return redirect()->route('customer-details.index');
        }

        $customerDetail->delete();

        $this->flashNotification('success', 'Cliente Eliminado', 'El cliente ha sido eliminado exitosamente.');
        return redirect()->route('customer-details.index');
    }

    public function search(Request $request)
    {
        $user = auth()->user();
        $queryValue = $request->input('query');
        
        $query = CustomerDetail::query();

        if ($user && $user->role_id !== 1) {
            $query->where('company_id', $user->company_id);
        }

        // Filtro opcional para clientes correspondientes a la visita de hoy
        if ($request->boolean('today_only') || $request->input('filter_today') == 1) {
            $query->visitToday();
            
            // Si el usuario es vendedor/preventista y tiene clientes asignados en customer_user
            if ($user && $user->role_id === 2) {
                $hasAssignments = \Illuminate\Support\Facades\DB::table('customer_user')
                    ->where('user_id', $user->id)
                    ->exists();
                if ($hasAssignments) {
                    $query->forSeller($user->id);
                }
            }
        }

        if (!empty($queryValue)) {
            $query->where(function($q) use ($queryValue) {
                $q->where('full_name', 'like', "%{$queryValue}%")
                  ->orWhere('identification', 'like', "%{$queryValue}%");
            });
        }

        $customers = $query->limit(50)->get();

        return response()->json($customers);
    }

    public function storeCustomers(Request $request)
    {
        try {
            $inserted = 0;
            $updated = 0;

            // Tomar el array de clientes desde el request
            $customers = $request->input('customers', []);

            \Illuminate\Support\Facades\DB::transaction(function () use ($customers, &$inserted, &$updated) {
                foreach ($customers as $customer) {
                    $companyId = $customer['company_id'] ?? null;
                    if (!$companyId && auth()->check()) {
                        $companyId = auth()->user()->company_id;
                    }

                    $customerData = [
                        'identification'  => $customer['identification'],
                        'full_name'       => $customer['full_name'],
                        'email'           => $customer['email'] ?? ($customer['identification'] . '@cliente.local'),
                        'phone'           => $customer['phone'] ?? '0',
                        'address'         => $customer['address'] ?? 'No registrada',
                        'company_id'      => $companyId,
                        'municipality'    => $customer['municipality'] ?? null,
                        'route_id'        => $customer['route_id'] ?? null,
                        'visit_monday'    => filter_var($customer['visit_monday'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'visit_tuesday'   => filter_var($customer['visit_tuesday'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'visit_wednesday' => filter_var($customer['visit_wednesday'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'visit_thursday'  => filter_var($customer['visit_thursday'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'visit_friday'    => filter_var($customer['visit_friday'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'visit_saturday'  => filter_var($customer['visit_saturday'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'visit_sunday'    => filter_var($customer['visit_sunday'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'web_access'      => filter_var($customer['web_access'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'web_user'        => $customer['web_user'] ?? null,
                        'web_password'    => !empty($customer['web_password']) ? bcrypt($customer['web_password']) : null,
                        'discount_rate'   => (float) ($customer['discount_rate'] ?? 0),
                        'updated_at'      => now(),
                    ];

                    $existingCustomer = CustomerDetail::where('identification', $customer['identification'])
                        ->where('company_id', $companyId)
                        ->first();

                    if ($existingCustomer) {
                        // Mantener contraseña anterior si no se envía una nueva
                        if (empty($customerData['web_password'])) {
                            unset($customerData['web_password']);
                        }
                        $existingCustomer->update($customerData);
                        $targetCustomer = $existingCustomer;
                        $updated++;
                    } else {
                        $customerData['created_at'] = now();
                        $targetCustomer = CustomerDetail::create($customerData);
                        $inserted++;
                    }

                    // Sincronizar vendedores asignados (seller_ids o user_ids)
                    $sellerIds = $customer['seller_ids'] ?? ($customer['user_ids'] ?? null);
                    if ($sellerIds !== null) {
                        $sellerIds = is_array($sellerIds) ? $sellerIds : [$sellerIds];
                        $targetCustomer->sellers()->sync($sellerIds);
                    } elseif (!empty($customer['seller_id'])) {
                        $targetCustomer->sellers()->sync([$customer['seller_id']]);
                    }
                }
            });

            return response()->json([
                'status'  => true,
                'message' => "Clientes procesados correctamente. Insertados: $inserted, Actualizados: $updated"
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function flashNotification($type, $title, $message)
    {
        session()->flash('notification', [
            'type' => $type,
            'title' => $title,
            'message' => $message
        ]);
    }
}
