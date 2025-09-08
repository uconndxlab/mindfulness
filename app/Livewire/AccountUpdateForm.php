<?php

namespace App\Livewire;

use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Livewire\Attributes\Validate;
use Livewire\Component;

class AccountUpdateForm extends Component
{
    // validation done dynamically
    public string $name = '';
    public string $password = '';
    public string $oldPass = '';

    public ?string $successMessage = null;
    public string $formKey;

    public function mount(): void
    {
        $user = Auth::user();
        $this->name = $user?->name ?? '';
        $this->formKey = uniqid();
    }

    public function update(): void
    {
        $user = Auth::user();
        if (!$user) {
            $this->addError('name', 'Not authenticated.');
            return;
        }

        // Determine what is changing before validating
        $nameChanged = $this->name !== ($user->name ?? '');
        $passwordChanging = $this->password !== '';

        if (!$nameChanged && !$passwordChanging) {
            $this->successMessage = 'No changes were made.';
            return;
        }

        // Build dynamic rules only for the fields that need checking
        $rules = [
            'name' => 'required|string|max:255',
            'oldPass' => ['required', 'current_password'],
        ];

        if ($passwordChanging) {
            $rules['password'] = [PasswordRule::min(8)->mixedCase()->numbers(), 'nullable'];
        }

        $this->validate($rules, [
            'name.max' => 'Name must be no longer than 255 characters.',
            'oldPass.required' => 'Please enter your password to save changes.',
            'oldPass.current_password' => 'The password you entered is incorrect.',
        ]);

        try {
            DB::transaction(function () use ($user, $nameChanged, $passwordChanging) {
                if ($passwordChanging) {
                    // Invalidate other sessions first, then update password
                    Auth::logoutOtherDevices($this->oldPass);
                    // Model cast will hash automatically
                    $user->password = $this->password;
                }

                if ($nameChanged) {
                    $user->name = $this->name;
                }

                $user->save();
            });

            // if password changed, rotate current session ID and CSRF token
            if ($passwordChanging) {
                request()->session()->regenerate();
                request()->session()->regenerateToken();
            }

            // Clear sensitive fields and show success
            $this->reset(['password', 'oldPass']);
            $this->formKey = uniqid();
            $this->successMessage = 'Your information has been updated successfully.';
        } catch (Exception $e) {
            $this->addError('name', 'Failed to update user information.');
        }
    }

    public function render()
    {
        return view('livewire.account-update-form');
    }
}
