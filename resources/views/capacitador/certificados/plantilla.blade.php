@php
    $logoPath = public_path('images/logo/alumco-full.svg');

    $cloudTopPath = public_path('images/undraw/clouds_top.svg');
    $cloudBottomPath = public_path('images/undraw/clouds_bottom.svg');

    $logoBase64 = file_exists($logoPath) ? base64_encode(file_get_contents($logoPath)) : null;

    $cloudTopBase64 = file_exists($cloudTopPath) ? base64_encode(file_get_contents($cloudTopPath)) : null;
    $cloudBottomBase64 = file_exists($cloudBottomPath) ? base64_encode(file_get_contents($cloudBottomPath)) : null;

    $firmaCapBase64 = null;
    if ($capacitador->firma_digital && Storage::disk('public')->exists($capacitador->firma_digital)) {
        $firmaCapBase64 = base64_encode(Storage::disk('public')->get($capacitador->firma_digital));
    }

    $firmaRepBase64 = null;
    if ($firmaRepLegal && Storage::disk('public')->exists($firmaRepLegal)) {
        $firmaRepBase64 = base64_encode(Storage::disk('public')->get($firmaRepLegal));
    }

    $sexoUsuario = strtolower(trim((string) ($user->sexo ?? '')));
    if (in_array($sexoUsuario, ['m', 'masculino', 'hombre'], true)) {
        $articulo = 'el';
        $colaborador = 'colaborador';
    } elseif (in_array($sexoUsuario, ['f', 'femenino', 'mujer'], true)) {
        $articulo = 'la';
        $colaborador = 'colaboradora';
    } else {
        $articulo = '';
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
            size: 8.5in 11in;
            margin: 0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html,
        body {
            width: 8.5in;
            height: 11in;
            font-family: 'Fira Sans', sans-serif;
            background: #ffffff;
            color: #4A4A4A;
            overflow: hidden;
        }

        .page {
            position: absolute;
            top: 0;
            left: 0;
            width: 8.5in;
            height: 11in;
            overflow: hidden;
            background: #ffffff;
        }

        .cloud {
            position: absolute;
            opacity: 0.28;
            z-index: 1;
        }

        .cloud-top {
            top: 0;
            left: 0;
            width: 8.5in;
            height: 30mm;
        }

        .cloud-bottom {
            left: 0;
            width: 8.5in;
            bottom: 0;
            height: 26mm;
        }

        .frame {
            position: absolute;
            top: 0;
            left: 0;
            z-index: 2;
            width: 8.5in;
            height: 11in;
            background: transparent;
        }

        .frame::before {
            content: none;
        }

        .content {
            position: absolute;
            top: 22mm;
            left: 18mm;
            z-index: 3;
            width: 179.9mm;
            height: 235mm;
        }

        .header {
            width: 100%;
            margin-bottom: 24mm;
        }

        .logo {
            float: left;
            height: 42px;
            width: auto;
        }

        .title {
            float: right;
            text-align: right;
            font-family: 'Sora', sans-serif;
            font-size: 11pt;
            font-weight: 700;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            color: #4A4A4A;
            line-height: 1.35;
        }

        .clear {
            clear: both;
        }

        .body {
            text-align: center;
            margin-bottom: 25mm;
        }

        .prelude {
            font-size: 10pt;
            color: #6B7280;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 10px;
        }

        .student-name {
            font-family: 'Sora', sans-serif;
            font-size: 35pt;
            font-weight: 800;
            color: #205099;
            line-height: 1.12;
            margin-bottom: 12px;
        }

        .completion-text {
            font-size: 11pt;
            color: #4A4A4A;
            margin-bottom: 9px;
        }

        .course-name {
            font-family: 'Sora', sans-serif;
            font-size: 18pt;
            font-weight: 700;
            color: #205099;
            font-style: italic;
            line-height: 1.3;
        }

        .signatures {
            width: 100%;
            margin-top: 0;
        }

        .signature-box {
            width: 45%;
            text-align: center;
            float: left;
        }

        .signature-box-right {
            width: 45%;
            text-align: center;
            float: right;
        }

        .signature-image-wrapper {
            height: 19mm;
            margin-bottom: 2mm;
        }

        .signature-image {
            max-height: 100%;
            max-width: 52mm;
            width: auto;
        }

        .signature-line {
            width: 62mm;
            margin: 0 auto 4px;
            border-bottom: 1.4px solid #205099;
        }

        .signature-name {
            font-family: 'Sora', sans-serif;
            font-size: 10.5pt;
            font-weight: 700;
            color: #205099;
        }

        .signature-role {
            font-size: 8pt;
            color: #6B7280;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .footer {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            width: auto;
            z-index: 4;
        }

        .issued-date {
            float: left;
            font-size: 9pt;
            color: #6B7280;
        }

        .verification {
            float: right;
            text-align: right;
        }

        .verification-label {
            font-size: 7pt;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #4A4A4A;
            margin-bottom: 3px;
        }

        .verification-code {
            font-family: 'Courier New', monospace;
            font-size: 8pt;
            color: #4A4A4A;
        }
    </style>
</head>
<body>
    <div class="page">
        @if ($cloudTopBase64)
            <img class="cloud cloud-top" src="data:image/svg+xml;base64,{{ $cloudTopBase64 }}" alt="Nubes decorativas superiores">
        @endif

        @if ($cloudBottomBase64)
            <img class="cloud cloud-bottom" src="data:image/svg+xml;base64,{{ $cloudBottomBase64 }}" alt="Nubes decorativas inferiores">
        @endif

        <div class="frame">
            <div class="content">
                <div class="header">
                    @if ($logoBase64)
                        <img class="logo" src="data:image/svg+xml;base64,{{ $logoBase64 }}" alt="Alumco">
                    @endif

                    <div class="title">
                        Certificado<br>
                        de Completado
                    </div>

                    <div class="clear"></div>
                </div>

                <div class="body">
                    <p class="prelude">{{ $presentacion }}</p>
                    <p class="student-name">{{ $user->name }}</p>
                    <p class="completion-text">ha completado satisfactoriamente el curso</p>
                    <p class="course-name">"{{ $curso->titulo }}"</p>
                </div>

                <div class="signatures">
                    <div class="signature-box">
                        <div class="signature-image-wrapper">
                            @if ($firmaCapBase64)
                                <img class="signature-image" src="data:image/png;base64,{{ $firmaCapBase64 }}" alt="Firma de {{ $capacitador->name }}">
                            @endif
                        </div>
                        <div class="signature-line"></div>
                        <div class="signature-name">{{ $capacitador->name }}</div>
                        <div class="signature-role">{{ $labelCapacitador }}</div>
                    </div>

                    <div class="signature-box-right">
                        <div class="signature-image-wrapper">
                            @if ($firmaRepBase64)
                                <img class="signature-image" src="data:image/png;base64,{{ $firmaRepBase64 }}" alt="Firma del representante legal">
                            @endif
                        </div>
                        <div class="signature-line"></div>
                        <div class="signature-name">Representante Legal</div>
                        <div class="signature-role">Fundación Alumco</div>
                    </div>

                    <div class="clear"></div>
                </div>

                <div class="footer">
                    <div class="issued-date">
                        Emitido el {{ now()->format('d') }} de {{ \Carbon\Carbon::now()->locale('es')->isoFormat('MMMM') }} de {{ now()->format('Y') }}
                    </div>

                    <div class="verification">
                        <div class="verification-label">Código de verificación</div>
                        <div class="verification-code">{{ strtoupper(substr($codigo, 0, 8)) }}</div>
                    </div>

                    <div class="clear"></div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
