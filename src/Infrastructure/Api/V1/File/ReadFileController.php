<?php

namespace Chatbot\Infrastructure\Api\V1\File;

use Chatbot\Infrastructure\Api\V1\AbstractController;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\Assistant\FileApi;
use Chatbot\Domain\Model\File\FileId;
use Chatbot\Domain\Model\File\FileMetadataRepositoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/file')]
class ReadFileController extends AbstractController
{
    public function __construct(
        private FileApi $fileApi,
        private FileMetadataRepositoryInterface $fileMetadataRepository
    ) {
    }

    #[Route('/list', name: 'file_list', methods: ['GET'])]
    public function list(Request $request): Response
    {
        try {
            $openAiFiles = $this->fileApi->list();
            $filesWithMetadata = [];

            foreach ($openAiFiles as $openAiFile) {
                $fileId = new FileId($openAiFile['id']);
                $fileMetadata = $this->fileMetadataRepository->findById($fileId);

                $fileWithMetadata = $openAiFile;
                if ($fileMetadata) {
                    $fileWithMetadata['original_filename'] = $fileMetadata->getOriginalFilename();
                }

                $filesWithMetadata[] = $fileWithMetadata;
            }

            $data = (object) [
                'files' => $filesWithMetadata,
                'count' => count($filesWithMetadata)
            ];

            return $this->writeSuccessfulResponse($data);
        } catch (\Exception $e) {
            return $this->writeUnsuccessfulResponse($e);
        }
    }

    #[Route('/get/{fileId}', name: 'file_get', methods: ['GET'])]
    public function get(string $fileId): Response
    {
        try {
            if (empty($fileId)) {
                return $this->writeUnsuccessfulResponse(
                    new \InvalidArgumentException('File ID is required'),
                    400
                );
            }

            $fileInfo = $this->fileApi->get($fileId);

            $fileMetadata = $this->fileMetadataRepository->findById(new FileId($fileId));
            if ($fileMetadata) {
                $fileInfo['original_filename'] = $fileMetadata->getOriginalFilename();
            }

            $data = (object) [
                'file' => $fileInfo
            ];

            return $this->writeSuccessfulResponse($data);
        } catch (\Exception $e) {
            return $this->writeUnsuccessfulResponse($e);
        }
    }
}
