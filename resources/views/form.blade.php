<!DOCTYPE html>
<html>
<head>
    <title>Форма для выборки данных</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<div class="container">
    <h1>Выборка данных по датам</h1>
    <!-- Форма для отправки данных -->
    <form method="POST" action="{{ route('form') }}">
        @csrf
        <div class="row">
            <label for="dateFrom">Дата с:</label>
            <div class="row_input">
                <input type="date" id="dateFrom" name="dateFrom" value="{{ old('dateFrom', $dateFrom ?? '') }}"
                       required>
            </div>
        </div>
        <div class="row">
            <label for="dateTo">Дата по:</label>
            <div class="row_input">
                <input type="date" id="dateTo" name="dateTo" value="{{ old('dateTo', $dateTo ?? '') }}" required>
            </div>
        </div>
        <button type="submit">Загрузить данные</button>
    </form>
    @if (isset($success))
        <div class="success">Загрузка успешна</div>
    @elseif(isset($error))
        <div class="error">{{$error}}</div>
    @endif
</div>
</body>
</html>
