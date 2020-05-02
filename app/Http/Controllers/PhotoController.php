<?php

namespace App\Http\Controllers;

use App\Http\Resources\Photos;
use App\Photo;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PhotoController extends Controller
{

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    function createImage(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $validation = Validator::make($request->all(), [
            'photo' => ['required', 'file', 'mimes:jpeg,jpg,png'],
        ]);
        if ($validation->fails()) {
            return response()->json(collect($validation->errors()->getMessages())->map(function ($item, $key) {
                return $item[0];
            })->all(), 422);
        }
        $new_image_path = $request->file('photo')->store('');
        $newImage = new Photo();
        $newImage->path = $new_image_path;
        $user->images()->save($newImage);
        $newImage->refresh();
        return response()->json([
            'id' => $newImage->id,
            'name' => $newImage->name,
            'url' => asset("storage/{$newImage->path}")
        ], 201);
    }

    /**
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    function editImage(Request $request, $id)
    {
        /** @var User $user */
        $user = Auth::user();
        if (is_null(Photo::find($id)) || Gate::forUser($user)->denies('access-to-photo', $id)) {
            return response()->json('', 403);
        }
        $messages = [
            'name.required' => 'Название не может быть пустым',
            'photo.file' => 'Ну удалось загрузить файл',
            'photo.image' => 'Формат файла запрещён протоколами безопасности:(',
        ];
        $validation = Validator::make($request->all(), [
            'name' => ['sometimes', 'required'],
            'photo' => ['sometimes', 'file', 'image'],
        ], $messages);
        if ($validation->fails()) {
            return response()->json(collect($validation->errors()->getMessages())->map(function ($item, $key) {
                return $item[0];
            })->all(), 422);
        }

        /** @var Photo $photo */
        $photo = Photo::find($id);
        if ($request->has('name')) {
            $photo->name = $request->input('name');
        }
        if ($request->hasFile('photo')) {
            $new_path = $request->file('photo')->store('');
            if (Storage::exists("storage/{$photo->path}")) {
                Storage::delete("storage/{$photo->path}");
            }
            $photo->path = $new_path;
        }
        $photo->save();
        return response()->json([
            'id' => $photo->id,
            'name' => $photo->name,
            'url' => asset("storage/{$photo->path}")
        ], 200);

    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    function allPhotos(Request $request)
    {
        return response()->json(Photos::collection(Photo::with('users_via_sharing')->get()));
    }

    /**
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    function getPhoto(Request $request, $id)
    {
        return response()->json(new Photos(Photo::with('users_via_sharing')->find($id)));
    }

    /**
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    function deletePhoto(Request $request, $id)
    {
        $user = Auth::user();
        if (Gate::forUser($user)->denies('access-to-photo', $id)) {
            return response()->json('', 403);
        } else {
            $photo = Photo::find($id);
            if (Storage::exists("storage/{$photo->path}")) {
                Storage::delete("storage/{$photo->path}");
            }
            try {
                $photo->delete();
            } catch (\Exception $e) {
                return response()->json('', 403);
            }
            return response()->json('', 204);
        }
    }

    /**
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    function sharePhotos(Request $request, $id)
    {
        if (!$request->has('photos')) {
            return response()->json('', 404);
        }
        /** @var User $current_user */
        $current_user = Auth::user();
        /** @var User $sharing_user */
        $sharing_user = User::with('shared_to_me_images')->find($id);
        /** @var int[] $photos_to_share */
        $photos_to_share = $request->input('photos');
        foreach ($photos_to_share as $photo_id) {
            if (Gate::forUser($current_user)->denies('access-to-photo', $photo_id)) {
                return response()->json('', 404);
            }
            /** @var Photo $tmp_photo */
            $tmp_photo = Photo::find($photo_id);
            if (!(collect($sharing_user->shared_to_me_images)->contains($tmp_photo))) {
                $sharing_user->shared_to_me_images()->attach($tmp_photo);
            }
        }
        return response()->json([
            'existing_photos' => Photos::collection(Photo::with('users_via_sharing')->get())
        ], 201);

    }

}
