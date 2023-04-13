<script>
    /*СКРИПТ ОСНОВНОГО (АУНТИФИКАЦИИ, ПОЛУЧЕНИЕ ТОКЕНА)*/
    function sendToken(){
        let email = document.getElementById('sendEmail')
        let password = document.getElementById('sendPassword')
        let message = document.getElementById('message')

        if (email.value === '' || password.value === '' ){
            message.innerText = 'Введите логин или пароль'
            message.style.display = 'block'
        } else {
            message.innerText = ''
            message.style.display = 'none'

            let settings = ajax_settings(url + 'Setting/Create/AuthToken/'+ accountId, "GET", { email: email.value, password: password.value })
            console.log(url + 'Setting/Create/AuthToken/'+ accountId + ' settings ↓ ')
            console.log(settings)
            $.ajax(settings).done(function (json) {
                console.log(url + 'Setting/Create/AuthToken/' + accountId + ' response ↓ ')
                console.log(json)

                if (json.status === 200) {
                    message.style.display = 'none'
                    window.document.getElementById('token').value = json.auth_token
                    alertViewByColorName("success", "Ваш токен создан, не забудьте нажать на кнопку СОХРАНИТЬ")
                    $('.close').click();
                } else {
                    message.innerText = 'Не верный email или пароль'
                    message.style.display = 'block'
                }
            })

        }
    }

    function eye_password(){
        let input = document.getElementById('sendPassword')
        if (input.type === "password"){
            input.type = "text"
        } else {
            input.type = "password"
        }
    }
    function sendCollection(hideOrShow){
        if (hideOrShow === 'show') {
            $('#sendTokenByEmailAndPassword').modal({backdrop: 'static', keyboard: false})
            $('#sendTokenByEmailAndPassword').modal('show')
        }

        if (hideOrShow === 'hide') {
            $('#sendTokenByEmailAndPassword').modal('hide')
        }
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






    /*СКРИПТ ДОКУМЕЕТОВ ()*/
    function loading(createDocument, payment_type, OperationCash, OperationCard){
        window.document.getElementById('createDocument_asWay').value = createDocument
        if (createDocument == 4) window.document.getElementById('CustomCreateDocument').style.display = "Block"
        window.document.getElementById('payment_type').value = payment_type

        window.document.getElementById('OperationCash').value = OperationCash
        window.document.getElementById('OperationCard').value = OperationCard
    }

</script>
