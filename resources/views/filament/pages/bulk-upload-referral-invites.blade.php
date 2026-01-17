<x-filament-panels::page>
    <div class="space-y-6">

        <!-- FORM -->
        <div class="rounded-lg border bg-white p-6 dark:bg-gray-800">
            {{ $this->form }}

            <div class="mt-6 flex gap-3 border-t pt-6">
                @foreach ($this->getFormActions() as $action)
                    {{ $action }}
                @endforeach
            </div>
        </div>

        <!-- PROGRESS BAR -->
        @if($total > 0)
            <div class="rounded-lg bg-white p-4 dark:bg-gray-800">
                <div class="mb-2 flex justify-between text-sm">
                    <span>Progress</span>
                    <span>{{ $processed }} / {{ $total }}</span>
                </div>
                <div class="h-2 w-full rounded bg-gray-200">
                    <div class="h-2 rounded bg-blue-600"
                         style="width: {{ ($processed / max($total,1)) * 100 }}%"></div>
                </div>
            </div>
        @endif

        <!-- INVITE RESULTS (CSV + Manual) -->
        @if($showUploadResults)
            @include('filament.pages.partials.invite-results', [
                'title' => 'Invite Preview / Results',
                'results' => $uploadResults
            ])
        @endif

    </div>
</x-filament-panels::page>
