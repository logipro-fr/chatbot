<?php

namespace Chatbot\Infrastructure\Api\V1\File;

use Chatbot\Infrastructure\Api\V1\AbstractController;
use Chatbot\Infrastructure\LanguageModel\ChatGPT\Assistant\FileApi;
use Chatbot\Domain\Model\File\FileId;
use Chatbot\Domain\Model\File\FileMetadata;
use Chatbot\Domain\Model\File\FileMetadataRepositoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/file')]
class UploadFileController extends AbstractController
{
    public function __construct(
        private FileApi $fileApi,
        private FileMetadataRepositoryInterface $fileMetadataRepository
    ) {
    }

    #[Route('/upload', name: 'file_upload', methods: ['POST'])]
    public function upload(Request $request): Response
    {
        try {
            $uploadedFile = $request->files->get('file');

            if (!$uploadedFile) {
                return $this->writeUnsuccessfulResponse(
                    new \InvalidArgumentException('No file uploaded'),
                    400
                );
            }

            $purpose = $request->request->get('purpose', 'assistants');

            if ($purpose !== 'assistants') {
                return $this->writeUnsuccessfulResponse(
                    new \InvalidArgumentException('Invalid purpose. Must be "assistants"'),
                    400
                );
            }

            $filePath = $uploadedFile->getPathname();
            $openAiFileId = $this->fileApi->upload($filePath, $purpose);

            $fileId = new FileId($openAiFileId);
            $fileMetadata = new FileMetadata(
                $fileId,
                $uploadedFile->getClientOriginalName(),
                $purpose,
                $uploadedFile->getSize()
            );
            $this->fileMetadataRepository->save($fileMetadata);

            $data = (object) [
                'file_id' => $openAiFileId,
                'filename' => $uploadedFile->getClientOriginalName(),
                'purpose' => $purpose,
                'size' => $uploadedFile->getSize()
            ];

            return $this->writeSuccessfulResponse($data);
        } catch (\Exception $e) {
            return $this->writeUnsuccessfulResponse($e);
        }
    }
}
