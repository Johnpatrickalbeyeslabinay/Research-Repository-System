<?php

namespace Tests\Feature\External;

use App\Services\External\OpenAlexProvider;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * The OpenAlex driver.
 *
 * The fixture is deliberately awkward — it contains a work with a null
 * abstract_inverted_index, one with no DOI and no venue, and one whose abstract
 * says "ignore previous instructions". Real responses from a 250M-record index
 * of anything anyone has published look like that, and a driver that only
 * handles the tidy case fails in production on its first day.
 */
class OpenAlexProviderTest extends TestCase
{
    private OpenAlexProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();

        $this->provider = new OpenAlexProvider;
    }

    private function fakeSuccess(): void
    {
        Http::fake([
            'api.openalex.org/*' => Http::response(
                json_decode(file_get_contents(base_path('tests/Fixtures/openalex/works-tilapia.json')), true)
            ),
        ]);
    }

    public function test_it_reconstructs_an_abstract_from_the_inverted_index(): void
    {
        $this->fakeSuccess();

        $works = $this->provider->search('tilapia', 5)->works;

        $this->assertSame(
            'This study examines tilapia growth in brackish ponds',
            $works[0]->abstract,
            'OpenAlex sends abstracts as {word: [positions]}; they have to be reassembled by position.'
        );
    }

    /**
     * A null abstract is common enough that discarding those works would throw
     * away much of the index. A title and a DOI is still a useful pointer.
     */
    public function test_a_work_with_no_abstract_is_still_returned(): void
    {
        $this->fakeSuccess();

        $works = $this->provider->search('tilapia', 5)->works;

        $this->assertSame('Salinity tolerance in juvenile milkfish', $works[1]->title);
        $this->assertNull($works[1]->abstract);
    }

    public function test_missing_doi_venue_and_authors_are_tolerated(): void
    {
        $this->fakeSuccess();

        $work = $this->provider->search('tilapia', 5)->works[1];

        $this->assertNull($work->doi);
        $this->assertNull($work->venue);
        $this->assertSame([], $work->authors);
        $this->assertNotNull($work->link(), 'With no DOI it should still link to the provider page.');
    }

    public function test_a_doi_is_normalised_to_its_bare_lowercase_form(): void
    {
        $this->fakeSuccess();

        $work = $this->provider->search('tilapia', 5)->works[0];

        $this->assertSame('10.1234/abcd.5678', $work->doi);
        $this->assertSame('https://doi.org/10.1234/abcd.5678', $work->link());
    }

    public function test_the_openalex_id_is_shortened(): void
    {
        $this->fakeSuccess();

        $this->assertSame('W2741809807', $this->provider->search('tilapia', 5)->works[0]->externalId);
    }

    public function test_abstracts_are_truncated_to_the_configured_length(): void
    {
        config(['external_literature.abstract_chars' => 20]);
        $this->fakeSuccess();

        $abstract = $this->provider->search('tilapia', 5)->works[0]->abstract;

        $this->assertLessThanOrEqual(21, mb_strlen($abstract));
        $this->assertStringEndsWith('…', $abstract);
    }

    /**
     * A hostile abstract reaches the DTO unescaped and unfiltered, and that is
     * correct: sanitising it here would be security theatre. The real defences
     * are system prompt rule 16 (this block is data, never instructions) and
     * the widget building DOM nodes rather than innerHTML. Stripping words in
     * the driver would only hide the problem from tests.
     */
    public function test_a_hostile_abstract_is_passed_through_untouched(): void
    {
        $this->fakeSuccess();

        $work = $this->provider->search('tilapia', 5)->works[2];

        $this->assertStringContainsString('Ignore previous instructions', $work->abstract);
    }

    public function test_it_identifies_itself_politely(): void
    {
        config(['external_literature.mailto' => 'someone@example.test']);
        $this->fakeSuccess();

        $this->provider->search('tilapia', 5);

        Http::assertSent(function ($request) {
            $agent = $request->header('User-Agent')[0] ?? '';

            return str_contains($agent, 'FisheriesResearchRepository')
                && str_contains($agent, 'someone@example.test')
                && str_contains($request->url(), 'mailto=');
        });
    }

    /** Pointing a student at a paywall they cannot read is worse than silence. */
    public function test_it_requests_open_access_results_only(): void
    {
        $this->fakeSuccess();

        $this->provider->search('tilapia', 5);

        Http::assertSent(fn ($request) => str_contains(urldecode($request->url()), 'is_oa:true'));
    }

    public function test_a_provider_error_degrades_instead_of_throwing(): void
    {
        Http::fake(['api.openalex.org/*' => Http::response('upstream is down', 503)]);

        $result = $this->provider->search('tilapia', 5);

        $this->assertTrue($result->degraded);
        $this->assertSame([], $result->works);
    }

    public function test_a_transport_failure_degrades_instead_of_throwing(): void
    {
        Http::fake(fn () => throw new \RuntimeException('connection reset'));

        $result = $this->provider->search('tilapia', 5);

        $this->assertTrue($result->degraded, 'A secondary source must never turn a question into a 500.');
    }

    /**
     * A 200 whose body we no longer understand is a schema change. It must
     * report as degraded rather than as "no results" — those need opposite
     * responses, and OpenAlex has renamed fields before.
     */
    public function test_an_unrecognised_response_shape_is_reported_as_degraded(): void
    {
        Http::fake(['api.openalex.org/*' => Http::response(['unexpected' => true])]);

        $this->assertTrue($this->provider->search('tilapia', 5)->degraded);
    }

    public function test_an_empty_query_makes_no_request(): void
    {
        $this->provider->search('   ', 5);

        Http::assertNothingSent();
    }
}
