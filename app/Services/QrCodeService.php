<?php

namespace App\Services;

use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QRMarkupSVG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Facades\Storage;

class QrCodeService
{
    /**
     * Generate and store a QR code (SVG) file for a customer under the qrcodes disk.
     *
     * @return string the stored filename
     */
    public function generateForCustomer(string $customerCode, string $customerName): string
    {
        $data = sprintf('GASDELIVERY|%s|%s', $customerCode, $customerName);

        $filename = $customerCode . '.svg';

        $options = new QROptions([
            'outputInterface' => QRMarkupSVG::class,
            'eccLevel'        => EccLevel::H,
            'scale'           => 10,
            'addQuietzone'    => true,
            'outputBase64'    => false,
        ]);

        $svg = (new QRCode($options))->render($data);

        Storage::disk('qrcodes')->put($filename, $svg);

        return $filename;
    }
}