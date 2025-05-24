<x-app-layout>
    <div x-data="payrollEditor()">
        <x-slot name="header">
            <h2 class="text-xl font-bold">Monthly Payroll - {{ $month_label }}</h2>
        </x-slot>

        <div class="mb-4">
            <form method="GET" class="flex items-center gap-2">
                <label>Month:</label>
                <select name="month" class="border rounded py-1">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" {{ $m == $selectedMonth ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                        </option>
                    @endforeach
                </select>

                <label>Year:</label>
                <select name="year" class="border rounded py-1">
                    @foreach(range(date('Y') - 3, date('Y') + 1) as $y)
                        <option value="{{ $y }}" {{ $y == $selectedYear ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">
                    Filter
                </button>
            </form>
        </div>

        <table class="w-full bg-white border shadow text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border px-2 py-1">#</th>
                    <th class="border px-2 py-1">Name</th>
                    <th class="border px-2 py-1">Role</th>
                    <th class="border px-2 py-1">Base Salary</th>
                    <th class="border px-2 py-1">Allowance</th>
                    <th class="border px-2 py-1">Loan</th>
                    <th class="border px-2 py-1">Take Home</th>
                    <th class="border px-2 py-1">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $i => $user)
                    @php
                        $role = $user->roles->pluck('name')->join(', ');
                        $base = $user->salary->base_salary ?? 0;
                        $allow = $user->salary->total_allowance ?? 0;
                        $loan = $user->loans->sum('monthly_amount');
                        $takeHome = $base + $allow - $loan;
                        $paid = $user->payrollRecords->isNotEmpty();
                    @endphp
                    <tr>
                        <td class="border px-2 py-1">{{ $i + 1 }}</td>
                        <td class="border px-2 py-1">{{ $user->name }}</td>
                        <td class="border px-2 py-1">{{ $role }}</td>
                        <td class="border px-2 py-1 text-right" id="base-salary-{{ $user->id }}" data-value="{{ $base }}">Rp {{ number_format($base, 0, ',', '.') }}</td>
                        <td class="border px-2 py-1 text-right" id="allowance-{{ $user->id }}" data-value="{{ $allow }}">Rp {{ number_format($allow, 0, ',', '.') }}</td>
                        <td class="border px-2 py-1 text-right text-red-600" id="loan-display-{{ $user->id }}" @click="openLoanEditor({{ $user->id }})">
                            -Rp {{ number_format($user->loan_deduction_final, 0, ',', '.') }}
                        </td>
                        <td class="border px-2 py-1 text-right font-semibold" id="takehome-display-{{ $user->id }}">
                            Rp {{ number_format($user->take_home_final, 0, ',', '.') }}
                        </td>
                        <td class="border px-2 py-1 text-center">
                            @if (!$paid)
                                <button
                                    class="bg-blue-600 text-white px-2 py-1 rounded text-xs"
                                    onclick="confirmPayroll({{ $user->id }}, '{{ $selectedYear }}', '{{ $selectedMonth }}')">
                                    Pay
                                </button>
                            @else
                                <span class="text-green-600 font-semibold">Paid</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Modal Loan Editor -->
        <div x-show="loanEditorVisible"
            x-cloak
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
                <h2 class="text-xl font-bold">Edit Loan Deduction</h2>
                <button @click="loanEditorVisible = false" class="text-gray-500 hover:text-red-500 text-2xl leading-none">&times;</button>
            </div>

            <div class="space-y-3">
                <template x-if="editableLoans.length === 0">
                    <p class="text-gray-500 text-sm">No active loans for this user.</p>
                </template>

                <template x-for="loan in editableLoans" :key="loan.id">
                    <div class="flex items-center gap-2 border-b pb-2">
                        <input type="checkbox" :value="loan.id" x-model="selectedLoanIds" class="form-checkbox">
                        <div class="flex-1">
                            <div class="text-sm font-medium">
                                <span x-text="'Total Loan Rp ' + loan.total_amount.toLocaleString('id-ID')"></span>
                                <span class="text-gray-500">(x<span x-text="loan.installments"></span>)</span>
                            </div>
                        </div>
                        <div class="text-sm text-gray-600">Monthly:</div>
                        <input type="number"
                            class="border rounded px-2 py-1 w-28 text-sm"
                            x-model.number="loanOverrides[loan.id]"
                            :readonly="!selectedLoanIds.includes(loan.id)"
                            :class="{
                                'bg-gray-100 text-gray-500': !selectedLoanIds.includes(loan.id),
                                'bg-white text-black': selectedLoanIds.includes(loan.id)
                            }"
                        >
                    </div>
                </template>
            </div>

            <div class="flex justify-end mt-4 gap-2">
                <button @click="loanEditorVisible = false" class="bg-gray-300 text-gray-800 px-3 py-1 rounded hover:bg-gray-400">Cancel</button>
                <button @click="applyLoanEdits" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Confirm</button>
            </div>
            </div>
        </div>
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('payrollEditor', () => ({
                    loanEditorVisible: false,
                    editableLoans: [],
                    selectedLoanIds: [],
                    loanOverrides: {},
                    currentUserId: null,

                    openLoanEditor(userId) {
                        this.currentUserId = userId;
                        this.loanEditorVisible = true;

                        fetch(`/payroll/${userId}/loan-options?year={{ $selectedYear }}&month={{ $selectedMonth }}`)
                            .then(res => res.json())
                            .then(data => {
                                this.editableLoans = data;
                                this.selectedLoanIds = data.map(l => l.id);
                                this.loanOverrides = {};
                                data.forEach(l => {
                                    this.loanOverrides[l.id] = l.monthly_amount;
                                });
                            });
                    },

                    applyLoanEdits() {
                        const totalLoan = this.selectedLoanIds.reduce((total, id) => {
                            return total + (Number(this.loanOverrides[id]) || 0);
                        }, 0);

                        const baseSalary = Number(document.querySelector(`#base-salary-${this.currentUserId}`)?.dataset.value || 0);
                        const allowance = Number(document.querySelector(`#allowance-${this.currentUserId}`)?.dataset.value || 0);
                        const takeHome = baseSalary + allowance - totalLoan;

                        // console.log(this.currentUserId,baseSalary, allowance, totalLoan, takeHome);

                        const takeHomeEl = document.querySelector(`#takehome-display-${this.currentUserId}`);
                        if (takeHomeEl) {
                            takeHomeEl.innerText = 'Rp ' + takeHome.toLocaleString('id-ID');
                        }

                        const loanEl = document.querySelector(`#loan-display-${this.currentUserId}`);
                        if (loanEl) {
                            loanEl.innerText = '-Rp ' + totalLoan.toLocaleString('id-ID');
                        }

                        window[`loanOverrides_${this.currentUserId}`] = {
                            ids: this.selectedLoanIds,
                            values: this.loanOverrides
                        };

                        this.loanEditorVisible = false;
                    }
                }));
            });
            function confirmPayroll(userId, year, month) {
                fetch(`/payroll/${userId}/check-prorate?year=${year}&month=${month}`)
                    .then(res => res.json())
                    .then(data => {
                        const shouldProrate = data.should;
                        const proratedAmount = data.prorated_salary;
                        const fullAmount = data.full_salary;

                        if (shouldProrate) {
                            Swal.fire({
                                title: 'Use Prorated Salary?',
                                text: `Prorated: Rp ${proratedAmount.toLocaleString('id-ID')} vs Full: Rp ${fullAmount.toLocaleString('id-ID')}`,
                                icon: 'question',
                                showCancelButton: true,
                                showDenyButton: true,
                                confirmButtonText: 'Yes, Prorate',
                                denyButtonText: 'No, Manual Salary',
                                cancelButtonText: 'Cancel'
                            }).then(choice => {
                                if (choice.isConfirmed) {
                                    executePayroll(userId, year, month, true);
                                } else if ( choice.isDenied ) {
                                    executePayroll(userId, year, month, false, fullAmount);
                                }
                            });
                        } else {
                            executePayroll(userId, year, month, false);
                        }
                    });
            }

            function executePayroll(userId, year, month, isProrated, value=0) {
                Swal.fire({
                    title: 'Confirm Payroll',
                    text: `Proceed with ${isProrated ? 'prorated' : 'full'} salary?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Confirm',
                    cancelButtonText: 'Cancel',
                    ...(!isProrated ? {
                        input: 'number',
                        inputLabel: 'Manual Salary Amount',
                        inputPlaceholder: 'Enter amount in Rupiah',
                        inputAttributes: {
                            'aria-label': 'Enter amount in Rupiah'
                        },
                        inputValue: value,
                    } : {})
                }).then(result => {
                    if (result.isConfirmed) {
                        const override = window[`loanOverrides_${userId}`] || null;
                        fetch(`/payroll/${userId}/pay`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                year: parseInt(year),
                                month: parseInt(month),
                                is_prorated: isProrated,
                                loan_override: override
                            })
                        })
                        .then(async res => {
                            const text = await res.text();
                            if (!res.ok) {
                                try {
                                    const err = JSON.parse(text);
                                    throw new Error(err.message || 'Failed');
                                } catch (e) {
                                    console.error('Raw response:', text);
                                    throw new Error('Server error. Not a valid JSON response.');
                                }
                            }

                            Swal.fire({
                                icon: 'success',
                                title: 'Paid!',
                                text: 'Salary marked as paid.',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => location.reload());
                        })
                        .catch(err => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: err.message || 'Something went wrong.'
                            });
                        });
                    }
                });
            }
        </script>
    </div>
</x-app-layout>
