@extends('layout')
@section('item', 'link_6')
@section('content')

    <script> function ajax_settings(url, method, data){
            return {
                "url": url,
                "method": method,
                "timeout": 0,
                "headers": {"Content-Type": "application/json",},
                "data": data,
            }
        }</script>

    <div class="p-4 mx-1 mt-1 bg-white rounded py-3">

        @include('div.TopServicePartner')

            <div id="message_good" class="mt-2 alert alert-success alert-dismissible fade show in text-center" style="display: none">
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

            <div id="message" class="mt-2 alert alert-danger alert-dismissible fade show in text-center" style="display: none">
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>


        <form class="mt-3" action="" method="post">
        @csrf <!-- {{ csrf_field() }} -->
            <div class="row">
                <label for="idKassa" class="col-3 col-form-label"> Выберите кассу </label>
                <div class="col-9">
                    <select id="idKassa" name="idKassa" class="form-select text-black">
                        @foreach( $ArrayKassa as $item)
                            <option value="{{ $item->UniqueNumber }}"> {{ $item->Name }} </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-3">  </div>
                <div class="col-4 mx-3 row bg-success text-white p-1 col-form-label rounded ">
                    <div class="col-6"> Наличных в кассе: </div>
                    <div class="col-6 text-right" id="cashKassa"> </div>
                </div>
            </div>

            <hr>

            <div class='text-black text-center' >
                <div class="row ">
                    <div onclick="activate_btn('XReport')" class="col-2 btn btn-outline-dark textHover"> Получить X-Отчёт </div>
                    <div class="col-2"></div>
                    <div onclick="activate_btn('cash')" class="col-4 btn btn-outline-dark textHover"> Внесение/Изъятие </div>
                    <div class="col-2"></div>
                    <div onclick="activate_btn('ZReport')" class="col-2 btn btn-outline-dark textHover"> Получить Z-Отчёт </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        let idKassa = "{{$kassa}}"
        let accountId = '{{ $accountId }}'
        window.document.getElementById('idKassa').value = idKassa
        CashOnHand()
        NAME_HEADER_TOP_SERVICE("Смена")

        function Report(Params){
            window.document.getElementById('message').style.display = 'none'

            let url = "{{ Config::get("Global")['url'] }}" + "kassa/"+Params+"/" +accountId
            let data = {
                CashboxUniqueNumber: window.document.getElementById('idKassa').value,
            };

            let settings = ajax_settings(url, "GET", data);
            console.log(url + ' settings ↓ ')
            console.log(settings)
            $.ajax(settings).done(function (json) {
                console.log(url + ' response ↓ ')
                console.log(json)

                if (json.statusCode == 200){
                    window.document.getElementById('ReportRow').style.display = "Block"

                    if (Params == "XReport") window.document.getElementById("ReportName").innerText = 'СМЕННЫЙ Х-ОТЧЕТ'
                    if (Params == "ZReport") window.document.getElementById("ReportName").innerText = 'СМЕННЫЙ Z-ОТЧЕТ'

                    window.document.getElementById('TaxPayerName').innerText = json.Data.TaxPayerName
                    window.document.getElementById('TaxPayerIN').innerText = json.Data.TaxPayerIN
                    if (json.Data.TaxPayerVAT == true) {
                        window.document.getElementById('TaxPayerVAT').style.display = "Block"
                        window.document.getElementById('TaxPayerVATSeria').innerText = json.Data.TaxPayerVATSeria
                        window.document.getElementById('TaxPayerVATNumber').innerText = json.Data.TaxPayerVATNumber
                    }
                    window.document.getElementById('ReportNumber').innerText = json.Data.ReportNumber
                    window.document.getElementById('ShiftNumber').innerText = json.Data.ShiftNumber
                    window.document.getElementById('StartOn').innerText = json.Data.StartOn
                    window.document.getElementById('ReportOn').innerText = json.Data.ReportOn

                    window.document.getElementById('StartNonNullableSell').innerText = new Intl.NumberFormat().format(json.Data.StartNonNullable.Sell)
                    window.document.getElementById('StartNonNullableBuy').innerText = new Intl.NumberFormat().format(json.Data.StartNonNullable.Buy)
                    window.document.getElementById('StartNonNullableReturnSell').innerText = new Intl.NumberFormat().format(json.Data.StartNonNullable.ReturnSell)
                    window.document.getElementById('StartNonNullableReturnBuy').innerText = new Intl.NumberFormat().format(json.Data.StartNonNullable.ReturnBuy)

                    SellOrBuyOrReturn(json.Data.Sell, "Sell")
                    SellOrBuyOrReturn(json.Data.Buy, "Buy")
                    SellOrBuyOrReturn(json.Data.ReturnSell, "ReturnSell")
                    SellOrBuyOrReturn(json.Data.ReturnBuy, "ReturnBuy")

                    window.document.getElementById('PutMoneySum').innerText = new Intl.NumberFormat().format(json.Data.PutMoneySum)
                    window.document.getElementById('TakeMoneySum').innerText = new Intl.NumberFormat().format(json.Data.TakeMoneySum)
                    window.document.getElementById('CashEndNonNullableSell').innerText =  new Intl.NumberFormat().format(json.Data.EndNonNullable.Sell)

                    window.document.getElementById('EndNonNullableSell').innerText = new Intl.NumberFormat().format(json.Data.EndNonNullable.Sell)
                    window.document.getElementById('EndNonNullableBuy').innerText = new Intl.NumberFormat().format(json.Data.EndNonNullable.Buy)
                    window.document.getElementById('EndNonNullableReturnSell').innerText = new Intl.NumberFormat().format(json.Data.EndNonNullable.ReturnSell)
                    window.document.getElementById('EndNonNullableReturnBuy').innerText = new Intl.NumberFormat().format(json.Data.EndNonNullable.ReturnBuy)

                    window.document.getElementById('ControlSum').innerText = json.Data.ControlSum
                    window.document.getElementById('EndDocumentCount').innerText = json.Data.DocumentCount
                    window.document.getElementById('OfdName').innerText = json.Data.Ofd.Name

                    window.document.getElementById('CashboxIN').innerText = json.Data.CashboxIN
                    window.document.getElementById('CashboxRN').innerText = json.Data.CashboxRN
                    window.document.getElementById('CashboxSN').innerText = json.Data.CashboxSN

                } else {
                    window.document.getElementById('message').style.display = 'block'
                    window.document.getElementById('message').innerText = json.message
                }

            })
        }

        function CashOnHand(){
            window.document.getElementById('message').style.display = 'none'

            let url = "{{ Config::get("Global")['url'] }}" + "kassa/MoneyOperation/viewCash/" +accountId
            let data = {
                CashboxUniqueNumber: window.document.getElementById('idKassa').value,
                OperationType: 1,
                Sum: 0,
            };

            let settings = ajax_settings(url, "GET", data);
            console.log(url + ' settings ↓ ')
            console.log(settings)
            $.ajax(settings).done(function (json) {
                console.log(url + ' response ↓ ')
                console.log(json)

                if (json.statusCode == 200){
                    window.document.getElementById('cashKassa').innerText =  json.message
                } else {
                    window.document.getElementById('message').style.display = 'block'
                    window.document.getElementById('message').innerText = json.message
                }

            })
        }

        function saveValCash(){
            let url = "{{ Config::get("Global")['url'] }}" + "kassa/MoneyOperation/" +accountId
            let data = {
                CashboxUniqueNumber: window.document.getElementById('idKassa').value,
                OperationType: window.document.getElementById('operations').value,
                Sum: window.document.getElementById('inputSum').value,
            };

            let settings = ajax_settings(url, "GET", data);
            console.log(url + ' settings ↓ ')
            console.log(settings)
            $.ajax(settings).done(function (json) {
                console.log(url + ' response ↓ ')
                console.log(json)

                if (json.statusCode == 200){
                    console.log('true')
                    let message_good = window.document.getElementById('message_good');
                    message_good.style.display = 'block'
                    message_good.innerText = json.message
                    closeModal('cash')
                } else {
                    console.log('false')
                    window.document.getElementById('message').style.display = 'block'
                    window.document.getElementById('message').innerText = json.message
                    closeModal('cash')
                }

            })
        }

        function SellOrBuyOrReturn(json, params){
            let Type = "";
            window.document.getElementById(''+params+'Taken').innerText = json.Taken
            window.document.getElementById(''+params+'Count').innerText = json.Count

            if (json.PaymentsByTypesApiModel.length > 0) {
                for (let i = 0; i < json.PaymentsByTypesApiModel.length; i++) {
                    let PaymentsByTypesApiModelName = "";
                    if (json.PaymentsByTypesApiModel[i].Type == 1) { Type = "Card"; PaymentsByTypesApiModelName = "Банковская карта" }
                    if (json.PaymentsByTypesApiModel[i].Type == 0) { Type = "Cash"; PaymentsByTypesApiModelName = "Наличные" }

                    $('#' + params + 'PaymentsByTypesApiModel' +  Type).append('<td colspan="4"> &nbsp; &nbsp;' + PaymentsByTypesApiModelName + " </td>")
                    $('#' + params + 'PaymentsByTypesApiModel' + Type).append('<td style="text-align: right;" colspan="8">' + new Intl.NumberFormat().format(json.PaymentsByTypesApiModel[i].Sum) + " </td>")
                }
            }

                if (json.Discount > 0) {
                    Type = "Discount";
                    $('#' + params + 'PaymentsByTypesApiModel' + Type).append('<td colspan="4"> &nbsp; &nbsp;' + "Скидка" + " </td>")
                    $('#' + params + 'PaymentsByTypesApiModel' + Type).append('<td style="text-align: right;" colspan="8">' + new Intl.NumberFormat().format(json.Discount) + " </td>")
                }

                if (json.Markup > 0) {
                    Type = "Markup";
                    $('#' + params + 'PaymentsByTypesApiModel' + Type).append('<td colspan="4"> &nbsp; &nbsp;' + "Наценка" + " </td>")
                    $('#' + params + 'PaymentsByTypesApiModel' + Type).append('<td style="text-align: right;" colspan="8">' + new Intl.NumberFormat().format(json.Markup) + " </td>")
                }

                if (json.Change > 0) {
                    Type = "Change";
                    $('#' + params + 'PaymentsByTypesApiModel' + Type).append('<td colspan="4"> &nbsp; &nbsp;' + "Сдачи" + " </td>")
                    $('#' + params + 'PaymentsByTypesApiModel' + Type).append('<td style="text-align: right;" colspan="8">' + new Intl.NumberFormat().format(json.Change) + " </td>")
                }

                if (json.VAT > 0) {
                    Type = "VAT";
                    $('#' + params + 'PaymentsByTypesApiModel' + Type).append('<td colspan="4"> &nbsp; &nbsp;' + "НДС" + " </td>")
                    $('#' + params + 'PaymentsByTypesApiModel' + Type).append('<td style="text-align: right;" colspan="8">' + new Intl.NumberFormat().format(json.VAT) + " </td>")
                }


        }

    </script>

    <div class="modal fade" id="cash" tabindex="-1"  role="dialog" aria-labelledby="cashTitle" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cashTitle">Внесение</h5>
                    <div class="close" data-dismiss="modal" aria-label="Close" style="cursor: pointer;"><i onclick="closeModal('cash')" class="fa-regular fa-circle-xmark"></i></div>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <label for="operations" class="col-5 col-form-label"> Выберите операцию </label>
                        <div class="col-7">
                            <select id="operations" name="operations" class="form-select text-black" onchange="valueCash(this.value)">
                                <option value="0"> Внесение </option>
                                <option value="1"> Изъятие </option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <label for="operations" class="col-5 col-form-label">
                            <span id="inputGroupText" class="p-2 text-white bg-success rounded">Введите сумму </span>
                        </label>
                        <div class="col-7 input-group mt-1">
                            <input id="inputSum" name="inputSum" onkeypress="return isNumber(event)" type="text" class="form-control" aria-label="">
                            <div class="input-group-append">
                                <span class="input-group-text">.00</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button onclick="closeModal('cash')" type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                    <button onclick="saveValCash()" type="button" class="btn btn-primary">Сохранить</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="Report" tabindex="-1"  role="dialog" aria-labelledby="cashTitle" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"> Отчёт </h5>
                    <div class="close" data-dismiss="modal" aria-label="Close" style="cursor: pointer;"><i onclick="closeModal('Report')" class="fa-regular fa-circle-xmark"></i></div>
                </div>
                <div id="Print" class="modal-body divPrint" style="font-size: 14px">
                    <div id="ReportRow" class="row" style="display: none">
                        <div id="TaxPayerName" style="text-align: center"> </div>
                        <div style="text-align: center">БИН &nbsp; <span id="TaxPayerIN"></span> </div>
                        <div id="TaxPayerVAT" style="text-align: center; display: none"> НДС Серия &nbsp;
                            <span id="TaxPayerVATSeria"></span>
                            <span>&nbsp; № </span>
                            <span id="TaxPayerVATNumber"></span>
                        </div>
                        <hr style="border:1px dashed black; margin-top: 0 !important; margin-bottom: 0.5rem;">
                        <div id="ReportName" style="text-align: center"> СМЕННЫЙ </div>
                        <div class="d-flex justify-content-between" style="display: flex; justify-content: space-between;"> Документ &nbsp; <span id="ReportNumber"></span> </div>
                        <div style="text-align: center"> Смена &nbsp; <span id="ShiftNumber"></span> </div>
                        <div style="text-align: center">
                            <span id="StartOn" ></span>
                            &nbsp; - &nbsp;
                            <span id="ReportOn" ></span>
                        </div>
                        <div> НЕОБНУЛЯЕМАЯ СУММА НА НАЧАЛО СМЕНЫ</div>
                        <div id="StartNonNullable" style="display: flex;flex-direction: column;">
                            <table role="presentation" style="width: 100%; ">
                                <tbody>

                                    <tr style=" padding: 0;">
                                        <td style="padding: 0;" colspan="5">Продажа</td>
                                        <td id="StartNonNullableSell" style="padding: 0; text-align: right;" colspan="5"> 0 </td>
                                    </tr>
                                    <tr style=" padding: 0;">
                                        <td style="padding: 0;" colspan="5">Возврат продажи</td>
                                        <td id="StartNonNullableBuy" style="padding: 0; text-align: right;" colspan="5"> 0 </td>
                                    </tr>
                                    <tr style=" padding: 0;">
                                        <td style="padding: 0;" colspan="5">Покупка</td>
                                        <td id="StartNonNullableReturnSell" style="padding: 0; text-align: right;" colspan="5"> 0.0 </td>
                                    </tr>
                                    <tr style=" padding: 0;">
                                        <td style="padding: 0;" colspan="5">Возврат покупки</td>
                                        <td id="StartNonNullableReturnBuy" style="padding: 0; text-align: right;" colspan="5"> 0.0 </td>
                                    </tr>

                                </tbody>
                            </table>
                        </div>
                        <div>
                            <table role="presentation" style="width: 100%; ">
                                <tbody>
                                    <tr style=" padding: 0;">
                                        <td style="padding: 0;" colspan="4"></td>
                                        <td style="padding: 0;" colspan="4">Количество</td>
                                        <td style="text-align: right;" colspan="4">Сумма</td>
                                    </tr>

                                    <tr style="">
                                        <td colspan="4">ПРОДАЖА</td>
                                        <td id="SellCount" style="text-align: center;" colspan="4">0</td>
                                        <td id="SellTaken" style="text-align: right;" colspan="4">0</td>
                                    </tr>
                                    <tr id="SellPaymentsByTypesApiModelCash" style="margin-left: 0.1rem"></tr>
                                    <tr id="SellPaymentsByTypesApiModelCard" style="margin-left: 0.1rem"></tr>
                                    <tr id="SellPaymentsByTypesApiModelDiscount" style="margin-left: 0.1rem"></tr>
                                    <tr id="SellPaymentsByTypesApiModelMarkup" style="margin-left: 0.1rem"></tr>
                                    <tr id="SellPaymentsByTypesApiModelChange" style="margin-left: 0.1rem"></tr>
                                    <tr id="SellPaymentsByTypesApiModelVAT" style="margin-left: 0.1rem"></tr>

                                    <tr style="">
                                        <td colspan="4">ПОКУПКА</td>
                                        <td id="BuyCount" style="text-align: center;" colspan="4">0</td>
                                        <td id="BuyTaken" style="text-align: right;" colspan="4">0</td>
                                    </tr>
                                    <tr id="BuyPaymentsByTypesApiModel" style="margin-left: 0.1rem"></tr>
                                    <tr style="">
                                        <td colspan="4">ВОЗВРАТ ПРОДАЖИ</td>
                                        <td id="ReturnSellCount" style="text-align: center;" colspan="4">0</td>
                                        <td id="ReturnSellTaken" style="text-align: right;" colspan="4">0</td>
                                    </tr>
                                    <tr id="ReturnSellPaymentsByTypesApiModel" style="margin-left: 0.1rem"></tr>
                                    <tr style="">
                                        <td colspan="4">ВОЗВРАТ ПОКУПКИ</td>
                                        <td id="ReturnBuyCount" style="text-align: center;" colspan="4">0</td>
                                        <td id="ReturnBuyTaken" style="text-align: right;" colspan="4">0</td>
                                    </tr>
                                    <tr id="ReturnBuyPaymentsByTypesApiModel" style="margin-left: 0.1rem"></tr>
                                </tbody>
                            </table>
                        </div>
                        <div>
                            <table role="presentation" style="width: 100%;">
                                <tbody>

                                <tr style=" padding: 0;">
                                    <td style="padding: 0;" colspan="5">Внесения</td>
                                    <td id="PutMoneySum" style="padding: 0; text-align: right;" colspan="5">0</td>
                                </tr>
                                <tr style=" padding: 0;">
                                    <td style="padding: 0;" colspan="5">Изъятия</td>
                                    <td id="TakeMoneySum" style="padding: 0; text-align: right;" colspan="5">0</td>
                                </tr>

                                </tbody>
                            </table>
                        </div>

                        <div>
                            <table role="presentation" style="width: 100%; ">
                                <tbody>
                                <tr style=" padding: 0;">
                                    <td style="padding: 0;" colspan="4">НАЛИЧНЫХ В КАССЕ </td>
                                    <td id="CashEndNonNullableSell" style="text-align: right;" colspan="8">Сумма</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div> НЕОБНУЛЯЕМАЯ СУММА НА КОНЕЦ СМЕНЫ </div>
                        <div id="EndNonNullable" style="display: flex;flex-direction: column;">
                            <table role="presentation" style="width: 100%; ">
                                <tbody>

                                <tr style=" padding: 0;">
                                    <td style="padding: 0;" colspan="5">Продажа</td>
                                    <td id="EndNonNullableSell" style="padding: 0; text-align: right;" colspan="5"> 0 </td>
                                </tr>
                                <tr style=" padding: 0;">
                                    <td style="padding: 0;" colspan="5">Возврат продажи</td>
                                    <td id="EndNonNullableBuy" style="padding: 0; text-align: right;" colspan="5"> 0 </td>
                                </tr>
                                <tr style=" padding: 0;">
                                    <td style="padding: 0;" colspan="5">Покупка</td>
                                    <td id="EndNonNullableReturnSell" style="padding: 0; text-align: right;" colspan="5"> 0.0 </td>
                                </tr>
                                <tr style=" padding: 0;">
                                    <td style="padding: 0;" colspan="5">Возврат покупки</td>
                                    <td id="EndNonNullableReturnBuy" style="padding: 0; text-align: right;" colspan="5"> 0.0 </td>
                                </tr>

                                </tbody>
                            </table>
                        </div>

                        <div>
                            <table role="presentation" style="width: 100%; ">
                                <tbody>
                                <tr style=" padding: 0;">
                                    <td style="padding: 0;" colspan="4"> Контрольное значение </td>
                                    <td id="ControlSum" style="text-align: right;" colspan="8">Сумма</td>
                                </tr>
                                </tbody>
                            </table>
                            <table role="presentation" style="width: 100%; ">
                                <tbody>
                                <tr style=" padding: 0;">
                                    <td style="padding: 0;" colspan="4">  Количество документов сформированных за смену: </td>
                                    <td id="EndDocumentCount" style="text-align: right;" colspan="8">Сумма</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div> Сформировано оператором фискальных данных: &nbsp; <span id="OfdName"> </span>  </div>
                        <hr style="border:1px dashed black; margin-top: 0.5rem !important; margin-bottom: 0.5rem;">
                        <div style="text-align: center">ИНК ОФД: &nbsp; <span id="CashboxIN"></span> </div>
                        <div style="text-align: center">Код ККМ КГД (РНМ): &nbsp; <span id="CashboxRN"></span> </div>
                        <div style="text-align: center">ЗНМ: &nbsp; <span id="CashboxSN"></span> </div>
                        <hr style="border:1px dashed black; margin-top: 0.5rem !important; margin-bottom: 0.5rem;">
                        <div style="text-align: center">  *** Конец отчета *** </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button onclick="closeModal('XReport')" type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                    <button onclick="PrintDiv('Print');" type="button" class="btn btn-success" data-dismiss="modal">Распечатать</button>
                    <a target="_blank" href="{{ Config::get("Global")['webkassa'] }}" class="btn btn-primary">Открыть в WebKassa</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function PrintDiv(divName) {
            var printContents = document.getElementById(divName).innerHTML;
            var printContentsBODY = "<style> body { font-family: 'Helvetica', 'Arial', sans-serif !important; } </style>"
            var printContentsCSS = " <style> div{ font-size: 12px !important; } table tr td{ font-size: 12px !important; padding: 0 !important; margin: 0 !important; } html, body { margin: 0px; padding: 0px; border: 0px; width: 100%; height: 100%; font-size: 13px !important; } iframe { width: 200px; height: 200px; margin: 0px; padding: 0px; border: 0px; display: block; } </style>";
            w = window.open();

            w.document.write(printContents + printContentsCSS+ printContentsBODY);
            w.document.write('<scr' + 'ipt type="text/javascript">' + 'window.onload = function() { window.print(); window.close(); };' + '</sc' + 'ript>');

            w.document.close(); // necessary for IE >= 10
            w.focus(); // necessary for IE >= 10

            return true;
        }

        function isNumber(evt){
            var charCode = (evt.which) ? evt.which : event.keyCode
            if (charCode == 46){
                var inputValue = $("#card").val();
                var count = (inputValue.match(/'.'/g) || []).length;
                if(count<1){
                    if (inputValue.indexOf('.') < 1){
                        return true;
                    }
                    return false;
                }else{
                    return false;
                }
            }
            if (charCode != 46 && charCode > 31 && (charCode < 48 || charCode > 57)){
                return false;
            }
            return true;
        }

        function activate_btn(params){
            if (params == 'cash'){
                $('#cash').modal('show')
                window.document.getElementById('inputSum').value = 0
            }
            if (params == 'XReport'){
                Report("XReport")
                $('#Report').modal('show')
            }
            if (params == 'ZReport'){
                Report("ZReport")
                $('#Report').modal('show')
            }
        }

        function closeModal(params) {
            if (params == 'cash'){
                $('#cash').modal('hide')
            }
            if (params == 'Report'){
                $('#Report').modal('hide')
            }
        }

        function valueCash(val){

            if (val == 0 ) {
                window.document.getElementById('cashTitle').innerText = 'Внесение'
                document.getElementById('inputGroupText').classList.add('bg-success')
                document.getElementById('inputGroupText').classList.remove('bg-danger')
            }
            if (val == 1) {
                window.document.getElementById('cashTitle').innerText = 'Изъятие'
                document.getElementById('inputGroupText').classList.add('bg-danger')
                document.getElementById('inputGroupText').classList.remove('bg-success')
            }
        }

    </script>
@endsection

