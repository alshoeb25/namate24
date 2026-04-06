<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        @php
            $stats = $this->getStats();
        @endphp
        
        <!-- Total Coins in Wallets -->
        <div class="p-6 bg-gradient-to-br from-green-50 to-green-100 rounded-lg shadow-md border border-green-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-semibold">Total Coins in Wallets</p>
                    <p class="text-3xl font-bold text-green-600 mt-2">{{ number_format($stats['totalCoinsInWallets'], 0) }}</p>
                    <p class="text-xs text-gray-500 mt-1">Across all users</p>
                </div>
                <div class="text-4xl text-green-300">
                    <i class="fas fa-wallet"></i>
                </div>
            </div>
        </div>

        <!-- Coin Packages -->
        <div class="p-6 bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg shadow-md border border-blue-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-semibold">Coin Packages</p>
                    <p class="text-3xl font-bold text-blue-600 mt-2">{{ $stats['totalCoinPackages'] }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ $stats['activeCoinPackages'] }} active</p>
                </div>
                <div class="text-4xl text-blue-300">
                    <i class="fas fa-banknotes"></i>
                </div>
            </div>
        </div>

        <!-- Total Transactions -->
        <div class="p-6 bg-gradient-to-br from-orange-50 to-orange-100 rounded-lg shadow-md border border-orange-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-semibold">Total Transactions</p>
                    <p class="text-3xl font-bold text-orange-600 mt-2">{{ number_format($stats['totalTransactions'], 0) }}</p>
                    <p class="text-xs text-gray-500 mt-1">Coin transaction records</p>
                </div>
                <div class="text-4xl text-orange-300">
                    <i class="fas fa-arrow-right-arrow-left"></i>
                </div>
            </div>
        </div>

        <!-- Total Coins Awarded -->
        <div class="p-6 bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-lg shadow-md border border-emerald-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-semibold">Total Coins Awarded</p>
                    <p class="text-3xl font-bold text-emerald-600 mt-2">{{ number_format($stats['totalCoinsAwarded'], 0) }}</p>
                    <p class="text-xs text-gray-500 mt-1">From purchases & bonuses</p>
                </div>
                <div class="text-4xl text-emerald-300">
                    <i class="fas fa-arrow-down-circle"></i>
                </div>
            </div>
        </div>

        <!-- Total Coins Spent -->
        <div class="p-6 bg-gradient-to-br from-red-50 to-red-100 rounded-lg shadow-md border border-red-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-semibold">Total Coins Spent</p>
                    <p class="text-3xl font-bold text-red-600 mt-2">{{ number_format($stats['totalCoinsSpent'], 0) }}</p>
                    <p class="text-xs text-gray-500 mt-1">For unlocks & views</p>
                </div>
                <div class="text-4xl text-red-300">
                    <i class="fas fa-arrow-up-circle"></i>
                </div>
            </div>
        </div>

        <!-- Coin Flow Balance -->
        <div class="p-6 bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg shadow-md border border-purple-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-600 text-sm font-semibold">Coin Flow Balance</p>
                    @php
                        $balance = $stats['totalCoinsAwarded'] - $stats['totalCoinsSpent'];
                        $balanceClass = $balance >= 0 ? 'text-purple-600' : 'text-red-600';
                    @endphp
                    <p class="text-3xl font-bold {{ $balanceClass }} mt-2">{{ number_format($balance, 0) }}</p>
                    <p class="text-xs text-gray-500 mt-1">Awards - Spent</p>
                </div>
                <div class="text-4xl text-purple-300">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment History Section -->
    <div class="mb-8">
        <h3 class="text-2xl font-bold text-gray-800 mb-4">Payment History Statistics</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- Total Orders -->
            <div class="p-4 bg-gradient-to-br from-indigo-50 to-indigo-100 rounded-lg shadow-md border border-indigo-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-xs font-semibold uppercase">Total Orders</p>
                        <p class="text-2xl font-bold text-indigo-600 mt-1">{{ $stats['totalOrders'] }}</p>
                    </div>
                    <i class="fas fa-shopping-cart text-indigo-300 text-3xl"></i>
                </div>
            </div>

            <!-- Completed Orders -->
            <div class="p-4 bg-gradient-to-br from-green-50 to-green-100 rounded-lg shadow-md border border-green-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-xs font-semibold uppercase">Completed</p>
                        <p class="text-2xl font-bold text-green-600 mt-1">{{ $stats['completedOrders'] }}</p>
                    </div>
                    <i class="fas fa-check-circle text-green-300 text-3xl"></i>
                </div>
            </div>

            <!-- Pending Orders -->
            <div class="p-4 bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg shadow-md border border-yellow-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-xs font-semibold uppercase">Pending</p>
                        <p class="text-2xl font-bold text-yellow-600 mt-1">{{ $stats['pendingOrders'] }}</p>
                    </div>
                    <i class="fas fa-clock text-yellow-300 text-3xl"></i>
                </div>
            </div>

            <!-- Failed Orders -->
            <div class="p-4 bg-gradient-to-br from-red-50 to-red-100 rounded-lg shadow-md border border-red-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-xs font-semibold uppercase">Failed</p>
                        <p class="text-2xl font-bold text-red-600 mt-1">{{ $stats['failedOrders'] }}</p>
                    </div>
                    <i class="fas fa-times-circle text-red-300 text-3xl"></i>
                </div>
            </div>

            <!-- Total Payment Amount -->
            <div class="p-4 bg-gradient-to-br from-teal-50 to-teal-100 rounded-lg shadow-md border border-teal-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-600 text-xs font-semibold uppercase">Total Revenue</p>
                        <p class="text-2xl font-bold text-teal-600 mt-1">₹{{ number_format($stats['totalPaymentAmount'], 0) }}</p>
                    </div>
                    <i class="fas fa-money-bill-trend-up text-teal-300 text-3xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Payments Table -->
    <div class="mb-8">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-2xl font-bold text-gray-800">Recent Payments</h3>
            <a href="{{ route('filament.admin.resources.orders.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-semibold">
                <i class="fas fa-arrow-right"></i>
                View All Orders
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Order ID</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">User</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($this->getRecentPayments() as $order)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-sm font-mono text-gray-700">{{ substr($order->razorpay_order_id, 0, 15) }}...</td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                <span class="font-semibold">{{ $order->user->name }}</span>
                                <br>
                                <span class="text-xs text-gray-500">{{ $order->user->email }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm font-bold text-gray-900">₹{{ number_format($order->amount, 2) }}</td>
                            <td class="px-6 py-4 text-sm">
                                @php
                                    $statusClasses = [
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'initiated' => 'bg-yellow-100 text-yellow-800',
                                        'INITIATED' => 'bg-yellow-100 text-yellow-800',
                                        'completed' => 'bg-green-100 text-green-800',
                                        'paid' => 'bg-green-100 text-green-800',
                                        'failed' => 'bg-red-100 text-red-800',
                                        'FAILED' => 'bg-red-100 text-red-800',
                                        'cancelled' => 'bg-red-100 text-red-800',
                                        'CANCELLED' => 'bg-red-100 text-red-800',
                                    ];
                                    $class = $statusClasses[strtolower($order->status)] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $class }}">
                                    {{ ucfirst(strtolower($order->status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $order->created_at->format('M d, Y') }}
                                <br>
                                <span class="text-xs text-gray-500">{{ $order->created_at->format('h:i A') }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-inbox text-3xl mb-2"></i>
                                <p>No payment records found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Payment Transactions -->
    <div class="mb-8">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-2xl font-bold text-gray-800">Recent Payment Transactions</h3>
            <a href="{{ route('filament.admin.resources.payment-transactions.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm font-semibold">
                <i class="fas fa-arrow-right"></i>
                View All Transactions
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Transaction ID</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">User</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Coins</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Bonus</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($this->getRecentPaymentTransactions() as $transaction)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-sm font-mono text-gray-700">{{ substr($transaction->razorpay_payment_id ?? 'N/A', 0, 12) }}...</td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                <span class="font-semibold">{{ $transaction->user->name ?? 'Unknown' }}</span>
                                <br>
                                <span class="text-xs text-gray-500">{{ $transaction->user->email ?? 'N/A' }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm font-bold text-gray-900">₹{{ number_format($transaction->amount, 2) }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                <span class="font-semibold text-blue-600">{{ $transaction->coins }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                <span class="font-semibold text-green-600">{{ $transaction->bonus_coins ?? 0 }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                <span class="inline-flex items-center gap-1 px-2 py-1 bg-gray-100 rounded">
                                    {{ ucwords(str_replace('_', ' ', $transaction->type ?? 'N/A')) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @php
                                    $statusClasses = [
                                        'authorized' => 'bg-blue-100 text-blue-800',
                                        'AUTHORIZED' => 'bg-blue-100 text-blue-800',
                                        'captured' => 'bg-blue-100 text-blue-800',
                                        'CAPTURED' => 'bg-blue-100 text-blue-800',
                                        'settled' => 'bg-green-100 text-green-800',
                                        'SETTLED' => 'bg-green-100 text-green-800',
                                        'success' => 'bg-green-100 text-green-800',
                                        'SUCCESS' => 'bg-green-100 text-green-800',
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'PENDING' => 'bg-yellow-100 text-yellow-800',
                                        'failed' => 'bg-red-100 text-red-800',
                                        'FAILED' => 'bg-red-100 text-red-800',
                                        'refunded' => 'bg-gray-100 text-gray-800',
                                        'REFUNDED' => 'bg-gray-100 text-gray-800',
                                    ];
                                    $class = $statusClasses[strtolower($transaction->status)] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $class }}">
                                    {{ ucfirst(strtolower($transaction->status ?? 'unknown')) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $transaction->created_at->format('M d, Y') }}
                                <br>
                                <span class="text-xs text-gray-500">{{ $transaction->created_at->format('h:i A') }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-inbox text-3xl mb-2"></i>
                                <p>No payment transactions found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Quick Links Section -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Quick Links</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="{{ route('filament.admin.resources.coin-packages.index') }}" class="flex items-center gap-3 p-4 border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition">
                <i class="fas fa-banknotes text-blue-600 text-2xl"></i>
                <div>
                    <p class="font-semibold text-gray-800">Coin Packages</p>
                    <p class="text-xs text-gray-500">Manage packages</p>
                </div>
            </a>
            
            <a href="{{ route('filament.admin.resources.coin-transactions.index') }}" class="flex items-center gap-3 p-4 border border-gray-200 rounded-lg hover:bg-orange-50 hover:border-orange-300 transition">
                <i class="fas fa-arrow-right-arrow-left text-orange-600 text-2xl"></i>
                <div>
                    <p class="font-semibold text-gray-800">Transactions</p>
                    <p class="text-xs text-gray-500">Transaction logs</p>
                </div>
            </a>
            
            <a href="{{ route('filament.admin.resources.payment-transactions.index') }}" class="flex items-center gap-3 p-4 border border-gray-200 rounded-lg hover:bg-teal-50 hover:border-teal-300 transition">
                <i class="fas fa-credit-card text-teal-600 text-2xl"></i>
                <div>
                    <p class="font-semibold text-gray-800">Payment Transactions</p>
                    <p class="text-xs text-gray-500">Payment history</p>
                </div>
            </a>
            
            <a href="{{ route('filament.admin.resources.orders.index') }}" class="flex items-center gap-3 p-4 border border-gray-200 rounded-lg hover:bg-purple-50 hover:border-purple-300 transition">
                <i class="fas fa-shopping-cart text-purple-600 text-2xl"></i>
                <div>
                    <p class="font-semibold text-gray-800">Payment Orders</p>
                    <p class="text-xs text-gray-500">Order management</p>
                </div>
            </a>
            
            <a href="{{ route('filament.admin.resources.subscription-plans.index') }}" class="flex items-center gap-3 p-4 border border-gray-200 rounded-lg hover:bg-green-50 hover:border-green-300 transition">
                <i class="fas fa-star text-green-600 text-2xl"></i>
                <div>
                    <p class="font-semibold text-gray-800">Subscription Plans</p>
                    <p class="text-xs text-gray-500">Plan features</p>
                </div>
            </a>
        </div>
    </div>

    <!-- Info Section -->
    <div class="bg-blue-50 border-l-4 border-blue-500 rounded-lg p-6">
        <div class="flex items-start gap-4">
            <i class="fas fa-info-circle text-blue-600 text-xl mt-1"></i>
            <div>
                <h4 class="font-bold text-blue-900 mb-2">Wallet Management Overview</h4>
                <ul class="text-sm text-blue-800 space-y-1">
                    <li>• <strong>Coin Statistics:</strong> Monitor total coins in circulation, awarded, and spent</li>
                    <li>• <strong>Payment Statistics:</strong> Track order status, completion rate, and total revenue</li>
                    <li>• <strong>Coin Packages:</strong> Create and manage coin packages available for purchase</li>
                    <li>• <strong>Subscription Plans:</strong> Configure support features for different subscription tiers</li>
                </ul>
            </div>
        </div>
    </div>
</x-filament-panels::page>
