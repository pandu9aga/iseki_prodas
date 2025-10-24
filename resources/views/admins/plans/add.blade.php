@extends('layouts.main')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col order-0">
            <div class="card mb-3">
                <div class="d-flex align-items-end row">
                    <div class="col">
                        <div class="card-body">
                            <h5 class="card-title text-primary mb-0">Add Plan</h5>
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
                            <form role="form" class="text-start" action="{{ route('plan.create') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label for="Type_Plan" class="form-label">Type</label>
                                            <input type="text" class="form-control" id="Type_Plan" name="Type_Plan" placeholder="Type"/>
                                        </div>
                                        <div class="mb-3">
                                            <label for="Sequence_No_Plan" class="form-label">Sequence No</label>
                                            <input type="text" class="form-control" id="Sequence_No_Plan" name="Sequence_No_Plan" placeholder="Sequence No"/>
                                        </div>
                                        <div class="mb-3">
                                            <label for="Production_Date_Plan" class="form-label">Production Date</label>
                                            <input type="text" class="form-control" id="Production_Date_Plan" name="Production_Date_Plan" placeholder="Production Date"/>
                                        </div>
                                        <div class="mb-3">
                                            <label for="Model_Name_Plan" class="form-label">Model Name</label>
                                            <input type="text" class="form-control" id="Model_Name_Plan" name="Model_Name_Plan" placeholder="Model Name"/>
                                        </div>
                                        <div class="mb-3">
                                            <label for="Production_No_Plan" class="form-label">Production No</label>
                                            <input type="text" class="form-control" id="Production_No_Plan" name="Production_No_Plan" placeholder="Production No"/>
                                        </div>
                                        <div class="mb-3">
                                            <label for="Chasis_No_Plan" class="form-label">Chasis No</label>
                                            <input type="text" class="form-control" id="Chasis_No_Plan" name="Chasis_No_Plan" placeholder="Chasis No"/>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label for="Model_Label_Plan" class="form-label">Model Label</label>
                                            <input type="text" class="form-control" id="Model_Label_Plan" name="Model_Label_Plan" placeholder="Model Label"/>
                                        </div>
                                        <div class="mb-3">
                                            <label for="Safety_Frame_Label_Plan" class="form-label">Safety Frame Label</label>
                                            <input type="text" class="form-control" id="Safety_Frame_Label_Plan" name="Safety_Frame_Label_Plan" placeholder="Safety Frame Label"/>
                                        </div>
                                        <div class="mb-3">
                                            <label for="Model_Mower_Plan" class="form-label">Model Mower</label>
                                            <input type="text" class="form-control" id="Model_Mower_Plan" name="Model_Mower_Plan" placeholder="Model Mower"/>
                                        </div>
                                        <div class="mb-3">
                                            <label for="Mower_No_Plan" class="form-label">Mower No</label>
                                            <input type="text" class="form-control" id="Mower_No_Plan" name="Mower_No_Plan" placeholder="Mower No"/>
                                        </div>
                                        <div class="mb-3">
                                            <label for="Model_Collector_Plan" class="form-label">Model Collector</label>
                                            <input type="text" class="form-control" id="Model_Collector_Plan" name="Model_Collector_Plan" placeholder="Model Collector"/>
                                        </div>
                                        <div class="mb-3">
                                            <label for="Collector_No_Plan" class="form-label">Collector No</label>
                                            <input type="text" class="form-control" id="Collector_No_Plan" name="Collector_No_Plan" placeholder="Collector No"/>
                                        </div>
                                    </div>
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

@endsection
