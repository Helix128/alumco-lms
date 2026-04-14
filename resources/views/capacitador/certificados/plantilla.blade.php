@php
    $fontDir          = storage_path('fonts');
    $logoPath         = public_path('images/logo/alumco-full.svg');
    $logoBase64       = base64_encode(file_get_contents($logoPath));
    $labelCapacitador = ($capacitador->sexo ?? 'M') === 'F' ? 'Capacitadora' : 'Capacitador';
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @font-face {
            font-family: 'FiraSans';
            font-weight: 400;
            src: url('file://{{ $fontDir }}/FiraSans-Regular.ttf') format('truetype');
        }
        @font-face {
            font-family: 'FiraSans';
            font-weight: 700;
            src: url('file://{{ $fontDir }}/FiraSans-Bold.ttf') format('truetype');
        }
        @font-face {
            font-family: 'Sora';
            font-weight: 600;
            src: url('file://{{ $fontDir }}/Sora-SemiBold.ttf') format('truetype');
        }
        @font-face {
            font-family: 'Sora';
            font-weight: 800;
            src: url('file://{{ $fontDir }}/Sora-ExtraBold.ttf') format('truetype');
        }

        @page {
            size: A4 landscape;
            margin: 0;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'FiraSans', Arial, sans-serif;
            font-weight: 400;
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
            padding: 18mm 18mm;
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
            height: 36px;
            width: auto;
        }

        .titulo-cert {
            font-family: 'Sora', Arial, sans-serif;
            font-weight: 600;
            font-size: 10pt;
            color: #4A4A4A;
            letter-spacing: 3px;
            text-transform: uppercase;
            text-align: right;
        }

        .cuerpo {
            text-align: center;
        }

        .prezenta {
            font-family: 'FiraSans', Arial, sans-serif;
            font-size: 9pt;
            color: #6B7280;
            text-transform: uppercase;
            letter-spacing: 3px;
            margin-bottom: 8px;
        }

        .nombre-alumno {
            font-family: 'Sora', Arial, sans-serif;
            font-weight: 800;
            font-size: 34pt;
            color: #205099;
            margin-bottom: 10px;
            line-height: 1.1;
        }

        .texto-completado {
            font-family: 'FiraSans', Arial, sans-serif;
            font-size: 10pt;
            color: #4A4A4A;
            margin-bottom: 6px;
        }

        .nombre-curso {
            font-family: 'Sora', Arial, sans-serif;
            font-weight: 600;
            font-size: 16pt;
            color: #205099;
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
            font-family: 'Sora', Arial, sans-serif;
            font-weight: 600;
            font-size: 13pt;
            color: #205099;
            border-bottom: 1.5px solid #205099;
            padding-bottom: 4px;
            margin-bottom: 4px;
        }

        .firma-label {
            font-family: 'FiraSans', Arial, sans-serif;
            font-size: 7.5pt;
            color: #6B7280;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        .codigo-bloque {
            text-align: right;
        }

        .codigo-label {
            font-family: 'FiraSans', Arial, sans-serif;
            font-size: 7pt;
            color: #6B7280;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 3px;
        }

        .codigo-valor {
            font-family: 'DejaVu Sans Mono', 'Courier New', monospace;
            font-size: 8pt;
            color: #4A4A4A;
            letter-spacing: 1px;
        }

        .fecha-bloque {
            font-family: 'FiraSans', Arial, sans-serif;
            font-size: 9pt;
            color: #6B7280;
        }
    </style>
</head>
<body>
    <div class="borde-izq"></div>

    <div class="contenido">
        <div class="encabezado">
            <img class="logo-img" src="data:image/svg+xml;base64,{{ $logoBase64 }}" alt="Alumco">
            <div class="titulo-cert">
                Certificado<br>de Completado
            </div>
        </div>

        <div class="cuerpo">
            <p class="prezenta">Se certifica que</p>
            <p class="nombre-alumno">{{ $user->name }}</p>
            <p class="texto-completado">ha completado satisfactoriamente el curso</p>
            <p class="nombre-curso">&ldquo;{{ $curso->titulo }}&rdquo;</p>
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
                <div class="codigo-label">C&oacute;digo de verificaci&oacute;n</div>
                <div class="codigo-valor">{{ strtoupper(substr($codigo, 0, 8)) }}</div>
            </div>
        </div>
    </div>

    <div class="borde-der"></div>
</body>
</html>
