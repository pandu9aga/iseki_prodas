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
                    <div class="col-6">
                        <div class="card-body">
                            @if ($errors->any())
                                <div>
                                    @foreach ($errors->all() as $error)
                                        <p style="color:red;">{{ $error }}</p>
                                    @endforeach
                                </div>
                            @endif
                            <form role="form" class="text-start" action="{{ route('user.update', ['Id_User' => $Id_User->Id_User]) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('put')
                                <div class="mb-3">
                                    <label for="Name_User" class="form-label">Name</label>
                                    <input type="text" class="form-control @error('Name_User') is-invalid @enderror" id="Name_User" name="Name_User" placeholder="Name" value="{{ $Id_User->Name_User }}"/>
                                </div>
                                @error('Name_User')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="mb-3">
                                    <label for="Username_User" class="form-label">Username</label>
                                    <input type="text" class="form-control @error('Username_User') is-invalid @enderror" id="Username_User" name="Username_User" placeholder="Username" value="{{ $Id_User->Username_User }}"/>
                                </div>
                                @error('Username_User')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="mb-3">
                                    <label for="Password_User" class="form-label">Password</label>
                                    <input type="password" class="form-control @error('Password_User') is-invalid @enderror" id="Password_User" name="Password_User" placeholder="Password" value="{{ $Id_User->Password_User }}"/>
                                </div>
                                @error('Password_User')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="mb-3">
                                    <label for="Id_Type_User" class="form-label">Type</label>
                                    <select class="form-select" id="Id_Type_User" aria-label="Id_Type_User" name="Id_Type_User">
                                        @foreach ($type_user as $type)
                                            <option value="{{ $type->Id_Type_User }}" {{ $Id_User->Id_Type_User == $type->Id_Type_User ? 'selected' : '' }}>
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

@endsection

@section('script')

@endsection
