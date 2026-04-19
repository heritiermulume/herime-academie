<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CartGuestCheckoutService
{
    /**
     * @return array{user: User, plain_password: string|null}
     */
    public function resolveUserForGuestCartCheckout(string $name, string $email, string $phone): array
    {
        $name = trim($name);
        $email = mb_strtolower(trim($email));
        $phone = trim($phone);

        $incomingDigits = $this->normalizePhoneDigits($phone);

        if ($incomingDigits === '') {
            throw ValidationException::withMessages([
                'phone' => ['Veuillez saisir un numéro de téléphone valide.'],
            ]);
        }

        $user = User::whereRaw('LOWER(TRIM(email)) = ?', [$email])->first();

        if ($user) {
            $storedDigits = $this->normalizePhoneDigits((string) ($user->phone ?? ''));

            if ($storedDigits !== '' && $storedDigits !== $incomingDigits) {
                throw ValidationException::withMessages([
                    'phone' => ['Ce numéro ne correspond pas au compte associé à cette adresse e-mail. Utilisez le numéro enregistré ou connectez-vous via votre compte Herime.'],
                ]);
            }

            $updates = [];
            if ($storedDigits === '') {
                $updates['phone'] = $phone;
            }
            if ($name !== '' && $user->name !== $name) {
                $updates['name'] = $name;
            }
            if ($updates !== []) {
                $user->update($updates);
            }

            return ['user' => $user->fresh(), 'plain_password' => null];
        }

        $plainPassword = Str::password(12);

        try {
            $user = User::create([
                'name' => $name !== '' ? $name : 'Client',
                'email' => $email,
                'phone' => $phone,
                'password' => Hash::make($plainPassword),
                'role' => 'customer',
                'is_active' => true,
                'is_verified' => false,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'Duplicate')) {
                $sqlMsg = $e->getMessage();
                if (str_contains($sqlMsg, 'phone') || str_contains($sqlMsg, 'Phone')) {
                    throw ValidationException::withMessages([
                        'phone' => ['Ce numéro de téléphone est déjà utilisé. Connectez-vous ou indiquez un autre numéro.'],
                    ]);
                }

                throw ValidationException::withMessages([
                    'email' => ['Cette adresse e-mail est déjà utilisée. Connectez-vous avec ce compte ou utilisez une autre adresse.'],
                ]);
            }
            throw $e;
        }

        return ['user' => $user, 'plain_password' => $plainPassword];
    }

    private function normalizePhoneDigits(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        return $digits;
    }
}
