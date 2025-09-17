@extends('layouts.main')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col order-0">
            <div class="card mb-3">
                <div class="d-flex align-items-end row">
                    <div class="col">
                        <div class="card-body">
                            <h5 class="card-title text-primary mb-0">Add User</h5>
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
                            <form role="form" class="text-start" action="{{ route('user.create') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <label for="Name_User" class="form-label">Name</label>
                                    <input type="text" class="form-control" id="Name_User" name="Name_User" placeholder="Name"
                                    />
                                </div>
                                <div class="mb-3">
                                    <label for="Username_User" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="Username_User" name="Username_User" placeholder="Username"
                                    />
                                </div>
                                <div class="mb-3">
                                    <label for="Password_User" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="Password_User" name="Password_User" placeholder="Password"
                                    />
                                </div>
                                <div class="mb-3">
                                    <label for="Id_Type_User" class="form-label">Type</label>
                                    <select class="form-select" id="Id_Type_User" aria-label="Id_Type_User" name="Id_Type_User">
                                        @foreach ($type_user as $type)
                                            <option value="{{ $type->Id_Type_User }}">
                                                {{ $type->Name_Type_User }}
                                            </option>
                                        @endforeach
                                    </select>
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
<link href="{{asset('assets/css/datatables.min.css')}}" rel="stylesheet">
@endsection

@section('script')
<script src="{{asset('assets/js/datatables.min.js')}}"></script>
<script>
new DataTable('#example');
</script>
@endsection
