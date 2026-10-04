<?php

namespace App\Support;

use App\Models\AccessRequest;
use App\Models\User;
use App\Models\UserFile;
use Illuminate\Database\Eloquent\Builder;

/**
 * Centralized research access rules.
 */
final class ResearchAccess
{
    /**
     * General/public access rule.
     *
     * Approved + public research can be read publicly.
     * Directly authorized users can also access their own research.
     */
    public static function allows(?User $user, UserFile $file): bool
    {
        // Publicly approved research.
        if ($file->status === 'approved' && $file->visibility === 'public') {
            return true;
        }

        if ($user === null) {
            return false;
        }

        return self::isDirectlyAuthorized($user, $file);
    }

    /**
     * Repository access rule.
     *
     * Unlike allows(), this does NOT automatically grant access just because
     * the research is approved/public.
     *
     * Full Repository access requires:
     * - admin
     * - submitter/owner
     * - adviser
     * - listed co-author
     * - approved AccessRequest
     */
    public static function allowsRepository(?User $user, UserFile $file): bool
    {
        if ($user === null) {
            return false;
        }

        if ($file->status !== 'approved') {
            return false;
        }

        // Admin / owner / adviser / co-author.
        if (self::isDirectlyAuthorized($user, $file)) {
            return true;
        }

        // Approved access request.
        return AccessRequest::query()
            ->where('stud_file_id', $file->id)
            ->where('email', $user->email)
            ->where('status', 'approved')
            ->exists();
    }

    /**
     * Direct authorization without public visibility.
     */
    private static function isDirectlyAuthorized(User $user, UserFile $file): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($file->user_id === $user->id) {
            return true;
        }

        if ($file->adviser_id === $user->id) {
            return true;
        }

        return $file->authors
            ->contains(fn ($author) => $author->user_id === $user->id);
    }

    /**
     * SQL predicate for public/general access.
     */
    public static function sqlPredicate(?User $user, string $alias = 'sf'): array
    {
        $uid = $user?->id ?? 0;

        $isAdminLiteral =
            ($user !== null && $user->role === 'admin')
                ? 'true'
                : 'false';

        $sql = "(
            ({$alias}.status = 'approved' AND {$alias}.visibility = 'public')
            OR {$isAdminLiteral}
            OR {$alias}.user_id = ?
            OR {$alias}.adviser_id = ?
            OR EXISTS (
                SELECT 1
                FROM stud_file_author sfa
                WHERE sfa.stud_file_id = {$alias}.id
                  AND sfa.user_id = ?
            )
        )";

        return [
            $sql,
            [$uid, $uid, $uid],
        ];
    }

    /**
     * Eloquent scope for public/general access.
     */
    public static function scope(
        Builder $query,
        ?User $user
    ): Builder {
        $alias = $query->getModel()->getTable();

        [$sql, $bindings] = self::sqlPredicate(
            $user,
            $alias
        );

        return $query->whereRaw($sql, $bindings);
    }
}
