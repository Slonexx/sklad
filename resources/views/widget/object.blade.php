@extends('widget.widget')
@section('content')


        <div class="row gradient rounded p-2">
            <div class="col-6">
                <div class="mx-2 text-center"> <img src="{{ asset('integration.svg') }}" width="50%"   alt=""> </div>
            </div>
            <div class="col-2 ">

            </div>
        </div>

        <div id="messageGoodAlert" class=" mt-1 mx-3 p-2 alert alert-success text-center " style="display: none; font-size: 12px; margin-bottom: 5px !important;">    </div>
        <div id="messageErrorAlert" class=" mt-1 mx-3 p-2 alert alert-danger text-center " style="display: none; font-size: 12px; margin-bottom: 5px !important;">    </div>



        <div  class="mt-1 mx-4 text-center">
            <div class="row">
                <div class="col-1"> </div>
                <button id="btnF" onclick="fiscalization()" class="col-4 btn p-1 btn-warning text-white rounded-pill" style="font-size: 14px"></button>
                <div class="col-1"></div>
                <button id="TIS_search" onclick="getSearchToTIS()" class="col-4 btn p-1 btn-info text-white rounded-pill" style="font-size: 14px"> Посмотреть в кассе </button>
                <div class="col-1"></div>
            </div>
            <div class="row mt-2">
                <div class="col-1"> </div>
                <button id="CloseChangeWebKassa" onclick="CloseChangeWebKassa()" class="col-9 btn p-1 btn-danger text-white rounded-pill" style="font-size: 14px">Закрыть смену</button>
                <div class="col-1"></div>
            </div>
        </div>



    <script>
        const hostWindow = window.parent
        let Global_messageId = 0
        let accountId = "{{$accountId}}"
        let Global_object_Id
        let entity_type = "{{$entity}}"
        let cashbox = "{{$cashbox_id}}"


        function getSearchToTIS(){ window.open('{{Config::get("Global")['kassa']}}'+"history/receipts") }

        function CloseChangeWebKassa(){
            window.document.getElementById('CloseChangeWebKassa').style.display = 'none'
            let data = {
                cashbox_id: cashbox,
            };

            let settings = ajax_settings("{{Config::get("Global")['url']}}"+'kassa/ZReport/'+accountId, 'GET', data)
            console.log('Widget setting attributes: ↓')
            console.log(settings)

            $.ajax(settings).done(function (response) {
                console.log("{{Config::get("Global")['url']}}" + 'kassa/ZReport/'+accountId+' response ↓ ')
                console.log(settings)

                if (response.statusCode == 200) {
                    window.document.getElementById('messageGoodAlert').style.display = 'block'
                    window.document.getElementById("messageGoodAlert").innerText = "Смена Закрыта"
                }
                else {
                    window.document.getElementById('messageErrorAlert').style.display = 'block'
                    window.document.getElementById('messageErrorAlert').innerText = response.message
                    window.document.getElementById('CloseChangeWebKassa').style.display = 'block'
                }
            });
        }

        function ajax_settings(url, method, data){
            return {
                 "url": url,
                 "method": "GET",
                 "timeout": 0,
                 "headers": {"Content-Type": "application/json",},
                 "data": data,
             }
        }



        //let receivedMessage = {"name":"Open","extensionPoint":"document.customerorder.edit","objectId":"ac0c9983-acec-11ed-0a80-06ac001abb0c","messageId":5,"displayMode":"expanded"}

        window.addEventListener("message", function(event) {

        window.document.getElementById('messageGoodAlert').style.display = 'none'
        window.document.getElementById('messageErrorAlert').style.display = 'none'
        window.document.getElementById('CloseChangeWebKassa').style.display = 'none'

        const receivedMessage = event.data;
        if (receivedMessage.name === 'Open') {

            Global_object_Id = receivedMessage.objectId;
            let data = {
                accountId: accountId,
                entity_type: entity_type,
                objectId: Global_object_Id,
            };

            //receivedMessage = null;

            let settings = ajax_settings("{{Config::get("Global")['url']}}"+'widget/Info/Attributes/', 'GET', data)
            console.log('Widget setting attributes: ↓')
            console.log(settings)

            $.ajax(settings).done(function (response) {
                console.log("{{Config::get("Global")['url']}}" + 'widget/Info/Attributes/ response ↓ ')
                console.log(settings)

                let sendingMessage = {
                    name: "OpenFeedback",
                    correlationId: receivedMessage.messageId
                };
                hostWindow.postMessage(sendingMessage, '*');


                let btnF = window.document.getElementById('btnF')
                let TIS_search = window.document.getElementById('TIS_search')
                window.document.getElementById('CloseChangeWebKassa').style.display = 'block'

                if (response.ticket_id == null){
                    btnF.innerText = 'Фискализация';
                    window.document.getElementById('messageGoodAlert').style.display = 'none'
                    window.document.getElementById("messageGoodAlert").innerText = ""
                    TIS_search.style.display = 'none'
                } else {
                    btnF.innerText = 'Действие с чеком';
                    window.document.getElementById('messageGoodAlert').style.display = 'block'
                    window.document.getElementById("messageGoodAlert").innerText = "Чек уже создан. Фискальный номер:  " + response.ticket_id
                    TIS_search.style.display = 'block'
                }

            });
        }

         });



        function fiscalization(){
            Global_messageId++;
            let sendingMessage = {
                name: "ShowPopupRequest",
                messageId: Global_messageId,
                popupName: "fiscalizationPopup",
                popupParameters: {
                    object_Id:Global_object_Id,
                    accountId:accountId,
                    entity_type:entity_type,
                },
            };
            console.log("Widget Sending : ↓" )
            console.log(sendingMessage)
            hostWindow.postMessage(sendingMessage, '*');
        }


    </script>
@endsection
