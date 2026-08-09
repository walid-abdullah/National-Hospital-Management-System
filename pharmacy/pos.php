<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Pharmacist') {
    header("Location: ../login.php");
    exit();
}
require_once '../config/db.php';

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT hospital_id FROM users WHERE id = :user_id");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$hospital_id = $stmt->fetch(PDO::FETCH_ASSOC)['hospital_id'];

// Handle Checkout
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['checkout'])) {
    $patient_name = trim($_POST['patient_name']);
    $cart_data = json_decode($_POST['cart_data'], true);
    $total_amount = (float)$_POST['total_amount'];
    
    if(!empty($cart_data)) {
        try {
            $conn->beginTransaction();
            
            // Insert Sale
            $ins_sale = $conn->prepare("INSERT INTO pharmacy_sales (hospital_id, patient_name, total_amount) VALUES (?, ?, ?)");
            $ins_sale->execute([$hospital_id, $patient_name, $total_amount]);
            
            // Deduct Stock
            $upd_stock = $conn->prepare("UPDATE pharmacy_inventory SET stock_quantity = stock_quantity - ? WHERE id = ? AND hospital_id = ?");
            foreach($cart_data as $item) {
                $upd_stock->execute([$item['qty'], $item['id'], $hospital_id]);
            }
            
            $conn->commit();
            $success = "Sale completed successfully!";
        } catch (Exception $e) {
            $conn->rollBack();
            $error = "Transaction failed: " . $e->getMessage();
        }
    }
}

// Fetch all medicines with positive stock
$stmt = $conn->prepare("SELECT id, medicine_name, category, stock_quantity, unit_price FROM pharmacy_inventory WHERE hospital_id = :hospital_id AND stock_quantity > 0 ORDER BY medicine_name ASC");
$stmt->execute([':hospital_id' => $hospital_id]);
$medicines = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy POS - NHMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.3); }
        .dark .glass { background: rgba(30, 41, 59, 0.85); border: 1px solid rgba(255, 255, 255, 0.1); }
        .glass-nav { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(226, 232, 240, 0.8); }
        .dark .glass-nav { background: rgba(15, 23, 42, 0.9); border-bottom: 1px solid rgba(51, 65, 85, 0.8); }
        .custom-gradient-text { background: linear-gradient(135deg, #059669, #10b981); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .dark .custom-gradient-text { background: linear-gradient(135deg, #34d399, #6ee7b7); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        
        /* Custom scrollbar for cart */
        .cart-scroll::-webkit-scrollbar { width: 6px; }
        .cart-scroll::-webkit-scrollbar-track { background: transparent; }
        .cart-scroll::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 20px; }
        .dark .cart-scroll::-webkit-scrollbar-thumb { background-color: #475569; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased flex flex-col min-h-screen transition-colors duration-300 dark:bg-gray-900 dark:text-gray-100">
    <nav class="glass-nav sticky top-0 z-50 p-4 shadow-sm flex justify-between items-center">
        <h1 class="text-2xl font-extrabold custom-gradient-text tracking-tight">NHMS - Point of Sale</h1>
        <div class="flex items-center space-x-4">
            <a href="dashboard.php" class="text-gray-600 dark:text-gray-300 hover:text-emerald-600 transition font-medium">Dashboard</a>
            <a href="inventory.php" class="text-gray-600 dark:text-gray-300 hover:text-emerald-600 transition font-medium">Inventory</a>
            <a href="pos.php" class="text-emerald-600 font-medium">POS Terminal</a>
            <span class="border-l border-emerald-400 h-6 mx-2"></span>
            <a href="../logout.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded text-sm transition shadow text-white">Logout</a>
        </div>
    </nav>

    <div class="max-w-screen-2xl mx-auto mt-6 w-full p-4 grid grid-cols-1 lg:grid-cols-3 gap-6 flex-grow">
        
        <!-- Left Side: Medicine List -->
        <div class="lg:col-span-2 glass rounded-2xl shadow-xl flex flex-col h-[calc(100vh-140px)]">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 rounded-t-2xl">
                <input type="text" id="searchInput" placeholder="Search medicines by name or category..." class="w-full p-3 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500 outline-none shadow-sm transition">
            </div>
            
            <div class="overflow-y-auto flex-grow p-4 cart-scroll">
                <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4" id="medicineGrid">
                    <?php foreach($medicines as $med): ?>
                    <div class="med-card bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm hover:shadow-md border border-gray-100 dark:border-gray-700 transition cursor-pointer select-none active:scale-95 flex flex-col justify-between h-full"
                         data-id="<?php echo $med['id']; ?>" 
                         data-name="<?php echo htmlspecialchars($med['medicine_name']); ?>" 
                         data-price="<?php echo $med['unit_price']; ?>"
                         data-stock="<?php echo $med['stock_quantity']; ?>">
                         
                        <div>
                            <div class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 mb-1"><?php echo htmlspecialchars($med['category']); ?></div>
                            <h3 class="font-bold text-gray-800 dark:text-gray-100 leading-tight mb-2"><?php echo htmlspecialchars($med['medicine_name']); ?></h3>
                        </div>
                        <div class="flex justify-between items-end mt-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                            <div class="font-mono font-black text-lg">৳<?php echo number_format($med['unit_price'], 2); ?></div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Stock: <span class="font-bold <?php echo $med['stock_quantity']<20?'text-rose-500':'';?>"><?php echo $med['stock_quantity']; ?></span></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Right Side: Cart / Checkout -->
        <div class="glass rounded-2xl shadow-xl flex flex-col h-[calc(100vh-140px)] border-t-4 border-emerald-500">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-emerald-50 dark:bg-slate-800 rounded-t-2xl">
                <h2 class="text-xl font-bold flex items-center"><span class="text-2xl mr-2">🛒</span> Current Order</h2>
                <?php if(isset($success)) echo "<div class='mt-2 text-sm text-emerald-700 bg-emerald-100 p-2 rounded'>$success</div>"; ?>
                <?php if(isset($error)) echo "<div class='mt-2 text-sm text-rose-700 bg-rose-100 p-2 rounded'>$error</div>"; ?>
            </div>
            
            <div class="flex-grow overflow-y-auto p-4 cart-scroll" id="cartContainer">
                <div id="emptyCartMsg" class="text-center text-gray-400 mt-10">
                    <div class="text-4xl mb-2">🛍️</div>
                    <p>Cart is empty. Click items to add.</p>
                </div>
                <div id="cartItems" class="space-y-3 hidden">
                    <!-- Cart items injected via JS -->
                </div>
            </div>
            
            <div class="p-6 bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 rounded-b-2xl">
                <form method="POST" id="checkoutForm">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-gray-600 dark:text-gray-400 font-medium">Subtotal</span>
                        <span class="font-mono font-bold" id="cartSubtotal">৳0.00</span>
                    </div>
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-gray-600 dark:text-gray-400 font-medium">Tax (0%)</span>
                        <span class="font-mono font-bold">৳0.00</span>
                    </div>
                    <div class="flex justify-between items-center border-t border-gray-300 dark:border-gray-600 pt-4 mb-6">
                        <span class="text-xl font-black text-gray-800 dark:text-white">Total</span>
                        <span class="text-2xl font-black text-emerald-600 dark:text-emerald-400 font-mono" id="cartTotal">৳0.00</span>
                    </div>
                    
                    <input type="text" name="patient_name" placeholder="Patient Name (Optional)" class="w-full p-3 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-900 mb-4 focus:ring-2 focus:ring-emerald-500 outline-none">
                    
                    <input type="hidden" name="cart_data" id="cartDataInput">
                    <input type="hidden" name="total_amount" id="totalAmountInput">
                    
                    <button type="button" id="payBtn" disabled class="w-full bg-emerald-600 hover:bg-emerald-700 disabled:bg-gray-400 text-white font-bold py-4 rounded-xl shadow-lg transition-all text-lg flex justify-center items-center">
                        💳 Process Payment
                    </button>
                    <!-- Actual submit button hidden -->
                    <button type="submit" name="checkout" id="submitCheckout" class="hidden"></button>
                </form>
            </div>
        </div>

    </div>

    <!-- POS Javascript -->
    <script>
        let cart = {};
        
        // Search functionality
        const searchInput = document.getElementById('searchInput');
        const medCards = document.querySelectorAll('.med-card');
        
        searchInput.addEventListener('input', (e) => {
            const term = e.target.value.toLowerCase();
            medCards.forEach(card => {
                const name = card.dataset.name.toLowerCase();
                if(name.includes(term)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        // Add to cart
        medCards.forEach(card => {
            card.addEventListener('click', () => {
                const id = card.dataset.id;
                const name = card.dataset.name;
                const price = parseFloat(card.dataset.price);
                const stock = parseInt(card.dataset.stock);
                
                if (cart[id]) {
                    if (cart[id].qty < stock) {
                        cart[id].qty++;
                    } else {
                        alert("Not enough stock!");
                    }
                } else {
                    cart[id] = { id, name, price, qty: 1, max: stock };
                }
                
                updateCartUI();
            });
        });

        function updateCartUI() {
            const cartItemsDiv = document.getElementById('cartItems');
            const emptyMsg = document.getElementById('emptyCartMsg');
            const subtotalEl = document.getElementById('cartSubtotal');
            const totalEl = document.getElementById('cartTotal');
            const payBtn = document.getElementById('payBtn');
            const cartDataInput = document.getElementById('cartDataInput');
            const totalAmountInput = document.getElementById('totalAmountInput');
            
            cartItemsDiv.innerHTML = '';
            let total = 0;
            let count = 0;
            
            for (let id in cart) {
                count++;
                const item = cart[id];
                const itemTotal = item.price * item.qty;
                total += itemTotal;
                
                const div = document.createElement('div');
                div.className = 'flex justify-between items-center bg-white dark:bg-gray-900 p-3 rounded-lg border border-gray-100 dark:border-gray-700 shadow-sm';
                div.innerHTML = `
                    <div class="flex-1">
                        <div class="font-bold text-sm text-gray-800 dark:text-gray-100 line-clamp-1" title="${item.name}">${item.name}</div>
                        <div class="text-xs text-gray-500 font-mono">৳${item.price.toFixed(2)}</div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="flex items-center bg-gray-100 dark:bg-gray-800 rounded-md">
                            <button type="button" class="px-2 py-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-l text-gray-600 dark:text-gray-300" onclick="updateQty(${id}, -1)">-</button>
                            <span class="w-8 text-center font-bold text-sm">${item.qty}</span>
                            <button type="button" class="px-2 py-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-r text-gray-600 dark:text-gray-300" onclick="updateQty(${id}, 1)">+</button>
                        </div>
                        <div class="font-bold font-mono text-sm w-16 text-right">৳${itemTotal.toFixed(2)}</div>
                        <button type="button" class="text-rose-500 hover:text-rose-700" onclick="removeItem(${id})">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                `;
                cartItemsDiv.appendChild(div);
            }
            
            if (count > 0) {
                emptyMsg.classList.add('hidden');
                cartItemsDiv.classList.remove('hidden');
                payBtn.disabled = false;
            } else {
                emptyMsg.classList.remove('hidden');
                cartItemsDiv.classList.add('hidden');
                payBtn.disabled = true;
            }
            
            const totalFmt = `৳${total.toFixed(2)}`;
            subtotalEl.innerText = totalFmt;
            totalEl.innerText = totalFmt;
            
            // Prepare inputs for submission
            const cartArr = Object.values(cart).map(i => ({id: i.id, qty: i.qty}));
            cartDataInput.value = JSON.stringify(cartArr);
            totalAmountInput.value = total.toFixed(2);
        }

        window.updateQty = function(id, change) {
            if (cart[id]) {
                const newQty = cart[id].qty + change;
                if (newQty > 0 && newQty <= cart[id].max) {
                    cart[id].qty = newQty;
                } else if (newQty > cart[id].max) {
                    alert("Maximum stock reached!");
                } else if (newQty === 0) {
                    delete cart[id];
                }
                updateCartUI();
            }
        };
        
        window.removeItem = function(id) {
            delete cart[id];
            updateCartUI();
        }
        
        document.getElementById('payBtn').addEventListener('click', () => {
            if(confirm("Confirm Payment & Checkout?")) {
                document.getElementById('submitCheckout').click();
            }
        });
    </script>
</body>
</html>
