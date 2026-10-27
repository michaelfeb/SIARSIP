<?php

namespace App\Services;

use App\Models\Letter;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;

class LetterQrCodeService
{
    /**
     * Generate the QR image shown on a finalized letter's signature block.
     */
    public function forLetter(Letter $letter): string
    {
        $qrCode = QrCode::create($this->payload($letter))
            ->setErrorCorrectionLevel(ErrorCorrectionLevel::High)
            ->setSize(300)
            ->setMargin(10)
            ->setRoundBlockSizeMode(RoundBlockSizeMode::Margin);

        $logo = Logo::create(public_path('images/logo-ulm.png'))
            ->setResizeToWidth(64)
            ->setPunchoutBackground(true);

        return (new PngWriter)
            ->write($qrCode, $logo)
            ->getDataUri();
    }

    private function payload(Letter $letter): string
    {
        return sprintf(
            'SIARSIP|LETTER:%s|NUMBER:%s',
            $letter->getKey() ?? '-',
            $letter->letter_number ?? '-',
        );
    }
}
