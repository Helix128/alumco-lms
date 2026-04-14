    <!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @font-face {
            font-family: 'Fira Sans';
            font-weight: 400;
            src: url('{{ storage_path('fonts/FiraSans-Regular.ttf') }}') format('truetype');
        }
        @font-face {
            font-family: 'Fira Sans';
            font-weight: 700;
            src: url('{{ storage_path('fonts/FiraSans-Bold.ttf') }}') format('truetype');
        }
        @font-face {
            font-family: 'Fira Sans';
            font-weight: 900;
            src: url('{{ storage_path('fonts/FiraSans-Black.ttf') }}') format('truetype');
        }
        @font-face {
            font-family: 'Sora';
            font-weight: 700;
            src: url('{{ storage_path('fonts/Sora-Bold.ttf') }}') format('truetype');
        }
        @font-face {
            font-family: 'Sora';
            font-weight: 800;
            src: url('{{ storage_path('fonts/Sora-ExtraBold.ttf') }}') format('truetype');
        }

        @page {
            size: A4 landscape;
            margin: 0;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Fira Sans', sans-serif;
            background: #ffffff;
            width: 297mm;
            height: 210mm;
            display: flex;
            align-items: stretch;
        }

        .borde-izq {
            width: 14mm;
            background: #205099;
        }

        .borde-der {
            width: 14mm;
            background: #205099;
        }

        .contenido {
            flex: 1;
            padding: 14mm 18mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            border-top: 8px solid #AFDD83;
            border-bottom: 8px solid #AFDD83;
        }

        .encabezado {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .logo-img {
            width: 58mm;
            height: auto;
            display: block;
        }

        .titulo-cert {
            font-family: 'Sora', sans-serif;
            font-size: 11pt;
            font-weight: 700;
            color: #4A4A4A;
            letter-spacing: 3px;
            text-transform: uppercase;
            text-align: right;
        }

        .cuerpo {
            text-align: center;
        }

        .prezenta {
            font-size: 10pt;
            color: #4A4A4A;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 12px;
        }

        .nombre-alumno {
            font-size: 38pt;
            font-weight: 900;
            color: #205099;
            margin-bottom: 16px;
            line-height: 1.1;
        }

        .texto-completado {
            font-size: 11pt;
            color: #4A4A4A;
            margin-bottom: 12px;
        }

        .nombre-curso {
            font-size: 22pt;
            font-weight: 700;
            color: #205099;
            font-style: italic;
        }

        .pie {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .firma-bloque {
            text-align: center;
        }

        .firma-nombre {
            font-family: 'Sora', sans-serif;
            font-weight: 700;
            font-style: italic;
            font-size: 15pt;
            color: #205099;
            border-bottom: 1.5px solid #205099;
            padding-bottom: 4px;
            margin-bottom: 4px;
        }

        .firma-label {
            font-size: 8pt;
            color: #4A4A4A;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .codigo-bloque {
            text-align: right;
        }

        .codigo-label {
            font-size: 7pt;
            color: #4A4A4A;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 3px;
        }

        .codigo-valor {
            font-family: 'Courier New', monospace;
            font-size: 8pt;
            color: #4A4A4A;
        }

        .fecha-bloque {
            font-size: 9pt;
            color: #4A4A4A;
        }
    </style>
</head>
@php
    $sexoUsuario = strtolower(trim((string) ($user->sexo ?? '')));
    if (in_array($sexoUsuario, ['m', 'masculino', 'hombre'], true)) {
        $articulo    = 'el';
        $colaborador = 'colaborador';
    } elseif (in_array($sexoUsuario, ['f', 'femenino', 'mujer'], true)) {
        $articulo    = 'la';
        $colaborador = 'colaboradora';
    } else {
        $articulo    = '';
        $colaborador = 'colaborador/a';
    }
    $presentacion = trim('Se certifica que ' . $articulo . ' ' . $colaborador);

    $sexoCapacitador = strtolower(trim((string) ($capacitador->sexo ?? '')));
    if (in_array($sexoCapacitador, ['m', 'masculino', 'hombre'], true)) {
        $labelCapacitador = 'Capacitador';
    } elseif (in_array($sexoCapacitador, ['f', 'femenino', 'mujer'], true)) {
        $labelCapacitador = 'Capacitadora';
    } else {
        $labelCapacitador = 'Capacitador/a';
    }
@endphp
<body>
    <div class="borde-izq"></div>

    <div class="contenido">
        <div class="encabezado">
            <div>
                <img src="{{ public_path('images/logo/alumco-full.svg') }}" class="logo-img" alt="Alumco">
            </div>
            <div class="titulo-cert">
                Certificado<br>de Completado
            </div>
        </div>

        <div class="cuerpo">
            <p class="prezenta">{{ $presentacion }}</p>
            <p class="nombre-alumno">{{ $user->name }}</p>
            <p class="texto-completado">ha completado satisfactoriamente el curso</p>
            <p class="nombre-curso">"{{ $curso->titulo }}"</p>
        </div>

        <div class="pie">
            <div class="fecha-bloque">
                {{ now()->format('d') }} de {{ \Carbon\Carbon::now()->locale('es')->isoFormat('MMMM') }} de {{ now()->format('Y') }}
            </div>

            <div class="firma-bloque">
                <div class="firma-nombre">{{ $capacitador->name }}</div>
                <div class="firma-label">{{ $labelCapacitador }}</div>
            </div>

            <div class="codigo-bloque">
                <div class="codigo-label">Código de verificación</div>
                <div class="codigo-valor">{{ strtoupper(substr($codigo, 0, 8)) }}</div>
            </div>
        </div>
    </div>

    <div class="borde-der"></div>
</body>
</html>
