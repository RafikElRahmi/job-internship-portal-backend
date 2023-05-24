<?php

namespace App\Http\Controllers;

use App\Models\Posts;
use App\Models\PostulationsUser;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PostulationsUserController extends Controller
{
    public function store(Request $request)
    {
        try {
            $userid = User::where('email', $request->email)->first()->id;
            $postulationsUser = PostulationsUser::where('postid', $request->postid)->where('userid', $userid)->first();
            if (!$postulationsUser) {
                $file = $request->file('file');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('/public/files', $fileName);
                $author = Posts::find($request->postid)->author;
                $userid = User::where('email', $request->email)->first()->id;
                $postulate = new PostulationsUser();
                $postulate->author = $author;
                $postulate->postid = $request->postid;
                $postulate->userid = $userid;
                $postulate->filepath = $path;
                $postulate->status = 'pending';
                $postulate->save();
                $posts = Posts::get();
                foreach ($posts as $post) {
                    $user = User::where('email', $post->author)->first();
                    $post->email = $post->author;
                    $post->author = $user->firstname . ' ' . $user->lastname;
                    $fileContents = Storage::get($user->photo);
                    $fileContents = base64_encode($fileContents);
                    $post->photo = $fileContents;
                    $userid = User::where('email', $request->email)->first()->id;
                    $application = PostulationsUser::where(['postid' => $post->id])->where('userid', $userid)->first();
                    if ($application) {
                        $post->application =  true;
                    } else {
                        $post->application = false;
                    }
                }
                $sortedposts = collect($posts)->sortBy('created_at')->values()->all();
                return response()->json([
                    'status' => 200,
                    'message' => 'postulated',
                    'posts' =>  $sortedposts,
                ]);
            } else {
                return response()->json([
                    'status' => 200,
                    'message' => 'postulation already found',
                ]);
            }
        } catch (Exception $e) {
            Log::critical(($e));
            return response()->json([
                'status' => 404,
                'message' => 'error 404',
            ]);
        }
    }
    public function getforuser(Request $request)
    {
        try {
            $user = new User();
            $userid = $user->where('email', $request->email)->first()->id;
            $postulation = new PostulationsUser();
            $postulation = $postulation->where(['userid' => $userid])->get();
            foreach ($postulation as $postulate) {
                $user = User::where('email', $postulate->author)->first();
                $postulate->email = $postulate->author;
                $postulate->author = $user->firstname . ' ' . $user->lastname;
                $fileContents = Storage::get($user->photo);
                $fileContents = base64_encode($fileContents);
                $postulate->photo = $fileContents;
                $post = Posts::where('id', $postulate->postid)->first();
                $postulate->title = $post->title;
            }
            $sortedpostulation = collect($postulation)->sortBy('created_at')->values()->all();
            return response()->json([
                'status' => 200,
                'message' => 'succes',
                'postulations' => $sortedpostulation,
            ]);
        } catch (Exception $e) {
            Log::critical(($e));
            return response()->json([
                'status' => 404,
                'message' => $e,
            ]);
        }
    }
    public function deletepostulation(Request $request)
    {
        try {
            $deleted = new PostulationsUser();
            $deleted = $deleted->where('id', $request->id)->delete();
            $user = new User();
            $userid = $user->where('email', $request->email)->first()->id;
            $postulation = new PostulationsUser();
            $postulation = $postulation->where(['userid' => $userid])->get();
            foreach ($postulation as $postulate) {
                $user = User::where('email', $postulate->author)->first();
                $postulate->email = $postulate->author;
                $postulate->author = $user->firstname . ' ' . $user->lastname;
                $fileContents = Storage::get($user->photo);
                $fileContents = base64_encode($fileContents);
                $postulate->photo = $fileContents;
                $post = Posts::where('id', $postulate->postid)->first();
                $postulate->title = $post->title;
            }
            $sortedpostulation = collect($postulation)->sortBy('created_at')->values()->all();
            return response()->json([
                'status' => 200,
                'message' => 'succes',
                'postulations' => $sortedpostulation,
            ]);
        } catch (Exception $e) {
            Log::critical(($e));
            return response()->json([
                'status' => 404,
                'message' => $e,
            ]);
        }
    }
    public function getforsociety(Request $request)
    {
        try {
            $postulation = new PostulationsUser();
            $postulation = $postulation->where(['author' => $request->email])->get();
            foreach ($postulation as $postule) {
                $user = User::where('id', $postule->userid)->first();
                $postule->email = $user->email;
                $postule->author = $user->firstname . ' ' . $user->lastname;
                $fileContents = Storage::get($user->photo);
                $fileContents = base64_encode($fileContents);
                $postule->photo = $fileContents;
                $post = Posts::where('id', $postule->postid)->first();
                $postule->title = $post->title;
                $postule->content = $post->content;
            }
            $sortedpostulation = collect($postulation)->sortBy('created_at')->values()->all();
            return response()->json([
                'status' => 200,
                'message' => 'succes',
                'postulation' => $sortedpostulation,
            ]);
        } catch (Exception $e) {
            Log::critical(($e));
            return response()->json([
                'status' => 404,
                'message' => $e,
            ]);
        }
    }
    public function getpostulation(Request $request)
    {
        try {
            $postulation = new PostulationsUser();
            $postulation = $postulation->where('id', $request->idpostulation)->first();
            $user = User::where('email', $postulation->author)->first();
            $postulation->email = $user->email;
            $postulation->author = $user->firstname . ' ' . $user->lastname;
            $fileContents = Storage::get($user->photo);
            $fileContents = base64_encode($fileContents);
            $postulation->photo = $fileContents;
            $post = Posts::where('id', $postulation->postid)->first();
            $postulation->title = $post->title;
            $postulation->content = $post->content;
            return response()->json([
                'status' => 200,
                'message' => 'succes',
                'postulation' => $postulation,
            ]);
        } catch (Exception $e) {
            Log::critical(($e));
            return response()->json([
                'status' => 404,
                'message' => $e,
            ]);
        }
    }
    public function getpostulationsociety(Request $request)
    {
        try {
            $postulation = new PostulationsUser();
            $postulation = $postulation->where('id', $request->idpostulation)->first();
            $user = User::where('id', $postulation->userid)->first();
            $postulation->email = $user->email;
            $postulation->author = $user->firstname . ' ' . $user->lastname;
            $fileContents = Storage::get($user->photo);
            $fileContents = base64_encode($fileContents);
            $postulation->photo = $fileContents;
            $post = Posts::where('id', $postulation->postid)->first();
            $postulation->title = $post->title;
            $postulation->content = $post->content;
            return response()->json([
                'status' => 200,
                'message' => 'succes',
                'postulation' => $postulation,
            ]);
        } catch (Exception $e) {
            Log::critical(($e));
            return response()->json([
                'status' => 404,
                'message' => $e,
            ]);
        }
    }
    public function editpostulationsociety(Request $request)
    {
        try {
            $edited = PostulationsUser::where('id', $request->id)->first();
            $edited->status = $request->status;
            $edited->save();
            $postulation = new PostulationsUser();
            $postulation = $postulation->where(['author' => $request->email])->get();
            foreach ($postulation as $postule) {
                $user = User::where('id', $postule->userid)->first();
                $postule->email = $user->email;
                $postule->author = $user->firstname . ' ' . $user->lastname;
                $fileContents = Storage::get($user->photo);
                $fileContents = base64_encode($fileContents);
                $postule->photo = $fileContents;
                $post = Posts::where('id', $postule->postid)->first();
                $postule->title = $post->title;
                $postule->content = $post->content;
            }
            $sortedpostulation =
                collect($postulation)->sortBy('created_at')->values()->all();
            return response()->json([
                'status' => 200,
                'message' => 'succes',
                'postulation' => $sortedpostulation,
            ]);
        } catch (Exception $e) {
            Log::critical(($e));
            return response()->json([
                'status' => 404,
                'message' => $e,
            ]);
        }
    }
    public function downloadcv(Request $request)
    {
        try {
            $file = Storage::get($request->path);
            $file = base64_encode($file);
            return response()->json([
                'status' => 200,
                'message' => 'succes',
                'file' =>  $file,
            ]);
        } catch (Exception $e) {
            Log::critical(($e));
            return response()->json([
                'status' => 404,
                'message' => 'error 404',
            ]);
        }
    }
}
