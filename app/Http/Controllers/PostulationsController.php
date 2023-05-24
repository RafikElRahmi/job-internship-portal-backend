<?php

namespace App\Http\Controllers;

use App\Models\Postulations;
use App\Models\PostulationsUser;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpParser\Node\Expr\Cast\Object_;
use stdClass;

class PostulationsController extends Controller
{
    public function store(Request $request)
    {
        try {
            $email = $request->email;
            $userExists = User::where('email', $email)->exists();
            if ($userExists) {
                $posted = Postulations::where('email', $email)->exists();
                if (!$posted) {
                    $Postulation = new Postulations();
                    $Postulation->email = $request->email;
                    $Postulation->adress = $request->adress;
                    $Postulation->fax = $request->fax;
                    $Postulation->status = 'pending';
                    $Postulation->save();
                    return response()->json([
                        'status' => 200,
                        'message' => 'created',
                    ]);
                }
                return response()->json([
                    'status' => 200,
                    'message' => 'already exist',

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
    public function check(Request $request)
    {
        try {
            $email = $request->email;
            $userExists = User::where('email', $email)->exists();
            if ($userExists) {
                $posted = Postulations::where(['email' => $email])->first();
                if (!$posted) {
                    return response()->json([
                        'status' => 200,
                        'message' => 'no postulation',
                    ]);
                }
                return response()->json([
                    'status' => 200,
                    'message' => 'postulation found',
                    'email' => $posted->email,
                    'adress' => $posted->adress,
                    'fax' => $posted->fax,
                    'statusPostulation' => $posted->status,

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
    public function getall(Request $request)
    {
        try {
            $postulations = Postulations::all();
            foreach ($postulations as $postulation) {
                $user = User::where('email', $postulation->email)->first();
                $postulation->name = $user->firstname . ' ' . $user->lastname;
                $fileContents = Storage::get($user->photo);
                $postulation->photo = base64_encode($fileContents);
            }
            return response()->json([
                'status' => 200,
                'message' => 'user not found',
                'postulations' =>  $postulations,
            ]);
        } catch (Exception $e) {
            Log::critical(($e));
            return response()->json([
                'status' => 404,
                'message' => 'error 404',
            ]);
        }
    }
    public function editstate(Request $request)
    {
        try {
            $postulate = Postulations::where('id', $request->id)->first();
            $postulate->status = $request->state;
            if ($request->state === 'approved'){
                $user = User::where('email', $postulate->email)->first();
                $user->role = 'society';
                $user->education = '';
                $user->section = '';
                $user->adress = $postulate->adress;
                $user->fax = $postulate->fax;
                $user->save();
                PostulationsUser::where(['userid'=> $user->id])->delete();
            }
            $postulate->save();
            $postulations = Postulations::all();
            foreach ($postulations as $postulation) {
                $user = User::where('email', $postulation->email)->first();
                $postulation->name = $user->firstname . ' ' . $user->lastname;
                $fileContents = Storage::get($user->photo);
                $postulation->photo = base64_encode($fileContents);
            }
            return response()->json([
                'status' => 200,
                'message' => 'user not found',
                'postulations' =>  $postulations,
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
