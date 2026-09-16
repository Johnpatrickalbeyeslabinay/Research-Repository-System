<?php

namespace App\Support;

use App\Models\User;
use App\Models\UserFile;
use Illuminate\Database\Eloquent\Builder;

/**
 * The single definition of "who may read this research" — everywhere else
 * in the app (StudentController's view/download gate, and every retrieval
 * query the chatbot runs) delegates here instead of re-deriving the rule.
 *
 * The rule itself (unchanged from what StudentController::authorizeFileAccess()
 * already enforced before this class existed):
 *
 *   (status = 'approved' AND visibility = 'public')
 *   OR the user is an admin
 *   OR the user is the submitter      (stud_file.user_id)
 *   OR the user is the adviser        (stud_file.adviser_id)
 *   OR the user is a listed co-author (stud_file_author.user_id)
 *
 * This is the security boundary for the AI chatbot: VectorSearchService
 * applies sqlPredicate() inside the retrieval query itself, so a chunk from
 * research the asking user isn't allowed to read never leaves Postgres in
 * the first place — it is not filtered out afterward, and it is not left to
 * the UI to hide.
 */
final class ResearchAccess
{
    /**
     * Row-level check for a single already-loaded UserFile. Requires
     * $file->authors to be loaded (or it lazy-loads on first access, same
     * as the code this replaced).
     */
    public static function allows(?User $user, UserFile $file): bool
    {
        if ($file->status === 'approved' && $file->visibility === 'public') {
            return true;
        }

        if ($user === null) {
            return false;
        }

        if ($user->role === 'admin') {
            return true;
        }

        if ($file->user_id === $user->id) {
            return true;
        }

        if ($file->adviser_id === $user->id) {
            return true;
        }

        return $file->authors->contains(fn ($author) => $author->user_id === $user->id);
    }

    /**
     * The exact same rule as a raw SQL fragment, for any query joining
     * stud_file directly — chiefly App\Services\Retrieval\VectorSearchService,
     * which can't use allows() because it must never load a chunk from
     * inaccessible research in the first place.
     *
     * Uses positional `?` placeholders (never `:named` ones) because the
     * fragment this returns is typically spliced into a larger raw SQL
     * string that already has its own `?` placeholders (e.g. the `?::vector`
     * query embedding) — PDO does not allow mixing named and positional
     * placeholders in one prepared statement. The caller is responsible for
     * placing $bindings at the position matching where this fragment's `?`
     * marks land in the full query.
     *
     * $alias must be a hardcoded literal from the calling code (e.g. 'sf')
     * — NEVER built from request input — since it is concatenated directly
     * into the SQL text, unlike every actual value below, which is always a
     * bound parameter.
     *
     * @return array{0: string, 1: array<int, mixed>} [sql fragment, bindings in order]
     */
    public static function sqlPredicate(?User $user, string $alias = 'sf'): array
    {
        $uid = $user?->id ?? 0;
        // A strict boolean derived from the authenticated user, not user
        // input — inlined as a SQL literal rather than bound, since
        // binding a PHP bool through PDO for a Postgres boolean comparison
        // is driver-dependent and easy to get subtly wrong (`1 = true`
        // fails under Postgres' strict typing). 'true'/'false' are the
        // only two possible values here, never derived from a string.
        $isAdminLiteral = ($user !== null && $user->role === 'admin') ? 'true' : 'false';

        $sql = "(
            ({$alias}.status = 'approved' AND {$alias}.visibility = 'public')
            OR {$isAdminLiteral}
            OR {$alias}.user_id = ?
            OR {$alias}.adviser_id = ?
            OR EXISTS (
                SELECT 1 FROM stud_file_author sfa
                WHERE sfa.stud_file_id = {$alias}.id AND sfa.user_id = ?
            )
        )";

        return [$sql, [$uid, $uid, $uid]];
    }

    /**
     * Eloquent form for listings — e.g. a future "my accessible research"
     * query. Built from sqlPredicate() rather than a parallel set of
     * orWhere() clauses, so there is exactly one place this rule is spelled
     * out, in whichever of the three forms (row check, raw SQL, Eloquent)
     * a given caller needs.
     */
    public static function scope(Builder $query, ?User $user): Builder
    {
        $alias = $query->getModel()->getTable();
        [$sql, $bindings] = self::sqlPredicate($user, $alias);

        return $query->whereRaw($sql, $bindings);
    }
}
