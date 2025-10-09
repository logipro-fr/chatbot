<?php

namespace Chatbot\Infrastructure\Api\V1\File;

use Chatbot\Infrastructure\Api\V1\AbstractController;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\Assistant\FileApi;
use Chatbot\Domain\Model\File\FileId;
use Chatbot\Domain\Model\File\FileMetadataRepositoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/file')]
class DeleteFileController extends AbstractController
{
    public function __construct(
        private FileApi $fileApi,
        private FileMetadataRepositoryInterface $fileMetadataRepository
    ) {
    }

    #[Route('/delete/{fileId}', name: 'file_delete', methods: ['DELETE'])]
    public function delete(string $fileId): Response
    {
        try {
            if (empty($fileId)) {
                return $this->writeUnsuccessfulResponse(
                    new \InvalidArgumentException('File ID is required'),
                    400
                );
            }

            $this->fileMetadataRepository->delete(new FileId($fileId));

            $this->fileApi->delete($fileId);

            $data = (object) [
                'message' => 'File deleted successfully',
                'file_id' => $fileId
            ];

            return $this->writeSuccessfulResponse($data);
        } catch (\Exception $e) {
            return $this->writeUnsuccessfulResponse($e);
        }
    }
}
