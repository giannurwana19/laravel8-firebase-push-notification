@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                {{-- <center>
                    <button id="btn-nft-enable" onclick="initFirebaseMessagingRegistration()"
                        class="btn btn-danger btn-xs btn-flat">Allow for Notification</button>
                </center> --}}
                <div class="card">
                    <div class="card-header">{{ __('Dashboard') }}</div>

                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                        <form id="formSendNotification" action="{{ route('send.notification') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label>Title</label>
                                <input type="text" class="form-control" name="title">
                            </div>
                            <div class="form-group">
                                <label>Body</label>
                                <textarea class="form-control" name="body"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Send Notification</button>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://www.gstatic.com/firebasejs/7.23.0/firebase.js"></script>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
    <script>
        var firebaseConfig = {
            apiKey: "{{ env('FIREBASE_API_KEY') }}",
            authDomain: "{{ env('FIREBASE_AUTH_DOMAIN') }}",
            databaseURL: "{{ env('FIREBASE_DATABASE_URL') }}",
            projectId: "{{ env('FIREBASE_PROJECT_ID') }}",
            storageBucket: "{{ env('FIREBASE_STORAGE_BUCKET') }}",
            messagingSenderId: "{{ env('FIREBASE_MESSAGING_SENDER_ID') }}",
            appId: "{{ env('FIREBASE_APP_ID') }}",
            measurementId: "{{ env('FIREBASE_MEASUREMENT_ID') }}"
        };

        firebase.initializeApp(firebaseConfig);
        const messaging = firebase.messaging();

        if (Notification.permission !== 'granted') {
            Notification.requestPermission().then(permission => {
                if (permission === 'granted') {
                    console.log('Notification permission granted.');
                } else {
                    console.log('Notification permission denied.');
                }
            });
        }

        @if (!auth()->user()->device_token)
            initFirebaseMessagingRegistration();
        @endif

        function initFirebaseMessagingRegistration() {
            messaging
                .requestPermission()
                .then(function() {
                    return messaging.getToken()
                })
                .then(function(token) {
                    console.log(token);

                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    $.ajax({
                        url: '{{ route('save-token') }}',
                        type: 'POST',
                        data: {
                            token: token
                        },
                        dataType: 'JSON',
                        success: function(response) {
                            console.log('Token saved successfully.');
                        },
                        error: function(err) {
                            console.log('User Chat Token Error' + err);
                        },
                    });

                }).catch(function(err) {
                    console.log('User Chat Token Error' + err);
                });
        }

        $('#formSendNotification').on('submit', function(e) {
            e.preventDefault();
            let form = $(this)[0];

            $.ajax({
                type: form.method,
                url: form.action,
                data: new FormData(form),
                processData: false,
                contentType: false,
                success: function(response) {
                    form.reset();
                    alert('Berhasil mengirim notifikasi')
                },
                error: function(err, text) {
                    alert('Gagal mengirim notifikasi')
                }
            });
        });

        messaging.onMessage(function(payload) {
            const noteTitle = payload.notification.title;
            const noteOptions = {
                body: payload.notification.body,
                icon: payload.notification.icon,
            };
            new Notification(noteTitle, noteOptions);
        });
    </script>
@endsection
