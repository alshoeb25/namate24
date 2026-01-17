<div class="rounded-lg border bg-white p-6 dark:bg-gray-800">
    <h3 class="mb-4 text-lg font-semibold">{{ $title }}</h3>

    <!-- Summary Stats -->
    <div class="mb-6 grid grid-cols-4 gap-4">
        <div class="rounded bg-green-50 p-4 dark:bg-green-900/20">
            <p class="text-sm text-gray-600 dark:text-gray-400">Success</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $results['success'] ?? 0 }}</p>
        </div>
        <div class="rounded bg-red-50 p-4 dark:bg-red-900/20">
            <p class="text-sm text-gray-600 dark:text-gray-400">Failed</p>
            <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $results['failed'] ?? 0 }}</p>
        </div>
        <div class="rounded bg-yellow-50 p-4 dark:bg-yellow-900/20">
            <p class="text-sm text-gray-600 dark:text-gray-400">Duplicates</p>
            <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $results['duplicates'] ?? 0 }}</p>
        </div>
        <div class="rounded bg-blue-50 p-4 dark:bg-blue-900/20">
            <p class="text-sm text-gray-600 dark:text-gray-400">Total</p>
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                {{ ($results['success'] ?? 0) + ($results['failed'] ?? 0) + ($results['duplicates'] ?? 0) }}
            </p>
        </div>
    </div>

    <!-- Invites List -->
    @if (!empty($results['invites']))
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b dark:border-gray-700">
                        <th class="px-4 py-2 text-left text-sm font-semibold">Email</th>
                        <th class="px-4 py-2 text-left text-sm font-semibold">Coins</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($results['invites'] as $invite)
                        <tr class="border-b dark:border-gray-700">
                            <td class="px-4 py-2 text-sm">{{ $invite['email'] }}</td>
                            <td class="px-4 py-2 text-sm">{{ $invite['coins'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
