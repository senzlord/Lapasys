<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">Activity Logs</h2>
    </x-slot>

    <div class="p-6">
        <table class="w-full table-auto border">
            <thead>
                <tr class="bg-gray-100">
                    <th class="p-2 border">Date</th>
                    <th class="p-2 border">User</th>
                    <th class="p-2 border">Action</th>
                    <th class="p-2 border">Model</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($logs as $log)
                    <tr>
                        <td class="p-2 border">{{ $log->created_at->format('Y-m-d H:i') }}</td>
                        <td class="p-2 border">{{ $log->causer?->name ?? 'System' }}</td>
                        <td class="p-2 border">{{ $log->description }}</td>
                        <td class="p-2 border">{{ class_basename($log->subject_type) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-4">
            {{ $logs->links() }}
        </div>
    </div>
</x-app-layout>