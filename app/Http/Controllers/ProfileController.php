<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $userId = $request->user()->id;
        $user = \Cache::remember("user_profile_{$userId}", 3600, function() use ($request) {
            return $request->user();
        });

        return view('profile.edit', [
            'user' => $user,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Optimistic Locking Check
        if ($request->has('last_updated_at') && $user->updated_at->timestamp > (int) $request->input('last_updated_at')) {
            return Redirect::route('profile.edit')->withErrors(['name' => 'تم تحديث هذه البيانات بالفعل من جهاز آخر، يرجى تحديث الصفحة والمحاولة مجدداً.']);
        }

        $user->fill($request->safe()->except('avatar'));

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar && !str_starts_with($user->avatar, 'http')) {
                \Storage::disk('public')->delete($user->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
        }

        $user->save();

        // Invalidate Cache
        \Cache::forget("user_profile_{$user->id}");

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $userId = $user->id;
        $user->delete();

        // Invalidate Cache
        \Cache::forget("user_profile_{$userId}");

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
