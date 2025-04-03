<?php

namespace App\Service;

use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use GuzzleHttp\Exception\GuzzleException;
use Spatie\SimpleExcel\SimpleExcelReader;

class XlsFileManager
{
    private const TEMP_FILE_NAME = __DIR__ . '/../../var/temp.xlsx';
    private const URI = 'http://172.29.0.1:8080/api/xlsx-file';
    private ?array $error = null;

    public function __construct(private LoggerInterface $logger)
    {
    }

    public static function init(LoggerInterface $logger): self
    {
        return new self($logger);
    }

    public function getRows(): array
    {
        $content = self::getContent();

        if ($errors = $this->error) {
            return $errors;
        }

        file_put_contents(self::TEMP_FILE_NAME, $content);

        return SimpleExcelReader::create(self::TEMP_FILE_NAME)
            ->getRows()
            ->toArray();
    }

    private function getContent(): string
    {
        $client = new Client();

        try {
            $response = $client->request('GET', self::URI);
        } catch (GuzzleException $e) {
            $this->error['error'][] = $e->getMessage();
            $this->logger->error($e->getMessage());

            return '';
        }

        $responseCode = $response->getStatusCode();
        if (200 !== $responseCode) {
            $errorMessage = sprintf('Код ответа сервера: %s', $responseCode);

            $this->error['error'][] = $errorMessage;
            $this->logger->error($errorMessage);

            return '';
        }

        return $response->getBody()->getContents();
    }

    public static function removeTemplateFile(): void
    {
        unlink(self::TEMP_FILE_NAME);
    }
}