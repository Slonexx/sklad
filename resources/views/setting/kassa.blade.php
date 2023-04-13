@extends('layout')
@section('item', 'link_4')
@section('content')
    @include('setting.script_setting_app')
    <div class="p-4 mx-1 mt-1 bg-white rounded py-3">

        @include('div.TopServicePartner')
        @include('div.alert')
        @isset($message)
            <script>alertViewByColorName("danger", "{{ $message }}")</script>
        @endisset

        <form action="/Setting/Kassa/{{ $accountId }}?isAdmin={{ $isAdmin }}" method="post" class="mt-3">
        @csrf <!-- {{ csrf_field() }} -->

            <div class="row p-1 gradient_invert rounded text-black">
                <div class="col-11">
                    <div style="font-size: 20px">Профиль </div>
                </div>
            </div>
            <div class="mt-2 mx-2 mb-2" style="display: block">
                <div class="row">
                    <div class="col-6">
                        <label class="mt-1 mx-4"> Выберите профиль пользователя </label>
                    </div>
                    <div class="col-6">
                        <select id="profile_id" name="profile_id" class="form-select text-black" onchange="getCashbox(this.value)">
                        </select>
                    </div>
                </div>
            </div>

            <div class="row p-1 gradient_invert rounded text-black">
                <div class="col-11">
                    <div style="font-size: 20px">Доступная касса </div>
                </div>
            </div>
            <div class="mt-2 mx-2 mb-2" style="display: block">
                <div class="row">
                    <div class="col-6">
                        <label class="mt-1 mx-4"> Выберите кассу </label>
                    </div>
                    <div class="col-6">
                        <select id="cashbox_id" name="cashbox_id" class="form-select text-black" onchange="getSalePoint()">
                            <option value="0">Выберите профиль пользователя</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row p-1 gradient_invert rounded text-black">
                <div class="col-11">
                    <div style="font-size: 20px">Точка продаж в wipon </div>
                </div>
            </div>
            <div class="mt-2 mx-2 mb-2" style="display: block">
                <div class="row">
                    <div class="col-6">
                        <label class="mt-1 mx-4"> Выберите точка продаж </label>
                    </div>
                    <div class="col-6">
                        <select id="sale_point_id" name="sale_point_id" class="form-select text-black" onchange="">
                            <option value="0">Выберите кассу </option>
                        </select>
                    </div>
                </div>
            </div>


            <hr class="href_padding">
            <div class='d-flex justify-content-end text-black btnP' > <button class="btn btn-outline-dark textHover"> Сохранить </button> </div>
        </form>
    </div>

    <script>
        let url = '{{ Config::get("Global")['url'] }}';
        let accountId = "{{$accountId}}"
        NAME_HEADER_TOP_SERVICE("Настройки → Касса")
        getProfile()


        function getProfile(){

            let settings = ajax_settings(url + 'Setting/Kassa/profile_id/'+ accountId, "GET", null)
            console.log(url + 'Setting/Kassa/profile_id/'+ accountId + ' settings ↓ ')
            console.log(settings)
            $.ajax(settings).done(function (json) {
                console.log(url + 'Setting/Kassa/profile_id/' + accountId + ' response ↓ ')
                console.log(json)

                if (json.Code === 200) {
                    let data = json.Data
                    for (let index = 0; index<data.length; index++){
                        let option = document.createElement("option")
                        option.value= data[index].id
                        option.text= data[index].company.name
                        window.document.getElementById('profile_id').add(option)
                    }
                } else {
                    alertViewByColorName("danger", json.Data)
                }
                getCashbox(window.document.getElementById('profile_id').value)
            })
        }

        function getCashbox(value){

            $("#cashbox_id").empty();

            let settings = ajax_settings(url + 'Setting/Kassa/cashbox/'+ accountId, "GET", {'profile_id' : value})
            console.log(url + 'Setting/Kassa/cashbox/'+ accountId + ' settings ↓ ')
            console.log(settings)
            $.ajax(settings).done(function (json) {
                console.log(url + 'Setting/Kassa/cashbox/' + accountId + ' response ↓ ')
                console.log(json)

                if (json.Code === 200) {
                    let data = json.Data
                    for (let index = 0; index<data.length; index++){
                        let option = document.createElement("option")
                        option.value= data[index].id
                        option.text= data[index].name
                        window.document.getElementById('cashbox_id').add(option)
                    }
                } else {
                    alertViewByColorName("danger", json.Data)
                }
                getSalePoint()
            })
        }

        function getSalePoint(){
            $("#sale_point_id").empty();

            let value = window.document.getElementById('profile_id').value

            let settings = ajax_settings(url + 'Setting/Kassa/cashbox/'+ accountId, "GET", {'profile_id' : value})
            console.log(url + 'Setting/Kassa/cashbox/'+ accountId + ' settings ↓ ')
            console.log(settings)
            $.ajax(settings).done(function (json) {
                console.log(url + 'Setting/Kassa/cashbox/' + accountId + ' response ↓ ')
                console.log(json)

                if (json.Code === 200) {
                    let data = json.Data
                    for (let index = 0; index<data.length; index++){
                        console.log((data[index].sale_point))
                        if (window.document.getElementById('cashbox_id').value == data[index].id ){
                            let option = document.createElement("option")
                            option.value= data[index].sale_point.id
                            option.text= data[index].sale_point.name
                            window.document.getElementById('sale_point_id').add(option)
                        }
                    }
                } else {
                    alertViewByColorName("danger", json.Data)
                }

            })

        }
    </script>
@endsection



