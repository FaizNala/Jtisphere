@extends('layouts.template')
@section('content')
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">Profile</h3>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="row mb-4">
                <div class="col-md-12 d-flex justify-content-center">
                    <div class="avatar-container position-relative">
                        <img src="{{ Auth::user()->dosen->avatar ?? asset('default-avatar.png') }}"
                        alt="Profile Avatar"
                        class="rounded-circle img-thumbnail shadow"
                        style="max-width: 220px; width: 220px; height: 220px; object-fit: cover; border: 5px solid white;">
                    </div>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-user mr-2 text-primary"></i>
                                <div>
                                    <span class="text-muted">Username</span>
                                    <h5>{{ $user->username }}</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-id-card mr-2 text-success"></i>
                                <div>
                                    <span class="text-muted">Nama</span>
                                    <h5>{{ $user->nama }}</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-id-badge mr-2 text-info"></i>
                                <div>
                                    <span class="text-muted">NIP</span>
                                    <h5>{{ $user->nip }}</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-layer-group mr-2 text-warning"></i>
                                <div>
                                    <span class="text-muted">Level</span>
                                    <h5>{{ $user->level_nama }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12 text-center">
                    <button onclick="modalAction('{{ url('/profile/edit_ajax') }}')" class="btn btn-warning">
                        <i class="fas fa-user-edit mr-2"></i> Edit Profile
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="myModal" class="modal fade animate shake" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" data-width="75%" aria-hidden="true"></div>
@endsection

@push('js')
    <script>
        function modalAction(url = '') {
            $('#myModal').load(url, function() {
                $('#myModal').modal('show');
            });
        }

        function submitForm() {
            const form = document.getElementById('formAction');
            const formData = new FormData(form);

            $.ajax({
                url: form.action,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.status) {
                        $('#myModal').modal('hide');
                        location.reload();
                    } else {
                        let errorMessages = '';
                        for (const field in response.errors) {
                            errorMessages += response.errors[field].join('\n') + '\n';
                        }
                        alert(errorMessages);
                    }
                },
                error: function(xhr) {
                    alert('Terjadi kesalahan: ' + xhr.responseText);
                }
            });
        }
    </script>
@endpush
