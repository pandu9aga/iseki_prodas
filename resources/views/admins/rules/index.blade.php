@extends('layouts.main')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col order-0">
            <div class="card mb-3">
                <div class="d-flex align-items-end row">
                    <div class="col">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Rule</h5>
                            <a href="{{ route('rule.add') }}" class="btn btn-md btn-outline-primary"><span class="tf-icons bx bx-plus"></span> Add Rule</a>
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
                                            <th class="text-center text-uppercase text-primary">Rule</th>
                                            <th class="text-center text-uppercase text-primary">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ( $rules as $r )
                                        <tr>
                                            <td class="align-middle text-center">
                                                <span class="text-secondary">{{ $loop->iteration }}</span>
                                            </td>
                                            <td>
                                                <span class="text-secondary mb-0">{{ $r->Type_Rule }}</span>
                                            </td>
                                            <td>
                                                <span class="text-secondary mb-0">{{ $r->Rule_Rule }}</span>
                                            </td>
                                            <td class="align-middle text-center">
                                                <a href="{{ route('rule.edit', $r->Id_Rule) }}" class="btn btn-sm btn-outline-primary">
                                                    <span class="tf-icons bx bx-edit"></span>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#basicModal-{{ $r->Id_Rule }}">
                                                    <span class="tf-icons bx bx-trash"></span>
                                                </button>
                                            </td>
                                        </tr>
                                        <!-- Modal -->
                                        <div class="modal fade" id="basicModal-{{ $r->Id_Rule }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-danger">
                                                        <h5 class="modal-title text-white" id="exampleModalLabel1">Delete Rule</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col mb-3">
                                                                <label for="typeBasic" class="form-label">Type</label>
                                                                <input type="text" id="typeBasic" class="form-control" value="{{ $r->Type_Rule }}" readonly/>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                                        Cancel
                                                        </button>
                                                        <form action="{{ route('rule.destroy', $r->Id_Rule) }}" method="POST" style="display:inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger">Delete</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
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
