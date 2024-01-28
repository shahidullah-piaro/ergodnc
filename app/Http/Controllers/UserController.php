<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return new UserCollection(User::query()->orderBy('id', 'desc')->paginate(10));
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(SignupRequest $request)
        {
            $data = $request->validated(); // Use validated data directly

            // Handle file uploads 
            if (isset($data['file'])) {
                $data['file'] = $this->saveFile($data['file']);
            }

            if (isset($data['image'])) {
                $data['image'] = $this->saveImage($data['image']);
            }

            // $data = [
            //     'name' => $request->name,
            //     'email' => $request->email,
            //     'password' => Hash::make($request->password),
            //     'fathers_name' => $request->fathers_name,
            //     'mothers_name' => $request->mothers_name,
            //     'nid' => $request->nid,
            //     'start_date' => $request->start_date,
            //     'end_date' => $request->end_date,
            // ];

            $data['remember_token'] = Str::random(10); // Add remember_token

            $user = User::create($data);

            return response(new UserResource($user), 201);
        }


    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     */


    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated(); //Use validated data directly

        // Handle file uploads 
        if (isset($data['file'])) {
            $data['file'] = $this->saveFile($data['file']);

            // If there is an old image, delete it
            if ($user->file) {
                $absolutePath = public_path($user->file);
                File::delete($absolutePath);
            }
        }

        if (isset($data['image'])) {
            $data['image'] = $this->saveImage($data['image']);

            // If there is an old image, delete it
            if ($user->image) {
                $absolutePath = public_path($user->image);
                File::delete($absolutePath);
            }
        }

        // $data = [
        //     'name' => $request->name,
        //     'email' => $request->email,
        //     'password' => Hash::make($request->password),
        //     'fathers_name' => $request->fathers_name,
        //     'mothers_name' => $request->mothers_name,
        //     'nid' => $request->nid,
        //     'start_date' => $request->start_date,
        //     'end_date' => $request->end_date,
        // ];

        $data['remember_token'] = Str::random(10); //Add remember_token
        $user->update($data);
        return new UserResource($user);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        // If there is an old file, delete it
        if ($user->file) {
            $absolutePath = public_path($user->file);
            File::delete($absolutePath);
        }

        // If there is an old image, delete it
        if ($user->image) {
            $absolutePath = public_path($user->image);
            File::delete($absolutePath);
        }

        return response("", 204);
    }


    private function saveImage($image)
    {
        // Check if image is valid base64 string
        if (preg_match('/^data:image\/(\w+);base64,/', $image, $type)) {
            // Take out the base64 encoded text without mime type
            $image = substr($image, strpos($image, ',') + 1);
            // Get file extension
            $type = strtolower($type[1]); // jpg, png, gif

            // Check if file is an image
            if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
                throw new \Exception('invalid image type');
            }
            $image = str_replace(' ', '+', $image);
            $image = base64_decode($image);

            if ($image === false) {
                throw new \Exception('base64_decode failed');
            }
        } else {
            throw new \Exception('did not match data URI with image data');
        }

        $dir = 'images/';
        $file = Str::random() . '.' . $type;
        $absolutePath = public_path($dir);
        $relativePath = $dir . $file;
        if (!File::exists($absolutePath)) {
            File::makeDirectory($absolutePath, 0755, true);
        }
        file_put_contents($relativePath, $image);

        return $relativePath;
    }


    private function saveFile($file)
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

        // Save the file
        $dir = 'documents/'; // Adjust the directory path as needed
        $filename = Str::random() . '.' . $type;
        $absolutePath = public_path($dir);
        $relativePath = $dir . $filename;

        if (!File::exists($absolutePath)) {
            File::makeDirectory($absolutePath, 0755, true);
        }

        file_put_contents($relativePath, $decodedFile);

        return $relativePath;
    }
}
