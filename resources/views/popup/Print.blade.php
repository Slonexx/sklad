<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Print ТИС Prosklad</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/gh/davidshimjs/qrcodejs/qrcode.min.js"></script>

</head>

@if(isset($Message))

    <script>
        window.alert("{{$Message}}")
    </script>

@else

    <body style="width: 400px; font-family: 'Times New Roman', 'Arial', sans-serif !important; ">

    <div style="font-size: 16px !important;">
        <div id="main">

        </div>
    </div>

    <script>
        let PrintForm = @json($PrintFormat);
        console.log(PrintForm);
        for (let index = 0; index < PrintForm.length -12 ; index++){
            let value = '<div style="text-align: center">' + PrintForm[index].Value + "</div>"
            if (index < 7 ){
                if (PrintForm[index].Value === "------------------------------------------------") {
                    value = '<hr  style="border:1px dashed black; margin-top: 0.1rem !important; margin-bottom: 0.5rem;">'
                }
                $('#main').append(value)
            }

            if (index > 7 && index < 11){
                value = '<div>' + PrintForm[index].Value + "</div>"
                if (PrintForm[index].Value === "------------------------------------------------") {
                    value = '<hr  style="border:1px dashed black; margin-top: 0.1rem !important; margin-bottom: 0.5rem;">'
                }
                $('#main').append(value)
            }
            if (index > 10){
                if (PrintForm[index].Value === "------------------------------------------------") {
                    value = '<hr  style="border:1px dashed black; margin-top: 0.1rem !important; margin-bottom: 0.5rem;">'
                } else{
                    let text = (PrintForm[index].Value).replace( / /g, "      " )
                    if (text.length > 110){
                        console.log(PrintForm[index])
                        console.log(text.length)
                        if (PrintForm[index].Style === 1) {
                            value =
                                '<div style="white-space: normal">'
                                + '<table role="presentation" style="width: 100%; ">'
                                + '<tbody>'
                                + '<tr style=" padding: 0;">'
                                + '<td style="padding: 0;  text-align: left;" colspan="5">&nbsp;' + '<b>' + text.slice(0, 110) + '</td>'
                                + '<td style="padding: 0;  text-align: right;" colspan="5">' + text.slice(110) + '</td>'
                                + '</tr>'
                                + '</tbody>'
                                + '</table>'
                                + '</div>'
                        } else  {
                            value =
                                '<div style="white-space: normal">'
                                + '<table role="presentation" style="width: 100%; ">'
                                + '<tbody>'
                                + '<tr style=" padding: 0;">'
                                + '<td style="padding: 0;  text-align: left;" colspan="5">&nbsp;' + text.slice(0, 110) + '</td>'
                                + '<td style="padding: 0;  text-align: right;" colspan="5">' + text.slice(110) + '</td>'
                                + '</tr>'
                                + '</tbody>'
                                + '</table>'
                                + '</div>'
                        }
                    } else {
                        let text = (PrintForm[index].Value).replace( / /g, "&nbsp;" )
                        value = '<div style="white-space: normal">' + text + "</div>"
                    }



                }


                $('#main').append(value)
            }


        }

        for (let index = PrintForm.length -12; index < PrintForm.length ; index++){

            let value = '<div style="text-align: center">' + PrintForm[index].Value + "</div>"
            if (index < PrintForm.length - 6) {
                value = '<div>' + PrintForm[index].Value + "</div>"
            }

            if (PrintForm[index].Value === "------------------------------------------------") {
                value = '<hr  style="border:1px dashed black; margin-top: 0.1rem !important; margin-bottom: 0.5rem;">'
            }
            if (PrintForm[index].Style === 1) {
                value = '<div style="text-align: center">' + '<b>' + PrintForm[index].Value + '</b>'  + "</div>"
            }
            if (PrintForm[index].Type === 2){
                value = '<div id="qrcode" style="text-align: center"></div>'
            }




            $('#main').append(value)


            if (PrintForm[index].Type === 2){
                const qrcode = new QRCode(document.getElementById('qrcode'), {
                    text: PrintForm[index].Value,
                    width: 200,
                    height: 200,
                    colorDark : '#000',
                    colorLight : '#fff',
                    correctLevel : QRCode.CorrectLevel.H
                });
            }


            window.print()
        }


    </script>

    <style>
        table tr td{
            font-size: 13px !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        html, body {
            margin: 0px;
            padding: 0px;
            border: 0px;
            width: 100%;
            height: 100%;
        }
        body #qrcode {
            margin-left: 25.5%;
            margin-top: 0.2rem;
            margin-bottom: 0.2rem;
        }
    </style>

    </body>

@endif


</html>
