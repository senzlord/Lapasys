<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard
        </h2>
    </x-slot>

    <div>
        {{-- Example content based on role --}}
        @role('Super Admin')
            <div class="mb-4 p-4 bg-green-100 border">Hello Super Admin {{ $user->name }}</div>
        @endrole
        @role('Admin')
            <div class="mb-4 p-4 bg-blue-100 border">Hello Admin {{ $user->name }}</div>
        @endrole
        @role('Staff')
            <div class="mb-4 p-4 bg-yellow-100 border">Hello Staff {{ $user->name }}</div>
        @endrole
        <div class="p-4 bg-white border">This is a shared dashboard for all roles.</div>
    </div>
</x-app-layout>
