@extends('layouts.main')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col order-0">
            <div class="card mb-3">
                <div class="d-flex align-items-end row">
                    <div class="col">
                        <div class="card-body">
                            <h5 class="card-title text-primary mb-0">Add Rule</h5>
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
                            <form role="form" class="text-start" action="{{ route('rule.create') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <label for="Type_Rule" class="form-label">Type</label>
                                    <input type="text" class="form-control" id="Type_Rule" name="Type_Rule" placeholder="Type" required />
                                </div>

                                <!-- Pilihan semua rule -->
                                <div class="mb-3">
                                    <label class="form-label">Pilih Rule (opsional)</label><br>
                                    <div class="form-check">
                                        <input class="form-check-input rule-checkbox" type="checkbox" value="chadet" id="chadet">
                                        <label class="form-check-label" for="chadet">chadet</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input rule-checkbox" type="checkbox" value="parcom_ring_synchronizer" id="parcom_ring_synchronizer">
                                        <label class="form-check-label" for="parcom_ring_synchronizer">parcom_ring_synchronizer</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input rule-checkbox" type="checkbox" value="astra_engine" id="astra_engine">
                                        <label class="form-check-label" for="astra_engine">astra_engine</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input rule-checkbox" type="checkbox" value="astra_main_line_start" id="astra_main_line_start">
                                        <label class="form-check-label" for="astra_main_line_start">astra_main_line_start</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input rule-checkbox" type="checkbox" value="astra_main_line_end" id="astra_main_line_end">
                                        <label class="form-check-label" for="astra_main_line_end">astra_main_line_end</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input rule-checkbox" type="checkbox" value="astra_mower_collector" id="astra_mower_collector">
                                        <label class="form-check-label" for="astra_mower_collector">astra_mower_collector</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input rule-checkbox" type="checkbox" value="oiler" id="oiler">
                                        <label class="form-check-label" for="oiler">oiler</label>
                                    </div>
                                </div>

                                <!-- Area urutan (hanya untuk yang dipilih) -->
                                <div class="mb-3">
                                    <label class="form-label">Urutan Rule yang Dipilih</label>
                                    <ul id="selectedRules" class="list-group">
                                        <!-- Item akan muncul di sini saat dipilih -->
                                    </ul>
                                    <input type="hidden" name="Rule_Rule" id="Rule_Rule" />
                                </div>

                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary">Submit</button>
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

@section('style')

@endsection

@section('script')
<script src="{{ asset('assets/js/Sortable.min.js') }}"></script>
<script>
    const checkboxes = document.querySelectorAll('.rule-checkbox');
    const selectedList = document.getElementById('selectedRules');
    const ruleInput = document.getElementById('Rule_Rule');

    // Inisialisasi Sortable pada area urutan
    new Sortable(selectedList, {
        animation: 150,
        onEnd: updateRuleOrder
    });

    // Event saat checkbox berubah
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            const value = this.value;
            if (this.checked) {
                // Tambahkan ke daftar urutan
                const li = document.createElement('li');
                li.className = 'list-group-item';
                li.dataset.value = value;
                li.textContent = value;
                li.style.cursor = 'move';
                selectedList.appendChild(li);
            } else {
                // Hapus dari daftar
                const item = selectedList.querySelector(`li[data-value="${value}"]`);
                if (item) item.remove();
            }
            updateRuleOrder();
        });
    });

    function updateRuleOrder() {
        const items = selectedList.querySelectorAll('li');
        const ruleOrder = {};
        items.forEach((item, index) => {
            ruleOrder[index + 1] = item.dataset.value;
        });
        ruleInput.value = JSON.stringify(ruleOrder);
    }
</script>
@endsection
