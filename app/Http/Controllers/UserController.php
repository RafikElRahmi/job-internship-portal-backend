<?php

namespace App\Http\Controllers;

use App\Models\posts;
use App\Models\Postulations;
use App\Models\PostulationsUser;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use PhpParser\Node\Stmt\TryCatch;
use stdClass;

class UserController extends Controller
{
    public function store(Request $request)
    {
        try {
            $email = $request->email;
            $userExists = User::where('email', $email)->exists();
            if ($userExists) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Email already exists',

                ]);
            }
            $user = new User();
            $user->firstname = $request->firstname;
            $user->lastname = $request->lastname;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->education = $request->education;
            $user->section = $request->section;
            $user->adress = '';
            $user->fax = '';
            $user->password = Hash::make($request->password);
            $user->role = 'user';
            $user->photo = '/public/profile/unkown.png';
            $user->save();
            return response()->json([
                'status' => 200,
                'message' => 'register successfully',
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'email' => $user->email,
                'phone' => $user->phone,
                'education' => $user->education,
                'section' => $user->section,
                'role' => $user->role,
                'photo' => $user->photo
            ]);
        } catch (Exception $e) {
            Log::critical(($e));
            return response()->json([
                'status' => 404,
                'message' => 'register fail',
            ]);
        }
    }
    public function login(Request $request)
    {
        try {
            $user = User::where(['email' => $request->email])->first();
            if ($user) {
                if (Hash::check($request->password, $user->password)) {
                    return response()->json([
                        'status' => 200,
                        'message' => 'login successfully',
                        'firstname' => $user->firstname,
                        'lastname' => $user->lastname,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'education' => $user->education,
                        'section' => $user->section,
                        'adress' => $user->adress,
                        'fax' => $user->fax,
                        'role' => $user->role,
                        'photo' => $user->photo
                    ]);
                } else {
                    return response()->json([
                        'status' => 200,
                        'message' => 'wrong password',
                    ]);
                }
            } else {
                return response()->json([
                    'status' => 200,
                    'message' => 'wrong email',
                ]);
            }
        } catch (Exception $e) {
            Log::critical(($e));
            return response()->json([
                'status' => 404,
                'message' => 'login fail',
            ]);
        }
    }
    public function update(Request $request)
    {
        $fileContents = Storage::get($request->path);
        $fileContents = base64_encode($fileContents);
        return response()->json($fileContents);
    }
    public function upload(Request $request)
    {
        $user = User::where(['email' => $request->email])->first();
        $ImgName = time() . '_' . $request->file('image')->getClientOriginalName();
        $ImgPath = $request->file('image')->storeAs('/public/profile', $ImgName);
        $user->photo = $ImgPath;
        $user->save();
        $fileContents = Storage::get($ImgPath);
        $fileContents = base64_encode($fileContents);
        return response()->json($fileContents);
    }
    public function refresh(Request $request)
    {
        $user = User::where(['email' => $request->email])->first();
        return response()->json([
            'status' => 200,
            'message' => 'login successfully',
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'email' => $user->email,
            'phone' => $user->phone,
            'education' => $user->education,
            'section' => $user->section,
            'role' => $user->role,
            'photo' => $user->photo
        ]);
    }
    public function getprofile(Request $request)
    {
        try {
            $user = User::where(['email' => $request->email])->first();
            $fileContents = Storage::get($user->photo);
            $fileContents = base64_encode($fileContents);
            return response()->json([
                'status' => 200,
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'email' => $user->email,
                'phone' => $user->phone,
                'fax' => $user->fax,
                'adress' => $user->adress,
                'education' => $user->education,
                'section' => $user->section,
                'role' => $user->role,
                'photo' => $fileContents
            ]);
        } catch (Exception $e) {
            Log::critical(($e));
            return response()->json([
                'status' => 404
            ]);
        }
    }
    public function getallusers()
    {
        try {
            $users = User::where(['role' => 'user'])->get();
            foreach ($users as $user) {
                $fileContents = Storage::get($user->photo);
                $user->photo = base64_encode($fileContents);
                $user->name = $user->firstname . ' ' . $user->lastname;
            }
            $sortedusers =
                collect($users)->sortBy('name')->values()->all();
            return response()->json([
                'status' => 200,
                'users' => $sortedusers
            ]);
        } catch (Exception $e) {
            Log::critical(($e));
            return response()->json([
                'status' => 404
            ]);
        }
    }
    public function getallsocieties()
    {
        try {
            $users = User::where(['role' => 'society'])->get();
            foreach ($users as $user) {
                $fileContents = Storage::get($user->photo);
                $user->photo = base64_encode($fileContents);
                $user->name = $user->firstname . ' ' . $user->lastname;
            }
            $sortedsocieties =
                collect($users)->sortBy('name')->values()->all();
            return response()->json([
                'status' => 200,
                'users' => $sortedsocieties
            ]);
        } catch (Exception $e) {
            Log::critical(($e));
            return response()->json([
                'status' => 404
            ]);
        }
    }
    public function getadmins()
    {
        try {
            $users =
                User::where('role', 'admin')
                ->whereNotIn('email', ['superadmin@gmail.com'])
                ->get();
            foreach ($users as $user) {
                $fileContents = Storage::get($user->photo);
                $user->photo = base64_encode($fileContents);
                $user->name = $user->firstname . ' ' . $user->lastname;
            }
            $sortedadmins =
                collect($users)->sortBy('name')->values()->all();
            return response()->json([
                'status' => 200,
                'users' => $sortedadmins
            ]);
        } catch (Exception $e) {
            Log::critical(($e));
            return response()->json([
                'status' => 404
            ]);
        }
    }
    public function getuser(Request $request)
    {
        try {
            $user = User::where('id', $request->id)->first();
            $fileContents = Storage::get($user->photo);
            $user->photo = base64_encode($fileContents);
            $user->name = $user->firstname . ' ' . $user->lastname;
            return response()->json([
                'status' => 200,
                'user' => $user
            ]);
        } catch (Exception $e) {
            Log::critical(($e));
            return response()->json([
                'status' => 404
            ]);
        }
    }
    public function deleteuser(Request $request)
    {
        try {
            $user = User::where('id', $request->id)->first();
            $role = $user->role;
            Posts::where(['author' => $user->email])->delete();
            PostulationsUser::where(['userid' => $user->id])->delete();
            PostulationsUser::where(['author' => $user->email])->delete();
            Postulations::where(['email' => $user->email])->delete();
            $user->delete();
            if ($role === 'user') {
                $users = User::where(['role' => 'user'])->get();
                foreach ($users as $user) {
                    $fileContents = Storage::get($user->photo);
                    $user->photo = base64_encode($fileContents);
                    $user->name = $user->firstname . ' ' . $user->lastname;
                }
                $sortedusers =
                    collect($users)->sortBy('name')->values()->all();
            } else if ($role === 'society') {
                $users = User::where(['role' => 'society'])->get();
                foreach ($users as $user) {
                    $fileContents = Storage::get($user->photo);
                    $user->photo = base64_encode($fileContents);
                    $user->name = $user->firstname . ' ' . $user->lastname;
                }
                $sortedusers =
                    collect($users)->sortBy('name')->values()->all();
            }
            return response()->json([
                'status' => 200,
                'message' => 'deleted',
                'role' => $role,
                'users' => $sortedusers

            ]);
        } catch (Exception $e) {
            Log::critical(($e));
            return response()->json([
                'status' => 404
            ]);
        }
    }
    public function deleteadmin(Request $request)
    {
        try {
            $user = User::where('id', $request->id)->first();
            Posts::where(['author' => $user->email])->delete();
            PostulationsUser::where(['userid' => $user->id])->delete();
            Postulations::where(['email' => $user->email])->delete();
            $user->delete();
            $users =
                User::where('role', 'admin')
                ->whereNotIn('email', ['superadmin@gmail.com'])
                ->get();
            foreach ($users as $user) {
                $fileContents = Storage::get($user->photo);
                $user->photo = base64_encode($fileContents);
                $user->name = $user->firstname . ' ' . $user->lastname;
            }
            $sortedadmins =
                collect($users)->sortBy('name')->values()->all();
            return response()->json([
                'status' => 200,
                'message' => 'deleted',
                'users' => $sortedadmins

            ]);
        } catch (Exception $e) {
            Log::critical(($e));
            return response()->json([
                'status' => 404
            ]);
        }
    }
    public function upgradeuser(Request $request)
    {
        try {
            $user = User::where('id', $request->id)->first();
            Posts::where(['author' => $user->email])->delete();
            PostulationsUser::where(['userid' => $user->id])->delete();
            Postulations::where(['email' => $user->email])->delete();
            $user->role = 'admin';
            $user->education = '';
            $user->section = '';
            $user->fax = '';
            $user->adress = '';
            $user->save();
            $users = User::where(['role' => 'user'])->get();
            foreach ($users as $user) {
                $fileContents = Storage::get($user->photo);
                $user->photo = base64_encode($fileContents);
                $user->name = $user->firstname . ' ' . $user->lastname;
            }
            $sortedusers =
                collect($users)->sortBy('name')->values()->all();
            return response()->json([
                'status' => 200,
                'message' => 'deleted',
                'users' => $sortedusers

            ]);
        } catch (Exception $e) {
            Log::critical(($e));
            return response()->json([
                'status' => 404
            ]);
        }
    }
    public function dashboard(Request $request)
    {
        try {
            $users_count = new stdClass();
            $users_count->admins = User::where(['role' => 'admin'])->get()->count();
            $users_count->societies = User::where(['role' => 'society'])->get()->count();
            $users_count->users = User::where(['role' => 'user'])->get()->count();
            $users = new stdClass();
            $users->bachelor = User::where(['education' => "Bachelor's degree"])->get()->count();
            $users->master = User::where(['education' => "Master's degree"])->get()->count();
            $users->doctorate = User::where(['education' => "Doctorate degree"])->get()->count();
            $users->programming = User::where(['section' => "Computer programming"])->get()->count();
            $users->network = User::where(['section' => "Computer network"])->get()->count();
            $users->intelligence = User::where(['section' => "Artificial intelligence"])->get()->count();
            $users->science = User::where(['section' => "Data science"])->get()->count();
            $users->cyber = User::where(['section' => "Cyber security"])->get()->count();
            $posts = new stdClass();
            $posts->bachelor = Posts::where(['education' => "Bachelor's degree"])->get()->count();
            $posts->master = Posts::where(['education' => "Master's degree"])->get()->count();
            $posts->doctorate = Posts::where(['education' => "Doctorate degree"])->get()->count();
            $posts->programming = Posts::where(['section' => "Computer programming"])->get()->count();
            $posts->network = Posts::where(['section' => "Computer network"])->get()->count();
            $posts->intelligence = Posts::where(['section' => "Artificial intelligence"])->get()->count();
            $posts->science = Posts::where(['section' => "Data science"])->get()->count();
            $posts->cyber = Posts::where(['section' => "Cyber security"])->get()->count();
            $postulations = new stdClass();
            $postulations->bachelor = 0;
            $postulations->master = 0;
            $postulations->doctorate = 0;
            $postulations->programming = 0;
            $postulations->network = 0;
            $postulations->intelligence = 0;
            $postulations->science = 0;
            $postulations->cyber = 0;
            $postulationschecking = PostulationsUser::get();
            foreach ($postulationschecking as $postulationchecking) {
                $post = Posts::where('id', $postulationchecking->postid)->first();
                if ($post->education == "Bachelor's degree") {
                    $postulations->bachelor = $postulations->bachelor + 1;
                } else if ($post->education == "Master's degree") {
                    $postulations->master = $postulations->master + 1;
                } else if ($post->education == "Doctorate degree") {
                    $postulations->doctorate = $postulations->doctorate + 1;
                }
                if ($post->section == "Computer programming") {
                    $postulations->programming = $postulations->programming + 1;
                } else if ($post->section == "Computer network") {
                    $postulations->network = $postulations->network + 1;
                } else if ($post->section == "Artificial intelligence") {
                    $postulations->intelligence = $postulations->intelligence + 1;
                } else if ($post->section == "Data science") {
                    $postulations->science = $postulations->science + 1;
                } else if ($post->section == "Cyber security") {
                    $postulations->cyber = $postulations->cyber + 1;
                }
            }
            $postsnumber = new stdClass();
            $postsnumber->posts = Posts::all()->count();
            $postsnumber->postulations = PostulationsUser::all()->count();
            return response()->json([
                'status' => 200,
                'count' =>  $users_count,
                'users' =>  $users,
                'posts' =>  $posts,
                'postulations' =>  $postulations,
                'postsnumber' => $postsnumber,
            ]);
        } catch (Exception $e) {
            Log::critical(($e));
            return response()->json([
                'status' => 404,
                'message' => 'error 404',
            ]);
        }
    }
    public function dashboardsociety(Request $request)
    {
        try {
            $posts = new stdClass();
            $posts->bachelor = Posts::where(['author' => $request->email])->where(['education' => "Bachelor's degree"])->get()->count();
            $posts->master = Posts::where(['author' => $request->email])->where(['education' => "Master's degree"])->get()->count();
            $posts->doctorate = Posts::where(['author' => $request->email])->where(['education' => "Doctorate degree"])->get()->count();
            $posts->programming = Posts::where(['author' => $request->email])->where(['section' => "Computer programming"])->get()->count();
            $posts->network = Posts::where(['author' => $request->email])->where(['section' => "Computer network"])->get()->count();
            $posts->intelligence = Posts::where(['author' => $request->email])->where(['section' => "Artificial intelligence"])->get()->count();
            $posts->science = Posts::where(['author' => $request->email])->where(['section' => "Data science"])->get()->count();
            $posts->cyber = Posts::where(['author' => $request->email])->where(['section' => "Cyber security"])->get()->count();
            $postulations = new stdClass();
            $postulations->bachelor = 0;
            $postulations->master = 0;
            $postulations->doctorate = 0;
            $postulations->programming = 0;
            $postulations->network = 0;
            $postulations->intelligence = 0;
            $postulations->science = 0;
            $postulations->cyber = 0;
            $postulationschecking = PostulationsUser::where(['author' => $request->email])->get();
            foreach ($postulationschecking as $postulationchecking) {
                $post = Posts::where('id', $postulationchecking->postid)->first();
                if ($post->education == "Bachelor's degree") {
                    $postulations->bachelor = $postulations->bachelor + 1;
                } else if ($post->education == "Master's degree") {
                    $postulations->master = $postulations->master + 1;
                } else if ($post->education == "Doctorate degree") {
                    $postulations->doctorate = $postulations->doctorate + 1;
                }
                if ($post->section == "Computer programming") {
                    $postulations->programming = $postulations->programming + 1;
                } else if ($post->section == "Computer network") {
                    $postulations->network = $postulations->network + 1;
                } else if ($post->section == "Artificial intelligence") {
                    $postulations->intelligence = $postulations->intelligence + 1;
                } else if ($post->section == "Data science") {
                    $postulations->science = $postulations->science + 1;
                } else if ($post->section == "Cyber security") {
                    $postulations->cyber = $postulations->cyber + 1;
                }
            }
            $postsnumber = new stdClass();
            $postsnumber->posts = Posts::where(['author' => $request->email])->count();
            $postsnumber->postulations = PostulationsUser::where(['author' => $request->email])->count();
            return response()->json([
                'status' => 200,
                'posts' =>  $posts,
                'postulations' =>  $postulations,
                'postsnumber' => $postsnumber,
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

