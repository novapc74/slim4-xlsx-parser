<?php

namespace App\Routes;

use Throwable;
use App\Controller\RestController;
use Psr\Http\Message\ResponseInterface;

class GetXlsxFile extends RestController
{
    public function action(): ResponseInterface
    {
        $filePath = __DIR__ . '/../../' . env('XLSX_FILE_NAME');

        try {
            return $this->respondWithFileXlsx($filePath);
        } catch (Throwable $e) {
            return $this->respondWithError(253, $e->getMessage());
        }
    }
}