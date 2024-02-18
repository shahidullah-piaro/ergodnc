<?php

declare(strict_types=1);

namespace App\Modules\Students;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use InvalidArgumentException;

class StudentsRepository
{
    private $tableName = "students";
    private $selectColumns = [
        "students.id",
        "students.name",
        "students.email",
        "students.avatar",
        "students.deleted_at AS deletedAt",
        "students.created_at AS createdAt",
        "students.updated_at AS updatedAt"
    ];

    public function get(int $id) : Students
    {
        $selectColumns = implode(", ", $this->selectColumns);
        $result = json_decode(json_encode(
            DB::selectOne("SELECT $selectColumns
                FROM {$this->tableName}
                WHERE id = :id AND deleted_at IS NULL
            ", [
                "id" => $id
            ])
        ), true);

        if ($result === null) {
            throw new InvalidArgumentException("Invalid student id.");
        }

        return StudentsMapper::mapFrom($result);
    }

    public function update(Students $student): Students
    {
        // If there is an old file, delete it from both local storage and database
        if($student->getId()){

            $serverStudent = DB::table($this->tableName)
            ->where('id', $student->getId())
            ->first();

            if($serverStudent->avatar){
                $localPath = storage_path('app/public/'.$serverStudent->avatar);  // Construct local path

                // Delete the file from local storage
                if (File::exists($localPath)) {
                    File::delete($localPath);
                }
            }
        }
        return DB::transaction(function () use ($student) {
            DB::table($this->tableName)->updateOrInsert([
                "id" => $student->getId()
            ], $student->toSQL());

            $id = ($student->getId() === null || $student->getId() === 0)
                ? (int)DB::getPdo()->lastInsertId()
                : $student->getId();

            return $this->get($id);
        });
    }

    public function softDelete(int $id): bool
    {
        $student = DB::table($this->tableName)
        ->where('id', $id)
        ->first();

        // If there is an old file, delete it from both local storage and database
        if ($student->avatar) {
            $localPath = storage_path('app/public/'.$student->avatar);  // Construct local path

            // Delete the file from local storage
            if (File::exists($localPath)) {
                File::delete($localPath);
            }
        }

        $result = DB::table($this->tableName)
            ->where("id", $id)
            ->where("deleted_at", null)
            ->update([
                "deleted_at" => date("Y-m-d H:i:s")
            ]);

        if ($result !== 1) {
            throw new InvalidArgumentException("Invalid Students Id.");
        }

        return true;
    }

    public function getByCourseId(int $courseId): array
    {
        $selectColumns = implode(", ", $this->selectColumns);
        $result = json_decode(json_encode(
            DB::select("SELECT $selectColumns
                FROM students
                JOIN students_courses_enrollments ON students_courses_enrollments.courses_id = :courseId
                WHERE students.id = students_courses_enrollments.students_id
                AND students_courses_enrollments.deleted_at IS NULL
            ", [
                "courseId" => $courseId
            ])
        ), true);

        if (count($result) === 0) {
           return [];
        }

        return array_map(function ($row) {
            return StudentsMapper::mapFrom($row);
        }, $result);
    }

    /**
    * @return Students[]
    */
    public function index($page): array
    {

        $pagination = 4;  // Set desired page size
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;  // Get page number from request

        // Calculate offset dynamically based on page number and pagination
        $offset = ($page - 1) * $pagination;

        $baseUrl = 'http://127.0.0.1:8000/api/students';

        // Calculate total count (optional)
        $totalCount = DB::table($this->tableName)
            ->where('deleted_at', null)
            ->count();

        $totalPages = (int) ceil(($totalCount + 1) / $pagination);
        // Build the actual query with LIMIT and OFFSET
        $selectColumns = implode(", ", $this->selectColumns);
        $result = DB::select("SELECT $selectColumns
                            FROM {$this->tableName}
                            WHERE deleted_at IS NULL
                            LIMIT $pagination OFFSET $offset");
         
        
        // Validate inputs
        if ($totalPages <= 0 || $offset < 0) {
            throw new InvalidArgumentException('Invalid page size or offset: must be positive integers');
        }


        $currentPage = (int) ceil($offset / $totalPages) + 1; // 1-based indexing

        // Generate previous and next links, considering edge cases
        $previousLink = null;
        $nextLink = null;

        if ($currentPage > 1) {
            $previousLink = "<a href='" . $baseUrl . '?' . http_build_query(['page' => $currentPage - 1]) . "'>Previous</a>";
        }

        if ($currentPage < $totalPages) {
            $nextLink = "<a href='" . $baseUrl . '?' . http_build_query(['page' => $currentPage + 1]) . "'>Next</a>";
        }

        // Generate access links (considering edge cases and error handling)
        $pageLinks = [];
        if ($totalPages > 1) {
            // Adjust link range based on your preference
            $startPage = 1;

            for ($page = $startPage; $page <= $totalPages; $page++) {
                $link = "";

                if ($page === $currentPage) {
                    $link = "<strong>$page</strong>"; // Current page as bold
                } else {
                    // Construct access link using the provided base URL and query parameters
                    $queryParams = ['page' => $page];
                    $link = "<a href='" . $baseUrl . '?' . http_build_query($queryParams) . "'>$page</a>";
                }

                $pageLinks[] = $link;
            }
        }

        return [
            'data' => $result,
            'pages' => $totalPages,
            'links' => $pageLinks,
            'previous' => $previousLink,
            'next' => $nextLink,
        ];
    }
 
}
