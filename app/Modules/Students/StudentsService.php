<?php

declare(strict_types=1);

namespace App\Modules\Students;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class StudentsService
{
    private StudentsValidator $validator;
    private StudentsRepository $repository;

    public function __construct(
        StudentsValidator $validator,
        StudentsRepository $repository
    )
    {
        $this->validator = $validator;
        $this->repository = $repository;
    }

    public function get(int $id): Students
    {
        return $this->repository->get($id);
    }

    /**
     * @param integer $courseId
     * @return Students[]
     */
    public function getByCourseId(int $courseId) : array
    {
        return $this->repository->getByCourseId($courseId);
    }

    /**
     * @return Students[]
     */
    public function index($page) : array
    {
        return $this->repository->index($page);
    }

    public function update(array $data) : Students
    {
        $this->validator->validateUpdate($data);

        $data = array_merge(
            $data,
            [
                "avatar" => $this->saveFile($data['avatar'])
            ]
        );

        return $this->repository->update(
            StudentsMapper::mapFrom($data)
        );
    }

    public function softDelete(int $id): bool
    {
        return $this->repository->softDelete($id);
    }


    private function saveFile($file) : string
    {
        // Check for valid base64 string and data URI with file data
        if (!preg_match('/^data:application\/(pdf|msword|vnd.openxmlformats-officedocument.wordprocessingml.document);base64,/', $file, $type)) {
            throw new \Exception('Invalid base64 file or incorrect format');
        }

        // Extract base64 encoded text and extension
        $file = substr($file, strpos($file, ',') + 1);
        $type = strtolower($type[1]); // pdf, doc, docx

        // Check for allowed file types
        if (!in_array($type, ['pdf', 'doc', 'docx'])) {
            throw new \Exception('Invalid file type');
        }

        // Decode base64 data
        $file = str_replace(' ', '+', $file);
        $decodedFile = base64_decode($file);

        if ($decodedFile === false) {
            throw new \Exception('base64_decode failed');
        }
        
        // Save the file to a local path
        $localDir = 'app/public/documents/';  // Adjust this path as needed for your local storage
        $serverDir = 'documents/';  // Adjust this path as needed for your server
        $filename = Str::random() . '.' . $type;
        $localPath = storage_path($localDir);  // Use storage_path() for local storage
        $relativePath = $serverDir . $filename;

        if (!File::exists($localPath)) {
            File::makeDirectory($localPath, 0755, true);
        }

        file_put_contents($localPath . $filename, $decodedFile);  // Use $localPath for saving

        return $relativePath;
    }

}
