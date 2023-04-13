@extends('layout')
@section('item', 'link_1')
@section('content')
    <script>
        let url = "{{Config::get("Global")['url']}}";
        let accountId = '{{ $accountId }}'
    </script>
    <div class="p-4 mx-1 mt-1 bg-white rounded py-3">
        @if ( request()->isAdmin != null and request()->isAdmin != 'ALL' )
            <div class="mt-2 alert alert-danger alert-dismissible fade show in text-center "> Доступ к настройкам есть только у администратора
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

            <div id="message" class="mt-2 alert alert-info alert-dismissible fade show in text-center" style="display: none">
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @include('div.TopServicePartner')






    </div>
    <script>
        let hideOrShow = "{{ $hideOrShow }}"
        NAME_HEADER_TOP_SERVICE("Возможности интеграции")


        document.getElementById('message').style.display = 'none'
        sendCollection(hideOrShow);

        function sendCollection(hideOrShow){
            if (hideOrShow === 'show') {
                sendCollectionPersonal()
            }

            if (hideOrShow === 'hide') {
                //('#sendCollectionOfPersonalInformation').modal('hide');
            }

        }

        function sendCollectionPersonal(){
                let final = url + 'collectionOfPersonalInformation/' + accountId;
                let xmlHttpRequest = new XMLHttpRequest();
                xmlHttpRequest.addEventListener("load", function () {
                    let json = JSON.parse(this.responseText);
                });
                xmlHttpRequest.open("GET", final);
                xmlHttpRequest.send();

        }
    </script>
@endsection

