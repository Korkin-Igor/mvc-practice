<?php

namespace Service;

use Illuminate\Database\Capsule\Manager as Capsule;
use Model\Book;
use Model\BookCopy;
use Throwable;

class BookService
{
    private const COPY_IN_ROOM = 1;

    public function createBookWithAssets(array $payload): OperationResult
    {
        $coverRelativePath = null;
        $digitalRelativePath = null;

        try {
            Capsule::connection()->transaction(function () use ($payload, &$coverRelativePath, &$digitalRelativePath) {
                $book = Book::create([
                    'name' => $payload['name'],
                    'author' => $payload['author'],
                    'description' => $payload['description'] ?? '',
                    'link' => '',
                ]);

                $coverRelativePath = $this->storeCoverImage($book->id, $payload['cover_image']);
                $digitalRelativePath = $this->storeDigitalFile($book->id, $payload['digital_file']);

                $book->link = $digitalRelativePath;
                $book->save();

                $inventoryNumber = (string) $payload['inventory_number'];

                BookCopy::create([
                    'inventory_number' => $inventoryNumber,
                    'storage_place_id' => (int) $payload['storage_place_id'],
                    'status_id' => self::COPY_IN_ROOM,
                    'book_id' => $book->id,
                    'barcode' => 'BAR-' . $inventoryNumber,
                    'qr_code' => 'QR-' . $inventoryNumber,
                ]);
            });

            return OperationResult::success('Книга, обложка и электронный файл успешно загружены.');
        } catch (Throwable $exception) {
            $this->deleteFile($coverRelativePath);
            $this->deleteFile($digitalRelativePath);

            return OperationResult::failure('Не удалось загрузить книгу и файлы. Проверьте данные формы и права на запись.');
        }
    }

    private function storeCoverImage(int $bookId, array $file): string
    {
        $mimeToExtension = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
        ];

        $tmpName = (string) ($file['tmp_name'] ?? '');
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($tmpName);
        $extension = $mimeToExtension[$mime] ?? null;

        if ($extension === null) {
            throw new \RuntimeException('Invalid cover image type.');
        }

        $relativePath = '/public/uploads/covers/book-' . $bookId . '.' . $extension;
        $absolutePath = $this->projectRoot() . $relativePath;

        $this->ensureDirectory(dirname($absolutePath));

        if (!move_uploaded_file($tmpName, $absolutePath)) {
            throw new \RuntimeException('Failed to move uploaded cover image.');
        }

        return $relativePath;
    }

    private function storeDigitalFile(int $bookId, array $file): string
    {
        $extension = mb_strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION), 'UTF-8');
        $safeExtension = preg_replace('/[^a-z0-9]+/i', '', $extension) ?: 'bin';
        $tmpName = (string) ($file['tmp_name'] ?? '');

        $relativePath = '/public/uploads/files/book-' . $bookId . '-' . sha1_file($tmpName) . '.' . $safeExtension;
        $absolutePath = $this->projectRoot() . $relativePath;

        $this->ensureDirectory(dirname($absolutePath));

        if (!move_uploaded_file($tmpName, $absolutePath)) {
            throw new \RuntimeException('Failed to move uploaded digital file.');
        }

        return $relativePath;
    }

    private function ensureDirectory(string $directory): void
    {
        if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
            throw new \RuntimeException('Cannot create upload directory.');
        }
    }

    private function deleteFile(?string $relativePath): void
    {
        if (!$relativePath) {
            return;
        }

        $absolutePath = $this->projectRoot() . $relativePath;
        if (is_file($absolutePath)) {
            @unlink($absolutePath);
        }
    }

    private function projectRoot(): string
    {
        return dirname(__DIR__, 2);
    }
}
