<?php

namespace App\Http\Livewire\MasterApp\Settings;

use App\Core\File\Services\FileManagementService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class Profile extends Component
{
    use WithFileUploads;

    public bool $editing = false;

    public string $first_name = '';

    public string $last_name = '';

    public ?string $phone = null;

    public ?string $address = null;

    /** @var mixed */
    public $photo = null;

    public bool $remove_photo = false;

    public function mount(): void
    {
        $this->fillFromUser();
    }

    public function fillFromUser(): void
    {
        $user = Auth::user();
        $this->first_name = (string) ($user->first_name ?? '');
        $this->last_name = (string) ($user->last_name ?? '');
        $this->phone = $user->phone;
        $this->address = $user->address;
    }

    public function startEdit(): void
    {
        $this->editing = true;
        $this->fillFromUser();
        $this->photo = null;
        $this->remove_photo = false;
    }

    public function cancelEdit(): void
    {
        $this->editing = false;
        $this->fillFromUser();
        $this->photo = null;
        $this->remove_photo = false;
    }

    public function updatedPhone(?string $value): void
    {
        $digits = preg_replace('/\D+/', '', (string) $value);
        if ($digits === '') {
            $this->phone = null;

            return;
        }
        if (strlen($digits) === 10) {
            $this->phone = substr($digits, 0, 3).'-'.substr($digits, 3, 3).'-'.substr($digits, 6, 4);
        }
    }

    public function save(FileManagementService $fileService): void
    {
        $this->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'regex:/^\d{3}-\d{3}-\d{4}$/'],
            'address' => ['nullable', 'string', 'max:500'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ], [
            'phone.regex' => 'Phone must be in US format 123-456-7890.',
        ]);

        $user = Auth::user();
        $user->first_name = $this->first_name;
        $user->last_name = $this->last_name;
        $user->phone = $this->phone ?: null;
        $user->address = $this->address;

        if ($this->remove_photo && $user->photo) {
            $fileService->delete($user->photo);
            $user->photo = null;
        }

        if ($this->photo) {
            if ($user->photo) {
                $fileService->delete($user->photo);
            }
            $user->photo = $fileService->upload($this->photo, "users/{$user->id}/photo");
        }

        $user->save();

        $this->editing = false;
        $this->photo = null;
        $this->remove_photo = false;
        session()->flash('profile-status', 'profile-updated');
    }

    public function render()
    {
        return view('masterapp.livewire.settings.profile', [
            'user' => Auth::user()->load(['reportingManager.designation']),
        ]);
    }
}
