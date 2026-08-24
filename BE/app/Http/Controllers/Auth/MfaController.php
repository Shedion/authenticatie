<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PragmaRX\Google2FA\Google2FA;

class MfaController extends Controller
{
    /**
     * Stap 1: genereer een nieuwe MFA-secret voor de ingelogde gebruiker
     * en geef een otpauth-URL terug waar de frontend een QR-code van maakt.
     */
    public function enable(Request $request)
    {
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();

        $request->user()->update(['mfa_secret' => $secret]);

        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $request->user()->email,
            $secret
        );

        return response()->json([
            'secret' => $secret,
            'qr_code_url' => $qrCodeUrl,
        ]);
    }

    /**
     * Stap 2: gebruiker voert de eerste code in vanuit de authenticator-app.
     * Klopt die, dan wordt MFA pas echt actief gezet.
     */
    public function confirm(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $google2fa = new Google2FA();

        if (! $user->mfa_secret || ! $google2fa->verifyKey($user->mfa_secret, $request->code)) {
            return response()->json(['message' => 'Ongeldige code.'], 422);
        }

        $user->update(['mfa_enabled' => true]);

        return response()->json(['message' => 'MFA ingeschakeld.']);
    }

    /**
     * MFA weer uitschakelen.
     */
    public function disable(Request $request)
    {
        $request->user()->update([
            'mfa_enabled' => false,
            'mfa_secret' => null,
        ]);

        return response()->json(['message' => 'MFA uitgeschakeld.']);
    }

    /**
     * Stap 2 van het inloggen: na een correct wachtwoord vraagt de site om
     * de 6-cijferige code. Pas als die klopt, wordt het token uitgegeven.
     */
    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => ['required', 'integer'],
            'code' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::find($request->user_id);
        $google2fa = new Google2FA();

        if (! $user || ! $user->mfa_enabled || ! $user->mfa_secret) {
            return response()->json(['message' => 'Ongeldig verzoek.'], 401);
        }

        if (! $google2fa->verifyKey($user->mfa_secret, $request->code)) {
            return response()->json(['message' => 'Ongeldige of verlopen code.'], 401);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }
}