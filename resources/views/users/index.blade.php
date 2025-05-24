<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                All Users
            </h2>
            <a href="{{ route('users.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Add User
            </a>
        </div>
    </x-slot>

    <div>
        <table class="w-full bg-white border shadow rounded">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-4 py-2 border">#</th>
                    <th class="px-4 py-2 border">Name</th>
                    <th class="px-4 py-2 border">Username</th>
                    <th class="px-4 py-2 border">Email</th>
                    <th class="px-4 py-2 border">Role(s)</th>
                    <th class="px-4 py-2 border">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $index => $user)
                    <tr>
                        <td class="px-4 py-2 border">{{ $index + 1 }}</td>
                        <td class="px-4 py-2 border">{{ $user->name }}</td>
                        <td class="px-4 py-2 border">{{ $user->username }}</td>
                        <td class="px-4 py-2 border">{{ $user->email }}</td>
                        <td class="px-4 py-2 border">
                            {{ $user->roles->pluck('name')->join(', ') }}
                        </td>
                        <td class="px-4 py-2 border">
                            {{-- Later: Edit/Delete buttons here --}}
                            <button data-user-id="{{ $user->id }}" class="btn-edit-user text-blue-600 hover:underline">
                                Edit
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div x-show="$store.modal.showModal"
        x-data
        x-cloak
        @click="$store.modal.showModal = false"
        @keydown.window.escape="$store.modal.showModal = false"
        class="fixed inset-0 bg-black/30 z-50 flex justify-center items-center transition-opacity duration-300 ease-out"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
        <div @click.stop
            class="bg-white p-6 rounded-lg w-full max-w-3xl mx-auto shadow-lg transform transition-all scale-100"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold">Edit User</h2>
                <button @click="$store.modal.showModal = false" class="text-gray-500 hover:text-red-500">&times;</button>
            </div>

            <div class="flex space-x-4 border-b mb-4">
                <button @click="$store.modal.tab = 'biodata'" :class="$store.modal.tab === 'biodata' ? 'font-bold border-b-2' : ''">Biodata</button>
                <button @click="$store.modal.tab = 'employment'" :class="$store.modal.tab === 'employment' ? 'font-bold border-b-2' : ''">Employment</button>
                <button @click="$store.modal.tab = 'salary'" :class="$store.modal.tab === 'salary' ? 'font-bold border-b-2' : ''">Salary</button>
                <button @click="$store.modal.tab = 'loan'" :class="$store.modal.tab === 'loan' ? 'font-bold border-b-2' : ''">Loan</button>
                <button @click="$store.modal.tab = 'logs'" :class="$store.modal.tab === 'logs' ? 'font-bold border-b-2' : ''">Log User</button>
            </div>

            <div x-show="$store.modal.tab === 'biodata'" class="space-y-2">
                <label>
                    Name:
                    <input type="text" x-model="$store.modal.user.name" class="border p-1 w-full" />
                </label>

                <label>
                    Phone:
                    <input type="text" x-model="$store.modal.biodata.phone" class="border p-1 w-full" />
                </label>

                <label>
                    Address:
                    <input type="text" x-model="$store.modal.biodata.address" class="border p-1 w-full" />
                </label>

                <label>
                    Birth Date:
                    <input type="date" x-model="$store.modal.biodata.birth_date" class="border p-1 w-full" />
                </label>

                <label>
                    Gender:
                    <select x-model="$store.modal.biodata.gender" class="border p-1 w-full">
                        <option value="">-- Select --</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                    </select>
                </label>

                <button @click="saveBiodata()" class="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
            </div>

            <div x-show="$store.modal.tab === 'employment'" class="space-y-4">
                <h3 class="font-semibold">Employment History</h3>

                <table class="w-full text-sm border">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border px-2 py-1">Start Date</th>
                            <th class="border px-2 py-1">End Date</th>
                            <th class="border px-2 py-1">Status</th>
                            <th class="border px-2 py-1">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @include('components.table-empty-row', ['condition' => '$store.modal.employment.length === 0', 'colspan' => 3, 'message' => 'No employment history'])
                        <template x-for="item in $store.modal.employment" :key="item.id">
                            <tr>
                                <td class="border px-2 py-1" x-text="item.start_date"></td>
                                <td class="border px-2 py-1" x-text="item.end_date ?? '-'"></td>
                                <td class="border px-2 py-1 text-center">
                                    <span :class="item.end_date ? 'text-red-500' : 'text-green-600'" x-text="item.end_date ? 'Resigned' : 'Active'"></span>
                                </td>
                                <td class="border px-2 py-1">
                                    <button @click="$store.modal.editingEmployment = { ...item }"
                                        class="text-blue-600 text-xs hover:underline">Edit</button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>

                <hr class="my-4" />

                <h3 class="font-semibold" x-text="$store.modal.editingEmployment?.id ? 'Edit Employment' : 'Add Employment'"></h3>
                <template x-if="$store.modal.editingEmployment">
                    <form @submit.prevent="submitEmployment">
                        <div class="flex gap-2">
                            <input type="date" x-model="$store.modal.editingEmployment.start_date" class="border p-1 w-1/2" placeholder="Start Date">
                            <input type="date" x-model="$store.modal.editingEmployment.end_date" class="border p-1 w-1/2" placeholder="End Date (optional)">
                        </div>
                        <button type="submit"
                            class="mt-2 bg-indigo-600 text-white px-4 py-1 rounded"
                            x-text="$store.modal.editingEmployment?.id ? 'Update Employment' : 'Add Employment'">
                        </button>
                        <template x-if="$store.modal.editingEmployment?.id">
                            <button type="button"
                                @click="$store.modal.editingEmployment = { start_date: '', end_date: '' }"
                                class="bg-gray-300 text-gray-800 px-4 py-1 rounded hover:bg-gray-400">
                                Reset
                            </button>
                        </template>
                    </form>
                </template>
            </div>

            <div x-show="$store.modal.tab === 'salary'" class="space-y-2">
                <label>
                    Gaji Pokok (Base Salary):
                    <input type="number" x-model="$store.modal.salaryData.base_salary" class="border p-1 w-full" />
                </label>

                <label>
                    Total Tunjangan (Allowance):
                    <input type="number" x-model="$store.modal.salaryData.total_allowance" class="border p-1 w-full" />
                </label>

                <label>
                    Total Gaji (Calculated):
                    <input type="number"
                        :value="Number($store.modal.salaryData.base_salary || 0) + Number($store.modal.salaryData.total_allowance || 0)"
                        class="border p-1 w-full bg-gray-100" readonly />
                </label>

                <button @click="saveSalary()" class="bg-green-600 text-white px-4 py-2 rounded">Simpan Gaji</button>
            </div>

            <div x-show="$store.modal.tab === 'loan'" class="space-y-2">

                <h3 class="font-semibold">Active Loans</h3>
                <table class="w-full text-sm border">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border px-2 py-1">Total</th>
                            <th class="border px-2 py-1">Installments</th>
                            <th class="border px-2 py-1">Monthly</th>
                            <th class="border px-2 py-1">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @include('components.table-empty-row', ['condition' => '$store.modal.loans.length === 0', 'colspan' => 3, 'message' => 'No loans history'])
                        <template x-for="loan in $store.modal.loans" :key="loan.id">
                            <tr>
                                <td class="border px-2 py-1" x-text="'Rp. ' + Number(loan.total_amount).toLocaleString('id-ID')"></td>
                                <td class="border px-2 py-1" x-text="loan.installments + ' bulan'"></td>
                                <td class="border px-2 py-1" x-text="'Rp. ' + Number(loan.monthly_amount).toLocaleString('id-ID')"></td>
                                <td class="border px-2 py-1">
                                    <span :class="loan.status === 'active' ? 'text-green-600' : 'text-gray-500'" x-text="loan.status === 'active' ? 'Active' : 'Completed'"></span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>

                <hr class="my-4" />

                <h3 class="font-semibold">Active Loans</h3>
                <label>
                    Total Loan Amount:
                    <input type="number" x-model="$store.modal.newLoan.total_amount" class="border p-1 w-full" />
                </label>

                <label>
                    Installments (max 12):
                    <input type="number" min="1" max="12" x-model="$store.modal.newLoan.installments" class="border p-1 w-full" />
                </label>
                <div class="text-sm text-gray-700">
                    <template x-if="$store.modal.newLoan.total_amount && $store.modal.newLoan.installments">
                        <p>
                            Setiap bulan akan dipotong:
                            <strong>
                                Rp. <span
                                    x-text="(Number($store.modal.newLoan.total_amount) / Number($store.modal.newLoan.installments || 1)).toLocaleString('id-ID', { minimumFractionDigits: 2 })">
                                </span>
                            </strong>
                        </p>
                    </template>
                </div>
                <label>
                    Start Month:
                    <input type="month" x-model="$store.modal.newLoan.start_month" class="border p-1 w-full" />
                </label>

                <label>
                    Description:
                    <textarea x-model="$store.modal.newLoan.description" class="border p-1 w-full"></textarea>
                </label>

                <button @click="addLoan()" class="bg-purple-600 text-white px-4 py-2 rounded">Add Loan</button>
            </div>

            <div x-show="$store.modal.tab === 'logs'" class="space-y-2">
                <ul class="text-sm">
                    <template x-for="log in $store.modal.logs" :key="log.id">
                        <li class="border-b py-2">
                            <div class="font-semibold" x-text="log.description"></div>
                            <div class="text-gray-500">
                                <span x-text="new Date(log.created_at).toLocaleString()"></span> —
                                <span x-text="log.causer?.name ?? 'Unknown User'"></span>
                            </div>
                        </li>
                    </template>
                </ul>
            </div>
        </div>
    </div>
    <div x-show="$store.modal.isLoading"
        x-cloak
        x-transition
        class="fixed inset-0 bg-black bg-opacity-40 z-50 flex items-center justify-center">
        <div class="bg-white p-4 rounded shadow text-center">
            <svg class="animate-spin h-5 w-5 text-gray-700 mx-auto mb-2" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <p class="text-gray-700 text-sm">Loading user data...</p>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.btn-edit-user').forEach(button => {
                button.addEventListener('click', async () => {
                    Alpine.store('modal').isLoading = true;

                    const userId = button.getAttribute('data-user-id');
                    try {
                        const res = await fetch(`/users/${userId}/json`);
                        const data = await res.json();

                        Alpine.store('modal').editingEmployment = { start_date: '', end_date: '' };

                        Alpine.store('modal').user = data.user;
                        Alpine.store('modal').biodata = data.biodata || {
                            phone: '',
                            address: '',
                            birth_date: '',
                            gender: ''
                        };
                        Alpine.store('modal').employment = data.employment || [];
                        Alpine.store('modal').loans = data.loans || [];
                        Alpine.store('modal').salaryData = data.salary;
                        Alpine.store('modal').logs = data.logs;
                        Alpine.store('modal').tab = 'biodata';
                        // small delay for visual smoothness (optional)
                        setTimeout(() => {
                            Alpine.store('modal').isLoading = false;
                            Alpine.store('modal').showModal = true;
                        }, 200);

                    } catch (e) {
                        Alpine.store('modal').isLoading = false;
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed to load user',
                            text: e.message || 'Unexpected error'
                        });
                    }
                });
            });
        });

        document.addEventListener('alpine:init', () => {
            Alpine.store('modal', {
                showModal: false,
                isLoading: false,
                tab: 'biodata',
                user: { name: '', email: '' },
                salaryData: {},
                biodata: {
                    phone: '', address: '', birth_date: '', gender: ''
                },
                employment: [],
                editingEmployment: null,
                logs: [],
                loans: [],
                newLoan: {
                    total_amount: '',
                    installments: '',
                    description: '',
                    start_month: ''
                }
            });
        });

        function saveBiodata() {
            const id = Alpine.store('modal').user.id;

            fetch(`/users/${id}/biodata`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    name: Alpine.store('modal').user.name,
                    phone: Alpine.store('modal').biodata.phone,
                    address: Alpine.store('modal').biodata.address,
                    birth_date: Alpine.store('modal').biodata.birth_date,
                    gender: Alpine.store('modal').biodata.gender,
                })
            })
            .then(async res => {
                if (!res.ok) {
                    const error = await res.json();
                    const messages = Object.values(error.errors).flat().join('<br>');
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        html: messages
                    });
                    return;
                }

                const data = await res.json();
                Swal.fire({
                    icon: 'success',
                    title: 'Biodata updated!',
                    timer: 1500,
                    showConfirmButton: false
                });
                Alpine.store('modal').showModal = false;
            })
            .catch(err => {
                console.error("Request failed:", err);
                Swal.fire({
                    icon: 'error',
                    title: 'Oops!',
                    text: 'Something went wrong while saving biodata.'
                });
            });
        }
        function addEmployment() {
            const id = Alpine.store('modal').user.id;
            const payload = Alpine.store('modal').newEmployment;

            fetch(`/users/${id}/employment`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(payload)
            })
            .then(async res => {
                if (!res.ok) {
                    const error = await res.json();
                    const messages = Object.values(error.errors).flat().join('<br>');
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        html: messages
                    });
                    return;
                }

                const data = await res.json();
                Alpine.store('modal').employment.push(data.employment);
                Alpine.store('modal').newEmployment = { start_date: '', end_date: '' };
                Swal.fire('Success', 'Employment added', 'success');
            })
            .catch(err => {
                console.error("Request failed:", err);
                Swal.fire({
                    icon: 'error',
                    title: 'Oops!',
                    text: 'Something went wrong while adding employment.'
                });
            });
        }
        function submitEmployment() {
            const store = Alpine.store('modal');
            const id = store.user.id;
            const payload = store.editingEmployment;

            const isEdit = !!payload.id;

            const url = isEdit
                ? `/users/${id}/employment/${payload.id}`
                : `/users/${id}/employment`;

            const method = isEdit ? 'PUT' : 'POST';

            fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(payload)
            })
            .then(async res => {
                if (!res.ok) {
                    const error = await res.json();
                    const messages = Object.values(error.errors).flat().join('<br>');
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        html: messages
                    });
                    return;
                }

                const data = await res.json();

                if (isEdit) {
                    const idx = store.employment.findIndex(e => e.id === payload.id);
                    if (idx !== -1) store.employment[idx] = data.employment;
                } else {
                    store.employment.unshift(data.employment);
                }

                store.editingEmployment = null;

                Swal.fire('Success', isEdit ? 'Employment updated' : 'Employment added', 'success');
            })
            .catch(err => {
                Swal.fire('Error', 'Server error occurred', 'error');
            });
        }
        function saveSalary() {
            const id = Alpine.store('modal').user.id;

            const base = Number(Alpine.store('modal').salaryData.base_salary || 0);
            const allowance = Number(Alpine.store('modal').salaryData.total_allowance || 0);
            const total = base + allowance;

            fetch(`/users/${id}/salary`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    base_salary: base,
                    total_allowance: allowance,
                    total_salary: total
                })
            })
            .then(async res => {
                if (!res.ok) {
                    const error = await res.json();
                    const messages = Object.values(error.errors).flat().join('<br>');
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        html: messages
                    });
                    return;
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Salary saved!',
                    timer: 1500,
                    showConfirmButton: false
                });

                Alpine.store('modal').showModal = false;
            })
            .catch(err => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to save salary.'
                });
            });
        }
        function addLoan() {
            const userId = Alpine.store('modal').user.id;
            const payload = Alpine.store('modal').newLoan;

            // Check if the user already has active loans
            const activeLoans = Alpine.store('modal').loans.filter(l => l.status === 'active');

            if (activeLoans.length > 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Active Loan Detected',
                    text: 'This user already has an active loan. Are you sure you want to add another?',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, continue',
                    cancelButtonText: 'Cancel'
                }).then(result => {
                    if (result.isConfirmed) {
                        proceedLoanCreate(userId, payload);
                    }
                });
            } else {
                proceedLoanCreate(userId, payload);
            }
        }
        function proceedLoanCreate(userId, payload) {
            fetch(`/users/${userId}/loan`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(payload)
            })
            .then(async res => {
                if (!res.ok) {
                    const error = await res.json();
                    const messages = Object.values(error.errors).flat().join('<br>');
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        html: messages
                    });
                    return;
                }

                const data = await res.json();
                Alpine.store('modal').loans.unshift(data.loan);
                Alpine.store('modal').newLoan = {
                    total_amount: '', installments: '', description: '', start_month: ''
                };

                Swal.fire({
                    icon: 'success',
                    title: 'Loan added!',
                    timer: 1500,
                    showConfirmButton: false
                });
            });
        }
    </script>
</x-app-layout>
