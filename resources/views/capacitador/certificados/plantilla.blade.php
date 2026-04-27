@php
    $fontDir          = storage_path('fonts');
    $logoPath         = public_path('images/logo/alumco-full.svg');
    $logoBase64       = base64_encode(file_get_contents($logoPath));
    $labelCapacitador = ($capacitador->sexo ?? 'M') === 'F' ? 'Capacitadora' : 'Capacitador';

    // Preparar firmas en Base64
    $firmaCapBase64 = null;
    if ($capacitador->firma_digital && Storage::disk('public')->exists($capacitador->firma_digital)) {
        $firmaCapBase64 = base64_encode(Storage::disk('public')->get($capacitador->firma_digital));
    }

    $firmaRepBase64 = null;
    if ($firmaRepLegal && Storage::disk('public')->exists($firmaRepLegal)) {
        $firmaRepBase64 = base64_encode(Storage::disk('public')->get($firmaRepLegal));
    }
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
            size: letter landscape;
            margin: 0;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'FiraSans', Arial, sans-serif;
            font-weight: 400;
            background: #ffffff;
            width: 100%;
            height: 100%;
        }

        .borde-izq {
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 14mm;
            background: #205099;
        }

        .borde-der {
            position: absolute;
            right: 0; top: 0; bottom: 0;
            width: 14mm;
            background: #205099;
        }

        .contenedor {
            padding: 15mm 25mm;
            height: 100%;
            border-top: 6px solid #AFDD83;
            border-bottom: 6px solid #AFDD83;
            margin-left: 14mm;
            margin-right: 14mm;
            position: relative;
        }

        .encabezado {
            width: 100%;
            margin-bottom: 20mm;
        }

        .logo-img {
            height: 36px;
            width: auto;
            float: left;
        }

        .titulo-cert {
            font-family: 'Sora', Arial, sans-serif;
            font-weight: 600;
            font-size: 10pt;
            color: #4A4A4A;
            letter-spacing: 3px;
            text-transform: uppercase;
            float: right;
            text-align: right;
        }

        .clear { clear: both; }

        .cuerpo {
            text-align: center;
            margin-bottom: 25mm;
        }

        .prezenta {
            font-family: 'FiraSans', Arial, sans-serif;
            font-size: 10pt;
            color: #6B7280;
            text-transform: uppercase;
            letter-spacing: 3px;
            margin-bottom: 12px;
        }

        .nombre-alumno {
            font-family: 'Sora', Arial, sans-serif;
            font-weight: 800;
            font-size: 36pt;
            color: #205099;
            margin-bottom: 15px;
            line-height: 1.1;
        }

        .texto-completado {
            font-family: 'FiraSans', Arial, sans-serif;
            font-size: 11pt;
            color: #4A4A4A;
            margin-bottom: 10px;
        }

        .nombre-curso {
            font-family: 'Sora', Arial, sans-serif;
            font-weight: 600;
            font-size: 18pt;
            color: #205099;
        }

        .firmas-container {
            width: 100%;
            margin-top: 30mm;
        }

        .firma-box {
            width: 45%;
            float: left;
            text-align: center;
        }

        .firma-box-right {
            width: 45%;
            float: right;
            text-align: center;
        }

        .firma-img-wrapper {
            height: 22mm;
            margin-bottom: 2mm;
            display: block;
        }

        .firma-img {
            height: 100%;
            width: auto;
            max-width: 50mm;
            mix-blend-multiply;
        }

        .linea-firma {
            border-bottom: 1.5px solid #205099;
            margin-bottom: 4px;
            width: 60mm;
            margin-left: auto;
            margin-right: auto;
        }

        .firma-nombre {
            font-family: 'Sora', Arial, sans-serif;
            font-weight: 600;
            font-size: 11pt;
            color: #205099;
        }

        .firma-label {
            font-family: 'FiraSans', Arial, sans-serif;
            font-size: 8pt;
            color: #6B7280;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        .footer-info {
            position: absolute;
            bottom: 15mm;
            left: 25mm;
            right: 25mm;
            width: auto;
        }

        .fecha-bloque {
            font-family: 'FiraSans', Arial, sans-serif;
            font-size: 9pt;
            color: #6B7280;
            float: left;
        }

        .codigo-bloque {
            float: right;
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
    </style>
</head>
<body>
    <div class="borde-izq"></div>
    <div class="borde-der"></div>

    <div class="contenedor">
        <div class="encabezado">
            <img class="logo-img" src="data:image/svg+xml;base64,{{ $logoBase64 }}" alt="Alumco">
            <div class="titulo-cert">
                Certificado<br>de Completado
            </div>
            <div class="clear"></div>
        </div>

        <div class="cuerpo">
            <p class="prezenta">Se certifica que</p>
            <p class="nombre-alumno">{{ $user->name }}</p>
            <p class="texto-completado">ha completado satisfactoriamente el curso</p>
            <p class="nombre-curso">&ldquo;{{ $curso->titulo }}&rdquo;</p>
        </div>

        <div class="firmas-container">
            {{-- Firma del Capacitador --}}
            <div class="firma-box">
                <div class="firma-img-wrapper">
                    @if($firmaCapBase64)
                        <img src="data:image/png;base64,{{ $firmaCapBase64 }}" class="firma-img">
                    @endif
                </div>
                <div class="linea-firma"></div>
                <div class="firma-nombre">{{ $capacitador->name }}</div>
                <div class="firma-label">{{ $labelCapacitador }}</div>
            </div>

            {{-- Firma del Representante Legal --}}
            <div class="firma-box-right">
                <div class="firma-img-wrapper">
                    @if($firmaRepBase64)
                        <img src="data:image/png;base64,{{ $firmaRepBase64 }}" class="firma-img">
                    @endif
                </div>
                <div class="linea-firma"></div>
                <div class="firma-nombre">Representante Legal</div>
                <div class="firma-label">Fundación Alumco</div>
            </div>
            <div class="clear"></div>
        </div>

        <div class="footer-info">
            <div class="fecha-bloque">
                Emitido el {{ now()->format('d') }} de {{ \Carbon\Carbon::now()->locale('es')->isoFormat('MMMM') }} de {{ now()->format('Y') }}
            </div>

            <div class="codigo-bloque">
                <div class="codigo-label">C&oacute;digo de verificaci&oacute;n</div>
                <div class="codigo-valor">{{ strtoupper(substr($codigo, 0, 8)) }}</div>
            </div>
            <div class="clear"></div>
        </div>
    </div>
</body>
</html>
