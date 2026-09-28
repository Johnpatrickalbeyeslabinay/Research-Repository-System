<?php

namespace Tests\Unit;

use App\Services\Retrieval\RetrievalFilterSql;
use App\Services\Retrieval\Tier;
use PHPUnit\Framework\TestCase;

/**
 * The section clause both search arms share.
 *
 * Extends PHPUnit's TestCase directly — this is pure string building with no
 * framework dependency, the same reasoning as IntentDetectorTest.
 *
 * Worth its own test because the clause cannot be exercised end-to-end here:
 * it goes into pgvector and tsvector queries, and phpunit.xml points at sqlite
 * where neither the embedding column nor content_tsv exists. What IS testable
 * offline is the SQL text and — more importantly — the binding count, because a
 * placeholder/binding mismatch is the failure mode that turns a tier filter
 * into a fatal query error at runtime rather than a wrong result.
 */
class RetrievalFilterSqlSectionTest extends TestCase
{
    public function test_no_sections_produces_no_clause_and_no_bindings(): void
    {
        [$sql, $bindings] = RetrievalFilterSql::sectionClause([]);

        $this->assertSame('', $sql);
        $this->assertSame([], $bindings);
    }

    public function test_one_section_produces_a_single_placeholder(): void
    {
        [$sql, $bindings] = RetrievalFilterSql::sectionClause(['metadata']);

        $this->assertStringContainsString('rc.section IN (?)', $sql);
        $this->assertSame(['metadata'], $bindings);
    }

    public function test_two_sections_produce_two_placeholders(): void
    {
        [$sql, $bindings] = RetrievalFilterSql::sectionClause(['metadata', 'abstract']);

        $this->assertStringContainsString('rc.section IN (?, ?)', $sql);
        $this->assertSame(['metadata', 'abstract'], $bindings);
    }

    /**
     * The failure mode that matters: one fewer or one more `?` than bindings is
     * a fatal PDO error on a live query, not a subtly wrong result set.
     */
    public function test_placeholder_count_always_matches_binding_count(): void
    {
        foreach ([[], ['metadata'], ['metadata', 'abstract'], ['a', 'b', 'c', 'd']] as $sections) {
            [$sql, $bindings] = RetrievalFilterSql::sectionClause($sections);

            $this->assertSame(
                count($bindings),
                substr_count($sql, '?'),
                'Placeholder/binding mismatch for '.count($sections).' section(s).'
            );
        }
    }

    /** Positional only — PDO cannot mix `?` and `:named` in one statement. */
    public function test_it_never_emits_a_named_placeholder(): void
    {
        [$sql] = RetrievalFilterSql::sectionClause(['metadata', 'abstract']);

        $this->assertDoesNotMatchRegularExpression('/:[a-z_]+/i', $sql);
    }

    public function test_empty_and_non_string_sections_are_discarded(): void
    {
        [$sql, $bindings] = RetrievalFilterSql::sectionClause(['metadata', '', null]);

        $this->assertSame(['metadata'], $bindings);
        $this->assertStringContainsString('IN (?)', $sql);
    }

    public function test_the_chunk_alias_is_configurable(): void
    {
        [$sql] = RetrievalFilterSql::sectionClause(['metadata'], 'chunks');

        $this->assertStringContainsString('chunks.section IN', $sql);
    }

    /**
     * The tiers must map to genuinely different clauses, or the whole
     * abstract-first design is a no-op at the SQL level.
     */
    public function test_each_tier_produces_the_clause_it_should(): void
    {
        [, $metadata] = RetrievalFilterSql::sectionClause(Tier::Metadata->sections());
        [, $abstract] = RetrievalFilterSql::sectionClause(Tier::Abstract->sections());
        [$fullTextSql, $fullText] = RetrievalFilterSql::sectionClause(Tier::FullText->sections());

        $this->assertSame(['metadata'], $metadata);
        $this->assertSame(['metadata', 'abstract'], $abstract);

        // Full text must not restrict anything at all.
        $this->assertSame('', $fullTextSql);
        $this->assertSame([], $fullText);
    }
}
