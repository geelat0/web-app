@extends('app')  {{-- Main blade File --}}


{{-- Content of Pages --}}
@section('content')

<div class="col-lg-12 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title">Success Indicator</h4>
        {{-- <p class="card-description"> Add class <code>.table-bordered</code> --}}
        </p>
        <div class="row">
            <div class="col">
                <p class="d-inline-flex gap-1">
                    <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
                        <i class="mdi mdi-filter-outline"></i> Filter
                    </button>
                </p>
            </div>
            <div class="col d-flex justify-content-end mb-3" >

                <div id="table-buttons" class="d-flex">
                    <!-- Buttons will be appended here -->
                </div>
            </div>
        </div>
        <div class="collapse" id="collapseExample">
            <div class="card card-body">
                <div class="d-flex justify-content-center mb-3">
                    <div class="input-group input-group-sm me-3">
                        <input type="text" id="search-input" class="form-control form-control-sm" placeholder="Search...">
                    </div>
                    <div class="input-group input-group-sm">
                        <input type="text" id="date-range-picker" class="form-control form-control-sm" placeholder="Select date range">
                    </div>
                </div>
            </div>
        </div>
       
      </div>
    </div>
</div>


<div class="col-lg-12 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        
        {{-- <p class="card-description"> Add class <code>.table-bordered</code> --}}
        </p>
        
        <div class="table-responsive pt-3">
          <table id="org-table"  class="table table-striped" style="width: 100%">
            <tbody>
            </tbody>
          </table>
        </div>
      </div>
    </div>
</div>
   
@endsection