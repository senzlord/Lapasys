<template x-if="{{ $condition }}">
    <tr>
        <td colspan="{{ $colspan }}" class="text-center text-gray-500 py-2 italic">
            {{ $message ?? 'No data available.' }}
        </td>
    </tr>
</template>