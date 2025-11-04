@extends('layouts.main')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col order-0">
            <div class="card mb-3">
                <div class="d-flex align-items-end row">
                    <div class="col">
                        <div class="card-body">
                            <h5 class="card-title text-primary mb-0">Edit Rule</h5>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mb-3">
                <div class="d-flex align-items-end row">
                    <div class="col-6">
                        <div class="card-body">
                            @if ($errors->any())
                                <div>
                                    @foreach ($errors->all() as $error)
                                        <p style="color:red;">{{ $error }}</p>
                                    @endforeach
                                </div>
                            @endif
                            <form role="form" class="text-start" action="{{ route('rule.update', ['Id_Rule' => $Id_Rule->Id_Rule]) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="mb-3">
                                    <label for="Type_Rule" class="form-label">Type</label>
                                    <input type="text" class="form-control @error('Type_Rule') is-invalid @enderror" 
                                           id="Type_Rule" name="Type_Rule" 
                                           placeholder="Type" 
                                           value="{{ old('Type_Rule', $Id_Rule->Type_Rule) }}" required />
                                    @error('Type_Rule')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Pilihan semua rule -->
                                <div class="mb-3">
                                    <label class="form-label">Pilih Rule (opsional)</label><br>
                                    @php
                                        // Karena casting 'array', $Id_Rule->Rule_Rule sudah berupa array
                                        $savedRules = $Id_Rule->Rule_Rule ?: [];
                                        $savedValues = array_values($savedRules); // hanya nilai, bukan key
                                    @endphp

                                    @foreach(['parcom_ring_synchronizer', 'chadet', 'astra_engine', 'astra_main_line_start', 'astra_main_line_end', 'astra_mower_collector', 'oiler'] as $option)
                                        <div class="form-check">
                                            <input class="form-check-input rule-checkbox" 
                                                type="checkbox" 
                                                value="{{ $option }}" 
                                                id="{{ $option }}"
                                                {{ in_array($option, $savedValues) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="{{ $option }}">{{ $option }}</label>
                                        </div>
                                    @endforeach
                                </div>

                                <!-- Area urutan (diisi otomatis dari data) -->
                                <div class="mb-3">
                                    <label class="form-label">Urutan Rule yang Dipilih</label>
                                    <ul id="selectedRules" class="list-group">
                                        @php
                                            // Urutkan berdasarkan key: "1", "2", dst
                                            ksort($savedRules, SORT_NUMERIC);
                                        @endphp
                                        @foreach($savedRules as $key => $value) <!-- Perhatikan: kita iterasi key dan value -->
                                            <li class="list-group-item" data-value="{{ $value }}" style="cursor: move;">
                                                {{ $value }}
                                            </li>
                                        @endforeach
                                    </ul>
                                    <input type="hidden" name="Rule_Rule" id="Rule_Rule" />
                                </div>

                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary">Update</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="{{ asset('assets/js/Sortable.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const checkboxes = document.querySelectorAll('.rule-checkbox');
        const selectedList = document.getElementById('selectedRules');
        const ruleInput = document.getElementById('Rule_Rule');

        // Inisialisasi Sortable
        new Sortable(selectedList, {
            animation: 150,
            onEnd: updateRuleOrder
        });

        // Fungsi update hidden input
        function updateRuleOrder() {
            const items = selectedList.querySelectorAll('li');
            const ruleOrder = {};
            items.forEach((item, index) => {
                ruleOrder[index + 1] = item.dataset.value;
            });
            ruleInput.value = JSON.stringify(ruleOrder);
        }

        // Inisialisasi nilai awal di hidden input
        updateRuleOrder();

        // Event saat checkbox berubah
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function () {
                const value = this.value;
                if (this.checked) {
                    // Cek apakah sudah ada (hindari duplikat)
                    if (!selectedList.querySelector(`li[data-value="${value}"]`)) {
                        const li = document.createElement('li');
                        li.className = 'list-group-item';
                        li.dataset.value = value;
                        li.textContent = value;
                        li.style.cursor = 'move';
                        selectedList.appendChild(li);
                    }
                } else {
                    const item = selectedList.querySelector(`li[data-value="${value}"]`);
                    if (item) item.remove();
                }
                updateRuleOrder();
            });
        });
    });
</script>
@endsection