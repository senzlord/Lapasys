<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Create New User
        </h2>
    </x-slot>

    <div>
        <form method="POST" action="{{ route('users.store') }}">
            @csrf

            <div class="mb-4">
                <label for="name">Name</label>
                <input name="name" type="text" class="w-full border rounded p-2" required>
            </div>

            <div class="mb-4">
                <label for="username">Username</label>
                <input name="username" type="text" class="w-full border rounded p-2" required>
            </div>

            <div class="mb-4">
                <label for="email">Email</label>
                <input name="email" type="email" class="w-full border rounded p-2" required>
            </div>

            <div class="mb-4">
                <label for="password">Password</label>
                <input name="password" type="password" class="w-full border rounded p-2" required>
            </div>

            <div class="mb-4">
                <label for="role">Role</label>
                <select name="role" class="w-full border rounded p-2" required>
                    <option value="">Select role</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->name }}">{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>

            <button class="bg-blue-600 text-white px-4 py-2 rounded" type="submit">
                Create
            </button>
        </form>
    </div>
</x-app-layout>
