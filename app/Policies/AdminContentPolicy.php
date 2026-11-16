<?php
namespace App\Policies;
use App\Models\User;
class AdminContentPolicy
{
    public function before(User $user, string $ability): bool { return $user->canAccessAdministration(); }
    public function viewAny(User $user): bool { return $user->canAccessAdministration(); }
    public function view(User $user, $record): bool { return $user->canAccessAdministration(); }
    public function create(User $user): bool { return $user->canAccessAdministration(); }
    public function update(User $user, $record): bool { return $user->canAccessAdministration(); }
    public function delete(User $user, $record): bool { return $user->canAccessAdministration(); }
}
