<?php
namespace App\Services;

use App\Models\Event;
use Fpdf\Fpdf;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportService
{
    private bool $impactFontLoaded = false;

    public function exportCsv(Event $event): StreamedResponse
    {
        $attendances = $event->attendances()
            ->with(['position', 'headquarter'])
            ->orderBy('registered_at')
            ->get();

        $callback = function () use ($attendances) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Nombre Completo',
                'Número de Identificación',
                'Cargo',
                'Sede',
                'Fecha de Registro',
            ]);

            foreach ($attendances as $attendance) {
                fputcsv($handle, [
                    $attendance->full_name,
                    $attendance->id_number,
                    $attendance->position?->name ?? '',
                    $attendance->headquarter?->name ?? '',
                    $attendance->registered_at->format('Y-m-d'),
                ]);
            }

            fclose($handle);
        };

        $filename = 'asistencias-' . $event->slug . '-' . now()->format('Ymd') . '.csv';

        return new StreamedResponse($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function exportXlsx(Event $event, ?int $headquarterId = null): Response
    {
        $attendances = $event->attendances()
            ->with(['position', 'headquarter'])
            ->when($headquarterId, fn ($query) => $query->where('headquarter_id', $headquarterId))
            ->orderBy('registered_at')
            ->get();

        $writer = new XlsxWriter();

        $writer->setTitle($event->topic);

        $writer->setHeaders([
            'Nombre Completo',
            'Número de Identificación',
            'Cargo',
            'Sede',
            'Fecha de Registro',
        ]);

        foreach ($attendances as $attendance) {
            $writer->addRow([
                $attendance->full_name,
                $attendance->id_number,
                $attendance->position?->name ?? '',
                $attendance->headquarter?->name ?? '',
                $attendance->registered_at->format('Y-m-d'),
            ]);
        }

        $filename = 'asistencias-' . $event->slug . '-' . now()->format('Ymd') . '.xlsx';
        $content = $writer->getContent();

        return new Response(
            $content,
            200,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Content-Length' => strlen($content),
            ]
        );
    }

    public function exportPdf(Event $event, ?int $headquarterId = null): Response
    {
        $attendances = $event->attendances()
            ->with(['position', 'headquarter'])
            ->when($headquarterId, fn ($query) => $query->where('headquarter_id', $headquarterId))
            ->orderBy('registered_at')
            ->get();

        $this->configurePdfFontPath();

        $pdf = new class ('L', 'mm', 'A4') extends Fpdf {
            protected function Footer(): void
            {
                $this->SetY(-10);
                $this->SetFont('Helvetica', '', 6.5);
                $this->SetTextColor(90, 90, 90);
                $text = 'Aviso de privacidad: La información registrada en este formulario será utilizada exclusivamente para fines laborales y administrativos. La empresa garantiza el tratamiento confidencial de los datos personales conforme a la Ley 1581 de 2012, el Decreto 1074 de 2015 y su Política de Tratamiento de Datos Personales.';
                $converted = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $text);
                $this->MultiCell(0, 3, $converted !== false ? $converted : '', 0, 'C');
            }
        };
        $this->registerImpactFont($pdf);
        $pdf->SetMargins(8, 8, 8);
        $pdf->SetAutoPageBreak(false);
        $pdf->AddPage();

        $tableStartY = $this->drawAttendanceFormatHeader($pdf, $event);
        $rowY = $tableStartY;
        $rowNumber = 1;
        $rowHeight = 6.4;
        $bottomLimit = 202;
        $tempFiles = [];

        foreach ($attendances as $attendance) {
            if ($rowY + $rowHeight > $bottomLimit) {
                $pdf->AddPage();
                $tableStartY = $this->drawAttendanceFormatHeader($pdf, $event);
                $rowY = $tableStartY;
            }

            $this->drawAttendancePdfRow($pdf, $attendance, $rowNumber, $rowY, $rowHeight, $tempFiles);
            $rowY += $rowHeight;
            $rowNumber++;
        }

        foreach ($tempFiles as $tempFile) {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }

        $filename = 'asistencias-' . $event->slug . '-' . now()->format('Ymd') . '.pdf';
        $content = $pdf->Output('S');

        return new Response(
            $content,
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Content-Length' => strlen($content),
            ]
        );
    }

    private function drawAttendanceFormatHeader(Fpdf $pdf, Event $event): float
    {
        $this->drawTopInstitutionalHeader($pdf);
        $this->drawEventMainInformation($pdf, $event);

        return $this->drawAttendanceTableHeader($pdf, 66.0);
    }

    private function configurePdfFontPath(): void
    {
        $fontPath = public_path('fonts') . DIRECTORY_SEPARATOR;

        if (!defined('FPDF_FONTPATH') && is_dir($fontPath)) {
            define('FPDF_FONTPATH', $fontPath);
        }
    }

    private function registerImpactFont(Fpdf $pdf): void
    {
        $impactFont = $this->resolveImpactFontDefinition();

        if (!$impactFont) {
            $this->impactFontLoaded = false;
            return;
        }

        try {
            $pdf->AddFont('Impact', '', basename($impactFont));
            $this->impactFontLoaded = true;
        } catch (\Throwable $e) {
            $this->impactFontLoaded = false;
        }
    }

    private function resolveImpactFontDefinition(): ?string
    {
        $paths = [
            public_path('fonts/impact.php'),
            public_path('fonts/Impact.php'),
            public_path('fonts/IMPACT.php'),
        ];

        foreach ($paths as $path) {
            if ($path && file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    private function setImpactTitleFont(Fpdf $pdf, float $size): void
    {
        if ($this->impactFontLoaded) {
            $pdf->SetFont('Impact', '', $size);
            return;
        }

        $pdf->SetFont('Helvetica', 'B', $size);
    }

    private function drawTopInstitutionalHeader(Fpdf $pdf): void
    {
        $x = 8;
        $y = 8;
        $w = 281;
        $h = 22;
        $logoW = 35;
        $metaW = 45;
        $titleW = $w - $logoW - $metaW;
        $titleX = $x + $logoW;
        $metaX = $titleX + $titleW;

        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetLineWidth(0.55);

        $pdf->Rect($x, $y, $w, $h);
        $pdf->Line($titleX, $y, $titleX, $y + $h);
        $pdf->Line($metaX, $y, $metaX, $y + $h);
        // La división horizontal del título debe coincidir exactamente
        // con la división del bloque derecho: Código/Versión vs Fecha de edición.
        $middleLineY = $y + 11;
        $pdf->Line($titleX, $middleLineY, $metaX, $middleLineY);

        // El bloque derecho queda dividido en 2 celdas:
        // 1) Código y versión juntos.
        // 2) Fecha de edición.
        $pdf->Line($metaX, $middleLineY, $x + $w, $middleLineY);

        $logoPath = $this->resolveLogoPath();
        if ($logoPath) {
            $pdf->Image($logoPath, $x + 5.2, $y + 2.2, 24.5, 16.5);
        } else {
            $pdf->SetFont('Helvetica', 'B', 9.0);
            $pdf->SetXY($x + 2, $y + 8);
            $pdf->Cell($logoW - 4, 4, $this->pdfText('Mister Wings'), 0, 0, 'C');
        }

        $this->setImpactTitleFont($pdf, 19.8);
        $pdf->SetXY($titleX, $y + 2.2);
        $pdf->Cell($titleW, 8.2, $this->pdfText('FORMATO'), 0, 0, 'C');

        $this->setImpactTitleFont($pdf, 19.8);
        $pdf->SetXY($titleX, $middleLineY + 2.2);
        $pdf->Cell($titleW, 8.2, $this->pdfText('REGISTRO DE ASISTENCIA VIRTUAL'), 0, 0, 'C');

        $this->drawHeaderMetaLine($pdf, $metaX + 1.2, $y + 1.5, 'Código:', 'FO-SST-29');
        $this->drawHeaderMetaLine($pdf, $metaX + 1.2, $y + 6.0, 'Versión:', '01');

        $pdf->SetFont('Helvetica', 'B', 8.5);
        $pdf->SetXY($metaX + 1.2, $y + 12.0);
        $pdf->Cell(24, 3.6, $this->pdfText('Fecha de edición:'), 0, 0, 'L');
        $pdf->SetFont('Helvetica', '', 8.5);
        $pdf->SetXY($metaX + 1.2, $y + 16.3);
        $pdf->Cell($metaW - 2.4, 3.6, $this->pdfText('22-Jun-2026'), 0, 0, 'L');
    }

    private function drawHeaderMetaLine(Fpdf $pdf, float $x, float $y, string $label, string $value): void
    {
        $pdf->SetFont('Helvetica', 'B', 8.5);
        $pdf->SetXY($x, $y);
        $pdf->Cell(12.5, 3.8, $this->pdfText($label), 0, 0, 'L');
        $pdf->SetFont('Helvetica', '', 8.5);
        $pdf->Cell(30, 3.8, $this->pdfText($value), 0, 0, 'L');
    }

    private function drawEventMainInformation(Fpdf $pdf, Event $event): void
    {
        $x = 8;
        $y = 33.2;

        $date = $this->formatDateValue($event->date);
        $startTime = $this->formatTimeValue($event->start_time);
        $endTime = $this->formatTimeValue($event->end_time);

        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetLineWidth(0.25);

        $this->drawLineField($pdf, $x, $y, 46, 'FECHA:', $date, 14, 9.0, 17);
        $this->drawLineField($pdf, $x + 48, $y, 112, 'TEMA:', $event->topic ?? '', 13, 9.0, 70);
        $this->drawLineField($pdf, $x + 162, $y, 55, 'HORA INICIO:', $startTime, 26, 9.0, 12);
        $this->drawLineField($pdf, $x + 219, $y, 62, 'HORA FINAL:', $endTime, 25, 9.0, 12);

        $secondY = $y + 8;
        $this->drawLineField($pdf, $x, $secondY, 108, 'DIRIGE:', $event->directedBy?->name ?? '', 13, 9.0, 55);
        $this->drawLineField($pdf, $x + 111, $secondY, 99, 'CARGO/ÁREA:', $event->directed_by_position ?? '', 28, 9.0, 45);
        $this->drawLineField($pdf, $x + 213, $secondY, 68, 'LUGAR:', $event->place ?? '', 14, 9.0, 35);

        $thirdY = $y + 16;
        $pdf->SetFont('Helvetica', 'B', 9.0);
        $pdf->SetXY($x, $thirdY);
        $pdf->Cell(42, 4, $this->pdfText('MOTIVO DE LA REUNIÓN:'), 0, 0, 'L');

        $reason = trim((string) ($event->reason ?? ''));
        $selectedReasonKey = $this->getSelectedReasonKey($reason);
        $isOtherReason = $reason !== '' && $selectedReasonKey === null;

        $this->drawReasonOption($pdf, $x + 44, $thirdY, 55, 'INDUCCIÓN CORPORATIVA', $selectedReasonKey === 'induccion');
        $this->drawReasonOption($pdf, $x + 104, $thirdY, 40, 'REINDUCCIÓN', $selectedReasonKey === 'reinduccion');
        $this->drawReasonOption($pdf, $x + 150, $thirdY, 40, 'CAPACITACIÓN', $selectedReasonKey === 'capacitacion');
        $this->drawReasonOption($pdf, $x + 198, $thirdY, 83, 'DIVULGACIÓN DE INFORMACIÓN', $selectedReasonKey === 'divulgacion');

        $this->drawOtherReasonField($pdf, $x, $thirdY + 6.8, 281, $isOtherReason ? $reason : '', $isOtherReason);
    }

    private function drawLineField(
        Fpdf $pdf,
        float $x,
        float $y,
        float $w,
        string $label,
        string $value,
        float $labelW,
        float $fontSize = 9.0,
        int $valueLimit = 60
    ): void {
        $pdf->SetFont('Helvetica', 'B', $fontSize);
        $pdf->SetXY($x, $y);
        $pdf->Cell($labelW, 4, $this->pdfText($label), 0, 0, 'L');

        $lineX = $x + $labelW;
        $lineY = $y + 4.2;
        $pdf->Line($lineX, $lineY, $x + $w, $lineY);

        $pdf->SetFont('Helvetica', '', $fontSize - 0.2);
        $pdf->SetXY($lineX + 1, $y + 0.1);
        $pdf->Cell(max($w - $labelW - 1, 1), 3.8, $this->pdfText($value, $valueLimit), 0, 0, 'L');
    }

    private function drawReasonOption(Fpdf $pdf, float $x, float $y, float $w, string $label, bool $checked): void
    {
        $labelText = $this->pdfText($label);
        $checkSize = 4.6;
        $gap = 1.3;
        $labelWidth = max($w - $checkSize - $gap, 1);
        $boxY = $y - 0.7;
        $boxHeight = 5.1;
        $checkX = $x + $labelWidth + $gap;

        if ($checked) {
            $pdf->SetFillColor(254, 226, 226);
            $pdf->SetDrawColor(185, 28, 28);
            $pdf->SetTextColor(153, 27, 27);
            $pdf->SetLineWidth(0.35);
            $pdf->Rect($x, $boxY, $labelWidth, $boxHeight, 'DF');

            $pdf->SetFont('Helvetica', 'B', 8.2);
            $pdf->SetXY($x + 1, $y - 0.05);
            $pdf->Cell(max($labelWidth - 2, 1), 4, $labelText, 0, 0, 'L');

            $pdf->SetFillColor(220, 38, 38);
            $pdf->SetDrawColor(185, 28, 28);
            $pdf->Rect($checkX, $y - 0.45, $checkSize, $checkSize, 'DF');

            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFont('Helvetica', 'B', 8.0);
            $pdf->SetXY($checkX, $y - 0.35);
            $pdf->Cell($checkSize, $checkSize, 'X', 0, 0, 'C');
        } else {
            $pdf->SetDrawColor(0, 0, 0);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetLineWidth(0.25);
            $pdf->SetFont('Helvetica', 'B', 8.2);
            $pdf->SetXY($x, $y);
            $pdf->Cell($labelWidth, 4, $labelText, 0, 0, 'L');
            $pdf->Rect($checkX, $y - 0.45, $checkSize, $checkSize, 'D');
        }

        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetLineWidth(0.25);
    }

    private function drawOtherReasonField(Fpdf $pdf, float $x, float $y, float $w, string $value, bool $checked): void
    {
        $labelW = 13;
        $height = 5.8;
        $checkSize = 4.6;

        if ($checked) {
            $pdf->SetFillColor(254, 226, 226);
            $pdf->SetDrawColor(185, 28, 28);
            $pdf->SetTextColor(153, 27, 27);
            $pdf->SetLineWidth(0.35);
            $pdf->Rect($x, $y - 0.8, $w, $height, 'DF');
        } else {
            $pdf->SetDrawColor(0, 0, 0);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetLineWidth(0.25);
        }

        $pdf->SetFont('Helvetica', 'B', 9.0);
        $pdf->SetXY($x, $y);
        $pdf->Cell($labelW, 4, $this->pdfText('OTRO:'), 0, 0, 'L');

        $lineX = $x + $labelW;
        $lineY = $y + 4.2;
        $lineEndX = $checked ? $x + $w - 8 : $x + $w;
        $pdf->Line($lineX, $lineY, $lineEndX, $lineY);

        $pdf->SetFont('Helvetica', $checked ? 'B' : '', 8.8);
        $pdf->SetXY($lineX + 1, $y + 0.1);
        $pdf->Cell(max($lineEndX - $lineX - 1, 1), 3.8, $this->pdfText($value, 140), 0, 0, 'L');

        if ($checked) {
            $checkX = $x + $w - 6.5;
            $pdf->SetFillColor(220, 38, 38);
            $pdf->SetDrawColor(185, 28, 28);
            $pdf->Rect($checkX, $y - 0.4, $checkSize, $checkSize, 'DF');

            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFont('Helvetica', 'B', 8.0);
            $pdf->SetXY($checkX, $y - 0.3);
            $pdf->Cell($checkSize, $checkSize, 'X', 0, 0, 'C');
        }

        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetLineWidth(0.25);
    }

    private function drawAttendanceTableHeader(Fpdf $pdf, float $y): float
    {
        $x = 8;
        $colWidths = $this->attendanceColumnWidths();
        $headers = ['No.', 'NOMBRE COMPLETO', '# IDENTIFICACION', 'CARGO', 'SEDE', 'FIRMA'];

        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetLineWidth(0.45);
        $pdf->SetFont('Helvetica', 'B', 9.0);
        $pdf->SetXY($x, $y);

        foreach ($headers as $index => $header) {
            $pdf->Cell($colWidths[$index], 5.0, $this->pdfText($header), 1, 0, 'C');
        }

        return $y + 5.0;
    }

    private function drawAttendancePdfRow(Fpdf $pdf, $attendance, int $rowNumber, float $y, float $rowHeight, array &$tempFiles): void
    {
        $x = 8;
        $colWidths = $this->attendanceColumnWidths();

        $values = [
            (string) $rowNumber,
            $attendance?->full_name ?? '',
            $attendance?->id_number ?? '',
            $attendance?->position?->name ?? '',
            $attendance?->headquarter?->name ?? '',
        ];

        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetLineWidth(0.25);
        $pdf->SetFont('Helvetica', 'B', 9.0);
        $pdf->SetXY($x, $y);

        $pdf->Cell($colWidths[0], $rowHeight, $this->pdfText($values[0]), 1, 0, 'C');

        $pdf->SetFont('Helvetica', '', 9.0);
        $pdf->Cell($colWidths[1], $rowHeight, $this->pdfText($values[1], 60), 1, 0, 'L');
        $pdf->Cell($colWidths[2], $rowHeight, $this->pdfText($values[2], 26), 1, 0, 'C');
        $pdf->Cell($colWidths[3], $rowHeight, $this->pdfText($values[3], 28), 1, 0, 'C');
        $pdf->Cell($colWidths[4], $rowHeight, $this->pdfText($values[4], 24), 1, 0, 'C');

        $signatureX = $pdf->GetX();
        $signatureY = $pdf->GetY();
        $pdf->Cell($colWidths[5], $rowHeight, '', 1, 0, 'C');

        if ($attendance?->signature) {
            $imageData = $this->dataUriToBinary($attendance->signature);

            if ($imageData) {
                $tempFile = tempnam(sys_get_temp_dir(), 'sig_') . '.png';
                file_put_contents($tempFile, $imageData);
                $tempFiles[] = $tempFile;

                $pdf->Image($tempFile, $signatureX + 2, $signatureY + 0.4, $colWidths[5] - 4, $rowHeight - 0.8);
            }
        }
    }

    private function attendanceColumnWidths(): array
    {
        return [7, 91, 48, 48, 43, 44];
    }

    private function resolveLogoPath(): ?string
    {
        $paths = [
            public_path('images/logo_mw.png'),
            public_path('images/logo-mister-wings.png'),
            public_path('images/mister-wings-logo.png'),
            public_path('images/logo.png'),
            public_path('assets/images/logo.png'),
            public_path('assets/img/logo.png'),
        ];

        foreach ($paths as $path) {
            if ($path && file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    private function formatDateValue($value): string
    {
        if (!$value) {
            return '';
        }

        try {
            if ($value instanceof \Carbon\CarbonInterface) {
                return $value->format('d/m/Y');
            }

            return \Carbon\Carbon::parse($value)->format('d/m/Y');
        } catch (\Throwable $e) {
            return (string) $value;
        }
    }

    private function formatTimeValue($value): string
    {
        if (!$value) {
            return '';
        }

        try {
            if ($value instanceof \Carbon\CarbonInterface) {
                return $value->format('H:i');
            }

            $value = (string) $value;
            if (preg_match('/^\d{1,2}:\d{2}/', $value)) {
                return substr($value, 0, 5);
            }

            return \Carbon\Carbon::parse($value)->format('H:i');
        } catch (\Throwable $e) {
            return (string) $value;
        }
    }

    private function getReasonOptions(): array
    {
        return [
            'induccion' => 'INDUCCIÓN CORPORATIVA',
            'reinduccion' => 'REINDUCCIÓN',
            'capacitacion' => 'CAPACITACIÓN',
            'divulgacion' => 'DIVULGACIÓN DE INFORMACIÓN',
        ];
    }

    private function getSelectedReasonKey(?string $reason): ?string
    {
        $reason = $this->normalizeReasonValue($reason);

        if ($reason === '') {
            return null;
        }

        foreach ($this->getReasonOptions() as $key => $label) {
            if ($reason === $this->normalizeReasonValue($label)) {
                return $key;
            }
        }

        return null;
    }

    private function normalizeReasonValue(?string $text): string
    {
        $text = mb_strtoupper(trim((string) $text), 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text) ?? $text;
        $converted = iconv('UTF-8', 'ASCII//TRANSLIT', $text);

        return trim($converted !== false ? $converted : $text);
    }

    private function pdfText(?string $text, int $limit = 0): string
    {
        $text = trim((string) $text);
        $text = preg_replace('/\s+/', ' ', $text) ?? $text;

        if ($limit > 0) {
            $text = mb_substr($text, 0, $limit, 'UTF-8');
        }

        $text = str_replace(['—', '–'], '-', $text);
        $converted = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $text);

        return $converted !== false ? $converted : '';
    }

    private function dataUriToBinary(?string $dataUri): ?string
    {
        if (!$dataUri) {
            return null;
        }

        $pos = strpos($dataUri, 'base64,');
        if ($pos === false) {
            return null;
        }

        $base64 = substr($dataUri, $pos + 7);
        $binary = base64_decode($base64, true);

        return $binary !== false ? $binary : null;
    }
}
