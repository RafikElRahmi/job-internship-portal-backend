<?php

namespace App\Http\Controllers;

use App\Models\Posts;
use App\Models\PostulationsUser;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PostsController extends Controller
{
    public function create(Request $request)
    {
        try {
            $post = new Posts();
            $post->author = $request->author;
            $post->title = $request->title;
            $post->education = $request->education;
            $post->section = $request->section;
            $post->content = $request->content;
            $post->save();
            return response()->json([
                'status' => 200,
                'message' => 'created',
            ]);
        } catch (Exception $e) {
            Log::critical(($e));
            return response()->json([
                'status' => 404,
                'message' => "error 404",
            ]);
        }
    }
    public function update(Request $request)
    {
        try {
            $post = Posts::where('id', $request->id)->first();
            $post->title = $request->title;
            $post->education = $request->education;
            $post->section = $request->section;
            $post->content = $request->content;
            $post->save();
            $posts = Posts::where(['author'=>$post->author])->get();
            foreach ($posts as $post) {
                $user = User::where('email', $post->author)->first();
                $post->email = $user->email;
                $post->author = $user->firstname . ' ' . $user->lastname;
                $fileContents = Storage::get($user->photo);
                $fileContents = base64_encode($fileContents);
                $post->photo = $fileContents;
            }
            $sortedposts =
                collect($posts)->sortBy('created_at')->values()->all();
            return response()->json([
                'status' => 200,
                'message' => 'succes',
                'myposts' => $sortedposts,
            ]);
        } catch (Exception $e) {
            Log::critical(($e));
            return response()->json([
                'status' => 404,
                'message' => "error 404",
            ]);
        }
    }
    public function delete(Request $request)
    {
        try {
            $id = $request->id;
            PostulationsUser::where(['postid' => $id])->delete();
            $post = Posts::where('id', $id)->first();
            $post->delete();
            return response()->json([
                'status' => 200,
                'message' => 'post deleted',
            ]);
        } catch (Exception $e) {
            Log::critical(($e));
            return response()->json([
                'status' => 404,
                'message' => 'deletion fail',
            ]);
        }
    }
    public function getmine(Request $request)
    {
        try {
            $email = $request->email;
            $userExists = User::where('email', $email)->exists();
            if ($userExists) {
                $posts = Posts::where(['author' => $email])->get();
                foreach ($posts as $post) {
                    $user = User::where('email', $post->author)->first();
                    $post->email = $user->email;
                    $post->author = $user->firstname . ' ' . $user->lastname;
                    $fileContents = Storage::get($user->photo);
                    $fileContents = base64_encode($fileContents);
                    $post->photo = $fileContents;
                }
                $sortedposts =
                collect($posts)->sortBy('created_at')->values()->all();
                return response()->json([
                    'status' => 200,
                    'message' => 'succes',
                    'posts' => $sortedposts
                ]);
            }
            return response()->json([
                'status' => 200,
                'message' => 'user not found',
            ]);
        } catch (Exception $e) {
            Log::critical(($e));
            return response()->json([
                'status' => 404,
                'message' => 'error 404',
            ]);
        }
    }
    public function usergetall(Request $request)
    {
        try {
            $posts = Posts::get();
            foreach ($posts as $post) {
                $user = User::where('email', $post->author)->first();
                $post->email = $post->author;
                $post->author = $user->firstname . ' ' . $user->lastname;
                $fileContents = Storage::get($user->photo);
                $fileContents = base64_encode($fileContents);
                $post->photo = $fileContents;
                $userid = User::where('email', $request->email)->first()->id;
                $application = PostulationsUser::where(['postid' => $post->id])->where('userid' , $userid)->first();
                if ($application) {
                    $post->application =  true;
                } else {
                    $post->application = false;
                }
            }
            $sortedposts =
                collect($posts)->sortBy('created_at')->values()->all();
            return response()->json([
                'status' => 200,
                'message' => 'succes',
                'posts' =>  $sortedposts,
            ]);
        } catch (Exception $e) {
            Log::critical(($e));
            return response()->json([
                'status' => 404,
                'message' => 'error 404',
            ]);
        }
    }
    public function filter(Request $request)
    {
        try {
            $posts = new Posts();
            if ($request->education == 'all' && $request->section == 'all') {
                $posts = Posts::get();
            } else if ($request->section == 'all') {
                $posts = Posts::where('education', $request->education)->get();
            } else if ($request->education == 'all') {
                $posts = Posts::where('section', $request->section)->get();
            } else {
                $posts = Posts::where('education', $request->education)->where('section', $request->section)->get();
            }
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
            $sortedposts =
                collect($posts)->sortBy('created_at')->values()->all();
            return response()->json([
                'status' => 200,
                'message' => 'succes',
                'posts' =>  $sortedposts,
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
