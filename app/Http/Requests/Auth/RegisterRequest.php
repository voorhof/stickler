<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'terms' => ['required', 'accepted'],
        ];
    }

    /**
     * Create a new user instance from the request.
     */
    public function register(): User
    {
        $name = $this->string('name')->value();

        $user = User::create([
            'slug' => Str::limit(Str::slug($name, '-', 'nl'), 48, ''),
            'name' => $name,
            'email' => $this->string('email')->value(),
            'password' => Hash::make($this->string('password')->value()),
            'terms_accepted' => $this->boolean('terms'),
        ]);

        event(new Registered($user));

        Auth::login($user);

        $user->created_by_user_id = Auth::id();
        $user->updated_by_user_id = Auth::id();
        $user->saveQuietly();

        activity('authentication')
            ->by(Auth::user())
            ->on(Auth::user())
            ->event('register')
            ->log(__('User account registered.'));

        return $user;
    }
}
