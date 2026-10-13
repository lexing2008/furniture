<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Roles;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Site staff management (admin, manager, seo).
 */
class UserController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('users/IndexUsers', [
            'users' => User::query()
                ->employees()
                ->orderBy('name')
                ->paginate(20)
                ->through(fn (User $user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role->label(),
                    'email_verified_at' => $user->email_verified_at,
                    'created_at' => $user->created_at,
                    'is_current' => $user->is($request->user()),
                ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('users/CreateUser', [
            'roles' => Roles::employeeOptions(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        // Password is hashed by the model cast
        $user = User::create($request->validated());

        $user->sendEmailVerificationNotification();

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Пользователь {$user->email} добавлен, письмо для подтверждения email отправлено.");
    }

    public function edit(Request $request, User $user): Response
    {
        $this->ensureEmployee($user);

        return Inertia::render('users/EditUser', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->value,
                'email_verified_at' => $user->email_verified_at,
                'is_current' => $user->is($request->user()),
            ],
            'roles' => Roles::employeeOptions(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->ensureEmployee($user);

        $data = $request->validated();

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->fill($data);

        $emailChanged = $user->isDirty('email');

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($emailChanged) {
            $user->sendEmailVerificationNotification();
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', $emailChanged
                ? 'Пользователь сохранён. На новый email отправлено письмо для подтверждения.'
                : 'Пользователь сохранён.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->ensureEmployee($user);

        if ($user->is($request->user())) {
            return back()->with('error', 'Нельзя удалить самого себя.');
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Пользователь {$user->email} удалён.");
    }

    public function sendVerification(User $user): RedirectResponse
    {
        $this->ensureEmployee($user);

        if ($user->hasVerifiedEmail()) {
            return back()->with('error', 'Email этого пользователя уже подтверждён.');
        }

        $user->sendEmailVerificationNotification();

        return back()->with('success', "Письмо для подтверждения отправлено на {$user->email}.");
    }

    /**
     * Customers are not managed here.
     */
    private function ensureEmployee(User $user): void
    {
        abort_unless($user->isEmployee(), 404);
    }
}
