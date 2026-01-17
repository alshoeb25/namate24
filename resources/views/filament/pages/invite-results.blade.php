<div class="rounded-lg border bg-blue-50 p-6">
    <h2 class="mb-4 text-lg font-semibold">{{ $title }}</h2>

    <div class="grid grid-cols-4 gap-4 mb-6">
        <div class="bg-green-100 p-3 rounded">Success: {{ $results['success'] }}</div>
        <div class="bg-red-100 p-3 rounded">Failed: {{ $results['failed'] }}</div>
        <div class="bg-yellow-100 p-3 rounded">Duplicates: {{ $results['duplicates'] }}</div>
        <div class="bg-blue-100 p-3 rounded">
            Total: {{ $results['success'] + $results['failed'] + $results['duplicates'] }}
        </div>
    </div>

    @if(count($results['invites']))
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-100">
                    <th class="p-2">Email</th>
                    <th class="p-2">Coins</th>
                    <th class="p-2">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($results['invites'] as $invite)
                    <tr class="border-b">
                        <td class="p-2">{{ $invite['email'] }}</td>
                        <td class="p-2">{{ $invite['coins'] }}</td>
                        <td class="p-2">{{ $invite['status'] ?? 'pending' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
