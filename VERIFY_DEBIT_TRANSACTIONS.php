<?php
/**
 * Verify Debit Transactions for Coin Spending
 * 
 * Run this in your Laravel tinker to verify transactions are being created:
 * php artisan tinker
 * 
 * Then copy and paste the code below
 */

// 1. Check recent enquiry_post transactions
echo "=== Recent Enquiry Post Transactions ===\n";
$postTransactions = \App\Models\CoinTransaction::where('type', 'enquiry_post')
    ->latest()
    ->limit(5)
    ->get();

foreach ($postTransactions as $t) {
    echo "ID: {$t->id} | User: {$t->user->name} | Amount: {$t->amount} | Balance After: {$t->balance_after}\n";
    echo "Description: {$t->description}\n";
    echo "Meta: " . json_encode($t->meta) . "\n\n";
}

if ($postTransactions->isEmpty()) {
    echo "No enquiry_post transactions found yet.\n\n";
}

// 2. Check recent enquiry_unlock transactions
echo "=== Recent Enquiry Unlock Transactions ===\n";
$unlockTransactions = \App\Models\CoinTransaction::where('type', 'enquiry_unlock')
    ->latest()
    ->limit(5)
    ->get();

foreach ($unlockTransactions as $t) {
    echo "ID: {$t->id} | User: {$t->user->name} | Amount: {$t->amount} | Balance After: {$t->balance_after}\n";
    echo "Description: {$t->description}\n";
    echo "Meta: " . json_encode($t->meta) . "\n\n";
}

if ($unlockTransactions->isEmpty()) {
    echo "No enquiry_unlock transactions found yet.\n\n";
}

// 3. Check a specific user's coin balance and transactions
echo "=== Sample User Coin Balance ===\n";
$user = \App\Models\User::whereHas('coinTransactions', function ($q) {
    $q->whereIn('type', ['enquiry_post', 'enquiry_unlock']);
})->first();

if ($user) {
    echo "User: {$user->name}\n";
    echo "Current Balance: {$user->coins} coins\n";
    echo "Total Transactions: {$user->coinTransactions()->count()}\n";
    echo "Debit Transactions: {$user->coinTransactions()->whereIn('type', ['enquiry_post', 'enquiry_unlock'])->count()}\n\n";
    
    echo "Recent Transactions:\n";
    $user->coinTransactions()->latest()->limit(10)->get()->each(function ($t) {
        echo "- {$t->type}: {$t->amount} coins ({$t->description})\n";
    });
} else {
    echo "No users with enquiry transactions found.\n";
}

// 4. Check database schema
echo "\n=== Database Schema ===\n";
echo "coin_transactions table columns:\n";
\DB::connection()->getSchemaBuilder()->getColumnListing('coin_transactions');
$columns = \DB::table('information_schema.columns')
    ->where('table_name', 'coin_transactions')
    ->where('table_schema', \DB::getDatabaseName())
    ->get();

foreach ($columns as $col) {
    echo "- {$col->COLUMN_NAME} ({$col->COLUMN_TYPE})\n";
}

echo "\n✅ Verification complete!\n";
?>
