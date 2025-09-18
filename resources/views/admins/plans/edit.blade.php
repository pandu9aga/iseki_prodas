@extends('layouts.main')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col order-0">
            <div class="card mb-3">
                <div class="d-flex align-items-end row">
                    <div class="col">
                        <div class="card-body">
                            <h5 class="card-title text-primary mb-0">Edit User</h5>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mb-3">
                <div class="d-flex align-items-end row">
                    <div class="col">
                        <div class="card-body">
                            @if ($errors->any())
                                <div>
                                    @foreach ($errors->all() as $error)
                                        <p style="color:red;">{{ $error }}</p>
                                    @endforeach
                                </div>
                            @endif
                            <form role="form" class="text-start" action="{{ route('plan.update', ['Id_Plan' => $Id_Plan->Id_Plan]) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('put')
                                <div class="row">
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label for="Type_Plan" class="form-label">Type</label>
                                            <input type="text" class="form-control @error('Type_Plan') is-invalid @enderror"
                                                id="Type_Plan" name="Type_Plan" value="{{ old('Type_Plan', $Id_Plan->Type_Plan) }}" placeholder="Type"/>
                                            @error('Type_Plan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="mb-3">
                                            <label for="Sequence_No_Plan" class="form-label">Sequence No</label>
                                            <input type="text" class="form-control @error('Sequence_No_Plan') is-invalid @enderror"
                                                id="Sequence_No_Plan" name="Sequence_No_Plan" value="{{ old('Sequence_No_Plan', $Id_Plan->Sequence_No_Plan) }}" placeholder="Sequence No"/>
                                            @error('Sequence_No_Plan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="mb-3">
                                            <label for="Production_Date_Plan" class="form-label">Production Date</label>
                                            <input type="text" class="form-control @error('Production_Date_Plan') is-invalid @enderror"
                                                id="Production_Date_Plan" name="Production_Date_Plan" value="{{ old('Production_Date_Plan', $Id_Plan->Production_Date_Plan) }}"/>
                                            @error('Production_Date_Plan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="mb-3">
                                            <label for="Model_Name_Plan" class="form-label">Model Name</label>
                                            <input type="text" class="form-control @error('Model_Name_Plan') is-invalid @enderror"
                                                id="Model_Name_Plan" name="Model_Name_Plan" value="{{ old('Model_Name_Plan', $Id_Plan->Model_Name_Plan) }}" placeholder="Model Name"/>
                                            @error('Model_Name_Plan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="mb-3">
                                            <label for="Production_No_Plan" class="form-label">Production No</label>
                                            <input type="text" class="form-control @error('Production_No_Plan') is-invalid @enderror"
                                                id="Production_No_Plan" name="Production_No_Plan" value="{{ old('Production_No_Plan', $Id_Plan->Production_No_Plan) }}" placeholder="Production No"/>
                                            @error('Production_No_Plan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="mb-3">
                                            <label for="Chasis_No_Plan" class="form-label">Chasis No</label>
                                            <input type="text" class="form-control @error('Chasis_No_Plan') is-invalid @enderror"
                                                id="Chasis_No_Plan" name="Chasis_No_Plan" value="{{ old('Chasis_No_Plan', $Id_Plan->Chasis_No_Plan) }}" placeholder="Chasis No"/>
                                            @error('Chasis_No_Plan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>

                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label for="Model_Label_Plan" class="form-label">Model Label</label>
                                            <input type="text" class="form-control @error('Model_Label_Plan') is-invalid @enderror"
                                                id="Model_Label_Plan" name="Model_Label_Plan" value="{{ old('Model_Label_Plan', $Id_Plan->Model_Label_Plan) }}" placeholder="Model Label"/>
                                            @error('Model_Label_Plan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="mb-3">
                                            <label for="Safety_Frame_Label_Plan" class="form-label">Safety Frame Label</label>
                                            <input type="text" class="form-control @error('Safety_Frame_Label_Plan') is-invalid @enderror"
                                                id="Safety_Frame_Label_Plan" name="Safety_Frame_Label_Plan" value="{{ old('Safety_Frame_Label_Plan', $Id_Plan->Safety_Frame_Label_Plan) }}" placeholder="Safety Frame Label"/>
                                            @error('Safety_Frame_Label_Plan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="mb-3">
                                            <label for="Model_Mower_Plan" class="form-label">Model Mower</label>
                                            <input type="text" class="form-control @error('Model_Mower_Plan') is-invalid @enderror"
                                                id="Model_Mower_Plan" name="Model_Mower_Plan" value="{{ old('Model_Mower_Plan', $Id_Plan->Model_Mower_Plan) }}" placeholder="Model Mower"/>
                                            @error('Model_Mower_Plan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="mb-3">
                                            <label for="Mower_No_Plan" class="form-label">Mower No</label>
                                            <input type="text" class="form-control @error('Mower_No_Plan') is-invalid @enderror"
                                                id="Mower_No_Plan" name="Mower_No_Plan" value="{{ old('Mower_No_Plan', $Id_Plan->Mower_No_Plan) }}" placeholder="Mower No"/>
                                            @error('Mower_No_Plan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="mb-3">
                                            <label for="Model_Collector_Plan" class="form-label">Model Collector</label>
                                            <input type="text" class="form-control @error('Model_Collector_Plan') is-invalid @enderror"
                                                id="Model_Collector_Plan" name="Model_Collector_Plan" value="{{ old('Model_Collector_Plan', $Id_Plan->Model_Collector_Plan) }}" placeholder="Model Collector"/>
                                            @error('Model_Collector_Plan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        <div class="mb-3">
                                            <label for="Collector_No_Plan" class="form-label">Collector No</label>
                                            <input type="text" class="form-control @error('Collector_No_Plan') is-invalid @enderror"
                                                id="Collector_No_Plan" name="Collector_No_Plan" value="{{ old('Collector_No_Plan', $Id_Plan->Collector_No_Plan) }}" placeholder="Collector No"/>
                                            @error('Collector_No_Plan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
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

@section('style')
<link href="{{asset('assets/css/datatables.min.css')}}" rel="stylesheet">
@endsection

@section('script')
<script src="{{asset('assets/js/datatables.min.js')}}"></script>
<script>
new DataTable('#example');
</script>
@endsection
