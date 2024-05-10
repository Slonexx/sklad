
@extends('widget.widget')

@section('content')


    <div class="row gradient rounded p-2">
        <div class="col-6">
            <div class="mx-2"> <img src="{{ asset('integration.svg') }}" width="90%"   alt=""> </div>
        </div>
        <div class="col-2 ">

        </div>
    </div>

    <div class="row mt-4 rounded bg-white">
        <div class="col-1"></div>
        <div class="col-10">
            <div class="text-center">
                <div class="p-2 bg-danger text-white" style="padding-bottom: 1.5rem !important;">
                    <span id="errorMessage" class="s-min-10">

                    </span>
                    <span> <i class="fa-solid fa-ban "></i></span>
                </div>
            </div>
        </div>
    </div>


    <script>
        const hostWindow = window.parent
        let app = @json($message);

        if (app.length > 0) {
            window.document.getElementById('errorMessage').innerText = JSON.stringify(app)
        }

        window.addEventListener("message", function(event) {
            const receivedMessage = event.data;
            let sendingMessage = {
                name: "OpenFeedback",
                correlationId: receivedMessage.messageId
            };
            hostWindow.postMessage(sendingMessage, '*');

        })

    </script>


@endsection

<style>
    .s-min-10 {
        font-size: 10px;
    }
</style>
