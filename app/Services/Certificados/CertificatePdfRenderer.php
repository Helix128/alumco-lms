<?php

namespace App\Services\Certificados;

use App\Models\Certificado;
use App\Models\GlobalSetting;
use Barryvdh\DomPDF\Facade\Pdf as PdfFacade;
use Barryvdh\DomPDF\PDF;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;

class CertificatePdfRenderer
{
    private const QR_CODE_SIZE = 240;

    private const QR_CODE_MARGIN = 12;

    public function render(Certificado $certificado): PDF
    {
        $certificado->loadMissing(['user', 'curso.capacitador']);

        $user = $certificado->user;
        $curso = $certificado->curso;
        $capacitador = $curso->capacitador;
        $firmaRepLegal = GlobalSetting::get('firma_representante_legal', '');
        $codigo = $certificado->codigo_verificacion;
        $verificationUrl = route('certificados.verificar.show', $codigo);
        $qrCodeDataUri = $this->qrCodeDataUri($verificationUrl);
        $codigoDisplayLines = $this->codigoDisplayLines($codigo);
        $fechaEmision = $certificado->fecha_emision ?? $certificado->created_at;

        return PdfFacade::loadView('capacitador.certificados.plantilla', compact('user', 'curso', 'codigo', 'capacitador', 'firmaRepLegal', 'verificationUrl', 'qrCodeDataUri', 'codigoDisplayLines', 'fechaEmision'))
            ->setOptions([
                'default_paper_size' => 'letter',
                'default_paper_orientation' => 'portrait',
                'dpi' => 96,
                'enable_html5_parser' => true,
                'enable_font_subsetting' => true,
            ], true)
            ->setPaper('letter', 'portrait');
    }

    private function qrCodeDataUri(string $verificationUrl): string
    {
        return (new Builder(
            writer: new PngWriter,
            data: $verificationUrl,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: self::QR_CODE_SIZE,
            margin: self::QR_CODE_MARGIN,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
        ))->build()->getDataUri();
    }

    /**
     * @return list<string>
     */
    private function codigoDisplayLines(string $codigo): array
    {
        $parts = explode('-', $codigo);

        if (count($parts) === 5) {
            return [
                implode('-', array_slice($parts, 0, 3)),
                implode('-', array_slice($parts, 3)),
            ];
        }

        return str_split($codigo, 18);
    }
}
