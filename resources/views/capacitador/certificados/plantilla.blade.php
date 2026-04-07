<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            size: A4 landscape;
            margin: 0;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
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
            padding: 20mm 18mm;
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

        .logo-text {
            font-size: 28pt;
            font-weight: 900;
            color: #205099;
            letter-spacing: -1px;
        }

        .logo-sub {
            font-size: 9pt;
            color: #4A4A4A;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-top: 2px;
        }

        .titulo-cert {
            font-size: 11pt;
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
            margin-bottom: 6px;
        }

        .nombre-alumno {
            font-size: 32pt;
            font-weight: 900;
            color: #205099;
            margin-bottom: 8px;
            line-height: 1.1;
        }

        .texto-completado {
            font-size: 11pt;
            color: #4A4A4A;
            margin-bottom: 6px;
        }

        .nombre-curso {
            font-size: 18pt;
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
            font-family: Georgia, 'Times New Roman', serif;
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
<body>
    <div class="borde-izq"></div>

    <div class="contenido">
        <div class="encabezado">
            <div>
                <div class="logo-text">alumco</div>
                <div class="logo-sub">Capacitación Corporativa</div>
            </div>
            <div class="titulo-cert">
                Certificado<br>de Completado
            </div>
        </div>

        <div class="cuerpo">
            <p class="prezenta">Se certifica que</p>
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
                <div class="firma-label">Capacitador</div>
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
