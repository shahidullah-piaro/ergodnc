<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\SignupRequest;
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
        return new UserCollection(User::query()->orderBy('id', 'asc')->paginate(10));
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(SignupRequest $request)
        {
            $data = $request->validated(); // Use validated data directly
            //return $data;

            // Handle file uploads 
            if (isset($data['file'])) {
                $data['file'] = $this->saveFile($data['file']);
            }
            // Handle image uploads
            if (isset($data['image'])) {
                $data['image'] = $this->saveImage($data['image']);
            }
            // Handle audio uploads
            if (isset($data['audio'])) {
                $data['audio'] = $this->saveAudio($data['audio']);
            }
            // Handle video uploads
            if (isset($data['video'])) {
                $data['video'] = $this->saveVideo($data['video']);
            }



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

            // If there is an old file, delete it from both local storage and database
            if ($user->file) {
                $localPath = storage_path('app/public/'.$user->file);  // Construct local path

                // Delete the file from local storage
                if (File::exists($localPath)) {
                    File::delete($localPath);
                }
            }
        }
        // Handle image uploads
        if (isset($data['image'])) {
            $data['image'] = $this->saveImage($data['image']);

            // If there is an old file, delete it from both local storage and database
            if ($user->image) {
                $localPath = storage_path('app/public/'.$user->image);  // Construct local path

                // Delete the file from local storage
                if (File::exists($localPath)) {
                    File::delete($localPath);
                }
            }

        }

        // Handle audio uploads
        if (isset($data['audio'])) {
            $data['audio'] = $this->saveAudio($data['audio']);

            // If there is an old file, delete it from both local storage and database
            if ($user->audio) {
                $localPath = storage_path('app/public/'.$user->audio);  // Construct local path

                // Delete the file from local storage
                if (File::exists($localPath)) {
                    File::delete($localPath);
                }
            }
        }
        // Handle video uploads
        if (isset($data['video'])) {
            $data['video'] = $this->saveVideo($data['video']);

            // If there is an old file, delete it from both local storage and database
            if ($user->video) {
                $localPath = storage_path('app/public/'.$user->video);  // Construct local path

                // Delete the file from local storage
                if (File::exists($localPath)) {
                    File::delete($localPath);
                }
            }
        }



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

        // If there is an old file, delete it from both local storage and database
        if ($user->file) {
            $localPath = storage_path('app/public/'.$user->file);  // Construct local path

            // Delete the file from local storage
            if (File::exists($localPath)) {
                File::delete($localPath);
            }
        }

        // If there is an old file, delete it from both local storage and database
        if ($user->image) {
            $localPath = storage_path('app/public/'.$user->image);  // Construct local path

            // Delete the file from local storage
            if (File::exists($localPath)) {
                File::delete($localPath);
            }
        }




        // If there is an old file, delete it from both local storage and database
        if ($user->audio) {
            $localPath = storage_path('app/public/'.$user->audio);  // Construct local path

            // Delete the file from local storage
            if (File::exists($localPath)) {
                File::delete($localPath);
            }
        }



        // If there is an old file, delete it from both local storage and database
        if ($user->video) {
            $localPath = storage_path('app/public/'.$user->video);  // Construct local path

            // Delete the file from local storage
            if (File::exists($localPath)) {
                File::delete($localPath);
            }
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

        // Save the image in local path
        $localDir = 'app/public/images/';  // Adjust this path as needed for your local storage
        $serverDir = 'images/';  // Adjust this path as needed for your server
        $file = Str::random() . '.' . $type;
        $localPath = storage_path($localDir);  // Use storage_path() for local storage
        $relativePath = $serverDir . $file;

        if (!File::exists($localPath)) {
            File::makeDirectory($localPath, 0755, true);
        }

        file_put_contents($localPath . $file, $image);  // Use $localPath for saving

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

    private function saveAudio($audio)
    {
        // Check if audio is valid base64 string and data URI
        if (!preg_match('/^data:audio\/(\w+);base64,/', $audio, $type)) {
            throw new \Exception('Invalid base64 audio or incorrect format');
        }

        // Extract base64 encoded text and extension
        $audio = substr($audio, strpos($audio, ',') + 1);
        $type = strtolower($type[1]); // mp3, wav, etc.

        // Check if file is an audio file
        if (!in_array($type, ['mp3', 'wav', 'mpeg'])) { // Adjust allowed types as needed
            throw new \Exception('Invalid audio type');
        }

        $audio = str_replace(' ', '+', $audio);
        $audio = base64_decode($audio);

        if ($audio === false) {
            throw new \Exception('base64_decode failed');
        }

        // Save the file to a local path
        $localDir = 'app/public/audio/';  // Adjust this path as needed for your local storage
        $serverDir = 'audio/';  // Adjust this path as needed for your server
        $filename = Str::random() . '.' . $type;
        $localPath = storage_path($localDir);  // Use storage_path() for local storage
        $relativePath = $serverDir . $filename;

        if (!File::exists($localPath)) {
            File::makeDirectory($localPath, 0755, true);
        }

        file_put_contents($localPath . $filename, $audio);  // Use $localPath for saving

        return $relativePath;
    }


    private function saveVideo($video)
    {
        // Check if video is valid base64 string and data URI
        if (!preg_match('/^data:video\/(\w+);base64,/', $video, $type)) {
            throw new \Exception('Invalid base64 video or incorrect format');
        }

        // Extract base64 encoded text and extension
        $video = substr($video, strpos($video, ',') + 1);
        $type = strtolower($type[1]); // mp4, webm, etc.

        // Check if file is a video file
        if (!in_array($type, ['mp4', 'webm'])) { // Adjust allowed types as needed
            throw new \Exception('Invalid video type');
        }

        $video = str_replace(' ', '+', $video);
        $video = base64_decode($video);

        if ($video === false) {
            throw new \Exception('base64_decode failed');
        }

        // Save the file to a local path
        $localDir = 'app/public/video/';  // Adjust this path as needed for your local storage
        $serverDir = 'video/';  // Adjust this path as needed for your server
        $filename = Str::random() . '.' . $type;
        $localPath = storage_path($localDir);  // Use storage_path() for local storage
        $relativePath = $serverDir . $filename;

        if (!File::exists($localPath)) {
            File::makeDirectory($localPath, 0755, true);
        }

        file_put_contents($localPath . $filename, $video);  // Use $localPath for saving

        return $relativePath;
    }

}
