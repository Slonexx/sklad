<style>
    .head-full {
        height: 720px;
    }

    body {
        font-family: 'Helvetica', 'Arial', sans-serif;
        color: #444444;
        font-size: 8pt;
        background-color: #FAFAFA;
    }

    .main-container {
        display: flex;
        flex-direction: column;
        height: 100vh;
    }
    .content-container {
        overflow-y: auto;
        overflow-x: hidden;
        flex-grow: 1;
    }

    .gradient{
        background-image: linear-gradient(135deg, #f5f7fa 0%, #dadee6 100%);
    }
    .gradient_invert{
        background: radial-gradient(circle at 50.4% 50.5%, rgb(245, 247, 250) 0%, rgb(218, 222, 230) 90%);
    }

    /* Фиксированный боковых навигационных ссылок, полной высоты */
    .sidenav {
        height: 100%;
        width: 15%;
        position: fixed;
        z-index: 1;
        top: 0;
        left: 0;
        background-color: #eaeaea;
        overflow-x: hidden;
        padding-top: 20px;
    }

    /* Стиль боковых навигационных ссылок и раскрывающейся кнопки */
    .sidenav a, .dropdown-btn {
        padding: 6px 8px 6px 16px;
        text-decoration: none;
        font-size: 16px;
        color: #343434;
        display: block;
        border: none;
        background: none;
        width:100%;
        text-align: left;
        cursor: pointer;
        outline: none;
    }

    /* При наведении курсора мыши */
    .sidenav a:hover, .dropdown-btn:hover {
        background: radial-gradient(circle at 50.4% 50.5%, rgb(255, 189, 204) 0%, rgb(135, 2, 35) 90%);
        border-radius: 10px 10px 0px 0px;
        color: white;
        width: 100%;
    }

    /* Основное содержание */
    .main {
        margin-left: 15%; /* То же, что и ширина боковой навигации */
        font-size: 18px; /* Увеличенный текст для включения прокрутки */
        padding: 0 10px;
    }

    /* Добавить активный класс для кнопки активного выпадающего списка */
    .sidenav .active_sprint {
        background: radial-gradient(circle at 50.4% 50.5%, rgb(246, 94, 132) 0%, rgb(135, 2, 35) 90%);
        border-radius: 10px 10px 0px 0px ;
        color: white ;
        width: 100% ;
    }

    /* Выпадающий контейнер (по умолчанию скрыт). Необязательно: добавьте более светлый цвет фона и некоторые левые отступы, чтобы изменить дизайн выпадающего содержимого */
    .dropdown-container {
        display: none;
        background-color: #d5d5d5;
        padding: 5px;
    }

    /* Необязательно: стиль курсора вниз значок */
    .fa-caret-down {
        float: right;
        padding-right: 8px;
    }
</style>
