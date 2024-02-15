<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Modules\Core\HTTPResponseCodes;
use App\Modules\Students\StudentsService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Modules\Students\StudentsMapper;
use App\Http\Controllers\Log;


class StudentsController
{
    private StudentsService $service;

    public function __construct(StudentsService $service)
    {
        $this->service = $service;
    }

    public function get(int $id) : Response
    {
        try {
            return new response($this->service->get($id)->toArray());
        } catch (Exception $error) {
            return new Response(
                [
                    "exception" => get_class($error),
                    "errors" => $error->getMessage()
                ],
                HTTPResponseCodes::BadRequest["code"]
            );
        }
    }


    public function index() : Response
    {
        try {
            $data = $this->service->index();

            // Check if data is empty
            if (empty($data)) {
                return new Response(
                    [
                        "message" => "No data found",
                    ],
                    HTTPResponseCodes::NotFound["code"]
                );
            }

            return new Response(
                $data,
                HTTPResponseCodes::Sucess["code"]
            );
        } catch (Exception $error) {

            return new Response(
                [
                    "exception" => get_class($error),
                    "errors" => $error->getMessage()
                ],
                HTTPResponseCodes::BadRequest["code"]
            );
        }
    }

    public function update(Request $request): Response
    {
        try {
            $dataArray = ($request->toArray() !== [])
                ? $request->toArray()
                : $request->json()->all();

            return new Response(
                $this->service->update($dataArray)->toArray(),
                HTTPResponseCodes::Sucess["code"]
            );
        } catch (Exception $error) {
            return new Response(
                [
                    "exception" => get_class($error),
                    "errors" => $error->getMessage()
                ],
                HTTPResponseCodes::BadRequest["code"]
            );
        }
    }

    public function softDelete(int $id) : Response
    {
        try {
            return new response($this->service->softDelete($id));
        } catch (Exception $error) {
            return new Response(
                [
                    "exception" => get_class($error),
                    "errors" => $error->getMessage()
                ],
                HTTPResponseCodes::BadRequest["code"]
            );
        }
    }

}
