<?php

defined('BASEPATH') or exit('No direct script access allowed');

class QrGenerator
{

	public function generate($data, $size = 500)
	{
		$url = 'https://api.qrserver.com/v1/create-qr-code/?size=' . $size . 'x' . $size . '&data=' . urlencode($data);
		$qrImage = file_get_contents($url);

		if ($qrImage === false) {
			throw new Exception('Error al generar el código QR');
		}

		return $qrImage;
	}

	public function save($data, $path, $size = 500)
	{
		$qrImage = $this->generate($data, $size);

		if (file_put_contents($path, $qrImage) === false) {
			throw new Exception('Error al guardar el código QR en ' . $path);
		}

		return true;
	}
}
