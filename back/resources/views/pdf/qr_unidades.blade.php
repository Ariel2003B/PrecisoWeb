<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .qr-container { width: 100%; display: flex; flex-wrap: wrap; justify-content: space-between; }
        .qr-box {
            width: 30%;
            margin-bottom: 20px;
            text-align: center;
            border: 1px dashed #ccc;
            padding: 10px;
            box-sizing: border-box;
        }
        .qr-box img {
            width: 150px;
            height: 150px;
        }
        .label { margin-top: 5px; font-weight: bold; }
    </style>
</head>
<body>
    <h2 style="text-align: center;">Códigos QR por Unidad</h2>
    <div class="qr-container">
        @foreach ($qrData as $item)
            <div class="qr-box">
                <img src="{{ $item['qr'] }}" alt="QR Unidad {{ $item['id'] }}">
                <div class="label">ID: {{ $item['id'] }}</div>
                <div class="label">Placa: {{ $item['placa'] }}</div>
                <div class="label">Habilitación: {{ $item['numero_habilitacion'] }}</div>
            </div>
        @endforeach
    </div>
</body>
</html>
