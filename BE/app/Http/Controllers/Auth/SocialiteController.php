<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    /**
     * Stuurt de gebruiker naar GitHub om in te loggen.
     * Omdat dit via routes/web.php loopt, gebruikt Socialite de sessie
     * om een 'state'-waarde te onthouden en te controleren (CSRF-bescherming).
     */
    public function redirectToGithub()
    {
        return Socialite::driver('github')->redirect();
    }

    /**
     * GitHub stuurt de gebruiker hierheen terug na toestemming.
     * Socialite controleert zelf of de 'state' klopt, en gooit
     * anders automatisch een foutmelding.
     */
    public function handleGithubCallback()
    {
        $githubUser = Socialite::driver('github')->user();

        $user = User::where('email', $githubUser->getEmail())->first();

        if (! $user) {
            $user = User::create([
                'name' => $githubUser->getName() ?? $githubUser->getNickname(),
                'email' => $githubUser->getEmail(),
                'password' => Hash::make(Str::random(40)),
                'github_id' => $githubUser->getId(),
            ]);
        } elseif (! $user->github_id) {
            $user->update(['github_id' => $githubUser->getId()]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return redirect(config('services.frontend.url').'/oauth/callback?token='.$token);
    }
}