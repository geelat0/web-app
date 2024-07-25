@extends('app')

@section('content')

<div class="col-lg-12 grid-margin stretch-card">
    <div class="card">
        <div class="card-header">
          <div class="d-flex justify-content-center mt-3" >
            <h3>Enable Two Factor Authentication</h3>
          </div>

        </div>
        <div class="card-body">
           
        </div>
    </div>
</div>

<div class="col-lg-12 grid-margin stretch-card">
    <div class="card">
        <div class="card-header">

        </div>
        <div class="card-body">
            <ul class="nav nav-fill nav-tabs" role="tablist">
                <li class="nav-item" role="presentation">
                  <a class="nav-link active" id="fill-tab-0" data-bs-toggle="tab" href="#fill-tabpanel-0" role="tab" aria-controls="fill-tabpanel-0" aria-selected="true"> Install </a>
                </li>
                <li class="nav-item" role="presentation">
                  <a class="nav-link" id="fill-tab-1" data-bs-toggle="tab" href="#fill-tabpanel-1" role="tab" aria-controls="fill-tabpanel-1" aria-selected="false"> Scan QR</a>
                </li>
                <li class="nav-item" role="presentation">
                  <a class="nav-link" id="fill-tab-2" data-bs-toggle="tab" href="#fill-tabpanel-2" role="tab" aria-controls="fill-tabpanel-2" aria-selected="false"> OTP </a>
                </li>
              </ul>
              <div class="tab-content pt-5" id="tab-content">
                <div class="tab-pane active" id="fill-tabpanel-0" role="tabpanel" aria-labelledby="fill-tab-0">
                  <div class="d-flex justify-content-center mt-3" >
                    <p>Download the Google Authenticator</p>
                  </div>
                </div>
                <div class="tab-pane" id="fill-tabpanel-1" role="tabpanel" aria-labelledby="fill-tab-1">
                  <div class="d-flex justify-content-center mt-3" >
                    <p>Scan this QR code with your Google Authenticator app:</p>
                  </div>
                  <p class="text-center">
                    {!! $qr_code !!}
                  </p>

                  <div class="d-flex justify-content-center mt-3" >
                    <p>{{ $secret }}</p>
                  </div>
                </div>
                <div class="tab-pane" id="fill-tabpanel-2" role="tabpanel" aria-labelledby="fill-tab-2">

                  <div class="d-flex justify-content-center mt-3" >
                    <p>OTP</p>
                  </div>

                  <form id="changePasswordForm">
                    @csrf
                    <div class="row mb-3">
                      <div class="col"></div>
                      <div class="col">
                        <input id="otp"
                            type="number" min="0" max="999999" step="1"
                            class="form-control{{ $errors->has('otp') ? ' is-invalid' : '' }}"
                            autocomplete="off"
                            name="otp" value="" required autofocus>
                      </div>
                      <div class="col"></div>
                    </div>
                    
                    <div class="d-flex justify-content-center mb-2">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                  </form>



                </div>
              </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')

@endsection