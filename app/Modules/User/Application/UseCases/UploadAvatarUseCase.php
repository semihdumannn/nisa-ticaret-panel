<?php

namespace App\Modules\User\Application\UseCases;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadAvatarUseCase
{
    public function execute(User $user, UploadedFile $file): User
    {
        // Delete old avatar if exists
        if ($user->profile?->avatar_url) {
            $oldPath = parse_url($user->profile->avatar_url, PHP_URL_PATH);
            if ($oldPath) {
                Storage::disk('public')->delete(ltrim($oldPath, '/'));
            }
        }

        // Store new avatar
        $extension = $file->getClientOriginalExtension();
        $filename  = 'avatars/' . $user->id . '_' . Str::random(8) . '.' . $extension;
        $file->storeAs('', $filename, 'public');

        $avatarUrl = asset('storage/' . $filename);

        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            ['avatar_url' => $avatarUrl],
        );

        $user->load('profile');

        return $user;
    }
}
