<x-app-layout>
    <x-slot name="header">Nueva Orden</x-slot>

    <div class="card" style="padding:28px; position: relative;">
        <!-- Botón Ir al final -->
        <button type="button" onclick="window.scrollTo({top: document.body.scrollHeight, behavior: 'smooth'})" class="btn-secondary" style="position:fixed; right:20px; bottom:80px; z-index:100; border-radius:50%; width:45px; height:45px; display:flex; align-items:center; justify-content:center; box-shadow: 0 4px 12px rgba(0,0,0,0.15);" title="Ir al final">
            ↓
        </button>
        <!-- Botón Ir al inicio -->
        <button type="button" onclick="window.scrollTo({top: 0, behavior: 'smooth'})" class="btn-secondary" style="position:fixed; right:20px; bottom:20px; z-index:100; border-radius:50%; width:45px; height:45px; display:flex; align-items:center; justify-content:center; box-shadow: 0 4px 12px rgba(0,0,0,0.15);" title="Ir al inicio">
            ↑
        </button>

        <form action="{{ route('orders.store') }}" method="POST" id="order_form">
            @csrf

            @if ($errors->any())
                <div class="card" style="background:#FFF1F1; border:1px solid #FFD1D1; padding:16px; margin-bottom:24px; color:#EF4444;">
                    <div style="font-weight:800; margin-bottom:8px;">⚠️ Por favor corrige los siguientes errores:</div>
                    <ul style="margin:0; padding-left:20px; font-size:13px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid-cols-mobile-1" style="display:grid;grid-template-columns:1fr;gap:24px;margin-bottom:24px;">
                <!-- Buscador interactivo del Cliente -->
                <div style="display:flex; justify-content:between; align-items:end; gap:20px;">
                    <div style="flex:1;">
                        <label class="form-label">Cliente</label>
                        <input type="text" id="customer_search" class="form-input" placeholder="Buscar por nombre o identificación..." autocomplete="off" oninput="filterCustomers()">
                        <div id="customer_list" class="card" style="position:absolute;z-index:10;width:300px;max-height:200px;overflow-y:auto;display:none;margin-top:4px;"></div>
                        <input type="hidden" id="customer_id" name="customer_id" required>
                        <input type="hidden" name="status" value="pendiente">
                    </div>
                    <button type="button" onclick="confirmResetOrder()" class="btn-secondary" style="background:#FFEBEB; color:#EF4444; border:1px solid #FFD1D1; padding:10px 20px;">
                        🗑️ Borrar Orden
                    </button>
                </div>
                <x-input-error :messages="$errors->get('customer_id')" class="mt-1" />
            </div>

            <div class="grid-cols-mobile-1" style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
                <!-- Buscador de Productos -->
                <div class="card" style="padding:20px;background:#F8F7FF;border:1px dashed #6C3DE0;">
                    <label class="form-label" style="color:#6C3DE0;">Agregar Productos</label>
                    <input type="text" id="product_search" class="form-input" placeholder="Escribe nombre o código del producto..." autocomplete="off" oninput="filterProducts()">
                    <div id="product_list" class="card" style="position:absolute;z-index:10;width:300px;max-height:200px;overflow-y:auto;display:none;margin-top:4px;"></div>
                    
                    <div id="selected_product_details" style="margin-top:16px;display:none;background:#fff;padding:12px;border-radius:10px;border:1px solid #EDE9FF;">
                        <div style="font-weight:700;color:#1E1B2E;" id="product_name"></div>
                        <div style="font-size:12px;color:#9CA3AF;margin-bottom:12px;" id="product_code"></div>
                        
                        <div id="price_selection" style="margin-bottom:16px;">
                            <label class="form-label" style="font-size:11px;">1. Seleccionar Precio:</label>
                            <div id="price_options" style="display:flex;flex-direction:column;gap:5px;"></div>
                        </div>

                        <div style="margin-bottom:20px;">
                            <label class="form-label" style="font-size:11px;">2. Cantidad a agregar:</label>
                            <div style="display:flex; align-items:center; gap:10px;">
                                <button type="button" onclick="adjustMainQty(-1)" class="btn-secondary" style="padding:5px 12px; font-weight:bold; touch-action: manipulation; user-select: none;">-</button>
                                <input type="number" id="main_quantity" value="1" min="0.01" step="any" class="form-input" style="width:80px; text-align:center;">
                                <button type="button" onclick="adjustMainQty(1)" class="btn-secondary" style="padding:5px 12px; font-weight:bold; touch-action: manipulation; user-select: none;">+</button>
                            </div>
                        </div>

                        <button type="button" id="add_product_button" class="btn-primary" style="width:100%;justify-content:center;padding:12px; font-weight:bold;">
                            Añadir a la lista
                        </button>
                    </div>
                </div>

                <!-- Lista de Productos Seleccionados -->
                <div>
                    <div style="font-weight:700;color:#1E1B2E;margin-bottom:12px;display:flex;align-items:center;justify-content:between;width:100%;">
                        <span>📦 Productos en la Orden</span>
                        <div style="display:flex;gap:5px;">
                            <button type="button" onclick="document.getElementById('selected_products_list').scrollTop = 0" class="btn-secondary" style="padding:4px 8px;font-size:10px;">↑ Inicio Lista</button>
                            <button type="button" onclick="document.getElementById('selected_products_list').scrollTop = document.getElementById('selected_products_list').scrollHeight" class="btn-secondary" style="padding:4px 8px;font-size:10px;">↓ Fin Lista</button>
                        </div>
                    </div>
                    <div id="selected_products_list" style="display:flex;flex-direction:column;gap:10px;max-height:500px;overflow-y:auto;padding-right:5px;">
                        <!-- Se llena vía JS -->
                    </div>
                </div>
            </div>

            <!-- Observaciones y Totales -->
            <div id="order_totals" style="margin-top:32px;padding-top:24px;border-top:2px solid #F3F4F8;display:none;">
                <div style="display:flex; flex-direction:column; gap:24px;">
                    <div>
                        <label class="form-label">Observaciones del Pedido</label>
                        <textarea name="observations" id="observations" class="form-input" rows="3" placeholder="Añade notas adicionales aquí..." style="width:100% !important; max-width:100%;"></textarea>
                    </div>
                    
                    <div style="max-width:300px;margin-left:auto;display:flex;flex-direction:column;gap:10px; width:100%;">
                        <div style="display:flex;justify-content:space-between;color:#6B7280;font-size:14px;">
                            <span>Subtotal</span>
                            <span id="total_base_price">$<span>0.00</span></span>
                        </div>
                        <div style="display:flex;justify-content:space-between;color:#6B7280;font-size:14px;">
                            <span>Impuestos</span>
                            <span id="total_tax">$<span>0.00</span></span>
                        </div>
                        <div style="display:flex;justify-content:space-between;font-size:18px;font-weight:800;color:#1E1B2E;border-top:1.5px solid #E5E7EB;padding-top:10px;">
                            <span>Total Final</span>
                            <span id="total_price" style="color:#6C3DE0;">$<span>0.00</span></span>
                        </div>
                        
                        <button type="submit" onclick="clearDraft()" class="btn-primary" style="width:100%;justify-content:center;margin-top:12px;padding:14px; font-size:16px;">
                            Finalizar y Guardar Pedido
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Modal de Confirmación para Borrar -->
    <div id="reset_modal" style="display:none; position:fixed; inset:0; background:rgba(30,27,46,0.7); backdrop-filter:blur(4px); z-index:999; align-items:center; justify-content:center; padding:20px;">
        <div class="card" style="max-width:400px; width:100%; padding:32px; text-align:center;">
            <div style="font-size:48px; margin-bottom:16px;">⚠️</div>
            <h3 style="font-size:20px; font-weight:800; color:#1E1B2E; margin-bottom:12px;">¿Borrar todo el pedido?</h3>
            <p style="color:#6B7280; margin-bottom:24px;">Esta acción eliminará todos los productos y el cliente seleccionado. No se puede deshacer.</p>
            <div style="display:flex; gap:12px;">
                <button type="button" onclick="closeResetModal()" class="btn-secondary" style="flex:1; justify-content:center;">Cancelar</button>
                <button type="button" onclick="resetOrderNow()" class="btn-primary" style="flex:1; justify-content:center; background:#EF4444;">Sí, Borrar Todo</button>
            </div>
        </div>
    </div>

    <template id="product-template">
        <div class="product-item card" style="padding:12px;display:flex;align-items:center;justify-content:space-between;gap:12px;border:1px solid #F3F4F8;">
            <div style="flex:1;">
                <div class="product-name" style="font-size:13.5px;font-weight:600;color:#1E1B2E;"></div>
                <div class="product-item-code" style="font-size:10px; color:#6C3DE0; font-weight:bold; margin-bottom:2px;"></div>
                <div class="product-subtotal" style="font-size:12px;color:#9CA3AF;margin-top:2px;">
                    Unit: $<span class="unit-price">0.00</span> | Sub: $<span class="subtotal-amount">0.00</span>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:8px;">
                <button type="button" onclick="adjustItemQty(this, -1)" class="btn-secondary" style="padding:2px 8px; font-size:12px; touch-action: manipulation; user-select: none;">-</button>
                <input type="number" name="quantities[]" class="quantity-input form-input" style="width:60px;padding:5px 4px;text-align:center; font-size:13px;" min="0.01" step="any" value="1" onkeydown="if(event.key==='Enter'){event.preventDefault();}">
                <button type="button" onclick="adjustItemQty(this, 1)" class="btn-secondary" style="padding:2px 8px; font-size:12px; touch-action: manipulation; user-select: none;">+</button>
                
                <input type="hidden" name="products[]" class="product-input">
                <input type="hidden" class="product-tax-rate">
                <input type="hidden" name="base_prices[]" class="product-base-price">
                <button type="button" class="remove-product" style="color:#EF4444;background:none;border:none;cursor:pointer; padding:5px;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </div>
        </div>
    </template>

    <script>
        // Función para filtrar clientes por nombre o identificación
        function filterCustomers() {
            let query = document.getElementById('customer_search').value;
            const customerRoute = @json(route('customers.search'));
            const customerList = document.getElementById('customer_list');

            if (query.length > 1) {
                fetch(`${customerRoute}?query=${query}`)
                    .then(response => response.json())
                    .then(data => {
                        customerList.innerHTML = '';
                        if(data.length > 0) {
                            customerList.style.display = 'block';
                            data.forEach(customer => {
                                let div = document.createElement('div');
                                div.textContent = `${customer.full_name} - ${customer.identification}`;
                                div.classList.add('nav-item');
                                div.style.color = '#1E1B2E';
                                div.style.cursor = 'pointer';
                                div.addEventListener('click', function() {
                                    selectCustomer(customer);
                                });
                                customerList.appendChild(div);
                            });
                        } else {
                            customerList.style.display = 'none';
                        }
                    });
            } else {
                customerList.style.display = 'none';
            }
        }

        // Persistencia Loca (Draft)
        const DRAFT_KEY = 'order_draft_v1';

        window.addEventListener('load', () => {
            loadDraft();
            // Guardar automáticamente cada 5 segundos
            setInterval(saveDraft, 5000);
        });

        function saveDraft() {
            const items = [];
            document.querySelectorAll('.product-item').forEach(el => {
                items.push({
                    priceKey: el.dataset.priceKey,
                    productId: el.querySelector('.product-input').value,
                    name: el.querySelector('.product-name').textContent,
                    code: el.querySelector('.product-item-code').textContent.replace('[','').replace(']',''),
                    basePrice: el.querySelector('.product-base-price').value,
                    taxRate: el.querySelector('.product-tax-rate').value,
                    quantity: el.querySelector('.quantity-input').value
                });
            });

            const draft = {
                customerId: document.getElementById('customer_id').value,
                customerSearch: document.getElementById('customer_search').value,
                observations: document.getElementById('observations').value,
                items: items
            };
            localStorage.setItem(DRAFT_KEY, JSON.stringify(draft));
        }

        function loadDraft() {
            const data = localStorage.getItem(DRAFT_KEY);
            if (!data) return;
            const draft = JSON.parse(data);

            if (draft.customerId) {
                document.getElementById('customer_id').value = draft.customerId;
                document.getElementById('customer_search').value = draft.customerSearch;
            }
            if (draft.observations) {
                document.getElementById('observations').value = draft.observations;
            }

            if (draft.items && draft.items.length > 0) {
                draft.items.forEach(item => {
                    renderProductItem(item);
                });
                updateTotals();
            }
        }

        function clearDraft() {
            localStorage.removeItem(DRAFT_KEY);
        }

        function confirmResetOrder() {
            document.getElementById('reset_modal').style.display = 'flex';
        }

        function closeResetModal() {
            document.getElementById('reset_modal').style.display = 'none';
        }

        function resetOrderNow() {
            clearDraft();
            window.location.reload();
        }

        function adjustMainQty(val) {
            const input = document.getElementById('main_quantity');
            let current = parseFloat(input.value) || 0;
            input.value = (Math.max(0.01, current + val)).toFixed(2);
        }

        function adjustItemQty(btn, val) {
            const input = btn.parentElement.querySelector('.quantity-input');
            let current = parseFloat(input.value) || 0;
            input.value = (Math.max(0.01, current + val)).toFixed(2);
            updateTotals();
            saveDraft();
        }

        // Función para seleccionar cliente
        function selectCustomer(customer) {
            document.getElementById('customer_search').value = `${customer.full_name} - ${customer.identification}`;
            document.getElementById('customer_id').value = customer.id;
            document.getElementById('customer_list').style.display = 'none';
            saveDraft();
        }

        // Prevenir Enter en inputs de búsqueda
        document.getElementById('customer_search').addEventListener('keydown', (e) => { if(e.key === 'Enter') e.preventDefault(); });
        document.getElementById('product_search').addEventListener('keydown', (e) => { if(e.key === 'Enter') e.preventDefault(); });

        let selectedProduct = null;

        // Función para filtrar productos
        function filterProducts() {
            let query = document.getElementById('product_search').value;
            const productsRoute = @json(route('products.search'));
            const productList = document.getElementById('product_list');
            
            if (query.length > 1) {
                fetch(`${productsRoute}?query=${query}`)
                    .then(response => response.json())
                    .then(data => {
                        productList.innerHTML = '';
                        
                        if (data.length === 1) {
                            productList.style.display = 'none';
                            showProductDetails(data[0]);
                            return;
                        }

                        if(data.length > 0) {
                            productList.style.display = 'block';
                            const limitedData = data.slice(0, 20);
                            
                            limitedData.forEach(product => {
                                let div = document.createElement('div');
                                div.textContent = `${product.name} - ${product.code}`;
                                div.classList.add('nav-item');
                                div.style.color = '#1E1B2E';
                                div.style.cursor = 'pointer';
                                div.addEventListener('click', function() {
                                    showProductDetails(product);
                                });
                                productList.appendChild(div);
                            });
                        } else {
                            productList.style.display = 'none';
                        }
                    });
            } else {
                productList.style.display = 'none';
            }
        }

        function showProductDetails(product) {
            selectedProduct = product;
            document.getElementById('product_name').textContent = product.name;
            document.getElementById('product_code').textContent = `Cód: ${product.code}`;
            document.getElementById('main_quantity').value = 1;

            const priceOptions = document.getElementById('price_options');
            priceOptions.innerHTML = '';

            const taxRate = parseFloat(product.tax_rate) || 0;
            const prices = [
                { label: 'Precio 1', value: parseFloat(product.base_price_1 || product.base_price) },
                { label: 'Precio 2', value: parseFloat(product.base_price_2) },
                { label: 'Precio 3', value: parseFloat(product.base_price_3) }
            ];

            let firstPriceFound = false;
            prices.forEach((p) => {
                if (p.value > 0) {
                    const priceWithTax = p.value * (1 + (taxRate / 100));
                    const label = document.createElement('label');
                    label.style.display = 'flex';
                    label.style.alignItems = 'center';
                    label.style.gap = '8px';
                    label.style.padding = '8px';
                    label.style.border = '1px solid #EDE9FF';
                    label.style.borderRadius = '8px';
                    label.style.cursor = 'pointer';
                    label.style.fontSize = '13px';
                    label.innerHTML = `
                        <input type="radio" name="selected_price_radio" value="${p.value}" ${!firstPriceFound ? 'checked' : ''}>
                        <span>${p.label}: <strong>$${priceWithTax.toLocaleString('es-CO')}</strong></span>
                    `;
                    priceOptions.appendChild(label);
                    firstPriceFound = true;
                }
            });

            document.getElementById('selected_product_details').style.display = 'block';
            document.getElementById('product_list').style.display = 'none';
            document.getElementById('product_search').value = product.name;
        }

        function renderProductItem(data) {
            let template = document.getElementById('product-template').content.cloneNode(true);
            const item = template.querySelector('.product-item');
            item.dataset.priceKey = data.priceKey;

            const taxRate = parseFloat(data.taxRate);
            const basePrice = parseFloat(data.basePrice);
            const unitPriceWithTax = basePrice * (1 + (taxRate / 100));

            template.querySelector('.product-name').textContent = data.name;
            template.querySelector('.product-item-code').textContent = `[${data.code || 'N/A'}]`;
            template.querySelector('.product-input').value = data.productId;
            template.querySelector('.product-base-price').value = basePrice;
            template.querySelector('.product-tax-rate').value = taxRate;
            template.querySelector('.quantity-input').value = data.quantity;
            template.querySelector('.unit-price').textContent = unitPriceWithTax.toLocaleString('es-CO', {minimumFractionDigits: 2});
            
            template.querySelector('.quantity-input').addEventListener('change', () => {
                updateTotals();
                saveDraft();
            });
            
            template.querySelector('.remove-product').addEventListener('click', function() {
                this.closest('.product-item').remove();
                updateTotals();
                saveDraft();
            });

            document.getElementById('selected_products_list').appendChild(template);
        }

        document.getElementById('add_product_button').addEventListener('click', function() {
            if (!selectedProduct) return;

            const selectedPriceRadio = document.querySelector('input[name="selected_price_radio"]:checked');
            if (!selectedPriceRadio) { alert('Selecciona un precio'); return; }

            const chosenBasePrice = parseFloat(selectedPriceRadio.value);
            const quantityToAdd = parseFloat(document.getElementById('main_quantity').value) || 1;
            const priceKey = `${selectedProduct.id}_${chosenBasePrice}`;
            const existingProduct = findExistingProduct(priceKey);

            if (existingProduct) {
                const quantityInput = existingProduct.querySelector('.quantity-input');
                quantityInput.value = (parseFloat(quantityInput.value) + quantityToAdd).toFixed(2);
            } else {
                renderProductItem({
                    priceKey: priceKey,
                    productId: selectedProduct.id,
                    name: selectedProduct.name,
                    code: selectedProduct.code,
                    basePrice: chosenBasePrice,
                    taxRate: selectedProduct.tax_rate,
                    quantity: quantityToAdd
                });
            }

            updateTotals();
            saveDraft();
            document.getElementById('selected_product_details').style.display = 'none';
            document.getElementById('product_search').value = '';
            selectedProduct = null;
        });

        function findExistingProduct(priceKey) {
            const productItems = document.querySelectorAll('.product-item');
            for (let item of productItems) {
                if (item.dataset.priceKey === priceKey) return item;
            }
            return null;
        }

        function updateTotals() {
            let totalBasePrice = 0;
            let totalTax = 0;
            
            document.querySelectorAll('.product-item').forEach(item => {
                const quantity = parseFloat(item.querySelector('.quantity-input').value) || 0;
                const basePrice = parseFloat(item.querySelector('.product-base-price').value);
                const taxRate = parseFloat(item.querySelector('.product-tax-rate').value);
                
                const subtotalBase = basePrice * quantity;
                const subtotalTax = (basePrice * taxRate / 100) * quantity;
                const subtotalTotal = subtotalBase + subtotalTax;
                
                item.querySelector('.subtotal-amount').textContent = subtotalTotal.toLocaleString('es-CO', {minimumFractionDigits: 2});
                totalBasePrice += subtotalBase;
                totalTax += subtotalTax;
            });

            const totalFinal = totalBasePrice + totalTax;
            document.querySelector('#total_base_price span').textContent = totalBasePrice.toLocaleString('es-CO', {minimumFractionDigits: 2});
            document.querySelector('#total_tax span').textContent = totalTax.toLocaleString('es-CO', {minimumFractionDigits: 2});
            document.querySelector('#total_price span').textContent = totalFinal.toLocaleString('es-CO', {minimumFractionDigits: 2});
            
            document.getElementById('order_totals').style.display = 
                document.querySelectorAll('.product-item').length > 0 ? 'block' : 'none';
        }
    </script>

</x-app-layout>
