@php
    use Filament\Support\Facades\FilamentAsset;
@endphp

<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
                Referral Invites Statistics
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Overview of all referral invites
            </p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-5">
            <!-- Total Invites -->
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">Total Invites</dt>
                            <dd class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Used Invites -->
            <div class="rounded-lg border border-green-200 bg-green-50 p-6 shadow-sm dark:border-green-700 dark:bg-green-900/20">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="truncate text-sm font-medium text-green-600 dark:text-green-400">Used / Redeemed</dt>
                            <dd class="mt-1 text-3xl font-bold text-green-700 dark:text-green-300">{{ $stats['used'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Unused Invites -->
            <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-6 shadow-sm dark:border-yellow-700 dark:bg-yellow-900/20">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="truncate text-sm font-medium text-yellow-600 dark:text-yellow-400">Pending</dt>
                            <dd class="mt-1 text-3xl font-bold text-yellow-700 dark:text-yellow-300">{{ $stats['unused'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Total Coins Offered -->
            <div class="rounded-lg border border-blue-200 bg-blue-50 p-6 shadow-sm dark:border-blue-700 dark:bg-blue-900/20">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="truncate text-sm font-medium text-blue-600 dark:text-blue-400">Total Coins Offered</dt>
                            <dd class="mt-1 text-3xl font-bold text-blue-700 dark:text-blue-300">{{ $stats['total_coins_offered'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Total Coins Redeemed -->
            <div class="rounded-lg border border-purple-200 bg-purple-50 p-6 shadow-sm dark:border-purple-700 dark:bg-purple-900/20">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="truncate text-sm font-medium text-purple-600 dark:text-purple-400">Coins Redeemed</dt>
                            <dd class="mt-1 text-3xl font-bold text-purple-700 dark:text-purple-300">{{ $stats['total_coins_redeemed'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Info -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Summary</h2>
            
            <div class="space-y-4 text-sm text-gray-600 dark:text-gray-400">
                <div class="flex justify-between border-b border-gray-200 pb-2 dark:border-gray-700">
                    <span>Conversion Rate:</span>
                    <span class="font-semibold text-gray-900 dark:text-white">
                        @if($stats['total'] > 0)
                            {{ round(($stats['used'] / $stats['total']) * 100, 2) }}%
                        @else
                            0%
                        @endif
                    </span>
                </div>
                <div class="flex justify-between border-b border-gray-200 pb-2 dark:border-gray-700">
                    <span>Average Coins per Invite:</span>
                    <span class="font-semibold text-gray-900 dark:text-white">
                        @if($stats['total'] > 0)
                            {{ round($stats['total_coins_offered'] / $stats['total'], 2) }}
                        @else
                            0
                        @endif
                    </span>
                </div>
                <div class="flex justify-between">
                    <span>Coins Still Available:</span>
                    <span class="font-semibold text-gray-900 dark:text-white">
                        {{ $stats['total_coins_offered'] - $stats['total_coins_redeemed'] }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
