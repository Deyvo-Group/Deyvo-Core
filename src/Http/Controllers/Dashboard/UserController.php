<?php

declare(strict_types=1);

namespace Deyvo\Core\Http\Controllers\Dashboard;

use Deyvo\Core\Support\AuditLogger;
use Deyvo\Core\Support\Flash;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;

final class UserController
{
    public function __construct(
        private AuditLogger $audit,
    ) {
    }

    public function index(): View
    {
        $model = $this->model();
        $users = null;
        $error = null;

        if ($model !== null) {
            try {
                $users = $model::query()->latest('updated_at')->paginate(15);
            } catch (Throwable $exception) {
                $error = $exception->getMessage();
            }
        }

        return view('deyvo::dashboard.users.index', [
            'users' => $users,
            'userModel' => $model,
            'error' => $error,
        ]);
    }

    public function create(): View
    {
        abort_unless($this->model() !== null, 404);

        return view('deyvo::dashboard.users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $model = $this->requireModel();
        $user = new $model();
        $validated = $this->validated($request, $user, true);

        $user->setAttribute('name', $validated['name']);
        $user->setAttribute('email', $validated['email']);
        $user->setAttribute('password', bcrypt($validated['password']));
        $user->save();

        $this->audit->record('user.created', $user, [
            'email' => $user->getAttribute('email'),
        ]);
        Flash::success('User is aangemaakt.');

        return redirect()->route('deyvo.dashboard.users.index');
    }

    public function edit(string $user): View
    {
        return view('deyvo::dashboard.users.edit', [
            'user' => $this->user($user),
        ]);
    }

    public function update(Request $request, string $user): RedirectResponse
    {
        $user = $this->user($user);
        $validated = $this->validated($request, $user);

        $user->setAttribute('name', $validated['name']);
        $user->setAttribute('email', $validated['email']);

        if (($validated['password'] ?? null) !== null) {
            $user->setAttribute('password', bcrypt($validated['password']));
        }

        $changes = array_keys($user->getDirty());
        $user->save();

        $this->audit->record('user.updated', $user, [
            'email' => $user->getAttribute('email'),
            'changes' => $changes,
        ]);
        Flash::success('User is bijgewerkt.');

        return redirect()->route('deyvo.dashboard.users.index');
    }

    public function destroy(string $user): RedirectResponse
    {
        $user = $this->user($user);
        $label = $user->getAttribute('email') ?? $user->getKey();
        $user->delete();

        $this->audit->record('user.deleted', null, [
            'subject_label' => $label,
        ]);
        Flash::success('User is verwijderd.');

        return redirect()->route('deyvo.dashboard.users.index');
    }

    private function validated(Request $request, Model $user, bool $creating = false): array
    {
        $email = Rule::unique($user->getTable(), 'email');

        if (! $creating) {
            $email->ignore($user->getKey());
        }

        return Validator::validate($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', $email],
            'password' => [$creating ? 'required' : 'nullable', 'string', 'min:8', 'max:255'],
        ]);
    }

    private function user(string $id): Model
    {
        $model = $this->requireModel();
        $user = $model::query()->findOrFail($id);

        abort_unless($user instanceof Model, 404);

        return $user;
    }

    private function requireModel(): string
    {
        $model = $this->model();

        abort_unless($model !== null, 404);

        return $model;
    }

    private function model(): ?string
    {
        $model = config('deyvo-core.dashboard.users.model')
            ?: config('auth.providers.users.model');

        if (! is_string($model) || ! class_exists($model) || ! is_subclass_of($model, Model::class)) {
            return null;
        }

        return $model;
    }
}
