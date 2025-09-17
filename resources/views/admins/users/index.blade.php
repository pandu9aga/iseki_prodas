@extends('layouts.main')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col order-0">
            <div class="card mb-3">
                <div class="d-flex align-items-end row">
                    <div class="col">
                        <div class="card-body">
                            <h5 class="card-title text-primary">User</h5>
                            <a href="{{ route('user.add') }}" class="btn btn-md btn-outline-primary"><span class="tf-icons bx bx-plus"></span> Add User</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card mb-3">
                <div class="d-flex align-items-end row">
                    <div class="col">
                        <div class="card-body">
                            <div class="table-responsive text-nowrap">
                                <table id="example" class="table">
                                    <thead>
                                        <tr>
                                            <th class="text-center text-uppercase text-primary">No</th>
                                            <th class="text-center text-uppercase text-primary">Type</th>
                                            <th class="text-center text-uppercase text-primary">Username</th>
                                            <th class="text-center text-uppercase text-primary">Name</th>
                                            <th class="text-center text-uppercase text-primary">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ( $users as $u )
                                        <tr>
                                            <td class="align-middle text-center">
                                                <span class="text-secondary">{{ $loop->iteration }}</span>
                                            </td>
                                            <td>
                                                @php
                                                    $type = $type_user->firstWhere('Id_Type_User', $u->Id_Type_User);
                                                @endphp
                                                <span class="text-secondary mb-0">{{ $type ? $type->Name_Type_User : 'Unknown' }}</span>
                                            </td>
                                            <td>
                                                <span class="text-primary mb-0">{{ $u->Username_User }}</span>
                                            </td>
                                            <td>
                                                <span class="text-secondary mb-0">{{ $u->Name_User }}</span>
                                            </td>
                                            <td class="align-middle text-center">
                                                <a href="{{ route('user.edit', $u->Id_User) }}" class="btn btn-sm btn-outline-primary">
                                                    <span class="tf-icons bx bx-edit"></span>
                                                </a>
                                                <a href="{{ route('user.destroy', $u->Id_User) }}" class="btn btn-sm btn-outline-danger">
                                                    <span class="tf-icons bx bx-trash"></span>
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
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
