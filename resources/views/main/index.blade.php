@extends('layout')
@section('item', 'link_1')
@section('content')
    <script>
        let url = "{{Config::get("Global")['url']}}";
        let accountId = '{{ $accountId }}'
    </script>
    <div class="p-4 mx-1 mt-1 bg-white rounded py-3  main-container content-container">
        @if ( request()->isAdmin != null and request()->isAdmin != 'ALL' )
            <div class="mt-2 alert alert-danger alert-dismissible fade show in text-center "> Доступ к настройкам есть только у администратора
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

            <div id="message" class="mt-2 alert alert-info alert-dismissible fade show in text-center" style="display: none">
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @include('div.TopServicePartner')

            <div class="row mt-3" >
                <div class="col-6">
                    <div class="row">
                        <img style="width: 110px;" src="https://cdn-ru.bitrix24.kz/b9797699/landing/9d0/9d0212c479ea86a7aaad280ffd5624bf/prodano_1x.png">
                        <div> <strong>ФИСКАЛИЗАЦИЯ ПРОДАЖ</strong></div>
                        <div class="">
                            Можно фискализировать продажи из документов Заказ покупателя и Отгрузка с отправкой чека на WhatsApp или почту, также можно скачать или распечатать его.
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <img style="width: 50px;" src="https://cdn-ru.bitrix24.kz/b9797699/landing/9eb/9eb40996ac4f5bdf718e12a552f9e7d5/qrcode_1x.png">
                    <div class="mt"> <strong>РАБОТА С МАРКИРОВАННЫМИ ТОВАРАМИ</strong></div>
                    <div class="">
                        <div> Наше решение позволяет отправлять коды маркировки в ОФД для списания с вашего виртуального склада.</div>
                        <div> Фискализация продаж маркированных товаров происходит только через документ Отгрузка.</div>
                        <div> Фискализация возвратов маркированных товаров происходит только через документ Возврат покупателю.</div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-6">
                    <div class="row">
                        <div class="col-6">
                            <img style="width: 50px" src="https://cdn-ru.bitrix24.kz/b9797699/landing/f99/f99fd290bc5e4e4789ef97a323fa3c80/vozvrat_1x.png">
                            <div class="mt"> <strong>ФИСКАЛИЗАЦИЯ ВОЗВРАТОВ</strong></div>
                            <div class="">
                                Возврат можно произвести как из документов Заказ покупателя и Отгрузка, так и из Возврата покупателю.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <img style="width: 50px" src="https://cdn-ru.bitrix24.kz/b9797699/landing/5ec/5ec977c1dc197a31da442b454061c7ff/smena_1x.png">
                    <div class=""> <strong>X,Z-ОТЧЁТЫ, ВНЕСЕНИЕ/ИЗЪЯТИЕ</strong></div>
                    <div class="">
                        Не выходя из МоегоСклада можно закрыть смену (Z-отчёт), получить промежуточный итог смены (X-отчёт), произвести внесение или изъятие денежных средств.
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-6">
                    <img style="width: 50px" src="https://cdn-ru.bitrix24.kz/b9797699/landing/244/24459c884f02145508a55e45f7a7d718/dokumenty_1x.png">
                    <div class=""> <strong>АВТОМАТИЧЕСКОЕ СОЗДАНИЕ ДОКУМЕНТОВ</strong></div>
                    <div class="">
                        Вы можете упростить себе жизнь и настроить автоматическое создание Платежных документов (Ордера или Платежи).
                    </div>
                </div>
                <div class="col-6">
                    <img style="width: 50px" src="https://cdn-ru.bitrix24.kz/b9797699/landing/0ac/0ac2895cea94302bb70d9499bd769592/14_dney_1x.png">
                    <div class=""> <strong>14 ДНЕЙ БЕСПЛАТНО</strong></div>
                    <div class="">
                        Мы на 1000% уверены в своем приложении и поэтому готовы предоставить 14 дней, чтобы Вы могли оценить его возможности и уникальность.
                    </div>
                </div>
            </div>


            <div class="row mt-4">
                <div class="col-6">
                    <img style="width: 50px" src="https://cdn-ru.bitrix24.kz/b9797699/landing/a5d/a5d76d6870e8154035060f40b9848dca/Skoro_1x.png">
                    <div class=""> <strong>НОВЫЕ ВОЗМОЖНОСТИ</strong></div>
                    <div class="">
                        Мы не стоим на месте, поэтому совсем скоро вы сможете оценить новые фишки в нашем приложении. Ну и будем признательны за обратную связь.
                    </div>
                </div>
            </div>






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

