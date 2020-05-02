<?php

namespace App\Http\Controllers;

use App\Http\Resources\Users;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    function signUp(Request $request)
    {
        $messages = [
            'first_name.required' => 'Пожалуйста введите ваше имя',
            'surname.required' => 'Пожалуйста введите фамилию',
            'phone.required' => 'Пожалуйста введите номер телефона',
            'password.required' => 'Пожалуйста введите пароль',
            'password_confirmation.required' => 'Пожалуйста повторите пароль',
            'phone.size' => 'Телефон должен быть в 11 символов',
            'password_confirmation.same' => 'Пароли не совпадают',
        ];
        $validation = Validator::make($request->input(), [
            'first_name' => ['required'],
            'surname' => ['required'],
            'phone' => ['required', 'size:11'],
            'password' => ['required'],
            'password_confirmation' => ['required', 'same:password']
        ], $messages);
        if ($validation->fails()) {
            return response()->json(collect($validation->errors()->getMessages())->map(function ($item, $key) {
                return $item[0];
            })->all(), 422);
        } else {
            if (User::where('phone', $request->input('phone'))->count() != 0) {
                return response()->json(['phone' => 'Такой номер телефона уже зарегистрирован'], 422);

            } else {
                /** @var User $new_user */
                $new_user = User::create([
                    'first_name' => $request->input('first_name'),
                    'surname' => $request->input('surname'),
                    'phone' => $request->input('phone'),
                    'password' => Hash::make($request->input('password'))
                ]);
                return response()->json(['id' => $new_user->id], 201);
            }

        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    function login(Request $request)
    {
        $messages = [
            'phone.required' => 'Введите телефон',
            'password.required' => 'Введите пароль'
        ];
        $validation = Validator::make($request->input(), [
            'phone' => ['required'],
            'password' => ['required']
        ], $messages);
        if ($validation->fails()) {
            return response()->json(collect($validation->errors()->getMessages())->map(function ($item, $key) {
                return $item[0];
            })->all(), 422);
        } else {
            /** @var User $user */
            $user = User::where('phone', $request->input('phone'))->first();
            if (!is_null($user) && Hash::check($request->input('password'), $user->password)) {
                $newToken = Str::random(32);
                $user->wsr_token = Hash::make($newToken);
                $user->save();
                return response()->json(['token' => $newToken], 200);
            } else {
                return response()->json(['login' => 'Неверный логин или пароль'], 404);
            }
        }
    }


    /**
     * @return \Illuminate\Http\JsonResponse
     */
    function logout()
    {
        /** @var User|null $user */
        $user = Auth::user();
        $user->wsr_token = null;
        $user->save();
        return response()->json();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    function searchUser(Request $request)
    {
        if ($request->has('search')) {
            $user_params = Str::of($request->query('search'))->explode(' ');
            switch (count($user_params)) {
                case 3:
                    $users_collection = (User::where(
                        [
                            ['first_name', 'like', "%$user_params[0]%"],
                            ['surname', 'like', "%$user_params[1]%"],
                            ['phone', 'like', "%$user_params[2]%"]
                        ])
                        ->get()
                    );
                    break;
                case 2:
                    $users_collection = (
                    User::where([
                        ['first_name', 'like', "%$user_params[0]%"],
                        ['surname', 'like', "%$user_params[1]%"],
                    ])
                        ->orWhere([
                            ['first_name', 'like', "%$user_params[0]%"],
                            ['phone', 'like', "%$user_params[1]%"],
                        ])
                        ->orWhere([
                            ['surname', 'like', "%$user_params[0]%"],
                            ['phone', 'like', "%$user_params[1]%"],
                        ])
                        ->get()
                    );
                    break;
                case 1:
                    $users_collection = (User::where('first_name', 'like', "%$user_params[0]%")
                        ->orWhere('surname', 'like', "%$user_params[0]%")
                        ->orWhere('phone', 'like', "%$user_params[0]%")
                        ->get()
                    );
                    break;
                default:
                    return response()->json('', 404);
                    break;

            }
            return response()->json(Users::collection($users_collection->unique('id')->sortBy('id')->values()->all()));
        } else {
            return response()->json('', 404);
        }
    }
}
