--
-- PostgreSQL database dump
--

\restrict V7SHf0M7SojOhosey7Acz3oOCmPARko4vPSSMPgqFgsfNvYVLKX5PDHqlGlPO3c

-- Dumped from database version 17.6
-- Dumped by pg_dump version 18.6

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: auth; Type: SCHEMA; Schema: -; Owner: supabase_admin
--

CREATE SCHEMA auth;


ALTER SCHEMA auth OWNER TO supabase_admin;

--
-- Name: extensions; Type: SCHEMA; Schema: -; Owner: postgres
--

CREATE SCHEMA extensions;


ALTER SCHEMA extensions OWNER TO postgres;

--
-- Name: graphql; Type: SCHEMA; Schema: -; Owner: supabase_admin
--

CREATE SCHEMA graphql;


ALTER SCHEMA graphql OWNER TO supabase_admin;

--
-- Name: graphql_public; Type: SCHEMA; Schema: -; Owner: supabase_admin
--

CREATE SCHEMA graphql_public;


ALTER SCHEMA graphql_public OWNER TO supabase_admin;

--
-- Name: pgbouncer; Type: SCHEMA; Schema: -; Owner: pgbouncer
--

CREATE SCHEMA pgbouncer;


ALTER SCHEMA pgbouncer OWNER TO pgbouncer;

--
-- Name: realtime; Type: SCHEMA; Schema: -; Owner: supabase_admin
--

CREATE SCHEMA realtime;


ALTER SCHEMA realtime OWNER TO supabase_admin;

--
-- Name: storage; Type: SCHEMA; Schema: -; Owner: supabase_admin
--

CREATE SCHEMA storage;


ALTER SCHEMA storage OWNER TO supabase_admin;

--
-- Name: supabase_migrations; Type: SCHEMA; Schema: -; Owner: postgres
--

CREATE SCHEMA supabase_migrations;


ALTER SCHEMA supabase_migrations OWNER TO postgres;

--
-- Name: vault; Type: SCHEMA; Schema: -; Owner: supabase_admin
--

CREATE SCHEMA vault;


ALTER SCHEMA vault OWNER TO supabase_admin;

--
-- Name: pg_stat_statements; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS pg_stat_statements WITH SCHEMA extensions;


--
-- Name: EXTENSION pg_stat_statements; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION pg_stat_statements IS 'track planning and execution statistics of all SQL statements executed';


--
-- Name: pgcrypto; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS pgcrypto WITH SCHEMA extensions;


--
-- Name: EXTENSION pgcrypto; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION pgcrypto IS 'cryptographic functions';


--
-- Name: supabase_vault; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS supabase_vault WITH SCHEMA vault;


--
-- Name: EXTENSION supabase_vault; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION supabase_vault IS 'Supabase Vault Extension';


--
-- Name: uuid-ossp; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS "uuid-ossp" WITH SCHEMA extensions;


--
-- Name: EXTENSION "uuid-ossp"; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION "uuid-ossp" IS 'generate universally unique identifiers (UUIDs)';


--
-- Name: vector; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS vector WITH SCHEMA public;


--
-- Name: EXTENSION vector; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION vector IS 'vector data type and ivfflat and hnsw access methods';


--
-- Name: aal_level; Type: TYPE; Schema: auth; Owner: supabase_auth_admin
--

CREATE TYPE auth.aal_level AS ENUM (
    'aal1',
    'aal2',
    'aal3'
);


ALTER TYPE auth.aal_level OWNER TO supabase_auth_admin;

--
-- Name: code_challenge_method; Type: TYPE; Schema: auth; Owner: supabase_auth_admin
--

CREATE TYPE auth.code_challenge_method AS ENUM (
    's256',
    'plain'
);


ALTER TYPE auth.code_challenge_method OWNER TO supabase_auth_admin;

--
-- Name: factor_status; Type: TYPE; Schema: auth; Owner: supabase_auth_admin
--

CREATE TYPE auth.factor_status AS ENUM (
    'unverified',
    'verified'
);


ALTER TYPE auth.factor_status OWNER TO supabase_auth_admin;

--
-- Name: factor_type; Type: TYPE; Schema: auth; Owner: supabase_auth_admin
--

CREATE TYPE auth.factor_type AS ENUM (
    'totp',
    'webauthn',
    'phone',
    'recovery_code'
);


ALTER TYPE auth.factor_type OWNER TO supabase_auth_admin;

--
-- Name: oauth_authorization_status; Type: TYPE; Schema: auth; Owner: supabase_auth_admin
--

CREATE TYPE auth.oauth_authorization_status AS ENUM (
    'pending',
    'approved',
    'denied',
    'expired'
);


ALTER TYPE auth.oauth_authorization_status OWNER TO supabase_auth_admin;

--
-- Name: oauth_client_type; Type: TYPE; Schema: auth; Owner: supabase_auth_admin
--

CREATE TYPE auth.oauth_client_type AS ENUM (
    'public',
    'confidential'
);


ALTER TYPE auth.oauth_client_type OWNER TO supabase_auth_admin;

--
-- Name: oauth_registration_type; Type: TYPE; Schema: auth; Owner: supabase_auth_admin
--

CREATE TYPE auth.oauth_registration_type AS ENUM (
    'dynamic',
    'manual'
);


ALTER TYPE auth.oauth_registration_type OWNER TO supabase_auth_admin;

--
-- Name: oauth_response_type; Type: TYPE; Schema: auth; Owner: supabase_auth_admin
--

CREATE TYPE auth.oauth_response_type AS ENUM (
    'code'
);


ALTER TYPE auth.oauth_response_type OWNER TO supabase_auth_admin;

--
-- Name: one_time_token_type; Type: TYPE; Schema: auth; Owner: supabase_auth_admin
--

CREATE TYPE auth.one_time_token_type AS ENUM (
    'confirmation_token',
    'reauthentication_token',
    'recovery_token',
    'email_change_token_new',
    'email_change_token_current',
    'phone_change_token'
);


ALTER TYPE auth.one_time_token_type OWNER TO supabase_auth_admin;

--
-- Name: ENUM; Type: TYPE; Schema: public; Owner: postgres
--

CREATE TYPE public."ENUM" AS ENUM (
    'pending',
    'approved',
    'rejected'
);


ALTER TYPE public."ENUM" OWNER TO postgres;

--
-- Name: action; Type: TYPE; Schema: realtime; Owner: supabase_realtime_admin
--

CREATE TYPE realtime.action AS ENUM (
    'INSERT',
    'UPDATE',
    'DELETE',
    'TRUNCATE',
    'ERROR'
);


ALTER TYPE realtime.action OWNER TO supabase_realtime_admin;

--
-- Name: equality_op; Type: TYPE; Schema: realtime; Owner: supabase_realtime_admin
--

CREATE TYPE realtime.equality_op AS ENUM (
    'eq',
    'neq',
    'lt',
    'lte',
    'gt',
    'gte',
    'in',
    'like',
    'ilike',
    'is',
    'match',
    'imatch',
    'isdistinct'
);


ALTER TYPE realtime.equality_op OWNER TO supabase_realtime_admin;

--
-- Name: user_defined_filter; Type: TYPE; Schema: realtime; Owner: supabase_realtime_admin
--

CREATE TYPE realtime.user_defined_filter AS (
	column_name text,
	op realtime.equality_op,
	value text,
	negate boolean
);


ALTER TYPE realtime.user_defined_filter OWNER TO supabase_realtime_admin;

--
-- Name: wal_column; Type: TYPE; Schema: realtime; Owner: supabase_realtime_admin
--

CREATE TYPE realtime.wal_column AS (
	name text,
	type_name text,
	type_oid oid,
	value jsonb,
	is_pkey boolean,
	is_selectable boolean
);


ALTER TYPE realtime.wal_column OWNER TO supabase_realtime_admin;

--
-- Name: wal_rls; Type: TYPE; Schema: realtime; Owner: supabase_realtime_admin
--

CREATE TYPE realtime.wal_rls AS (
	wal jsonb,
	is_rls_enabled boolean,
	subscription_ids uuid[],
	errors text[]
);


ALTER TYPE realtime.wal_rls OWNER TO supabase_realtime_admin;

--
-- Name: buckettype; Type: TYPE; Schema: storage; Owner: supabase_storage_admin
--

CREATE TYPE storage.buckettype AS ENUM (
    'STANDARD',
    'ANALYTICS',
    'VECTOR'
);


ALTER TYPE storage.buckettype OWNER TO supabase_storage_admin;

--
-- Name: email(); Type: FUNCTION; Schema: auth; Owner: supabase_auth_admin
--

CREATE FUNCTION auth.email() RETURNS text
    LANGUAGE sql STABLE
    AS $$
  select 
  coalesce(
    nullif(current_setting('request.jwt.claim.email', true), ''),
    (nullif(current_setting('request.jwt.claims', true), '')::jsonb ->> 'email')
  )::text
$$;


ALTER FUNCTION auth.email() OWNER TO supabase_auth_admin;

--
-- Name: FUNCTION email(); Type: COMMENT; Schema: auth; Owner: supabase_auth_admin
--

COMMENT ON FUNCTION auth.email() IS 'Deprecated. Use auth.jwt() -> ''email'' instead.';


--
-- Name: jwt(); Type: FUNCTION; Schema: auth; Owner: supabase_auth_admin
--

CREATE FUNCTION auth.jwt() RETURNS jsonb
    LANGUAGE sql STABLE
    AS $$
  select 
    coalesce(
        nullif(current_setting('request.jwt.claim', true), ''),
        nullif(current_setting('request.jwt.claims', true), '')
    )::jsonb
$$;


ALTER FUNCTION auth.jwt() OWNER TO supabase_auth_admin;

--
-- Name: role(); Type: FUNCTION; Schema: auth; Owner: supabase_auth_admin
--

CREATE FUNCTION auth.role() RETURNS text
    LANGUAGE sql STABLE
    AS $$
  select 
  coalesce(
    nullif(current_setting('request.jwt.claim.role', true), ''),
    (nullif(current_setting('request.jwt.claims', true), '')::jsonb ->> 'role')
  )::text
$$;


ALTER FUNCTION auth.role() OWNER TO supabase_auth_admin;

--
-- Name: FUNCTION role(); Type: COMMENT; Schema: auth; Owner: supabase_auth_admin
--

COMMENT ON FUNCTION auth.role() IS 'Deprecated. Use auth.jwt() -> ''role'' instead.';


--
-- Name: uid(); Type: FUNCTION; Schema: auth; Owner: supabase_auth_admin
--

CREATE FUNCTION auth.uid() RETURNS uuid
    LANGUAGE sql STABLE
    AS $$
  select 
  coalesce(
    nullif(current_setting('request.jwt.claim.sub', true), ''),
    (nullif(current_setting('request.jwt.claims', true), '')::jsonb ->> 'sub')
  )::uuid
$$;


ALTER FUNCTION auth.uid() OWNER TO supabase_auth_admin;

--
-- Name: FUNCTION uid(); Type: COMMENT; Schema: auth; Owner: supabase_auth_admin
--

COMMENT ON FUNCTION auth.uid() IS 'Deprecated. Use auth.jwt() -> ''sub'' instead.';


--
-- Name: grant_pg_cron_access(); Type: FUNCTION; Schema: extensions; Owner: supabase_admin
--

CREATE FUNCTION extensions.grant_pg_cron_access() RETURNS event_trigger
    LANGUAGE plpgsql
    SET search_path TO ''
    AS $$
BEGIN
  IF EXISTS (
    SELECT
    FROM pg_event_trigger_ddl_commands() AS ev
    JOIN pg_extension AS ext
    ON ev.objid = ext.oid
    WHERE ext.extname = 'pg_cron'
  )
  THEN
    grant usage on schema cron to postgres with grant option;

    alter default privileges in schema cron grant all on tables to postgres with grant option;
    alter default privileges in schema cron grant all on functions to postgres with grant option;
    alter default privileges in schema cron grant all on sequences to postgres with grant option;

    alter default privileges for user supabase_admin in schema cron grant all
        on sequences to postgres with grant option;
    alter default privileges for user supabase_admin in schema cron grant all
        on tables to postgres with grant option;
    alter default privileges for user supabase_admin in schema cron grant all
        on functions to postgres with grant option;

    grant all privileges on all tables in schema cron to postgres with grant option;
    revoke all on table cron.job from postgres;
    grant select on table cron.job to postgres with grant option;
    revoke trigger on cron.job_run_details from postgres;
  END IF;
END;
$$;


ALTER FUNCTION extensions.grant_pg_cron_access() OWNER TO supabase_admin;

--
-- Name: FUNCTION grant_pg_cron_access(); Type: COMMENT; Schema: extensions; Owner: supabase_admin
--

COMMENT ON FUNCTION extensions.grant_pg_cron_access() IS 'Grants access to pg_cron';


--
-- Name: grant_pg_graphql_access(); Type: FUNCTION; Schema: extensions; Owner: supabase_admin
--

CREATE FUNCTION extensions.grant_pg_graphql_access() RETURNS event_trigger
    LANGUAGE plpgsql
    SET search_path TO ''
    AS $_$
begin
    if not exists (
        select 1
        from pg_catalog.pg_event_trigger_ddl_commands() ev
        join pg_catalog.pg_extension e on ev.objid = e.oid
        where e.extname = 'pg_graphql'
    ) then
        return;
    end if;

    drop function if exists graphql_public.graphql;
    create or replace function graphql_public.graphql(
        "operationName" text default null,
        query text default null,
        variables jsonb default null,
        extensions jsonb default null
    )
        returns jsonb
        language sql
    as $$
        select graphql.resolve(
            query := query,
            variables := coalesce(variables, '{}'),
            "operationName" := "operationName",
            extensions := extensions
        );
    $$;

    -- Attach the wrapper to the extension so DROP EXTENSION cascades to it,
    -- which in turn triggers set_graphql_placeholder to reinstall the "not enabled" stub.
    alter extension pg_graphql add function graphql_public.graphql(text, text, jsonb, jsonb);

    grant usage on schema graphql to postgres, anon, authenticated, service_role;
    grant execute on function graphql.resolve to postgres, anon, authenticated, service_role;
    grant usage on schema graphql to postgres with grant option;
    grant usage on schema graphql_public to postgres with grant option;
end;
$_$;


ALTER FUNCTION extensions.grant_pg_graphql_access() OWNER TO supabase_admin;

--
-- Name: FUNCTION grant_pg_graphql_access(); Type: COMMENT; Schema: extensions; Owner: supabase_admin
--

COMMENT ON FUNCTION extensions.grant_pg_graphql_access() IS 'Grants access to pg_graphql';


--
-- Name: grant_pg_net_access(); Type: FUNCTION; Schema: extensions; Owner: supabase_admin
--

CREATE FUNCTION extensions.grant_pg_net_access() RETURNS event_trigger
    LANGUAGE plpgsql
    SET search_path TO ''
    AS $$
BEGIN
  IF EXISTS (
    SELECT 1
    FROM pg_event_trigger_ddl_commands() AS ev
    JOIN pg_extension AS ext
    ON ev.objid = ext.oid
    WHERE ext.extname = 'pg_net'
  )
  THEN
    IF NOT EXISTS (
      SELECT 1
      FROM pg_roles
      WHERE rolname = 'supabase_functions_admin'
    )
    THEN
      CREATE USER supabase_functions_admin NOINHERIT CREATEROLE LOGIN NOREPLICATION;
    END IF;

    GRANT USAGE ON SCHEMA net TO supabase_functions_admin, postgres, anon, authenticated, service_role;

    IF EXISTS (
      SELECT FROM pg_extension
      WHERE extname = 'pg_net'
      -- all versions in use on existing projects as of 2025-02-20
      -- version 0.12.0 onwards don't need these applied
      AND extversion IN ('0.2', '0.6', '0.7', '0.7.1', '0.8.0', '0.10.0', '0.11.0')
    ) THEN
      ALTER function net.http_get(url text, params jsonb, headers jsonb, timeout_milliseconds integer) SECURITY DEFINER;
      ALTER function net.http_post(url text, body jsonb, params jsonb, headers jsonb, timeout_milliseconds integer) SECURITY DEFINER;

      ALTER function net.http_get(url text, params jsonb, headers jsonb, timeout_milliseconds integer) SET search_path = net;
      ALTER function net.http_post(url text, body jsonb, params jsonb, headers jsonb, timeout_milliseconds integer) SET search_path = net;

      REVOKE ALL ON FUNCTION net.http_get(url text, params jsonb, headers jsonb, timeout_milliseconds integer) FROM PUBLIC;
      REVOKE ALL ON FUNCTION net.http_post(url text, body jsonb, params jsonb, headers jsonb, timeout_milliseconds integer) FROM PUBLIC;

      GRANT EXECUTE ON FUNCTION net.http_get(url text, params jsonb, headers jsonb, timeout_milliseconds integer) TO supabase_functions_admin, postgres, anon, authenticated, service_role;
      GRANT EXECUTE ON FUNCTION net.http_post(url text, body jsonb, params jsonb, headers jsonb, timeout_milliseconds integer) TO supabase_functions_admin, postgres, anon, authenticated, service_role;
    END IF;
  END IF;
END;
$$;


ALTER FUNCTION extensions.grant_pg_net_access() OWNER TO supabase_admin;

--
-- Name: FUNCTION grant_pg_net_access(); Type: COMMENT; Schema: extensions; Owner: supabase_admin
--

COMMENT ON FUNCTION extensions.grant_pg_net_access() IS 'Grants access to pg_net';


--
-- Name: pgrst_ddl_watch(); Type: FUNCTION; Schema: extensions; Owner: supabase_admin
--

CREATE FUNCTION extensions.pgrst_ddl_watch() RETURNS event_trigger
    LANGUAGE plpgsql
    SET search_path TO ''
    AS $$
DECLARE
  cmd record;
BEGIN
  FOR cmd IN SELECT * FROM pg_event_trigger_ddl_commands()
  LOOP
    IF cmd.command_tag IN (
      'CREATE SCHEMA', 'ALTER SCHEMA'
    , 'CREATE TABLE', 'CREATE TABLE AS', 'SELECT INTO', 'ALTER TABLE'
    , 'CREATE FOREIGN TABLE', 'ALTER FOREIGN TABLE'
    , 'CREATE VIEW', 'ALTER VIEW'
    , 'CREATE MATERIALIZED VIEW', 'ALTER MATERIALIZED VIEW'
    , 'CREATE FUNCTION', 'ALTER FUNCTION'
    , 'CREATE TRIGGER'
    , 'CREATE TYPE', 'ALTER TYPE'
    , 'CREATE RULE'
    , 'COMMENT'
    )
    -- don't notify in case of CREATE TEMP table or other objects created on pg_temp
    AND cmd.schema_name is distinct from 'pg_temp'
    THEN
      NOTIFY pgrst, 'reload schema';
    END IF;
  END LOOP;
END; $$;


ALTER FUNCTION extensions.pgrst_ddl_watch() OWNER TO supabase_admin;

--
-- Name: pgrst_drop_watch(); Type: FUNCTION; Schema: extensions; Owner: supabase_admin
--

CREATE FUNCTION extensions.pgrst_drop_watch() RETURNS event_trigger
    LANGUAGE plpgsql
    SET search_path TO ''
    AS $$
DECLARE
  obj record;
BEGIN
  FOR obj IN SELECT * FROM pg_event_trigger_dropped_objects()
  LOOP
    IF obj.object_type IN (
      'schema'
    , 'table'
    , 'foreign table'
    , 'view'
    , 'materialized view'
    , 'function'
    , 'trigger'
    , 'type'
    , 'rule'
    )
    AND obj.is_temporary IS false -- no pg_temp objects
    THEN
      NOTIFY pgrst, 'reload schema';
    END IF;
  END LOOP;
END; $$;


ALTER FUNCTION extensions.pgrst_drop_watch() OWNER TO supabase_admin;

--
-- Name: set_graphql_placeholder(); Type: FUNCTION; Schema: extensions; Owner: supabase_admin
--

CREATE FUNCTION extensions.set_graphql_placeholder() RETURNS event_trigger
    LANGUAGE plpgsql
    SET search_path TO ''
    AS $_$
    DECLARE
    graphql_is_dropped bool;
    BEGIN
    graphql_is_dropped = (
        SELECT ev.schema_name = 'graphql_public'
        FROM pg_event_trigger_dropped_objects() AS ev
        WHERE ev.schema_name = 'graphql_public'
    );

    IF graphql_is_dropped
    THEN
        create or replace function graphql_public.graphql(
            "operationName" text default null,
            query text default null,
            variables jsonb default null,
            extensions jsonb default null
        )
            returns jsonb
            language plpgsql
            set search_path to ''
        as $$
            DECLARE
                server_version float;
            BEGIN
                server_version = (SELECT (SPLIT_PART((select version()), ' ', 2))::float);

                IF server_version >= 14 THEN
                    RETURN jsonb_build_object(
                        'errors', jsonb_build_array(
                            jsonb_build_object(
                                'message', 'pg_graphql extension is not enabled.'
                            )
                        )
                    );
                ELSE
                    RETURN jsonb_build_object(
                        'errors', jsonb_build_array(
                            jsonb_build_object(
                                'message', 'pg_graphql is only available on projects running Postgres 14 onwards.'
                            )
                        )
                    );
                END IF;
            END;
        $$;
    END IF;

    END;
$_$;


ALTER FUNCTION extensions.set_graphql_placeholder() OWNER TO supabase_admin;

--
-- Name: FUNCTION set_graphql_placeholder(); Type: COMMENT; Schema: extensions; Owner: supabase_admin
--

COMMENT ON FUNCTION extensions.set_graphql_placeholder() IS 'Reintroduces placeholder function for graphql_public.graphql';


--
-- Name: graphql(text, text, jsonb, jsonb); Type: FUNCTION; Schema: graphql_public; Owner: supabase_admin
--

CREATE FUNCTION graphql_public.graphql("operationName" text DEFAULT NULL::text, query text DEFAULT NULL::text, variables jsonb DEFAULT NULL::jsonb, extensions jsonb DEFAULT NULL::jsonb) RETURNS jsonb
    LANGUAGE plpgsql
    AS $$
            DECLARE
                server_version float;
            BEGIN
                server_version = (SELECT (SPLIT_PART((select version()), ' ', 2))::float);

                IF server_version >= 14 THEN
                    RETURN jsonb_build_object(
                        'errors', jsonb_build_array(
                            jsonb_build_object(
                                'message', 'pg_graphql extension is not enabled.'
                            )
                        )
                    );
                ELSE
                    RETURN jsonb_build_object(
                        'errors', jsonb_build_array(
                            jsonb_build_object(
                                'message', 'pg_graphql is only available on projects running Postgres 14 onwards.'
                            )
                        )
                    );
                END IF;
            END;
        $$;


ALTER FUNCTION graphql_public.graphql("operationName" text, query text, variables jsonb, extensions jsonb) OWNER TO supabase_admin;

--
-- Name: get_auth(text); Type: FUNCTION; Schema: pgbouncer; Owner: supabase_admin
--

CREATE FUNCTION pgbouncer.get_auth(p_usename text) RETURNS TABLE(username text, password text)
    LANGUAGE plpgsql SECURITY DEFINER
    SET search_path TO ''
    AS $_$
  BEGIN
      RAISE DEBUG 'PgBouncer auth request: %', p_usename;

      RETURN QUERY
      SELECT
          rolname::text,
          CASE WHEN rolvaliduntil < now()
              THEN null
              ELSE rolpassword::text
          END
      FROM pg_authid
      WHERE rolname=$1 and rolcanlogin;
  END;
  $_$;


ALTER FUNCTION pgbouncer.get_auth(p_usename text) OWNER TO supabase_admin;

--
-- Name: apply_rls(jsonb, integer); Type: FUNCTION; Schema: realtime; Owner: supabase_realtime_admin
--

CREATE FUNCTION realtime.apply_rls(wal jsonb, max_record_bytes integer DEFAULT (1024 * 1024)) RETURNS SETOF realtime.wal_rls
    LANGUAGE plpgsql
    AS $$
declare
    -- Regclass of the table e.g. public.notes
    entity_ regclass = (quote_ident(wal ->> 'schema') || '.' || quote_ident(wal ->> 'table'))::regclass;

    -- I, U, D, T: insert, update ...
    action realtime.action = (
        case wal ->> 'action'
            when 'I' then 'INSERT'
            when 'U' then 'UPDATE'
            when 'D' then 'DELETE'
            else 'ERROR'
        end
    );

    -- Is row level security enabled for the table
    is_rls_enabled bool = relrowsecurity from pg_class where oid = entity_;

    subscriptions realtime.subscription[] = array_agg(subs)
        from
            realtime.subscription subs
        where
            subs.entity = entity_
            -- Filter by action early - only get subscriptions interested in this action
            -- action_filter column can be: '*' (all), 'INSERT', 'UPDATE', or 'DELETE'
            and (subs.action_filter = '*' or subs.action_filter = action::text);

    -- Subscription vars
    working_role regrole;
    working_selected_columns text[];
    claimed_role regrole;
    claims jsonb;

    subscription_id uuid;
    subscription_has_access bool;
    visible_to_subscription_ids uuid[] = '{}';

    -- structured info for wal's columns
    columns realtime.wal_column[];
    -- previous identity values for update/delete
    old_columns realtime.wal_column[];

    error_record_exceeds_max_size boolean = octet_length(wal::text) > max_record_bytes;

    -- Primary jsonb output for record
    output jsonb;

    -- Loop record for iterating unique roles (outer loop)
    role_record record;
    -- Loop record for iterating unique selected_columns within a role (inner loop)
    cols_record record;
    -- Subscription ids visible at the role level (before fanning out by selected_columns)
    visible_role_sub_ids uuid[] = '{}';

begin
    perform set_config('role', null, true);

    columns =
        array_agg(
            (
                x->>'name',
                x->>'type',
                x->>'typeoid',
                realtime.cast(
                    (x->'value') #>> '{}',
                    coalesce(
                        (x->>'typeoid')::regtype, -- null when wal2json version <= 2.4
                        (x->>'type')::regtype
                    )
                ),
                (pks ->> 'name') is not null,
                true
            )::realtime.wal_column
        )
        from
            jsonb_array_elements(wal -> 'columns') x
            left join jsonb_array_elements(wal -> 'pk') pks
                on (x ->> 'name') = (pks ->> 'name');

    old_columns =
        array_agg(
            (
                x->>'name',
                x->>'type',
                x->>'typeoid',
                realtime.cast(
                    (x->'value') #>> '{}',
                    coalesce(
                        (x->>'typeoid')::regtype, -- null when wal2json version <= 2.4
                        (x->>'type')::regtype
                    )
                ),
                (pks ->> 'name') is not null,
                true
            )::realtime.wal_column
        )
        from
            jsonb_array_elements(wal -> 'identity') x
            left join jsonb_array_elements(wal -> 'pk') pks
                on (x ->> 'name') = (pks ->> 'name');

    for role_record in
        select claims_role
        from (select distinct claims_role from unnest(subscriptions)) t
        order by claims_role::text
    loop
        working_role := role_record.claims_role;

        -- Update `is_selectable` for columns and old_columns (once per role)
        columns =
            array_agg(
                (
                    c.name,
                    c.type_name,
                    c.type_oid,
                    c.value,
                    c.is_pkey,
                    pg_catalog.has_column_privilege(working_role, entity_, c.name, 'SELECT')
                )::realtime.wal_column
            )
            from
                unnest(columns) c;

        old_columns =
                array_agg(
                    (
                        c.name,
                        c.type_name,
                        c.type_oid,
                        c.value,
                        c.is_pkey,
                        pg_catalog.has_column_privilege(working_role, entity_, c.name, 'SELECT')
                    )::realtime.wal_column
                )
                from
                    unnest(old_columns) c;

        if action <> 'DELETE' and count(1) = 0 from unnest(columns) c where c.is_pkey then
            -- Fan out 400 error per distinct selected_columns for this role
            for cols_record in
                select selected_columns
                from (select distinct selected_columns from unnest(subscriptions) s where s.claims_role = working_role) t
                order by coalesce(array_to_string(selected_columns, ','), '')
            loop
                working_selected_columns := cols_record.selected_columns;
                return next (
                    jsonb_build_object(
                        'schema', wal ->> 'schema',
                        'table', wal ->> 'table',
                        'type', action
                    ),
                    is_rls_enabled,
                    (select array_agg(s.subscription_id) from unnest(subscriptions) as s where s.claims_role = working_role and (s.selected_columns is not distinct from working_selected_columns)),
                    array['Error 400: Bad Request, no primary key']
                )::realtime.wal_rls;
            end loop;

        -- The claims role does not have SELECT permission to the primary key of entity
        elsif action <> 'DELETE' and sum(c.is_selectable::int) <> count(1) from unnest(columns) c where c.is_pkey then
            -- Fan out 401 error per distinct selected_columns for this role
            for cols_record in
                select selected_columns
                from (select distinct selected_columns from unnest(subscriptions) s where s.claims_role = working_role) t
                order by coalesce(array_to_string(selected_columns, ','), '')
            loop
                working_selected_columns := cols_record.selected_columns;
                return next (
                    jsonb_build_object(
                        'schema', wal ->> 'schema',
                        'table', wal ->> 'table',
                        'type', action
                    ),
                    is_rls_enabled,
                    (select array_agg(s.subscription_id) from unnest(subscriptions) as s where s.claims_role = working_role and (s.selected_columns is not distinct from working_selected_columns)),
                    array['Error 401: Unauthorized']
                )::realtime.wal_rls;
            end loop;

        else
            -- Create the prepared statement (once per role)
            if is_rls_enabled and action <> 'DELETE' then
                if (select 1 from pg_prepared_statements where name = 'walrus_rls_stmt' limit 1) > 0 then
                    deallocate walrus_rls_stmt;
                end if;
                execute realtime.build_prepared_statement_sql('walrus_rls_stmt', entity_, columns);
            end if;

            -- Collect all visible subscription IDs for this role (filter check + RLS check)
            visible_role_sub_ids = '{}';

            for subscription_id, claims in (
                    select
                        subs.subscription_id,
                        subs.claims
                    from
                        unnest(subscriptions) subs
                    where
                        subs.entity = entity_
                        and subs.claims_role = working_role
                        and (
                            realtime.is_visible_through_filters(columns, subs.filters)
                            or (
                              action = 'DELETE'
                              and realtime.is_visible_through_filters(old_columns, subs.filters)
                            )
                        )
            ) loop

                if not is_rls_enabled or action = 'DELETE' then
                    visible_role_sub_ids = visible_role_sub_ids || subscription_id;
                else
                    -- Check if RLS allows the role to see the record
                    perform
                        -- Trim leading and trailing quotes from working_role because set_config
                        -- doesn't recognize the role as valid if they are included
                        set_config('role', trim(both '"' from working_role::text), true),
                        set_config('request.jwt.claims', claims::text, true);

                    execute 'execute walrus_rls_stmt' into subscription_has_access;

                    -- Reset the role on every FOR..LOOP batch execution.
                    -- The first batch of 10 rows is pre-fetched using the current connection role (PG internal behaviour)
                    -- then we have to reset it again otherwise it would use the role defined in the `set_config` above
                    -- to fetch the remaining rows when rows>10, which could be a user-defined role that lacks execution grants.
                    -- The flow is:
                    --   1. run batch with conn role
                    --   2. set_config working_role
                    --   3. execute walrus
                    --   4. reset role (revert)
                    --   5. repeat
                    perform set_config('role', null, true);

                    if subscription_has_access then
                        visible_role_sub_ids = visible_role_sub_ids || subscription_id;
                    end if;
                end if;
            end loop;

            perform set_config('role', null, true);

            -- Inner loop: per distinct selected_columns for this role
            for cols_record in
                select selected_columns
                from (select distinct selected_columns from unnest(subscriptions) s where s.claims_role = working_role) t
                order by coalesce(array_to_string(selected_columns, ','), '')
            loop
                working_selected_columns := cols_record.selected_columns;

                output = jsonb_build_object(
                    'schema', wal ->> 'schema',
                    'table', wal ->> 'table',
                    'type', action,
                    'commit_timestamp', to_char(
                        ((wal ->> 'timestamp')::timestamptz at time zone 'utc'),
                        'YYYY-MM-DD"T"HH24:MI:SS.MS"Z"'
                    ),
                    'columns', (
                        select
                            jsonb_agg(
                                jsonb_build_object(
                                    'name', pa.attname,
                                    'type', pt.typname
                                )
                                order by pa.attnum asc
                            )
                        from
                            pg_attribute pa
                            join pg_type pt
                                on pa.atttypid = pt.oid
                            left join (
                                select unnest(conkey) as pkey_attnum
                                from pg_constraint
                                where conrelid = entity_ and contype = 'p'
                            ) pk on pk.pkey_attnum = pa.attnum
                        where
                            attrelid = entity_
                            and attnum > 0
                            and pg_catalog.has_column_privilege(working_role, entity_, pa.attname, 'SELECT')
                            and (working_selected_columns is null or pa.attname = any(working_selected_columns) or pk.pkey_attnum is not null)
                    )
                )
                -- Add "record" key for insert and update
                || case
                    when action in ('INSERT', 'UPDATE') then
                        jsonb_build_object(
                            'record',
                            (
                                select
                                    jsonb_object_agg(
                                        -- if unchanged toast, get column name and value from old record
                                        coalesce((c).name, (oc).name),
                                        case
                                            when (c).name is null then (oc).value
                                            else (c).value
                                        end
                                    )
                                from
                                    unnest(columns) c
                                    full outer join unnest(old_columns) oc
                                        on (c).name = (oc).name
                                where
                                    coalesce((c).is_selectable, (oc).is_selectable)
                                    and (working_selected_columns is null or coalesce((c).name, (oc).name) = any(working_selected_columns) or coalesce((c).is_pkey, (oc).is_pkey))
                                    and ( not error_record_exceeds_max_size or (octet_length((c).value::text) <= 64))
                            )
                        )
                    else '{}'::jsonb
                end
                -- Add "old_record" key for update and delete
                || case
                    when action = 'UPDATE' then
                        jsonb_build_object(
                                'old_record',
                                (
                                    select jsonb_object_agg((c).name, (c).value)
                                    from unnest(old_columns) c
                                    where
                                        (c).is_selectable
                                        and (working_selected_columns is null or (c).name = any(working_selected_columns) or (c).is_pkey)
                                        and ( not error_record_exceeds_max_size or (octet_length((c).value::text) <= 64))
                                )
                            )
                    when action = 'DELETE' then
                        jsonb_build_object(
                            'old_record',
                            (
                                select jsonb_object_agg((c).name, (c).value)
                                from unnest(old_columns) c
                                where
                                    (c).is_selectable
                                    and (working_selected_columns is null or (c).name = any(working_selected_columns) or (c).is_pkey)
                                    and ( not error_record_exceeds_max_size or (octet_length((c).value::text) <= 64))
                                    and ( not is_rls_enabled or (c).is_pkey ) -- if RLS enabled, we can't secure deletes so filter to pkey
                            )
                        )
                    else '{}'::jsonb
                end;

                -- Filter visible_role_sub_ids to those matching the current selected_columns group
                visible_to_subscription_ids = coalesce(
                    (
                        select array_agg(s.subscription_id)
                        from unnest(subscriptions) s
                        where s.claims_role = working_role
                          and (s.selected_columns is not distinct from working_selected_columns)
                          and s.subscription_id = any(visible_role_sub_ids)
                    ),
                    '{}'::uuid[]
                );

                return next (
                    output,
                    is_rls_enabled,
                    visible_to_subscription_ids,
                    case
                        when error_record_exceeds_max_size then array['Error 413: Payload Too Large']
                        else '{}'
                    end
                )::realtime.wal_rls;
            end loop;

        end if;
    end loop;

    perform set_config('role', null, true);
end;
$$;


ALTER FUNCTION realtime.apply_rls(wal jsonb, max_record_bytes integer) OWNER TO supabase_realtime_admin;

--
-- Name: broadcast_changes(text, text, text, text, text, record, record, text); Type: FUNCTION; Schema: realtime; Owner: supabase_realtime_admin
--

CREATE FUNCTION realtime.broadcast_changes(topic_name text, event_name text, operation text, table_name text, table_schema text, new record, old record, level text DEFAULT 'ROW'::text) RETURNS void
    LANGUAGE plpgsql
    AS $$
DECLARE
    -- Declare a variable to hold the JSONB representation of the row
    row_data jsonb := '{}'::jsonb;
BEGIN
    IF level = 'STATEMENT' THEN
        RAISE EXCEPTION 'function can only be triggered for each row, not for each statement';
    END IF;
    -- Check the operation type and handle accordingly
    IF operation = 'INSERT' OR operation = 'UPDATE' OR operation = 'DELETE' THEN
        row_data := jsonb_build_object('old_record', OLD, 'record', NEW, 'operation', operation, 'table', table_name, 'schema', table_schema);
        PERFORM realtime.send (row_data, event_name, topic_name);
    ELSE
        RAISE EXCEPTION 'Unexpected operation type: %', operation;
    END IF;
EXCEPTION
    WHEN OTHERS THEN
        RAISE EXCEPTION 'Failed to process the row: %', SQLERRM;
END;

$$;


ALTER FUNCTION realtime.broadcast_changes(topic_name text, event_name text, operation text, table_name text, table_schema text, new record, old record, level text) OWNER TO supabase_realtime_admin;

--
-- Name: build_prepared_statement_sql(text, regclass, realtime.wal_column[]); Type: FUNCTION; Schema: realtime; Owner: supabase_realtime_admin
--

CREATE FUNCTION realtime.build_prepared_statement_sql(prepared_statement_name text, entity regclass, columns realtime.wal_column[]) RETURNS text
    LANGUAGE sql
    AS $$
      /*
      Builds a sql string that, if executed, creates a prepared statement to
      tests retrive a row from *entity* by its primary key columns.
      Example
          select realtime.build_prepared_statement_sql('public.notes', '{"id"}'::text[], '{"bigint"}'::text[])
      */
          select
      'prepare ' || prepared_statement_name || ' as
          select
              exists(
                  select
                      1
                  from
                      ' || entity || '
                  where
                      ' || string_agg(quote_ident(pkc.name) || '=' || quote_nullable(pkc.value #>> '{}') , ' and ') || '
              )'
          from
              unnest(columns) pkc
          where
              pkc.is_pkey
          group by
              entity
      $$;


ALTER FUNCTION realtime.build_prepared_statement_sql(prepared_statement_name text, entity regclass, columns realtime.wal_column[]) OWNER TO supabase_realtime_admin;

--
-- Name: cast(text, regtype); Type: FUNCTION; Schema: realtime; Owner: supabase_realtime_admin
--

CREATE FUNCTION realtime."cast"(val text, type_ regtype) RETURNS jsonb
    LANGUAGE plpgsql IMMUTABLE
    AS $$
declare
  res jsonb;
begin
  if type_::text = 'bytea' then
    return to_jsonb(val);
  end if;
  execute format('select to_jsonb(%L::'|| type_::text || ')', val) into res;
  return res;
end
$$;


ALTER FUNCTION realtime."cast"(val text, type_ regtype) OWNER TO supabase_realtime_admin;

--
-- Name: check_equality_op(realtime.equality_op, regtype, text, text); Type: FUNCTION; Schema: realtime; Owner: supabase_realtime_admin
--

CREATE FUNCTION realtime.check_equality_op(op realtime.equality_op, type_ regtype, val_1 text, val_2 text) RETURNS boolean
    LANGUAGE plpgsql IMMUTABLE
    AS $$
/*
Casts *val_1* and *val_2* as type *type_* and check the *op* condition for truthiness
*/
declare
    op_symbol text = (
        case
            when op = 'eq' then '='
            when op = 'neq' then '!='
            when op = 'lt' then '<'
            when op = 'lte' then '<='
            when op = 'gt' then '>'
            when op = 'gte' then '>='
            when op = 'in' then '= any'
            else 'UNKNOWN OP'
        end
    );
    res boolean;
begin
    execute format(
        'select %L::'|| type_::text || ' ' || op_symbol
        || ' ( %L::'
        || (
            case
                when op = 'in' then type_::text || '[]'
                else type_::text end
        )
        || ')', val_1, val_2) into res;
    return res;
end;
$$;


ALTER FUNCTION realtime.check_equality_op(op realtime.equality_op, type_ regtype, val_1 text, val_2 text) OWNER TO supabase_realtime_admin;

--
-- Name: check_equality_op(realtime.equality_op, regtype, text, text, boolean); Type: FUNCTION; Schema: realtime; Owner: supabase_realtime_admin
--

CREATE FUNCTION realtime.check_equality_op(op realtime.equality_op, type_ regtype, val_1 text, val_2 text, negate boolean) RETURNS boolean
    LANGUAGE plpgsql STABLE
    AS $$
declare
    op_symbol text;
    res boolean;
begin
    -- IS DISTINCT FROM / IS NOT DISTINCT FROM: infix, both sides typed literals
    if op = 'isdistinct' then
        execute format(
            'select %L::%s %s %L::%s',
            val_1,
            type_::text,
            case when negate then 'IS NOT DISTINCT FROM' else 'IS DISTINCT FROM' end,
            val_2,
            type_::text
        ) into res;
        return res;
    end if;

    -- IS requires a keyword RHS (NULL, TRUE, FALSE, UNKNOWN), not a typed literal
    if op = 'is' then
        if val_2 not in ('null', 'true', 'false', 'unknown') then
            raise exception 'invalid value for is filter: must be null, true, false, or unknown';
        end if;
        execute format(
            'select %L::%s %s %s',
            val_1,
            type_::text,
            case when negate then 'IS NOT' else 'IS' end,
            upper(val_2)
        ) into res;
        return res;
    end if;

    op_symbol = case
        when op = 'eq'    then '='
        when op = 'neq'   then '!='
        when op = 'lt'    then '<'
        when op = 'lte'   then '<='
        when op = 'gt'    then '>'
        when op = 'gte'   then '>='
        when op = 'in'    then '= any'
        when op = 'like'   then 'LIKE'
        when op = 'ilike'  then 'ILIKE'
        when op = 'match'  then '~'
        when op = 'imatch' then '~*'
        else null
    end;

    if op_symbol is null then
        raise exception 'unsupported equality operator: %', op::text;
    end if;

    execute format(
        'select %L::%s %s (%L::%s)',
        val_1,
        type_::text,
        op_symbol,
        val_2,
        case when op = 'in' then type_::text || '[]' else type_::text end
    ) into res;

    return case when negate then not res else res end;
end;
$$;


ALTER FUNCTION realtime.check_equality_op(op realtime.equality_op, type_ regtype, val_1 text, val_2 text, negate boolean) OWNER TO supabase_realtime_admin;

--
-- Name: is_visible_through_filters(realtime.wal_column[], realtime.user_defined_filter[]); Type: FUNCTION; Schema: realtime; Owner: supabase_realtime_admin
--

CREATE FUNCTION realtime.is_visible_through_filters(columns realtime.wal_column[], filters realtime.user_defined_filter[]) RETURNS boolean
    LANGUAGE sql STABLE
    AS $$
    select
        filters is null
        or array_length(filters, 1) is null
        or coalesce(
            count(col.name) = count(1)
            and sum(
                realtime.check_equality_op(
                    op:=f.op,
                    type_:=coalesce(col.type_oid::regtype, col.type_name::regtype),
                    val_1:=col.value #>> '{}',
                    val_2:=f.value,
                    negate:=coalesce(f.negate, false)
                )::int
            ) filter (where col.name is not null) = count(col.name),
            false
        )
    from
        unnest(filters) f
        left join unnest(columns) col
            on f.column_name = col.name;
$$;


ALTER FUNCTION realtime.is_visible_through_filters(columns realtime.wal_column[], filters realtime.user_defined_filter[]) OWNER TO supabase_realtime_admin;

--
-- Name: list_changes(name, name, integer, integer); Type: FUNCTION; Schema: realtime; Owner: supabase_realtime_admin
--

CREATE FUNCTION realtime.list_changes(publication name, slot_name name, max_changes integer, max_record_bytes integer) RETURNS TABLE(wal jsonb, is_rls_enabled boolean, subscription_ids uuid[], errors text[], slot_changes_count bigint)
    LANGUAGE sql
    SET log_min_messages TO 'fatal'
    AS $$
  WITH pub AS (
    SELECT
      concat_ws(
        ',',
        CASE WHEN bool_or(pubinsert) THEN 'insert' ELSE NULL END,
        CASE WHEN bool_or(pubupdate) THEN 'update' ELSE NULL END,
        CASE WHEN bool_or(pubdelete) THEN 'delete' ELSE NULL END
      ) AS w2j_actions,
      coalesce(
        string_agg(
          realtime.quote_wal2json(format('%I.%I', schemaname, tablename)::regclass),
          ','
        ) filter (WHERE ppt.tablename IS NOT NULL),
        ''
      ) AS w2j_add_tables
    FROM pg_publication pp
    LEFT JOIN pg_publication_tables ppt ON pp.pubname = ppt.pubname
    WHERE pp.pubname = publication
    GROUP BY pp.pubname
    LIMIT 1
  ),
  -- MATERIALIZED ensures pg_logical_slot_get_changes is called exactly once
  w2j AS MATERIALIZED (
    SELECT x.*, pub.w2j_add_tables
    FROM pub,
         pg_logical_slot_get_changes(
           slot_name, null, max_changes,
           'include-pk', 'true',
           'include-transaction', 'false',
           'include-timestamp', 'true',
           'include-type-oids', 'true',
           'format-version', '2',
           'actions', pub.w2j_actions,
           'add-tables', pub.w2j_add_tables
         ) x
  ),
  slot_count AS (
    SELECT count(*)::bigint AS cnt
    FROM w2j
    WHERE w2j.w2j_add_tables <> ''
  ),
  rls_filtered AS (
    SELECT xyz.wal, xyz.is_rls_enabled, xyz.subscription_ids, xyz.errors
    FROM w2j,
         realtime.apply_rls(
           wal := w2j.data::jsonb,
           max_record_bytes := max_record_bytes
         ) xyz(wal, is_rls_enabled, subscription_ids, errors)
    WHERE w2j.w2j_add_tables <> ''
      AND xyz.subscription_ids[1] IS NOT NULL
  )
  SELECT rf.wal, rf.is_rls_enabled, rf.subscription_ids, rf.errors, sc.cnt
  FROM rls_filtered rf, slot_count sc

  UNION ALL

  SELECT null, null, null, null, sc.cnt
  FROM slot_count sc
  WHERE NOT EXISTS (SELECT 1 FROM rls_filtered)
$$;


ALTER FUNCTION realtime.list_changes(publication name, slot_name name, max_changes integer, max_record_bytes integer) OWNER TO supabase_realtime_admin;

--
-- Name: list_changes_sync(name, name, integer, integer); Type: FUNCTION; Schema: realtime; Owner: supabase_realtime_admin
--

CREATE FUNCTION realtime.list_changes_sync(publication name, slot_name name, max_changes integer, max_record_bytes integer) RETURNS TABLE(wal jsonb, is_rls_enabled boolean, subscription_ids uuid[], errors text[], slot_changes_count bigint)
    LANGUAGE sql
    SET log_min_messages TO 'fatal'
    AS $$
  WITH pub AS (
    SELECT
      concat_ws(
        ',',
        CASE WHEN bool_or(pubinsert) THEN 'insert' ELSE NULL END,
        CASE WHEN bool_or(pubupdate) THEN 'update' ELSE NULL END,
        CASE WHEN bool_or(pubdelete) THEN 'delete' ELSE NULL END
      ) AS w2j_actions,
      coalesce(
        string_agg(
          realtime.quote_wal2json(format('%I.%I', schemaname, tablename)::regclass),
          ','
        ) filter (WHERE ppt.tablename IS NOT NULL),
        ''
      ) AS w2j_add_tables
    FROM pg_publication pp
    LEFT JOIN pg_publication_tables ppt ON pp.pubname = ppt.pubname
    WHERE pp.pubname = publication
    GROUP BY pp.pubname
    LIMIT 1
  ),
  -- MATERIALIZED ensures the slot is read exactly once.
  consumed AS MATERIALIZED (
    SELECT x.*, pub.w2j_add_tables
    FROM pub,
         realtime.settled_changes(
           slot_name, max_changes,
           'include-pk', 'true',
           'include-transaction', 'false',
           'include-timestamp', 'true',
           'include-type-oids', 'true',
           'format-version', '2',
           'actions', pub.w2j_actions,
           'add-tables', pub.w2j_add_tables
         ) x
  ),
  slot_count AS (
    SELECT count(*)::bigint AS cnt
    FROM consumed
    WHERE consumed.w2j_add_tables <> ''
  ),
  rls_filtered AS (
    SELECT xyz.wal, xyz.is_rls_enabled, xyz.subscription_ids, xyz.errors
    FROM consumed,
         realtime.apply_rls(
           wal := consumed.data::jsonb,
           max_record_bytes := max_record_bytes
         ) xyz(wal, is_rls_enabled, subscription_ids, errors)
    WHERE consumed.w2j_add_tables <> ''
      AND xyz.subscription_ids[1] IS NOT NULL
  )
  SELECT rf.wal, rf.is_rls_enabled, rf.subscription_ids, rf.errors, sc.cnt
  FROM rls_filtered rf, slot_count sc

  UNION ALL

  SELECT null, null, null, null, sc.cnt
  FROM slot_count sc
  WHERE NOT EXISTS (SELECT 1 FROM rls_filtered)
$$;


ALTER FUNCTION realtime.list_changes_sync(publication name, slot_name name, max_changes integer, max_record_bytes integer) OWNER TO supabase_realtime_admin;

--
-- Name: quote_wal2json(regclass); Type: FUNCTION; Schema: realtime; Owner: supabase_realtime_admin
--

CREATE FUNCTION realtime.quote_wal2json(entity regclass) RETURNS text
    LANGUAGE sql IMMUTABLE STRICT
    AS $$
  SELECT
    realtime.wal2json_escape_identifier(nsp.nspname::text)
    || '.'
    || realtime.wal2json_escape_identifier(pc.relname::text)
  FROM pg_class pc
  JOIN pg_namespace nsp ON pc.relnamespace = nsp.oid
  WHERE pc.oid = entity
$$;


ALTER FUNCTION realtime.quote_wal2json(entity regclass) OWNER TO supabase_realtime_admin;

--
-- Name: send(jsonb, text, text, boolean); Type: FUNCTION; Schema: realtime; Owner: supabase_realtime_admin
--

CREATE FUNCTION realtime.send(payload jsonb, event text, topic text, private boolean DEFAULT true) RETURNS void
    LANGUAGE plpgsql
    AS $$
DECLARE
  generated_id uuid;
  final_payload jsonb;
BEGIN
  BEGIN
    generated_id := gen_random_uuid();

    -- Check if payload has an 'id' key, if not, add the generated UUID
    IF payload ? 'id' THEN
      final_payload := payload;
    ELSE
      final_payload := jsonb_set(payload, '{id}', to_jsonb(generated_id));
    END IF;

    -- Set the topic configuration
    EXECUTE format('SET LOCAL realtime.topic TO %L', topic);

    INSERT INTO realtime.messages (id, payload, event, topic, private, extension)
    VALUES (generated_id, final_payload, event, topic, private, 'broadcast');
  EXCEPTION
    WHEN OTHERS THEN
      RAISE WARNING 'WarnSendingBroadcastMessage: %', SQLERRM;
  END;
END;
$$;


ALTER FUNCTION realtime.send(payload jsonb, event text, topic text, private boolean) OWNER TO supabase_realtime_admin;

--
-- Name: send_binary(bytea, text, text, boolean); Type: FUNCTION; Schema: realtime; Owner: supabase_realtime_admin
--

CREATE FUNCTION realtime.send_binary(payload bytea, event text, topic text, private boolean DEFAULT true) RETURNS void
    LANGUAGE plpgsql
    AS $$
DECLARE
  generated_id uuid;
BEGIN
  BEGIN
    generated_id := gen_random_uuid();

    EXECUTE format('SET LOCAL realtime.topic TO %L', topic);

    INSERT INTO realtime.messages (id, binary_payload, event, topic, private, extension)
    VALUES (generated_id, payload, event, topic, private, 'broadcast');
  EXCEPTION
    WHEN OTHERS THEN
      RAISE WARNING 'WarnSendingBroadcastMessage: %', SQLERRM;
  END;
END;
$$;


ALTER FUNCTION realtime.send_binary(payload bytea, event text, topic text, private boolean) OWNER TO supabase_realtime_admin;

--
-- Name: settled_changes(name, integer, text[]); Type: FUNCTION; Schema: realtime; Owner: supabase_realtime_admin
--

CREATE FUNCTION realtime.settled_changes(slot_name name, max_changes integer, VARIADIC opts text[]) RETURNS TABLE(lsn pg_lsn, xid xid, data text)
    LANGUAGE plpgsql
    AS $$
declare
  upto pg_lsn;
  total bigint;
  xids xid[];
  starts bigint[];
  snapshot pg_snapshot;
  running_xids xid[];
  xmax_age int;
  cut bigint;
begin
  -- Each statement in a volatile function takes its own snapshot, which is what lets the
  -- check below see a writer that was still in flight when the peek ran. Under REPEATABLE
  -- READ the snapshot never advances, so a deferred change would never be released.
  if current_setting('transaction_isolation') <> 'read committed' then
    raise exception 'realtime.settled_changes requires READ COMMITTED';
  end if;

  -- The peek and the read below cover the same WAL, so the read cannot reach a commit the
  -- check never saw.
  upto := pg_current_wal_flush_lsn();

  -- One entry per transaction, in commit order: its xid and the position of its first
  -- change. The peek uses the caller's own options, so max_changes counts exactly what the
  -- read counts. A non-transactional logical message is emitted as soon as it is decoded,
  -- tagged with the xid of whatever transaction wrote it, so it does not mark where that
  -- transaction starts.
  select coalesce(sum(g.n), 0),
         array_agg(g.x order by g.first) filter (where g.first is not null),
         array_agg(g.first order by g.first) filter (where g.first is not null)
    into total, xids, starts
    from (
      select p.xid as x, count(*) as n,
             min(p.ord) filter (where not case
               when starts_with(p.data, '{"action":"M"') then (p.data::jsonb->>'transactional')::boolean is false
               else false
             end) as first
      from pg_logical_slot_peek_changes(slot_name, upto, max_changes, variadic opts)
           with ordinality as p(lsn, xid, data, ord)
      group by p.xid
    ) g;

  -- Nothing for the caller, but the slot still has to move past what the peek covered.
  if total = 0 then
    perform pg_replication_slot_advance(slot_name, upto);
    return;
  end if;

  if xids is not null then
    -- Taken after the peek is materialized, so a writer that was still in flight during
    -- decoding is guaranteed to show up here.
    snapshot := pg_current_snapshot();

    -- A commit record reaches the WAL before the writer leaves the proc array, so a change
    -- can be decoded while its row is invisible. apply_rls would resolve a policy against a
    -- row it cannot see and authorize it for nobody, while the read consumed it regardless.
    --
    -- xip lists transactions running when the snapshot was taken. It does not cover a writer
    -- whose xid sits at or beyond xmax, which never appears there, so the horizon is checked
    -- too. age() counts backwards from the current xid and so compares correctly across
    -- wraparound.
    select coalesce(array_agg(running.x::xid), array[]::xid[])
      into running_xids
      from pg_snapshot_xip(snapshot) running(x);
    xmax_age := age(pg_snapshot_xmax(snapshot)::xid);

    select min(u.s) into cut
      from unnest(xids, starts) as u(x, s)
      where u.x = any(running_xids) or age(u.x) <= xmax_age;
  end if;

  -- The read stops right after the commit that brings its count to upto_nchanges, so the
  -- count of changes in front of the first unsettled transaction stops it just before that
  -- transaction.
  if cut is null then
    return query
      select p.* from pg_logical_slot_get_changes(slot_name, upto, max_changes, variadic opts) p;
  elsif cut > 1 then
    return query
      select p.* from pg_logical_slot_get_changes(slot_name, upto, (cut - 1)::int, variadic opts) p;
  end if;
end;
$$;


ALTER FUNCTION realtime.settled_changes(slot_name name, max_changes integer, VARIADIC opts text[]) OWNER TO supabase_realtime_admin;

--
-- Name: subscription_check_filters(); Type: FUNCTION; Schema: realtime; Owner: supabase_realtime_admin
--

CREATE FUNCTION realtime.subscription_check_filters() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
declare
    col_names text[] = coalesce(
            array_agg(a.attname order by a.attnum),
            '{}'::text[]
        )
        from
            pg_catalog.pg_attribute a
        where
            a.attrelid = new.entity
            and a.attnum > 0
            and not a.attisdropped
            and pg_catalog.has_column_privilege(
                (new.claims ->> 'role'),
                a.attrelid,
                a.attnum,
                'SELECT'
            );
    filter realtime.user_defined_filter;
    col_type regtype;
    in_val jsonb;
    selected_col text;
begin
    for filter in select * from unnest(new.filters) loop
        if not filter.column_name = any(col_names) then
            raise exception 'invalid column for filter %', filter.column_name;
        end if;

        col_type = (
            select atttypid::regtype
            from pg_catalog.pg_attribute
            where attrelid = new.entity
                  and attname = filter.column_name
        );
        if col_type is null then
            raise exception 'failed to lookup type for column %', filter.column_name;
        end if;

        if filter.op = 'in'::realtime.equality_op then
            in_val = realtime.cast(filter.value, (col_type::text || '[]')::regtype);
            if coalesce(jsonb_array_length(in_val), 0) > 100 then
                raise exception 'too many values for `in` filter. Maximum 100';
            end if;
        elsif filter.op = 'is'::realtime.equality_op then
            -- `is` requires a keyword RHS rather than a typed literal
            if filter.value not in ('null', 'true', 'false', 'unknown') then
                raise exception 'invalid value for is filter: must be null, true, false, or unknown';
            end if;
            -- IS NULL works for any type, but IS TRUE/FALSE/UNKNOWN require a boolean
            -- operand. Reject the non-null keywords on non-boolean columns here so they
            -- don't abort apply_rls at WAL time.
            if filter.value <> 'null' and col_type <> 'boolean'::regtype then
                raise exception 'is % filter requires a boolean column, got %', filter.value, col_type::text;
            end if;
        elsif filter.op in ('like'::realtime.equality_op, 'ilike'::realtime.equality_op) then
            -- like/ilike apply the text pattern operator (~~); reject column types that
            -- have no such operator instead of failing at WAL time
            if not exists (
                select 1 from pg_catalog.pg_operator
                where oprname = '~~' and oprleft = col_type
            ) then
                raise exception 'operator % requires a text-compatible column type, got %', filter.op::text, col_type::text;
            end if;
        elsif filter.op in ('match'::realtime.equality_op, 'imatch'::realtime.equality_op) then
            -- match/imatch apply the regex operators ~ / ~*; reject column types that have
            -- no such operator (e.g. integer) instead of failing at WAL time, mirroring the
            -- like/ilike guard above.
            if not exists (
                select 1 from pg_catalog.pg_operator
                where oprname = case when filter.op = 'imatch'::realtime.equality_op then '~*' else '~' end
                  and oprleft = col_type
                  and oprright = col_type
                  and oprresult = 'boolean'::regtype
            ) then
                raise exception 'operator % requires a text-compatible column type, got %', filter.op::text, col_type::text;
            end if;
            -- validate the regex eagerly so a bad pattern is rejected here, not inside
            -- apply_rls where it would abort the WAL stream for the entity
            begin
                perform '' ~ filter.value;
            exception when others then
                raise exception 'invalid regular expression for % filter: %', filter.op::text, sqlerrm;
            end;
        else
            -- eq/neq/lt/lte/gt/gte: value must be coercable to the type
            perform realtime.cast(filter.value, col_type);
        end if;
    end loop;

    if new.selected_columns is not null then
        for selected_col in select * from unnest(new.selected_columns) loop
            if not selected_col = any(col_names) then
                raise exception 'invalid column for select %', selected_col;
            end if;
        end loop;
    end if;

    -- Apply consistent order to filters so the unique constraint can't be tricked by a
    -- different filter order. negate is part of the sort key.
    new.filters = coalesce(
        array_agg(f order by f.column_name, f.op, f.value, f.negate),
        '{}'
    ) from unnest(new.filters) f;

    -- Normalize selected_columns order so ARRAY['a','b'] and ARRAY['b','a'] are treated
    -- as the same subscription group in apply_rls. Preserve an empty array as '{}'
    -- ("primary keys only") so it stays distinct from NULL ("all columns"); array_agg
    -- over an empty set would otherwise collapse '{}' back to NULL.
    if new.selected_columns is not null then
        new.selected_columns = coalesce(
            (
                select array_agg(c order by c)
                from unnest(new.selected_columns) c
            ),
            '{}'::text[]
        );
    end if;

    return new;
end;
$$;


ALTER FUNCTION realtime.subscription_check_filters() OWNER TO supabase_realtime_admin;

--
-- Name: to_regrole(text); Type: FUNCTION; Schema: realtime; Owner: supabase_realtime_admin
--

CREATE FUNCTION realtime.to_regrole(role_name text) RETURNS regrole
    LANGUAGE sql IMMUTABLE
    AS $$ select role_name::regrole $$;


ALTER FUNCTION realtime.to_regrole(role_name text) OWNER TO supabase_realtime_admin;

--
-- Name: topic(); Type: FUNCTION; Schema: realtime; Owner: supabase_realtime_admin
--

CREATE FUNCTION realtime.topic() RETURNS text
    LANGUAGE sql STABLE
    AS $$
select nullif(current_setting('realtime.topic', true), '')::text;
$$;


ALTER FUNCTION realtime.topic() OWNER TO supabase_realtime_admin;

--
-- Name: wal2json_escape_identifier(text); Type: FUNCTION; Schema: realtime; Owner: supabase_realtime_admin
--

CREATE FUNCTION realtime.wal2json_escape_identifier(name text) RETURNS text
    LANGUAGE sql IMMUTABLE STRICT
    AS $$
  -- Prefix `\`, `,`, `.`, and any whitespace with `\`
  SELECT regexp_replace(name, '([\\,.[:space:]])', '\\\1', 'g')
$$;


ALTER FUNCTION realtime.wal2json_escape_identifier(name text) OWNER TO supabase_realtime_admin;

--
-- Name: allow_any_operation(text[]); Type: FUNCTION; Schema: storage; Owner: supabase_storage_admin
--

CREATE FUNCTION storage.allow_any_operation(expected_operations text[]) RETURNS boolean
    LANGUAGE sql STABLE
    AS $$
  WITH current_operation AS (
    SELECT storage.operation() AS raw_operation
  ),
  normalized AS (
    SELECT CASE
      WHEN raw_operation LIKE 'storage.%' THEN substr(raw_operation, 9)
      ELSE raw_operation
    END AS current_operation
    FROM current_operation
  )
  SELECT EXISTS (
    SELECT 1
    FROM normalized n
    CROSS JOIN LATERAL unnest(expected_operations) AS expected_operation
    WHERE expected_operation IS NOT NULL
      AND expected_operation <> ''
      AND n.current_operation = CASE
        WHEN expected_operation LIKE 'storage.%' THEN substr(expected_operation, 9)
        ELSE expected_operation
      END
  );
$$;


ALTER FUNCTION storage.allow_any_operation(expected_operations text[]) OWNER TO supabase_storage_admin;

--
-- Name: allow_only_operation(text); Type: FUNCTION; Schema: storage; Owner: supabase_storage_admin
--

CREATE FUNCTION storage.allow_only_operation(expected_operation text) RETURNS boolean
    LANGUAGE sql STABLE
    AS $$
  WITH current_operation AS (
    SELECT storage.operation() AS raw_operation
  ),
  normalized AS (
    SELECT
      CASE
        WHEN raw_operation LIKE 'storage.%' THEN substr(raw_operation, 9)
        ELSE raw_operation
      END AS current_operation,
      CASE
        WHEN expected_operation LIKE 'storage.%' THEN substr(expected_operation, 9)
        ELSE expected_operation
      END AS requested_operation
    FROM current_operation
  )
  SELECT CASE
    WHEN requested_operation IS NULL OR requested_operation = '' THEN FALSE
    ELSE COALESCE(current_operation = requested_operation, FALSE)
  END
  FROM normalized;
$$;


ALTER FUNCTION storage.allow_only_operation(expected_operation text) OWNER TO supabase_storage_admin;

--
-- Name: can_insert_object(text, text, uuid, jsonb); Type: FUNCTION; Schema: storage; Owner: supabase_storage_admin
--

CREATE FUNCTION storage.can_insert_object(bucketid text, name text, owner uuid, metadata jsonb) RETURNS void
    LANGUAGE plpgsql
    AS $$
BEGIN
  INSERT INTO "storage"."objects" ("bucket_id", "name", "owner", "metadata") VALUES (bucketid, name, owner, metadata);
  -- hack to rollback the successful insert
  RAISE sqlstate 'PT200' using
  message = 'ROLLBACK',
  detail = 'rollback successful insert';
END
$$;


ALTER FUNCTION storage.can_insert_object(bucketid text, name text, owner uuid, metadata jsonb) OWNER TO supabase_storage_admin;

--
-- Name: enforce_bucket_lifecycle_service_role(); Type: FUNCTION; Schema: storage; Owner: supabase_storage_admin
--

CREATE FUNCTION storage.enforce_bucket_lifecycle_service_role() RETURNS trigger
    LANGUAGE plpgsql
    SET search_path TO 'pg_catalog'
    AS $$
BEGIN
  IF current_user::text IS DISTINCT FROM TG_ARGV[0]
     AND (
       OLD.lifecycle_configuration IS DISTINCT FROM NEW.lifecycle_configuration
       OR OLD.lifecycle_configuration_generation IS DISTINCT FROM NEW.lifecycle_configuration_generation
     ) THEN
    -- AFTER runs only after caller RLS has accepted the proposed row. The API
    -- recognizes this specific error after rolling back its permission probe;
    -- direct non-service writes still fail and cannot persist the change.
    RAISE EXCEPTION 'bucket control columns may only be changed by the configured storage service role'
      USING ERRCODE = 'PST01',
            SCHEMA = TG_TABLE_SCHEMA,
            TABLE = TG_TABLE_NAME,
            CONSTRAINT = TG_NAME;
  END IF;

  RETURN NULL;
END;
$$;


ALTER FUNCTION storage.enforce_bucket_lifecycle_service_role() OWNER TO supabase_storage_admin;

--
-- Name: enforce_bucket_name_length(); Type: FUNCTION; Schema: storage; Owner: supabase_storage_admin
--

CREATE FUNCTION storage.enforce_bucket_name_length() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
begin
    if length(new.name) > 100 then
        raise exception 'bucket name "%" is too long (% characters). Max is 100.', new.name, length(new.name);
    end if;
    return new;
end;
$$;


ALTER FUNCTION storage.enforce_bucket_name_length() OWNER TO supabase_storage_admin;

--
-- Name: extension(text); Type: FUNCTION; Schema: storage; Owner: supabase_storage_admin
--

CREATE FUNCTION storage.extension(name text) RETURNS text
    LANGUAGE plpgsql IMMUTABLE
    AS $$
DECLARE
    _parts text[];
    _filename text;
BEGIN
    -- Split on "/" to get path segments
    SELECT string_to_array(name, '/') INTO _parts;
    -- Get the last path segment (the actual filename)
    SELECT _parts[array_length(_parts, 1)] INTO _filename;
    -- Extract extension: reverse, split on '.', then reverse again
    RETURN reverse(split_part(reverse(_filename), '.', 1));
END
$$;


ALTER FUNCTION storage.extension(name text) OWNER TO supabase_storage_admin;

--
-- Name: filename(text); Type: FUNCTION; Schema: storage; Owner: supabase_storage_admin
--

CREATE FUNCTION storage.filename(name text) RETURNS text
    LANGUAGE plpgsql IMMUTABLE
    AS $$
DECLARE
    _parts text[];
BEGIN
    SELECT string_to_array(name, '/') INTO _parts;
    RETURN _parts[array_length(_parts, 1)];
END
$$;


ALTER FUNCTION storage.filename(name text) OWNER TO supabase_storage_admin;

--
-- Name: foldername(text); Type: FUNCTION; Schema: storage; Owner: supabase_storage_admin
--

CREATE FUNCTION storage.foldername(name text) RETURNS text[]
    LANGUAGE plpgsql IMMUTABLE
    AS $$
DECLARE
    _parts text[];
BEGIN
    -- Split on "/" to get path segments
    SELECT string_to_array(name, '/') INTO _parts;
    -- Return everything except the last segment
    RETURN _parts[1 : array_length(_parts,1) - 1];
END
$$;


ALTER FUNCTION storage.foldername(name text) OWNER TO supabase_storage_admin;

--
-- Name: get_common_prefix(text, text, text); Type: FUNCTION; Schema: storage; Owner: supabase_storage_admin
--

CREATE FUNCTION storage.get_common_prefix(p_key text, p_prefix text, p_delimiter text) RETURNS text
    LANGUAGE sql IMMUTABLE
    AS $$
SELECT CASE
    WHEN p_delimiter <> ''
         AND position(p_delimiter IN substring(p_key FROM length(p_prefix) + 1)) > 0
    THEN left(
        p_key,
        length(p_prefix)
            + position(p_delimiter IN substring(p_key FROM length(p_prefix) + 1))
            + length(p_delimiter) - 1
    )
    ELSE NULL
END;
$$;


ALTER FUNCTION storage.get_common_prefix(p_key text, p_prefix text, p_delimiter text) OWNER TO supabase_storage_admin;

--
-- Name: get_size_by_bucket(text, text); Type: FUNCTION; Schema: storage; Owner: supabase_storage_admin
--

CREATE FUNCTION storage.get_size_by_bucket(noncurrent_versions text DEFAULT 'include'::text, delete_markers text DEFAULT 'include'::text) RETURNS TABLE(size bigint, bucket_id text)
    LANGUAGE plpgsql STABLE
    AS $$
BEGIN
    -- COALESCE first: NULL NOT IN (...) evaluates to NULL (not TRUE), so a
    -- bare NOT IN check silently leaves an explicit NULL argument unreset.
    noncurrent_versions := COALESCE(noncurrent_versions, 'include');
    delete_markers := COALESCE(delete_markers, 'include');
    IF noncurrent_versions NOT IN ('exclude', 'only', 'include') THEN
        noncurrent_versions := 'include';
    END IF;
    IF delete_markers NOT IN ('exclude', 'only', 'include') THEN
        delete_markers := 'include';
    END IF;

    return query
        select sum((metadata->>'size')::bigint)::bigint as size, obj.bucket_id
        from "storage".objects as obj
        where (noncurrent_versions != 'exclude' OR obj.archived_at IS NULL)
          and (noncurrent_versions != 'only' OR obj.archived_at IS NOT NULL)
          and (delete_markers != 'exclude' OR NOT obj.is_delete_marker)
          and (delete_markers != 'only' OR obj.is_delete_marker)
        group by obj.bucket_id;
END
$$;


ALTER FUNCTION storage.get_size_by_bucket(noncurrent_versions text, delete_markers text) OWNER TO supabase_storage_admin;

--
-- Name: list_multipart_uploads_with_delimiter(text, text, text, integer, text, text, text); Type: FUNCTION; Schema: storage; Owner: supabase_storage_admin
--

CREATE FUNCTION storage.list_multipart_uploads_with_delimiter(bucket_id text, prefix_param text, delimiter_param text, max_keys integer DEFAULT 100, next_key_token text DEFAULT ''::text, next_upload_token text DEFAULT ''::text, raw_prefix_param text DEFAULT NULL::text) RETURNS TABLE(key text, id text, created_at timestamp with time zone)
    LANGUAGE sql STABLE
    AS $_$
WITH candidates AS (
    SELECT
        upload.key AS object_key,
        CASE
            WHEN position($3 IN substring(upload.key FROM length(coalesce($7, $2)) + 1)) > 0
            THEN left(
                upload.key,
                length(coalesce($7, $2))
                    + position($3 IN substring(upload.key FROM length(coalesce($7, $2)) + 1))
                    + length($3) - 1
            )
            ELSE upload.key
        END AS result_key,
        upload.id,
        upload.created_at,
        position($3 IN substring(upload.key FROM length(coalesce($7, $2)) + 1)) > 0 AS is_common_prefix
    FROM storage.s3_multipart_uploads AS upload
    WHERE upload.bucket_id = $1
      AND upload.key COLLATE "C" LIKE $2 || '%'
), filtered AS (
    SELECT candidate.*
    FROM candidates AS candidate
    WHERE $5 = ''
       OR candidate.result_key COLLATE "C" > $5
       OR (
           candidate.result_key COLLATE "C" = $5
           AND NOT candidate.is_common_prefix
           AND $6 <> ''
           -- A completed or aborted marker repeats the remaining same-key uploads.
           AND COALESCE(
               (candidate.created_at, candidate.id COLLATE "C") > (
                   SELECT marker.created_at, marker.id COLLATE "C"
                   FROM storage.s3_multipart_uploads AS marker
                   WHERE marker.bucket_id = $1
                     AND marker.key COLLATE "C" = $5
                     AND marker.id = $6
               ),
               TRUE
           )
       )
), ranked AS (
    SELECT
        filtered.*,
        row_number() OVER (
            PARTITION BY filtered.result_key COLLATE "C"
            ORDER BY filtered.created_at, filtered.id COLLATE "C"
        ) AS prefix_rank
    FROM filtered
)
SELECT ranked.result_key, ranked.id, ranked.created_at
FROM ranked
WHERE NOT ranked.is_common_prefix OR ranked.prefix_rank = 1
ORDER BY ranked.result_key COLLATE "C", ranked.created_at, ranked.id COLLATE "C"
LIMIT $4;
$_$;


ALTER FUNCTION storage.list_multipart_uploads_with_delimiter(bucket_id text, prefix_param text, delimiter_param text, max_keys integer, next_key_token text, next_upload_token text, raw_prefix_param text) OWNER TO supabase_storage_admin;

--
-- Name: list_objects_with_delimiter(text, text, text, integer, text, text, text, text, text, timestamp with time zone, text); Type: FUNCTION; Schema: storage; Owner: supabase_storage_admin
--

CREATE FUNCTION storage.list_objects_with_delimiter(_bucket_id text, prefix_param text, delimiter_param text, max_keys integer DEFAULT 100, start_after text DEFAULT ''::text, next_token text DEFAULT ''::text, sort_order text DEFAULT 'asc'::text, noncurrent_versions text DEFAULT 'exclude'::text, delete_markers text DEFAULT 'exclude'::text, next_token_archived_at timestamp with time zone DEFAULT NULL::timestamp with time zone, next_token_version text DEFAULT ''::text) RETURNS TABLE(name text, id uuid, metadata jsonb, updated_at timestamp with time zone, created_at timestamp with time zone, last_accessed_at timestamp with time zone, version text, archived_at timestamp with time zone, is_delete_marker boolean, is_versioned boolean)
    LANGUAGE plpgsql STABLE
    AS $_$
DECLARE
    v_peek_name TEXT;
    v_current RECORD;
    v_common_prefix TEXT;

    -- Configuration
    v_is_asc BOOLEAN;
    v_prefix TEXT;
    v_start TEXT;
    v_start_relative TEXT;
    v_upper_bound TEXT;
    v_file_batch_size INT;
    v_version_filter TEXT;

    -- true when noncurrent_versions can return >1 row per name; keeps them
    -- ordered most-recent-first and lets pagination resume mid-key
    v_multi_row BOOLEAN;
    v_name_order TEXT;
    v_exact_range_predicate TEXT;
    v_strict_range_predicate TEXT;
    v_inclusive_range_predicate TEXT;

    -- Seek state for the current name. archived_at is normalized to JavaScript's
    -- millisecond precision and version breaks ties within the same millisecond.
    -- Current rows use 'infinity'; NULL means no tiebreak has been established.
    v_next_seek TEXT;
    v_next_seek_at TIMESTAMPTZ;
    v_next_seek_version TEXT;
    v_next_seek_strict BOOLEAN := false;
    v_cursor_is_folder BOOLEAN;
    v_count INT := 0;
    v_previous_seek TEXT;
    v_previous_seek_at TIMESTAMPTZ;
    v_previous_seek_version TEXT;
    v_previous_count INT;

    -- Dynamic SQL for batch query only
    v_batch_query TEXT;
    v_batch_query_strict TEXT;
    v_delete_marker_peek_query TEXT;
    v_delete_marker_peek_query_strict TEXT;

BEGIN
    -- ========================================================================
    -- INITIALIZATION
    -- ========================================================================
    v_is_asc := lower(coalesce(sort_order, 'asc')) = 'asc';
    v_prefix := coalesce(prefix_param, '');
    v_start := CASE WHEN coalesce(next_token, '') <> '' THEN next_token ELSE coalesce(start_after, '') END;
    v_file_batch_size := LEAST(GREATEST(max_keys * 2, 100), 1000);
    v_next_seek_at := NULL;
    v_next_seek_version := '';

    -- COALESCE first: NULL NOT IN (...) evaluates to NULL (not TRUE), so a
    -- bare NOT IN check silently leaves an explicit NULL argument unreset.
    noncurrent_versions := COALESCE(noncurrent_versions, 'exclude');
    delete_markers := COALESCE(delete_markers, 'exclude');
    IF noncurrent_versions NOT IN ('exclude', 'only', 'include') THEN
        noncurrent_versions := 'exclude';
    END IF;
    IF delete_markers NOT IN ('exclude', 'only', 'include') THEN
        delete_markers := 'exclude';
    END IF;

    v_multi_row := noncurrent_versions IN ('only', 'include');
    v_name_order := CASE WHEN v_is_asc THEN 'ASC' ELSE 'DESC' END;

    v_version_filter := '';
    IF noncurrent_versions = 'exclude' THEN
        v_version_filter := v_version_filter || ' AND o.archived_at IS NULL';
    ELSIF noncurrent_versions = 'only' THEN
        v_version_filter := v_version_filter || ' AND o.archived_at IS NOT NULL';
    END IF;
    IF delete_markers = 'exclude' THEN
        v_version_filter := v_version_filter || ' AND NOT o.is_delete_marker';
    ELSIF delete_markers = 'only' THEN
        v_version_filter := v_version_filter || ' AND o.is_delete_marker';
    END IF;

    -- Calculate upper bound for prefix filtering (bytewise, using COLLATE "C")
    IF v_prefix = '' THEN
        v_upper_bound := NULL;
    ELSE
        v_upper_bound := left(v_prefix, -1) || chr(ascii(right(v_prefix, 1)) + 1);
    END IF;

    -- Keep caller-provided cursors inside the requested prefix range.
    IF v_start <> '' AND v_upper_bound IS NOT NULL THEN
        IF v_is_asc THEN
            IF v_start COLLATE "C" < v_prefix COLLATE "C" THEN
                v_start := '';
            ELSIF v_start COLLATE "C" >= v_upper_bound COLLATE "C" THEN
                RETURN;
            END IF;
        ELSE
            IF v_start COLLATE "C" < v_prefix COLLATE "C" THEN
                RETURN;
            ELSIF v_start COLLATE "C" >= v_upper_bound COLLATE "C" THEN
                v_start := '';
            END IF;
        END IF;
    END IF;

    v_start_relative := substring(v_start FROM length(v_prefix) + 1);

    -- Direction affects only the indexed name range and its ordering. Cursor
    -- state transitions and within-key version ordering stay shared.
    IF v_is_asc THEN
        v_exact_range_predicate := 'TRUE';
        v_strict_range_predicate := 'o.name COLLATE "C" > $2';
        v_inclusive_range_predicate := 'o.name COLLATE "C" >= $2';
        IF v_upper_bound IS NOT NULL THEN
            v_exact_range_predicate := 'o.name COLLATE "C" < $3';
            v_strict_range_predicate := v_strict_range_predicate || ' AND o.name COLLATE "C" < $3';
            v_inclusive_range_predicate := v_inclusive_range_predicate || ' AND o.name COLLATE "C" < $3';
        END IF;
    ELSE
        v_exact_range_predicate := 'TRUE';
        v_strict_range_predicate := 'o.name COLLATE "C" < $2';
        v_inclusive_range_predicate := 'o.name COLLATE "C" < $2';
        IF v_prefix <> '' THEN
            v_exact_range_predicate := 'o.name COLLATE "C" >= $3';
            v_strict_range_predicate := v_strict_range_predicate || ' AND o.name COLLATE "C" >= $3';
            v_inclusive_range_predicate := v_inclusive_range_predicate || ' AND o.name COLLATE "C" >= $3';
        END IF;
    END IF;

    -- Build batch query (dynamic SQL - called infrequently, amortized over many rows)
    -- The multi-row order matches the externally serialized cursor exactly:
    -- archived_at at millisecond precision, then version as the final tiebreak.
    --
    -- When v_multi_row, the seek is a keyset tuple comparison ("name > $2 OR
    -- (name = $2 AND tiebreak)") - Postgres won't split that OR into indexable
    -- form (confirmed even with fully literal values), so as one WHERE clause
    -- it forces a full bucket scan filtered row-by-row. Splitting it into two
    -- independently-indexable branches (exact name match with the tiebreak
    -- filter, vs. strictly-past names) combined with UNION ALL lets each
    -- branch keep name as a real index condition; the outer ORDER BY/LIMIT
    -- re-merges them into the same page the single query used to produce.
    IF v_multi_row THEN
        v_batch_query := format(
            $sql$
            SELECT *
            FROM (
                (
                    SELECT o.name, o.id, o.updated_at, o.created_at,
                           o.last_accessed_at, o.metadata, o.version,
                           o.archived_at, o.is_delete_marker, o.is_versioned
                    FROM storage.objects o
                    WHERE o.bucket_id = $1
                      AND o.name COLLATE "C" = $2
                      AND %s
                      AND NOT $7::boolean
                      AND (
                          $5::timestamptz IS NULL
                          OR COALESCE(date_trunc('milliseconds', o.archived_at), 'infinity'::timestamptz) < $5
                          OR (
                              COALESCE(date_trunc('milliseconds', o.archived_at), 'infinity'::timestamptz) = $5
                              AND COALESCE(o.version, '') > $6
                          )
                      )
                      %s
                    ORDER BY
                        COALESCE(date_trunc('milliseconds', o.archived_at), 'infinity'::timestamptz) DESC,
                        COALESCE(o.version, '') ASC
                    LIMIT $4
                )
                UNION ALL
                (
                    SELECT o.name, o.id, o.updated_at, o.created_at,
                           o.last_accessed_at, o.metadata, o.version,
                           o.archived_at, o.is_delete_marker, o.is_versioned
                    FROM storage.objects o
                    WHERE o.bucket_id = $1
                      AND %s
                      %s
                    ORDER BY
                        o.name COLLATE "C" %s,
                        COALESCE(date_trunc('milliseconds', o.archived_at), 'infinity'::timestamptz) DESC,
                        COALESCE(o.version, '') ASC
                    LIMIT $4
                )
            ) sub
            ORDER BY
                sub.name COLLATE "C" %s,
                COALESCE(date_trunc('milliseconds', sub.archived_at), 'infinity'::timestamptz) DESC,
                COALESCE(sub.version, '') ASC
            LIMIT $4
            $sql$,
            v_exact_range_predicate,
            v_version_filter,
            v_strict_range_predicate,
            v_version_filter,
            v_name_order,
            v_name_order
        );
    ELSE
        v_batch_query := format(
            $sql$
            SELECT o.name, o.id, o.updated_at, o.created_at,
                   o.last_accessed_at, o.metadata, o.version,
                   o.archived_at, o.is_delete_marker, o.is_versioned
            FROM storage.objects o
            WHERE o.bucket_id = $1
              AND %s
              %s
            ORDER BY o.name COLLATE "C" %s, o.archived_at DESC
            LIMIT $4
            $sql$,
            v_inclusive_range_predicate,
            v_version_filter,
            v_name_order
        );

        -- Strict counterpart of the query above: used once the single-row
        -- ASC batch advance (below) has left v_next_seek pointing at the
        -- last row already emitted, so an inclusive predicate would
        -- re-match it forever. Only single-row mode ever sets strict mode,
        -- so this variant is never needed when v_multi_row.
        v_batch_query_strict := format(
            $sql$
            SELECT o.name, o.id, o.updated_at, o.created_at,
                   o.last_accessed_at, o.metadata, o.version,
                   o.archived_at, o.is_delete_marker, o.is_versioned
            FROM storage.objects o
            WHERE o.bucket_id = $1
              AND %s
              %s
            ORDER BY o.name COLLATE "C" %s, o.archived_at DESC
            LIMIT $4
            $sql$,
            v_strict_range_predicate,
            v_version_filter,
            v_name_order
        );
    END IF;

    -- The static peek predicates cannot use the partial delete-marker index
    -- once PL/pgSQL switches to a generic plan because whether
    -- is_delete_marker is required remains parameter-dependent. Reuse the
    -- already-specialized batch query with a one-row limit for this sparse
    -- filter so the plan sees a literal `o.is_delete_marker` predicate.
    IF delete_markers = 'only' THEN
        v_delete_marker_peek_query :=
            'SELECT marker_page.name FROM (' || v_batch_query || ') marker_page LIMIT 1';
        IF NOT v_multi_row THEN
            v_delete_marker_peek_query_strict :=
                'SELECT marker_page.name FROM (' || v_batch_query_strict || ') marker_page LIMIT 1';
        END IF;
    END IF;

    -- ========================================================================
    -- SEEK INITIALIZATION: Determine starting position
    -- ========================================================================
    IF v_start = '' THEN
        IF v_is_asc THEN
            v_next_seek := v_prefix;
        ELSE
            -- DESC without cursor performs one specialized initial seek so
            -- partial current-version and delete-marker indexes remain available.
            EXECUTE format(
                'SELECT o.name FROM storage.objects o WHERE o.bucket_id = $1%s%s ORDER BY o.name COLLATE "C" DESC LIMIT 1',
                CASE WHEN v_upper_bound IS NOT NULL
                    THEN ' AND o.name COLLATE "C" >= $2 AND o.name COLLATE "C" < $3'
                    ELSE ''
                END,
                v_version_filter
            )
            INTO v_next_seek
            USING _bucket_id, v_prefix, v_upper_bound;

            IF v_next_seek IS NOT NULL THEN
                v_next_seek := v_next_seek || delimiter_param;
            ELSE
                RETURN;
            END IF;
        END IF;
    ELSE
        -- Folder continuation tokens retain their trailing delimiter. A
        -- delimiter-less startAfter is always a literal key boundary.
        v_cursor_is_folder := delimiter_param <> ''
            AND v_start_relative <> ''
            AND right(v_start_relative, length(delimiter_param)) = delimiter_param;

        IF v_cursor_is_folder THEN
            v_next_seek := CASE
                WHEN right(v_start, length(delimiter_param)) = delimiter_param
                    THEN v_start
                ELSE v_start || delimiter_param
            END;
            IF v_is_asc THEN
                v_next_seek := left(v_next_seek, -1)
                    || chr(ascii(right(v_next_seek, 1)) + 1);
            END IF;
            v_next_seek_strict := NOT v_is_asc;
        ELSE
            -- leaf object: when v_multi_row, stay on v_start with the
            -- caller-supplied tiebreak so a page boundary mid-key resumes
            -- that key's remaining rows instead of skipping them. Truncate
            -- to milliseconds like every other v_next_seek_at assignment -
            -- harmless today since object.ts's cursor always round-trips
            -- through JS Date first, but this shouldn't rely on that.
            IF v_multi_row THEN
                v_next_seek := v_start;
                v_next_seek_at := date_trunc('milliseconds', next_token_archived_at);
                v_next_seek_version := coalesce(next_token_version, '');
                v_next_seek_strict := coalesce(next_token, '') = '';
            ELSIF v_is_asc THEN
                v_next_seek := v_start;
                v_next_seek_strict := true;
            ELSE
                v_next_seek := v_start;
            END IF;
        END IF;
    END IF;

    -- ========================================================================
    -- MAIN LOOP: Hybrid peek-then-batch algorithm
    -- Uses STATIC SQL for peek (hot path) and DYNAMIC SQL for batch
    -- ========================================================================
    LOOP
        EXIT WHEN v_count >= max_keys;

        v_previous_seek := v_next_seek;
        v_previous_seek_at := v_next_seek_at;
        v_previous_seek_version := v_next_seek_version;
        v_previous_count := v_count;

        -- STEP 1: PEEK using STATIC SQL (plan cached, very fast)
        -- v_multi_row is branched here (rather than folded into the WHERE
        -- clause as a bound parameter) so each concrete query keeps an
        -- unconditional seek predicate - once PL/pgSQL switches to its
        -- cached generic plan (after 5 calls), a parameter-gated
        -- "(NOT v_multi_row AND name >= $x) OR (v_multi_row AND ...)"
        -- predicate stops the planner from using name as an index
        -- condition at all, degrading every subsequent peek to a full
        -- index scan filtered row-by-row instead of a bounded range scan.
        -- v_multi_row's seek predicate is a keyset tuple comparison
        -- ("name > x OR (name = x AND tiebreak)") - Postgres does not
        -- split this OR into indexable form even with fully literal
        -- values, so it falls back to a full scan filtered row-by-row.
        -- Splitting it into two independently-indexable branches (exact
        -- name match with the tiebreak filter, vs. strictly-past name)
        -- combined with UNION ALL lets each branch keep name as a real
        -- index condition; the outer ORDER BY/LIMIT picks whichever of
        -- the (at most 2) rows sorts first.
        IF delete_markers = 'only' THEN
            EXECUTE CASE WHEN v_next_seek_strict AND NOT v_multi_row
                THEN v_delete_marker_peek_query_strict
                ELSE v_delete_marker_peek_query
            END
                INTO v_peek_name
                USING _bucket_id, v_next_seek,
                    CASE WHEN v_is_asc THEN COALESCE(v_upper_bound, v_prefix) ELSE v_prefix END,
                    1, v_next_seek_at, v_next_seek_version, v_next_seek_strict;
        ELSIF v_multi_row THEN
            IF v_is_asc THEN
                IF v_upper_bound IS NOT NULL THEN
                    SELECT sub.name INTO v_peek_name FROM (
                        (SELECT o.name FROM storage.objects o
                         WHERE o.bucket_id = _bucket_id AND o.name COLLATE "C" = v_next_seek
                           AND o.name COLLATE "C" < v_upper_bound
                           AND NOT v_next_seek_strict
                           AND (v_next_seek_at IS NULL
                                OR COALESCE(date_trunc('milliseconds', o.archived_at), 'infinity'::timestamptz) < v_next_seek_at
                                OR (COALESCE(date_trunc('milliseconds', o.archived_at), 'infinity'::timestamptz) = v_next_seek_at
                                    AND COALESCE(o.version, '') > v_next_seek_version))
                           AND (noncurrent_versions != 'exclude' OR o.archived_at IS NULL)
                           AND (noncurrent_versions != 'only' OR o.archived_at IS NOT NULL)
                           AND (delete_markers != 'exclude' OR NOT o.is_delete_marker)
                           AND (delete_markers != 'only' OR o.is_delete_marker)
                         ORDER BY COALESCE(date_trunc('milliseconds', o.archived_at), 'infinity'::timestamptz) DESC, COALESCE(o.version, '') ASC LIMIT 1)
                        UNION ALL
                        (SELECT o.name FROM storage.objects o
                         WHERE o.bucket_id = _bucket_id AND o.name COLLATE "C" > v_next_seek AND o.name COLLATE "C" < v_upper_bound
                           AND (noncurrent_versions != 'exclude' OR o.archived_at IS NULL)
                           AND (noncurrent_versions != 'only' OR o.archived_at IS NOT NULL)
                           AND (delete_markers != 'exclude' OR NOT o.is_delete_marker)
                           AND (delete_markers != 'only' OR o.is_delete_marker)
                         ORDER BY o.name COLLATE "C" ASC LIMIT 1)
                    ) sub ORDER BY sub.name COLLATE "C" ASC LIMIT 1;
                ELSE
                    SELECT sub.name INTO v_peek_name FROM (
                        (SELECT o.name FROM storage.objects o
                         WHERE o.bucket_id = _bucket_id AND o.name COLLATE "C" = v_next_seek
                           AND NOT v_next_seek_strict
                           AND (v_next_seek_at IS NULL
                                OR COALESCE(date_trunc('milliseconds', o.archived_at), 'infinity'::timestamptz) < v_next_seek_at
                                OR (COALESCE(date_trunc('milliseconds', o.archived_at), 'infinity'::timestamptz) = v_next_seek_at
                                    AND COALESCE(o.version, '') > v_next_seek_version))
                           AND (noncurrent_versions != 'exclude' OR o.archived_at IS NULL)
                           AND (noncurrent_versions != 'only' OR o.archived_at IS NOT NULL)
                           AND (delete_markers != 'exclude' OR NOT o.is_delete_marker)
                           AND (delete_markers != 'only' OR o.is_delete_marker)
                         ORDER BY COALESCE(date_trunc('milliseconds', o.archived_at), 'infinity'::timestamptz) DESC, COALESCE(o.version, '') ASC LIMIT 1)
                        UNION ALL
                        (SELECT o.name FROM storage.objects o
                         WHERE o.bucket_id = _bucket_id AND o.name COLLATE "C" > v_next_seek
                           AND (noncurrent_versions != 'exclude' OR o.archived_at IS NULL)
                           AND (noncurrent_versions != 'only' OR o.archived_at IS NOT NULL)
                           AND (delete_markers != 'exclude' OR NOT o.is_delete_marker)
                           AND (delete_markers != 'only' OR o.is_delete_marker)
                         ORDER BY o.name COLLATE "C" ASC LIMIT 1)
                    ) sub ORDER BY sub.name COLLATE "C" ASC LIMIT 1;
                END IF;
            ELSE
                IF v_upper_bound IS NOT NULL THEN
                    SELECT sub.name INTO v_peek_name FROM (
                        (SELECT o.name FROM storage.objects o
                         WHERE o.bucket_id = _bucket_id AND o.name COLLATE "C" = v_next_seek
                           AND o.name COLLATE "C" >= v_prefix
                           AND NOT v_next_seek_strict
                           AND (v_next_seek_at IS NULL
                                OR COALESCE(date_trunc('milliseconds', o.archived_at), 'infinity'::timestamptz) < v_next_seek_at
                                OR (COALESCE(date_trunc('milliseconds', o.archived_at), 'infinity'::timestamptz) = v_next_seek_at
                                    AND COALESCE(o.version, '') > v_next_seek_version))
                           AND (noncurrent_versions != 'exclude' OR o.archived_at IS NULL)
                           AND (noncurrent_versions != 'only' OR o.archived_at IS NOT NULL)
                           AND (delete_markers != 'exclude' OR NOT o.is_delete_marker)
                           AND (delete_markers != 'only' OR o.is_delete_marker)
                         ORDER BY COALESCE(date_trunc('milliseconds', o.archived_at), 'infinity'::timestamptz) DESC, COALESCE(o.version, '') ASC LIMIT 1)
                        UNION ALL
                        (SELECT o.name FROM storage.objects o
                         WHERE o.bucket_id = _bucket_id AND o.name COLLATE "C" < v_next_seek AND o.name COLLATE "C" >= v_prefix
                           AND (noncurrent_versions != 'exclude' OR o.archived_at IS NULL)
                           AND (noncurrent_versions != 'only' OR o.archived_at IS NOT NULL)
                           AND (delete_markers != 'exclude' OR NOT o.is_delete_marker)
                           AND (delete_markers != 'only' OR o.is_delete_marker)
                         ORDER BY o.name COLLATE "C" DESC LIMIT 1)
                    ) sub ORDER BY sub.name COLLATE "C" DESC LIMIT 1;
                ELSE
                    SELECT sub.name INTO v_peek_name FROM (
                        (SELECT o.name FROM storage.objects o
                         WHERE o.bucket_id = _bucket_id AND o.name COLLATE "C" = v_next_seek
                           AND NOT v_next_seek_strict
                           AND (v_next_seek_at IS NULL
                                OR COALESCE(date_trunc('milliseconds', o.archived_at), 'infinity'::timestamptz) < v_next_seek_at
                                OR (COALESCE(date_trunc('milliseconds', o.archived_at), 'infinity'::timestamptz) = v_next_seek_at
                                    AND COALESCE(o.version, '') > v_next_seek_version))
                           AND (noncurrent_versions != 'exclude' OR o.archived_at IS NULL)
                           AND (noncurrent_versions != 'only' OR o.archived_at IS NOT NULL)
                           AND (delete_markers != 'exclude' OR NOT o.is_delete_marker)
                           AND (delete_markers != 'only' OR o.is_delete_marker)
                         ORDER BY COALESCE(date_trunc('milliseconds', o.archived_at), 'infinity'::timestamptz) DESC, COALESCE(o.version, '') ASC LIMIT 1)
                        UNION ALL
                        (SELECT o.name FROM storage.objects o
                         WHERE o.bucket_id = _bucket_id AND o.name COLLATE "C" < v_next_seek
                           AND (noncurrent_versions != 'exclude' OR o.archived_at IS NULL)
                           AND (noncurrent_versions != 'only' OR o.archived_at IS NOT NULL)
                           AND (delete_markers != 'exclude' OR NOT o.is_delete_marker)
                           AND (delete_markers != 'only' OR o.is_delete_marker)
                         ORDER BY o.name COLLATE "C" DESC LIMIT 1)
                    ) sub ORDER BY sub.name COLLATE "C" DESC LIMIT 1;
                END IF;
            END IF;
        ELSE
            -- Single-row mode is always noncurrent_versions='exclude'. Keep
            -- this predicate literal so generic plans use the current index.
            IF v_is_asc THEN
                IF v_next_seek_strict AND v_upper_bound IS NOT NULL THEN
                    SELECT o.name INTO v_peek_name FROM storage.objects o
                    WHERE o.bucket_id = _bucket_id
                      AND o.name COLLATE "C" > v_next_seek
                      AND o.name COLLATE "C" < v_upper_bound
                      AND o.archived_at IS NULL
                      AND (delete_markers != 'exclude' OR NOT o.is_delete_marker)
                      AND (delete_markers != 'only' OR o.is_delete_marker)
                    ORDER BY o.name COLLATE "C" ASC LIMIT 1;
                ELSIF v_next_seek_strict THEN
                    SELECT o.name INTO v_peek_name FROM storage.objects o
                    WHERE o.bucket_id = _bucket_id
                      AND o.name COLLATE "C" > v_next_seek
                      AND o.archived_at IS NULL
                      AND (delete_markers != 'exclude' OR NOT o.is_delete_marker)
                      AND (delete_markers != 'only' OR o.is_delete_marker)
                    ORDER BY o.name COLLATE "C" ASC LIMIT 1;
                ELSIF v_upper_bound IS NOT NULL THEN
                    SELECT o.name INTO v_peek_name FROM storage.objects o
                    WHERE o.bucket_id = _bucket_id
                      AND o.name COLLATE "C" >= v_next_seek
                      AND o.name COLLATE "C" < v_upper_bound
                      AND o.archived_at IS NULL
                      AND (delete_markers != 'exclude' OR NOT o.is_delete_marker)
                      AND (delete_markers != 'only' OR o.is_delete_marker)
                    ORDER BY o.name COLLATE "C" ASC LIMIT 1;
                ELSE
                    SELECT o.name INTO v_peek_name FROM storage.objects o
                    WHERE o.bucket_id = _bucket_id
                      AND o.name COLLATE "C" >= v_next_seek
                      AND o.archived_at IS NULL
                      AND (delete_markers != 'exclude' OR NOT o.is_delete_marker)
                      AND (delete_markers != 'only' OR o.is_delete_marker)
                    ORDER BY o.name COLLATE "C" ASC LIMIT 1;
                END IF;
            ELSE
                IF v_upper_bound IS NOT NULL THEN
                    SELECT o.name INTO v_peek_name FROM storage.objects o
                    WHERE o.bucket_id = _bucket_id
                      AND o.name COLLATE "C" < v_next_seek
                      AND o.name COLLATE "C" >= v_prefix
                      AND o.archived_at IS NULL
                      AND (delete_markers != 'exclude' OR NOT o.is_delete_marker)
                      AND (delete_markers != 'only' OR o.is_delete_marker)
                    ORDER BY o.name COLLATE "C" DESC LIMIT 1;
                ELSE
                    SELECT o.name INTO v_peek_name FROM storage.objects o
                    WHERE o.bucket_id = _bucket_id
                      AND o.name COLLATE "C" < v_next_seek
                      AND o.archived_at IS NULL
                      AND (delete_markers != 'exclude' OR NOT o.is_delete_marker)
                      AND (delete_markers != 'only' OR o.is_delete_marker)
                    ORDER BY o.name COLLATE "C" DESC LIMIT 1;
                END IF;
            END IF;
        END IF;

        EXIT WHEN v_peek_name IS NULL;

        -- STEP 2: Check if this is a FOLDER or FILE
        v_common_prefix := storage.get_common_prefix(v_peek_name, v_prefix, delimiter_param);

        IF v_common_prefix IS NOT NULL THEN
            -- FOLDER: Emit and skip to next folder (no heap access needed)
            name := v_common_prefix;
            id := NULL;
            updated_at := NULL;
            created_at := NULL;
            last_accessed_at := NULL;
            metadata := NULL;
            version := NULL;
            archived_at := NULL;
            is_delete_marker := NULL;
            is_versioned := NULL;
            RETURN NEXT;
            v_count := v_count + 1;

            -- Advance seek past the folder range
            IF v_is_asc THEN
                v_next_seek := left(v_common_prefix, -1)
                    || chr(ascii(right(v_common_prefix, 1)) + 1);
            ELSE
                v_next_seek := v_common_prefix;
            END IF;
            v_next_seek_at := NULL;
            v_next_seek_version := '';
            v_next_seek_strict := NOT v_is_asc;
        ELSE
            -- FILE: Batch fetch using DYNAMIC SQL (overhead amortized over many rows)
            -- For ASC: upper_bound is the exclusive upper limit (< condition)
            -- For DESC: prefix is the inclusive lower limit (>= condition)
            FOR v_current IN EXECUTE CASE WHEN v_next_seek_strict AND NOT v_multi_row THEN v_batch_query_strict ELSE v_batch_query END
                USING _bucket_id, v_next_seek,
                CASE WHEN v_is_asc THEN COALESCE(v_upper_bound, v_prefix) ELSE v_prefix END, v_file_batch_size, v_next_seek_at, v_next_seek_version,
                v_next_seek_strict
            LOOP
                v_common_prefix := storage.get_common_prefix(v_current.name, v_prefix, delimiter_param);

                IF v_common_prefix IS NOT NULL THEN
                    -- Hit a folder: exit batch, let peek handle it. Reset
                    -- strict mode too it may have been set by an earlier
                    -- row in this same batch (see the single-row ASC advance
                    -- below), and v_next_seek here is the folder-triggering
                    -- row's own name, which the next peek must find inclusively.
                    v_next_seek := CASE
                        WHEN v_is_asc THEN v_current.name
                        ELSE v_current.name || delimiter_param
                    END;
                    v_next_seek_at := NULL;
                    v_next_seek_version := '';
                    v_next_seek_strict := false;
                    EXIT;
                END IF;

                -- Emit file
                name := v_current.name;
                id := v_current.id;
                updated_at := v_current.updated_at;
                created_at := v_current.created_at;
                last_accessed_at := v_current.last_accessed_at;
                metadata := v_current.metadata;
                version := v_current.version;
                archived_at := v_current.archived_at;
                is_delete_marker := v_current.is_delete_marker;
                is_versioned := v_current.is_versioned;
                RETURN NEXT;
                v_count := v_count + 1;

                -- when v_multi_row, stay on this name and record its
                -- archived_at as the new tiebreak so remaining rows for the
                -- same key are picked up before moving to the next name
                IF v_multi_row THEN
                    v_next_seek := v_current.name;
                    v_next_seek_at := COALESCE(date_trunc('milliseconds', v_current.archived_at), 'infinity'::timestamptz);
                    v_next_seek_version := COALESCE(v_current.version, '');
                    v_next_seek_strict := false;
                ELSIF v_is_asc THEN
                    -- Appending the delimiter as a fake lexical successor
                    -- would skip a real key like `name || '!'` (or any
                    -- character sorting below the delimiter), which sorts
                    -- between `name` and `name || delimiter`. Track the real
                    -- name and mark the next comparison strict instead.
                    v_next_seek := v_current.name;
                    v_next_seek_strict := true;
                ELSE
                    v_next_seek := v_current.name;
                END IF;

                EXIT WHEN v_count >= max_keys;
            END LOOP;
        END IF;

        IF v_count = v_previous_count
           AND v_next_seek IS NOT DISTINCT FROM v_previous_seek
           AND v_next_seek_at IS NOT DISTINCT FROM v_previous_seek_at
           AND v_next_seek_version IS NOT DISTINCT FROM v_previous_seek_version THEN
            RAISE EXCEPTION 'storage.list_objects_with_delimiter made no progress at seek (%, %, %)',
                v_next_seek, v_next_seek_at, v_next_seek_version;
        END IF;
    END LOOP;
END;
$_$;


ALTER FUNCTION storage.list_objects_with_delimiter(_bucket_id text, prefix_param text, delimiter_param text, max_keys integer, start_after text, next_token text, sort_order text, noncurrent_versions text, delete_markers text, next_token_archived_at timestamp with time zone, next_token_version text) OWNER TO supabase_storage_admin;

--
-- Name: operation(); Type: FUNCTION; Schema: storage; Owner: supabase_storage_admin
--

CREATE FUNCTION storage.operation() RETURNS text
    LANGUAGE plpgsql STABLE
    AS $$
BEGIN
    RETURN current_setting('storage.operation', true);
END;
$$;


ALTER FUNCTION storage.operation() OWNER TO supabase_storage_admin;

--
-- Name: protect_bucket_control_columns(); Type: FUNCTION; Schema: storage; Owner: supabase_storage_admin
--

CREATE FUNCTION storage.protect_bucket_control_columns() RETURNS trigger
    LANGUAGE plpgsql
    SET search_path TO 'pg_catalog'
    AS $$
DECLARE
  configuration_changed boolean;
BEGIN
  IF TG_OP = 'INSERT' THEN
    IF NEW.lifecycle_configuration IS NOT NULL
       OR NEW.lifecycle_configuration_generation IS NOT NULL THEN
      IF NOT pg_has_role(current_user, TG_ARGV[0], 'MEMBER') THEN
        RAISE EXCEPTION 'only members of the configured storage service role may insert lifecycle policy state'
          USING ERRCODE = '42501',
                HINT = format(
                  'Insert with both lifecycle columns NULL and configure lifecycle through the Storage API afterward, or insert as a member of %I.',
                  TG_ARGV[0]
                );
      END IF;
    END IF;

    RETURN NEW;
  END IF;

  configuration_changed =
    OLD.lifecycle_configuration IS DISTINCT FROM NEW.lifecycle_configuration
    OR OLD.lifecycle_configuration_generation IS DISTINCT FROM NEW.lifecycle_configuration_generation;

  IF NOT configuration_changed THEN
    RETURN NEW;
  END IF;

  IF NEW.type IS DISTINCT FROM 'STANDARD' THEN
    RAISE EXCEPTION 'bucket versioning and lifecycle controls require a Standard bucket'
      USING ERRCODE = '0A000';
  END IF;

  IF NEW.lifecycle_configuration IS NULL
     AND NEW.lifecycle_configuration_generation IS NULL THEN
    RETURN NEW;
  END IF;

  IF NEW.lifecycle_configuration IS NULL
     OR NEW.lifecycle_configuration_generation IS NULL
     OR OLD.lifecycle_configuration IS NOT DISTINCT FROM NEW.lifecycle_configuration
     OR OLD.lifecycle_configuration_generation IS NOT DISTINCT FROM NEW.lifecycle_configuration_generation THEN
    RAISE EXCEPTION 'a changed lifecycle policy requires a new non-null generation'
      USING ERRCODE = '22023';
  END IF;

  RETURN NEW;
END;
$$;


ALTER FUNCTION storage.protect_bucket_control_columns() OWNER TO supabase_storage_admin;

--
-- Name: protect_delete(); Type: FUNCTION; Schema: storage; Owner: supabase_storage_admin
--

CREATE FUNCTION storage.protect_delete() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    -- Check if storage.allow_delete_query is set to 'true'
    IF COALESCE(current_setting('storage.allow_delete_query', true), 'false') != 'true' THEN
        RAISE EXCEPTION 'Direct deletion from storage tables is not allowed. Use the Storage API instead.'
            USING HINT = 'This prevents accidental data loss from orphaned objects.',
                  ERRCODE = '42501';
    END IF;
    RETURN NULL;
END;
$$;


ALTER FUNCTION storage.protect_delete() OWNER TO supabase_storage_admin;

--
-- Name: search(text, text, integer, integer, integer, text, text, text, text, text); Type: FUNCTION; Schema: storage; Owner: supabase_storage_admin
--

CREATE FUNCTION storage.search(prefix text, bucketname text, limits integer DEFAULT 100, levels integer DEFAULT 1, offsets integer DEFAULT 0, search text DEFAULT ''::text, sortcolumn text DEFAULT 'name'::text, sortorder text DEFAULT 'asc'::text, noncurrent_versions text DEFAULT 'exclude'::text, delete_markers text DEFAULT 'exclude'::text) RETURNS TABLE(name text, id uuid, updated_at timestamp with time zone, created_at timestamp with time zone, last_accessed_at timestamp with time zone, metadata jsonb, version text, archived_at timestamp with time zone, is_delete_marker boolean, is_versioned boolean)
    LANGUAGE plpgsql STABLE
    AS $_$
DECLARE
    v_peek_name TEXT;
    v_current RECORD;
    v_common_prefix TEXT;
    v_delimiter CONSTANT TEXT := '/';

    -- Configuration
    v_limit INT;
    v_prefix TEXT;
    v_prefix_lower TEXT;
    v_prefix_len INT;
    v_prefix_start INT;
    v_combined_levels INT;
    v_is_asc BOOLEAN;
    v_order_by TEXT;
    v_sort_order TEXT;
    v_upper_bound TEXT;
    v_file_batch_size INT;
    v_version_filter TEXT;
    v_multi_row BOOLEAN;

    -- Dynamic SQL for batch query only
    v_batch_query TEXT;
    v_delete_marker_peek_query TEXT;
    v_delete_marker_peek_query_strict TEXT;

    -- Seek state
    v_next_seek TEXT;
    v_next_seek_at TIMESTAMPTZ;
    v_next_seek_version TEXT;
    v_next_seek_strict BOOLEAN := false;
    v_count INT := 0;
    v_skipped INT := 0;
    v_previous_seek TEXT;
    v_previous_seek_at TIMESTAMPTZ;
    v_previous_seek_version TEXT;
    v_previous_count INT;
    v_previous_skipped INT;
BEGIN
    -- ========================================================================
    -- INITIALIZATION
    -- ========================================================================
    v_limit := LEAST(coalesce(limits, 100), 1500);
    v_prefix := coalesce(prefix, '') || coalesce(search, '');
    v_prefix_lower := lower(v_prefix);
    v_prefix_len := length(coalesce(prefix, ''));
    v_prefix_start := coalesce(array_length(string_to_array(coalesce(prefix, ''), v_delimiter), 1), 1);
    v_combined_levels := coalesce(array_length(string_to_array(v_prefix, v_delimiter), 1), 1);
    v_is_asc := lower(coalesce(sortorder, 'asc')) = 'asc';
    v_file_batch_size := LEAST(GREATEST(v_limit * 2, 100), 1000);
    v_next_seek_at := NULL;
    v_next_seek_version := '';

    -- COALESCE first: NULL NOT IN (...) evaluates to NULL (not TRUE), so a
    -- bare NOT IN check silently leaves an explicit NULL argument unreset.
    noncurrent_versions := COALESCE(noncurrent_versions, 'exclude');
    delete_markers := COALESCE(delete_markers, 'exclude');
    IF noncurrent_versions NOT IN ('exclude', 'only', 'include') THEN
        noncurrent_versions := 'exclude';
    END IF;
    IF delete_markers NOT IN ('exclude', 'only', 'include') THEN
        delete_markers := 'exclude';
    END IF;

    v_multi_row := noncurrent_versions IN ('only', 'include');

    v_version_filter := '';
    IF noncurrent_versions = 'exclude' THEN
        v_version_filter := v_version_filter || ' AND o.archived_at IS NULL';
    ELSIF noncurrent_versions = 'only' THEN
        v_version_filter := v_version_filter || ' AND o.archived_at IS NOT NULL';
    END IF;
    IF delete_markers = 'exclude' THEN
        v_version_filter := v_version_filter || ' AND NOT o.is_delete_marker';
    ELSIF delete_markers = 'only' THEN
        v_version_filter := v_version_filter || ' AND o.is_delete_marker';
    END IF;

    -- Validate sort column
    CASE lower(coalesce(sortcolumn, 'name'))
        WHEN 'name' THEN v_order_by := 'name';
        WHEN 'updated_at' THEN v_order_by := 'updated_at';
        WHEN 'created_at' THEN v_order_by := 'created_at';
        WHEN 'last_accessed_at' THEN v_order_by := 'last_accessed_at';
        ELSE v_order_by := 'name';
    END CASE;

    v_sort_order := CASE WHEN v_is_asc THEN 'asc' ELSE 'desc' END;

    -- ========================================================================
    -- NON-NAME SORTING: Use path_tokens approach
    -- ========================================================================
    IF v_order_by != 'name' THEN
        RETURN QUERY EXECUTE format(
            $sql$
            WITH folders AS (
                SELECT array_to_string(path_tokens[$1:$2], '/') AS folder
                FROM storage.objects
                WHERE objects.name ILIKE $3 || '%%'
                  AND bucket_id = $4
                  AND array_length(objects.path_tokens, 1) <> $2
                  AND ($7 != 'exclude' OR objects.archived_at IS NULL)
                  AND ($7 != 'only' OR objects.archived_at IS NOT NULL)
                  AND ($8 != 'exclude' OR NOT objects.is_delete_marker)
                  AND ($8 != 'only' OR objects.is_delete_marker)
                GROUP BY folder
                ORDER BY folder %s
            )
            (SELECT folder AS "name",
                   NULL::uuid AS id,
                   NULL::timestamptz AS updated_at,
                   NULL::timestamptz AS created_at,
                   NULL::timestamptz AS last_accessed_at,
                   NULL::jsonb AS metadata,
                   NULL::text AS version,
                   NULL::timestamptz AS archived_at,
                   NULL::boolean AS is_delete_marker,
                   NULL::boolean AS is_versioned FROM folders)
            UNION ALL
            (SELECT array_to_string(path_tokens[$1:$2], '/') AS "name",
                   id, updated_at, created_at, last_accessed_at, metadata,
                   version, archived_at, is_delete_marker, is_versioned
             FROM storage.objects
             WHERE objects.name ILIKE $3 || '%%'
               AND bucket_id = $4
               AND array_length(objects.path_tokens, 1) = $2
               AND ($7 != 'exclude' OR objects.archived_at IS NULL)
               AND ($7 != 'only' OR objects.archived_at IS NOT NULL)
               AND ($8 != 'exclude' OR NOT objects.is_delete_marker)
               AND ($8 != 'only' OR objects.is_delete_marker)
             -- name, then version, as tiebreaks so two versions of the same
             -- key tying on the sort column still sort deterministically
             ORDER BY %I %s, name COLLATE "C" %s, COALESCE(version, '') %s)
            LIMIT $5 OFFSET $6
            $sql$, v_sort_order, v_order_by, v_sort_order, v_sort_order, v_sort_order
        ) USING v_prefix_start, v_combined_levels, v_prefix, bucketname, v_limit, offsets, noncurrent_versions, delete_markers;
        RETURN;
    END IF;

    -- ========================================================================
    -- NAME SORTING: Hybrid skip-scan with batch optimization
    -- ========================================================================

    -- Calculate upper bound for prefix filtering
    IF v_prefix_lower = '' THEN
        v_upper_bound := NULL;
    ELSIF right(v_prefix_lower, 1) = v_delimiter THEN
        v_upper_bound := left(v_prefix_lower, -1) || chr(ascii(v_delimiter) + 1);
    ELSE
        v_upper_bound := left(v_prefix_lower, -1) || chr(ascii(right(v_prefix_lower, 1)) + 1);
    END IF;

    -- Build a resume-safe batch query. The exact-name branch returns remaining
    -- versions after the current (archived_at, version) boundary; the strict
    -- name branch returns subsequent keys. UNION ALL keeps both predicates
    -- independently indexable.
    IF v_is_asc THEN
        IF v_upper_bound IS NOT NULL THEN
            v_batch_query := 'SELECT * FROM (' ||
                '(SELECT o.name, o.id, o.updated_at, o.created_at, o.last_accessed_at, o.metadata, o.version, o.archived_at, o.is_delete_marker, o.is_versioned FROM storage.objects o ' ||
                'WHERE o.bucket_id = $1 AND lower(o.name) COLLATE "C" = $2 AND ($5::timestamptz IS NULL OR COALESCE(o.archived_at, ''infinity''::timestamptz) < $5 OR (COALESCE(o.archived_at, ''infinity''::timestamptz) = $5 AND COALESCE(o.version, '''') > $6))' ||
                v_version_filter || ' ORDER BY COALESCE(o.archived_at, ''infinity''::timestamptz) DESC, COALESCE(o.version, '''') ASC LIMIT $4) UNION ALL ' ||
                '(SELECT o.name, o.id, o.updated_at, o.created_at, o.last_accessed_at, o.metadata, o.version, o.archived_at, o.is_delete_marker, o.is_versioned FROM storage.objects o ' ||
                'WHERE o.bucket_id = $1 AND lower(o.name) COLLATE "C" > $2 AND lower(o.name) COLLATE "C" < $3' || v_version_filter ||
                ' ORDER BY lower(o.name) COLLATE "C" ASC, COALESCE(o.archived_at, ''infinity''::timestamptz) DESC, COALESCE(o.version, '''') ASC LIMIT $4)' ||
                ') sub ORDER BY lower(sub.name) COLLATE "C" ASC, COALESCE(sub.archived_at, ''infinity''::timestamptz) DESC, COALESCE(sub.version, '''') ASC LIMIT $4';
        ELSE
            v_batch_query := 'SELECT * FROM (' ||
                '(SELECT o.name, o.id, o.updated_at, o.created_at, o.last_accessed_at, o.metadata, o.version, o.archived_at, o.is_delete_marker, o.is_versioned FROM storage.objects o ' ||
                'WHERE o.bucket_id = $1 AND lower(o.name) COLLATE "C" = $2 AND ($5::timestamptz IS NULL OR COALESCE(o.archived_at, ''infinity''::timestamptz) < $5 OR (COALESCE(o.archived_at, ''infinity''::timestamptz) = $5 AND COALESCE(o.version, '''') > $6))' ||
                v_version_filter || ' ORDER BY COALESCE(o.archived_at, ''infinity''::timestamptz) DESC, COALESCE(o.version, '''') ASC LIMIT $4) UNION ALL ' ||
                '(SELECT o.name, o.id, o.updated_at, o.created_at, o.last_accessed_at, o.metadata, o.version, o.archived_at, o.is_delete_marker, o.is_versioned FROM storage.objects o ' ||
                'WHERE o.bucket_id = $1 AND lower(o.name) COLLATE "C" > $2' || v_version_filter ||
                ' ORDER BY lower(o.name) COLLATE "C" ASC, COALESCE(o.archived_at, ''infinity''::timestamptz) DESC, COALESCE(o.version, '''') ASC LIMIT $4)' ||
                ') sub ORDER BY lower(sub.name) COLLATE "C" ASC, COALESCE(sub.archived_at, ''infinity''::timestamptz) DESC, COALESCE(sub.version, '''') ASC LIMIT $4';
        END IF;
    ELSE
        IF v_upper_bound IS NOT NULL THEN
            v_batch_query := 'SELECT * FROM (' ||
                '(SELECT o.name, o.id, o.updated_at, o.created_at, o.last_accessed_at, o.metadata, o.version, o.archived_at, o.is_delete_marker, o.is_versioned FROM storage.objects o ' ||
                'WHERE o.bucket_id = $1 AND lower(o.name) COLLATE "C" = $2 AND ($5::timestamptz IS NULL OR COALESCE(o.archived_at, ''infinity''::timestamptz) < $5 OR (COALESCE(o.archived_at, ''infinity''::timestamptz) = $5 AND COALESCE(o.version, '''') > $6))' ||
                v_version_filter || ' ORDER BY COALESCE(o.archived_at, ''infinity''::timestamptz) DESC, COALESCE(o.version, '''') ASC LIMIT $4) UNION ALL ' ||
                '(SELECT o.name, o.id, o.updated_at, o.created_at, o.last_accessed_at, o.metadata, o.version, o.archived_at, o.is_delete_marker, o.is_versioned FROM storage.objects o ' ||
                'WHERE o.bucket_id = $1 AND lower(o.name) COLLATE "C" < $2 AND lower(o.name) COLLATE "C" >= $3' || v_version_filter ||
                ' ORDER BY lower(o.name) COLLATE "C" DESC, COALESCE(o.archived_at, ''infinity''::timestamptz) DESC, COALESCE(o.version, '''') ASC LIMIT $4)' ||
                ') sub ORDER BY lower(sub.name) COLLATE "C" DESC, COALESCE(sub.archived_at, ''infinity''::timestamptz) DESC, COALESCE(sub.version, '''') ASC LIMIT $4';
        ELSE
            v_batch_query := 'SELECT * FROM (' ||
                '(SELECT o.name, o.id, o.updated_at, o.created_at, o.last_accessed_at, o.metadata, o.version, o.archived_at, o.is_delete_marker, o.is_versioned FROM storage.objects o ' ||
                'WHERE o.bucket_id = $1 AND lower(o.name) COLLATE "C" = $2 AND ($5::timestamptz IS NULL OR COALESCE(o.archived_at, ''infinity''::timestamptz) < $5 OR (COALESCE(o.archived_at, ''infinity''::timestamptz) = $5 AND COALESCE(o.version, '''') > $6))' ||
                v_version_filter || ' ORDER BY COALESCE(o.archived_at, ''infinity''::timestamptz) DESC, COALESCE(o.version, '''') ASC LIMIT $4) UNION ALL ' ||
                '(SELECT o.name, o.id, o.updated_at, o.created_at, o.last_accessed_at, o.metadata, o.version, o.archived_at, o.is_delete_marker, o.is_versioned FROM storage.objects o ' ||
                'WHERE o.bucket_id = $1 AND lower(o.name) COLLATE "C" < $2' || v_version_filter ||
                ' ORDER BY lower(o.name) COLLATE "C" DESC, COALESCE(o.archived_at, ''infinity''::timestamptz) DESC, COALESCE(o.version, '''') ASC LIMIT $4)' ||
                ') sub ORDER BY lower(sub.name) COLLATE "C" DESC, COALESCE(sub.archived_at, ''infinity''::timestamptz) DESC, COALESCE(sub.version, '''') ASC LIMIT $4';
        END IF;
    END IF;

    -- Keep the delete-marker predicate literal so the cached generic
    -- plan can use idx_objects_delete_markers during the main-loop peek.
    IF delete_markers = 'only' THEN
        IF v_multi_row THEN
            v_delete_marker_peek_query :=
                'SELECT marker_page.name FROM (' || v_batch_query || ') marker_page LIMIT 1';
        ELSIF v_is_asc THEN
            -- Two separate literal query strings, not one gated by a bound
            -- boolean: folding "$n AND op1 OR NOT $n AND op2" into a single
            -- query defeats the generic plan's ability to push either
            -- comparison into the index. Branching in PL/pgSQL control flow
            -- instead keeps each query's index condition intact.
            v_delete_marker_peek_query :=
                'SELECT o.name FROM storage.objects o WHERE o.bucket_id = $1 ' ||
                'AND lower(o.name) COLLATE "C" >= $2' ||
                CASE WHEN v_upper_bound IS NOT NULL
                    THEN ' AND lower(o.name) COLLATE "C" < $3'
                    ELSE ''
                END ||
                v_version_filter ||
                ' ORDER BY lower(o.name) COLLATE "C" ASC LIMIT 1';
            -- Strict variant: used once the single-row ASC batch advance
            -- (below) has left v_next_seek pointing at the last row already
            -- emitted, so a plain >= would re-match it forever.
            v_delete_marker_peek_query_strict :=
                'SELECT o.name FROM storage.objects o WHERE o.bucket_id = $1 ' ||
                'AND lower(o.name) COLLATE "C" > $2' ||
                CASE WHEN v_upper_bound IS NOT NULL
                    THEN ' AND lower(o.name) COLLATE "C" < $3'
                    ELSE ''
                END ||
                v_version_filter ||
                ' ORDER BY lower(o.name) COLLATE "C" ASC LIMIT 1';
        ELSE
            v_delete_marker_peek_query :=
                'SELECT o.name FROM storage.objects o WHERE o.bucket_id = $1 ' ||
                'AND lower(o.name) COLLATE "C" < $2' ||
                CASE WHEN v_upper_bound IS NOT NULL
                    THEN ' AND lower(o.name) COLLATE "C" >= $3'
                    ELSE ''
                END ||
                v_version_filter ||
                ' ORDER BY lower(o.name) COLLATE "C" DESC LIMIT 1';
        END IF;
    END IF;

    -- Initialize seek position
    IF v_is_asc THEN
        v_next_seek := v_prefix_lower;
    ELSE
        -- DESC performs one specialized initial seek so partial current-version
        -- and delete-marker indexes remain available.
        EXECUTE format(
            'SELECT o.name FROM storage.objects o WHERE o.bucket_id = $1%s%s ORDER BY lower(o.name) COLLATE "C" DESC LIMIT 1',
            CASE WHEN v_upper_bound IS NOT NULL
                THEN ' AND lower(o.name) COLLATE "C" >= $2 AND lower(o.name) COLLATE "C" < $3'
                ELSE ''
            END,
            v_version_filter
        )
        INTO v_peek_name
        USING bucketname, v_prefix_lower, v_upper_bound;

        IF v_peek_name IS NOT NULL THEN
            v_next_seek := lower(v_peek_name) || v_delimiter;
        ELSE
            RETURN;
        END IF;
    END IF;

    -- ========================================================================
    -- MAIN LOOP: Hybrid peek-then-batch algorithm
    -- Uses STATIC SQL for peek (hot path) and DYNAMIC SQL for batch and
    -- the delete-marker-only path
    -- ========================================================================
    LOOP
        EXIT WHEN v_count >= v_limit;

        v_previous_seek := v_next_seek;
        v_previous_seek_at := v_next_seek_at;
        v_previous_seek_version := v_next_seek_version;
        v_previous_count := v_count;
        v_previous_skipped := v_skipped;

        -- STEP 1: PEEK
        v_peek_name := NULL;
        IF delete_markers = 'only' THEN
            EXECUTE CASE WHEN v_next_seek_strict
                THEN v_delete_marker_peek_query_strict
                ELSE v_delete_marker_peek_query
            END
                INTO v_peek_name
                USING bucketname, v_next_seek,
                    CASE WHEN v_is_asc THEN COALESCE(v_upper_bound, v_prefix_lower) ELSE v_prefix_lower END,
                    1, v_next_seek_at, v_next_seek_version;
        ELSIF v_multi_row AND v_next_seek_at IS NOT NULL THEN
            SELECT o.name INTO v_peek_name
            FROM storage.objects o
            WHERE o.bucket_id = bucketname
              AND lower(o.name) COLLATE "C" = v_next_seek
              AND (COALESCE(o.archived_at, 'infinity'::timestamptz) < v_next_seek_at
                   OR (COALESCE(o.archived_at, 'infinity'::timestamptz) = v_next_seek_at
                       AND COALESCE(o.version, '') > v_next_seek_version))
              AND (noncurrent_versions != 'exclude' OR o.archived_at IS NULL)
              AND (noncurrent_versions != 'only' OR o.archived_at IS NOT NULL)
              AND (delete_markers != 'exclude' OR NOT o.is_delete_marker)
              AND (delete_markers != 'only' OR o.is_delete_marker)
            ORDER BY COALESCE(o.archived_at, 'infinity'::timestamptz) DESC,
                     COALESCE(o.version, '') ASC
            LIMIT 1;

            -- The current key is exhausted. Clear its version boundary and
            -- make the following ASC name peek strict. Appending '/' is not a
            -- valid lexical successor because keys ending in characters such
            -- as '!' sort between the exhausted name and name || '/'.
            IF v_peek_name IS NULL THEN
                IF v_is_asc THEN
                    v_next_seek_strict := true;
                END IF;
                v_next_seek_at := NULL;
                v_next_seek_version := '';
            END IF;
        END IF;

        -- Single-row mode is always noncurrent_versions='exclude'. Keep the
        -- current-row predicate literal so generic plans use the current index.
        IF delete_markers != 'only' AND v_peek_name IS NULL AND NOT v_multi_row THEN
            IF v_is_asc THEN
                IF v_next_seek_strict AND v_upper_bound IS NOT NULL THEN
                    SELECT o.name INTO v_peek_name FROM storage.objects o
                    WHERE o.bucket_id = bucketname AND lower(o.name) COLLATE "C" > v_next_seek AND lower(o.name) COLLATE "C" < v_upper_bound
                      AND o.archived_at IS NULL
                      AND (delete_markers != 'exclude' OR NOT o.is_delete_marker)
                    ORDER BY lower(o.name) COLLATE "C" ASC LIMIT 1;
                ELSIF v_next_seek_strict THEN
                    SELECT o.name INTO v_peek_name FROM storage.objects o
                    WHERE o.bucket_id = bucketname AND lower(o.name) COLLATE "C" > v_next_seek
                      AND o.archived_at IS NULL
                      AND (delete_markers != 'exclude' OR NOT o.is_delete_marker)
                    ORDER BY lower(o.name) COLLATE "C" ASC LIMIT 1;
                ELSIF v_upper_bound IS NOT NULL THEN
                    SELECT o.name INTO v_peek_name FROM storage.objects o
                    WHERE o.bucket_id = bucketname AND lower(o.name) COLLATE "C" >= v_next_seek AND lower(o.name) COLLATE "C" < v_upper_bound
                      AND o.archived_at IS NULL
                      AND (delete_markers != 'exclude' OR NOT o.is_delete_marker)
                    ORDER BY lower(o.name) COLLATE "C" ASC LIMIT 1;
                ELSE
                    SELECT o.name INTO v_peek_name FROM storage.objects o
                    WHERE o.bucket_id = bucketname AND lower(o.name) COLLATE "C" >= v_next_seek
                      AND o.archived_at IS NULL
                      AND (delete_markers != 'exclude' OR NOT o.is_delete_marker)
                    ORDER BY lower(o.name) COLLATE "C" ASC LIMIT 1;
                END IF;
            ELSIF v_upper_bound IS NOT NULL THEN
                SELECT o.name INTO v_peek_name FROM storage.objects o
                WHERE o.bucket_id = bucketname AND lower(o.name) COLLATE "C" < v_next_seek AND lower(o.name) COLLATE "C" >= v_prefix_lower
                  AND o.archived_at IS NULL
                  AND (delete_markers != 'exclude' OR NOT o.is_delete_marker)
                ORDER BY lower(o.name) COLLATE "C" DESC LIMIT 1;
            ELSE
                SELECT o.name INTO v_peek_name FROM storage.objects o
                WHERE o.bucket_id = bucketname AND lower(o.name) COLLATE "C" < v_next_seek
                  AND o.archived_at IS NULL
                  AND (delete_markers != 'exclude' OR NOT o.is_delete_marker)
                ORDER BY lower(o.name) COLLATE "C" DESC LIMIT 1;
            END IF;
        ELSIF delete_markers != 'only' AND v_peek_name IS NULL AND v_is_asc THEN
            IF v_next_seek_strict AND v_upper_bound IS NOT NULL THEN
                SELECT o.name INTO v_peek_name FROM storage.objects o
                WHERE o.bucket_id = bucketname AND lower(o.name) COLLATE "C" > v_next_seek AND lower(o.name) COLLATE "C" < v_upper_bound
                  AND (noncurrent_versions != 'exclude' OR o.archived_at IS NULL)
                  AND (noncurrent_versions != 'only' OR o.archived_at IS NOT NULL)
                  AND (delete_markers != 'exclude' OR NOT o.is_delete_marker)
                  AND (delete_markers != 'only' OR o.is_delete_marker)
                ORDER BY lower(o.name) COLLATE "C" ASC LIMIT 1;
            ELSIF v_next_seek_strict THEN
                SELECT o.name INTO v_peek_name FROM storage.objects o
                WHERE o.bucket_id = bucketname AND lower(o.name) COLLATE "C" > v_next_seek
                  AND (noncurrent_versions != 'exclude' OR o.archived_at IS NULL)
                  AND (noncurrent_versions != 'only' OR o.archived_at IS NOT NULL)
                  AND (delete_markers != 'exclude' OR NOT o.is_delete_marker)
                  AND (delete_markers != 'only' OR o.is_delete_marker)
                ORDER BY lower(o.name) COLLATE "C" ASC LIMIT 1;
            ELSIF v_upper_bound IS NOT NULL THEN
                SELECT o.name INTO v_peek_name FROM storage.objects o
                WHERE o.bucket_id = bucketname AND lower(o.name) COLLATE "C" >= v_next_seek AND lower(o.name) COLLATE "C" < v_upper_bound
                  AND (noncurrent_versions != 'exclude' OR o.archived_at IS NULL)
                  AND (noncurrent_versions != 'only' OR o.archived_at IS NOT NULL)
                  AND (delete_markers != 'exclude' OR NOT o.is_delete_marker)
                  AND (delete_markers != 'only' OR o.is_delete_marker)
                ORDER BY lower(o.name) COLLATE "C" ASC LIMIT 1;
            ELSE
                SELECT o.name INTO v_peek_name FROM storage.objects o
                WHERE o.bucket_id = bucketname AND lower(o.name) COLLATE "C" >= v_next_seek
                  AND (noncurrent_versions != 'exclude' OR o.archived_at IS NULL)
                  AND (noncurrent_versions != 'only' OR o.archived_at IS NOT NULL)
                  AND (delete_markers != 'exclude' OR NOT o.is_delete_marker)
                  AND (delete_markers != 'only' OR o.is_delete_marker)
                ORDER BY lower(o.name) COLLATE "C" ASC LIMIT 1;
            END IF;
        ELSIF delete_markers != 'only' AND v_peek_name IS NULL THEN
            IF v_upper_bound IS NOT NULL THEN
                SELECT o.name INTO v_peek_name FROM storage.objects o
                WHERE o.bucket_id = bucketname AND lower(o.name) COLLATE "C" < v_next_seek AND lower(o.name) COLLATE "C" >= v_prefix_lower
                  AND (noncurrent_versions != 'exclude' OR o.archived_at IS NULL)
                  AND (noncurrent_versions != 'only' OR o.archived_at IS NOT NULL)
                  AND (delete_markers != 'exclude' OR NOT o.is_delete_marker)
                  AND (delete_markers != 'only' OR o.is_delete_marker)
                ORDER BY lower(o.name) COLLATE "C" DESC LIMIT 1;
            ELSE
                SELECT o.name INTO v_peek_name FROM storage.objects o
                WHERE o.bucket_id = bucketname AND lower(o.name) COLLATE "C" < v_next_seek
                  AND (noncurrent_versions != 'exclude' OR o.archived_at IS NULL)
                  AND (noncurrent_versions != 'only' OR o.archived_at IS NOT NULL)
                  AND (delete_markers != 'exclude' OR NOT o.is_delete_marker)
                  AND (delete_markers != 'only' OR o.is_delete_marker)
                ORDER BY lower(o.name) COLLATE "C" DESC LIMIT 1;
            END IF;
        END IF;

        EXIT WHEN v_peek_name IS NULL;

        -- If the peek landed on a different key than we were tracking, any
        -- version boundary belongs to the OLD key and must not leak into the
        -- new one - e.g. the deleteMarkers='only' peek doesn't know or care
        -- whether it's continuing the same key or jumping to a new one, so
        -- it never clears these itself.
        IF lower(v_peek_name) IS DISTINCT FROM v_next_seek THEN
            v_next_seek_at := NULL;
            v_next_seek_version := '';
        END IF;

        -- The peek is authoritative for the next key to process. This is
        -- especially important after exhausting a multi-version key: the
        -- version boundary has been cleared, so executing the batch against
        -- a stale v_next_seek would replay every version of that old key.
        v_next_seek := lower(v_peek_name);
        v_next_seek_strict := false;

        -- STEP 2: Check if this is a FOLDER or FILE
        v_common_prefix := storage.get_common_prefix(lower(v_peek_name), v_prefix_lower, v_delimiter);

        IF v_common_prefix IS NOT NULL THEN
            -- FOLDER: Handle offset, emit if needed, skip to next folder
            IF v_skipped < offsets THEN
                v_skipped := v_skipped + 1;
            ELSE
                name := substring(rtrim(storage.get_common_prefix(v_peek_name, v_prefix, v_delimiter), v_delimiter) from v_prefix_len + 1);
                id := NULL;
                updated_at := NULL;
                created_at := NULL;
                last_accessed_at := NULL;
                metadata := NULL;
                version := NULL;
                archived_at := NULL;
                is_delete_marker := NULL;
                is_versioned := NULL;
                RETURN NEXT;
                v_count := v_count + 1;
            END IF;

            -- Advance seek past the folder range
            IF v_is_asc THEN
                v_next_seek := lower(left(v_common_prefix, -1)) || chr(ascii(v_delimiter) + 1);
            ELSE
                v_next_seek := lower(v_common_prefix);
            END IF;
            v_next_seek_at := NULL;
            v_next_seek_version := '';
        ELSE
            -- FILE: Batch fetch using DYNAMIC SQL (overhead amortized over many rows)
            -- For ASC: upper_bound is the exclusive upper limit (< condition)
            -- For DESC: prefix_lower is the inclusive lower limit (>= condition)
            FOR v_current IN EXECUTE v_batch_query
                USING bucketname, v_next_seek,
                    CASE WHEN v_is_asc THEN COALESCE(v_upper_bound, v_prefix_lower) ELSE v_prefix_lower END, v_file_batch_size,
                    v_next_seek_at, v_next_seek_version
            LOOP
                v_common_prefix := storage.get_common_prefix(lower(v_current.name), v_prefix_lower, v_delimiter);

                IF v_common_prefix IS NOT NULL THEN
                    -- Hit a folder: exit batch, let peek handle it. Reset
                    -- strict mode too - it may have been set by an earlier
                    -- row in this same batch (see the single-row ASC advance
                    -- below), and v_next_seek here is the folder-triggering
                    -- row's own name, which the next peek must find inclusively.
                    v_next_seek := CASE
                        WHEN v_is_asc THEN lower(v_current.name)
                        ELSE lower(v_current.name) || v_delimiter
                    END;
                    v_next_seek_at := NULL;
                    v_next_seek_version := '';
                    v_next_seek_strict := false;
                    EXIT;
                END IF;

                -- Handle offset skipping
                IF v_skipped < offsets THEN
                    v_skipped := v_skipped + 1;
                ELSE
                    -- Emit file
                    name := substring(v_current.name from v_prefix_len + 1);
                    id := v_current.id;
                    updated_at := v_current.updated_at;
                    created_at := v_current.created_at;
                    last_accessed_at := v_current.last_accessed_at;
                    metadata := v_current.metadata;
                    version := v_current.version;
                    archived_at := v_current.archived_at;
                    is_delete_marker := v_current.is_delete_marker;
                    is_versioned := v_current.is_versioned;
                    RETURN NEXT;
                    v_count := v_count + 1;
                END IF;

                -- Multi-row mode must remain on this key until all of its
                -- versions have crossed the internal batch boundary.
                IF v_multi_row THEN
                    v_next_seek := lower(v_current.name);
                    v_next_seek_at := COALESCE(v_current.archived_at, 'infinity'::timestamptz);
                    v_next_seek_version := COALESCE(v_current.version, '');
                ELSIF v_is_asc THEN
                    -- Appending the delimiter as a fake lexical successor would
                    -- skip a real key like `name || '!'` (or any character
                    -- sorting below the delimiter), which sorts between `name`
                    -- and `name || delimiter`. Track the real name and mark the
                    -- next comparison strict instead - same fix as the
                    -- exhausted-key case above.
                    v_next_seek := lower(v_current.name);
                    v_next_seek_strict := true;
                ELSE
                    v_next_seek := lower(v_current.name);
                END IF;

                EXIT WHEN v_count >= v_limit;
            END LOOP;
        END IF;

        IF v_count = v_previous_count
           AND v_skipped = v_previous_skipped
           AND v_next_seek IS NOT DISTINCT FROM v_previous_seek
           AND v_next_seek_at IS NOT DISTINCT FROM v_previous_seek_at
           AND v_next_seek_version IS NOT DISTINCT FROM v_previous_seek_version THEN
            RAISE EXCEPTION 'storage.search made no progress at seek (%, %, %)',
                v_next_seek, v_next_seek_at, v_next_seek_version;
        END IF;
    END LOOP;
END;
$_$;


ALTER FUNCTION storage.search(prefix text, bucketname text, limits integer, levels integer, offsets integer, search text, sortcolumn text, sortorder text, noncurrent_versions text, delete_markers text) OWNER TO supabase_storage_admin;

--
-- Name: search_by_timestamp(text, text, integer, integer, text, text, text, text, text, text, text); Type: FUNCTION; Schema: storage; Owner: supabase_storage_admin
--

CREATE FUNCTION storage.search_by_timestamp(p_prefix text, p_bucket_id text, p_limit integer, p_level integer, p_start_after text, p_sort_order text, p_sort_column text, p_sort_column_after text, noncurrent_versions text DEFAULT 'exclude'::text, delete_markers text DEFAULT 'exclude'::text, p_start_after_version text DEFAULT ''::text) RETURNS TABLE(key text, name text, id uuid, updated_at timestamp with time zone, created_at timestamp with time zone, last_accessed_at timestamp with time zone, metadata jsonb, version text, archived_at timestamp with time zone, is_delete_marker boolean, is_versioned boolean)
    LANGUAGE plpgsql STABLE
    AS $_$
DECLARE
    v_cursor_op text;
    v_query text;
    v_prefix text;
    v_prefix_pattern text;
    v_sort_order text;
    v_sort_column text;
    v_version_tiebreak text;
BEGIN
    v_prefix := coalesce(p_prefix, '');
    -- Keep the raw prefix for common-prefix calculations and escape only LIKE metacharacters.
    v_prefix_pattern := replace(v_prefix, chr(92), chr(92) || chr(92));
    v_prefix_pattern := replace(v_prefix_pattern, '%', chr(92) || '%');
    v_prefix_pattern := replace(v_prefix_pattern, '_', chr(92) || '_');

    -- COALESCE first: NULL NOT IN (...) evaluates to NULL (not TRUE), so a
    -- bare NOT IN check silently leaves an explicit NULL argument unreset.
    noncurrent_versions := COALESCE(noncurrent_versions, 'exclude');
    delete_markers := COALESCE(delete_markers, 'exclude');
    IF noncurrent_versions NOT IN ('exclude', 'only', 'include') THEN
        noncurrent_versions := 'exclude';
    END IF;
    IF delete_markers NOT IN ('exclude', 'only', 'include') THEN
        delete_markers := 'exclude';
    END IF;

    -- $9 is only populated in multi-row mode; it's always '' otherwise, so
    -- only use each row's real version as a tiebreak in multi-row mode.
    v_version_tiebreak := CASE WHEN noncurrent_versions IN ('only', 'include') THEN 'COALESCE(version, '''')' ELSE '''''' END;

    -- Defense-in-depth: this function is independently reachable and must
    -- not trust p_sort_order/p_sort_column to already be validated by a
    -- caller. Normalize to the same strict allow-list storage.search_v2
    -- uses before interpolating anything into dynamic SQL below.
    v_sort_order := lower(coalesce(p_sort_order, 'asc'));
    IF v_sort_order NOT IN ('asc', 'desc') THEN
        v_sort_order := 'asc';
    END IF;

    v_sort_column := lower(coalesce(p_sort_column, 'updated_at'));
    IF v_sort_column NOT IN ('updated_at', 'created_at') THEN
        v_sort_column := 'updated_at';
    END IF;

    IF v_sort_order = 'asc' THEN
        v_cursor_op := '>';
    ELSE
        v_cursor_op := '<';
    END IF;

    v_query := format($sql$
        WITH raw_objects AS (
            SELECT
                o.name AS obj_name,
                o.id AS obj_id,
                o.updated_at AS obj_updated_at,
                o.created_at AS obj_created_at,
                o.last_accessed_at AS obj_last_accessed_at,
                o.metadata AS obj_metadata,
                o.version AS obj_version,
                o.archived_at AS obj_archived_at,
                o.is_delete_marker AS obj_is_delete_marker,
                o.is_versioned AS obj_is_versioned,
                storage.get_common_prefix(o.name, $1, '/') AS common_prefix
            FROM storage.objects o
            WHERE o.bucket_id = $2
              AND o.name COLLATE "C" LIKE $10 || '%%'
              AND ($7 != 'exclude' OR o.archived_at IS NULL)
              AND ($7 != 'only' OR o.archived_at IS NOT NULL)
              AND ($8 != 'exclude' OR NOT o.is_delete_marker)
              AND ($8 != 'only' OR o.is_delete_marker)
        ),
        -- Aggregate common prefixes (folders)
        -- Both created_at and updated_at use MIN(obj_created_at) to match the old prefixes table behavior
        aggregated_prefixes AS (
            SELECT
                common_prefix AS name,
                NULL::uuid AS id,
                MIN(obj_created_at) AS updated_at,
                MIN(obj_created_at) AS created_at,
                NULL::timestamptz AS last_accessed_at,
                NULL::jsonb AS metadata,
                NULL::text AS version,
                NULL::timestamptz AS archived_at,
                NULL::boolean AS is_delete_marker,
                NULL::boolean AS is_versioned,
                TRUE AS is_prefix
            FROM raw_objects
            WHERE common_prefix IS NOT NULL
            GROUP BY common_prefix
        ),
        leaf_objects AS (
            SELECT
                obj_name AS name,
                obj_id AS id,
                obj_updated_at AS updated_at,
                obj_created_at AS created_at,
                obj_last_accessed_at AS last_accessed_at,
                obj_metadata AS metadata,
                obj_version AS version,
                obj_archived_at AS archived_at,
                obj_is_delete_marker AS is_delete_marker,
                obj_is_versioned AS is_versioned,
                FALSE AS is_prefix
            FROM raw_objects
            WHERE common_prefix IS NULL
        ),
        combined AS (
            SELECT * FROM aggregated_prefixes
            UNION ALL
            SELECT * FROM leaf_objects
        ),
        filtered AS (
            SELECT *
            FROM combined
            WHERE (
                $5 = ''
                OR ROW(
                    COALESCE(date_trunc('milliseconds', %I), 'epoch'::timestamptz),
                    name COLLATE "C",
                    %s
                ) %s ROW(
                    -- truncated the same way as the stored value above
                    date_trunc('milliseconds', COALESCE(NULLIF($6, '')::timestamptz, 'epoch'::timestamptz)),
                    $5,
                    $9
                )
            )
        )
        SELECT
            split_part(name, '/', $3) AS key,
            name,
            id,
            updated_at,
            created_at,
            last_accessed_at,
            metadata,
            version,
            archived_at,
            is_delete_marker,
            is_versioned
        FROM filtered
        ORDER BY
            COALESCE(date_trunc('milliseconds', %I), 'epoch'::timestamptz) %s,
            name COLLATE "C" %s,
            COALESCE(version, '') %s
        LIMIT $4
    $sql$,
        v_sort_column,
        v_version_tiebreak,
        v_cursor_op,
        v_sort_column,
        v_sort_order,
        v_sort_order,
        v_sort_order
    );

    -- version is the third tiebreak component for two versions of the same
    -- key tying on both timestamp and name (see filtered CTE / ORDER BY above)
    RETURN QUERY EXECUTE v_query
    USING v_prefix, p_bucket_id, p_level, p_limit, p_start_after, p_sort_column_after, noncurrent_versions, delete_markers, coalesce(p_start_after_version, ''), v_prefix_pattern;
END;
$_$;


ALTER FUNCTION storage.search_by_timestamp(p_prefix text, p_bucket_id text, p_limit integer, p_level integer, p_start_after text, p_sort_order text, p_sort_column text, p_sort_column_after text, noncurrent_versions text, delete_markers text, p_start_after_version text) OWNER TO supabase_storage_admin;

--
-- Name: search_v2(text, text, integer, integer, text, text, text, text, text, text, timestamp with time zone, text, boolean); Type: FUNCTION; Schema: storage; Owner: supabase_storage_admin
--

CREATE FUNCTION storage.search_v2(prefix text, bucket_name text, limits integer DEFAULT 100, levels integer DEFAULT 1, start_after text DEFAULT ''::text, sort_order text DEFAULT 'asc'::text, sort_column text DEFAULT 'name'::text, sort_column_after text DEFAULT ''::text, noncurrent_versions text DEFAULT 'exclude'::text, delete_markers text DEFAULT 'exclude'::text, start_after_archived_at timestamp with time zone DEFAULT NULL::timestamp with time zone, start_after_version text DEFAULT ''::text, start_after_is_continuation boolean DEFAULT false) RETURNS TABLE(key text, name text, id uuid, updated_at timestamp with time zone, created_at timestamp with time zone, last_accessed_at timestamp with time zone, metadata jsonb, version text, archived_at timestamp with time zone, is_delete_marker boolean, is_versioned boolean)
    LANGUAGE plpgsql STABLE
    AS $$
DECLARE
    v_sort_col text;
    v_sort_ord text;
    v_limit int;
BEGIN
    -- Cap limit to maximum of 1500 records
    v_limit := LEAST(coalesce(limits, 100), 1500);

    -- Validate and normalize sort_order
    v_sort_ord := lower(coalesce(sort_order, 'asc'));
    IF v_sort_ord NOT IN ('asc', 'desc') THEN
        v_sort_ord := 'asc';
    END IF;

    -- Validate and normalize sort_column
    v_sort_col := lower(coalesce(sort_column, 'name'));
    IF v_sort_col NOT IN ('name', 'updated_at', 'created_at') THEN
        v_sort_col := 'name';
    END IF;

    -- Route to appropriate implementation
    IF v_sort_col = 'name' THEN
        -- Use list_objects_with_delimiter for name sorting (most efficient: O(k * log n))
        RETURN QUERY
        SELECT
            split_part(l.name, '/', levels) AS key,
            l.name AS name,
            l.id,
            l.updated_at,
            l.created_at,
            l.last_accessed_at,
            l.metadata,
            l.version,
            l.archived_at,
            l.is_delete_marker,
            l.is_versioned
        FROM storage.list_objects_with_delimiter(
            bucket_name,
            coalesce(prefix, ''),
            '/',
            v_limit,
            CASE WHEN start_after_is_continuation THEN '' ELSE start_after END,
            CASE WHEN start_after_is_continuation THEN start_after ELSE '' END,
            v_sort_ord,
            noncurrent_versions,
            delete_markers,
            start_after_archived_at,
            start_after_version
        ) l;
    ELSE
        -- Use aggregation approach for timestamp sorting
        -- Not efficient for large datasets but supports correct pagination
        RETURN QUERY SELECT * FROM storage.search_by_timestamp(
            prefix, bucket_name, v_limit, levels, start_after,
            v_sort_ord, v_sort_col, sort_column_after,
            noncurrent_versions, delete_markers, start_after_version
        );
    END IF;
END;
$$;


ALTER FUNCTION storage.search_v2(prefix text, bucket_name text, limits integer, levels integer, start_after text, sort_order text, sort_column text, sort_column_after text, noncurrent_versions text, delete_markers text, start_after_archived_at timestamp with time zone, start_after_version text, start_after_is_continuation boolean) OWNER TO supabase_storage_admin;

--
-- Name: update_updated_at_column(); Type: FUNCTION; Schema: storage; Owner: supabase_storage_admin
--

CREATE FUNCTION storage.update_updated_at_column() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    NEW.updated_at = now();
    RETURN NEW; 
END;
$$;


ALTER FUNCTION storage.update_updated_at_column() OWNER TO supabase_storage_admin;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: audit_log_entries; Type: TABLE; Schema: auth; Owner: supabase_auth_admin
--

CREATE TABLE auth.audit_log_entries (
    instance_id uuid,
    id uuid NOT NULL,
    payload json,
    created_at timestamp with time zone,
    ip_address character varying(64) DEFAULT ''::character varying NOT NULL
);


ALTER TABLE auth.audit_log_entries OWNER TO supabase_auth_admin;

--
-- Name: TABLE audit_log_entries; Type: COMMENT; Schema: auth; Owner: supabase_auth_admin
--

COMMENT ON TABLE auth.audit_log_entries IS 'Auth: Audit trail for user actions.';


--
-- Name: custom_oauth_providers; Type: TABLE; Schema: auth; Owner: supabase_auth_admin
--

CREATE TABLE auth.custom_oauth_providers (
    id uuid DEFAULT gen_random_uuid() NOT NULL,
    provider_type text NOT NULL,
    identifier text NOT NULL,
    name text NOT NULL,
    client_id text NOT NULL,
    client_secret text NOT NULL,
    acceptable_client_ids text[] DEFAULT '{}'::text[] NOT NULL,
    scopes text[] DEFAULT '{}'::text[] NOT NULL,
    pkce_enabled boolean DEFAULT true NOT NULL,
    attribute_mapping jsonb DEFAULT '{}'::jsonb NOT NULL,
    authorization_params jsonb DEFAULT '{}'::jsonb NOT NULL,
    enabled boolean DEFAULT true NOT NULL,
    email_optional boolean DEFAULT false NOT NULL,
    issuer text,
    discovery_url text,
    skip_nonce_check boolean DEFAULT false NOT NULL,
    cached_discovery jsonb,
    discovery_cached_at timestamp with time zone,
    authorization_url text,
    token_url text,
    userinfo_url text,
    jwks_uri text,
    created_at timestamp with time zone DEFAULT now() NOT NULL,
    updated_at timestamp with time zone DEFAULT now() NOT NULL,
    custom_claims_allowlist text[] DEFAULT '{}'::text[] NOT NULL,
    CONSTRAINT custom_oauth_providers_authorization_url_https CHECK (((authorization_url IS NULL) OR (authorization_url ~~ 'https://%'::text))),
    CONSTRAINT custom_oauth_providers_authorization_url_length CHECK (((authorization_url IS NULL) OR (char_length(authorization_url) <= 2048))),
    CONSTRAINT custom_oauth_providers_client_id_length CHECK (((char_length(client_id) >= 1) AND (char_length(client_id) <= 512))),
    CONSTRAINT custom_oauth_providers_discovery_url_length CHECK (((discovery_url IS NULL) OR (char_length(discovery_url) <= 2048))),
    CONSTRAINT custom_oauth_providers_identifier_format CHECK ((identifier ~ '^[a-z0-9][a-z0-9:-]{0,48}[a-z0-9]$'::text)),
    CONSTRAINT custom_oauth_providers_issuer_length CHECK (((issuer IS NULL) OR ((char_length(issuer) >= 1) AND (char_length(issuer) <= 2048)))),
    CONSTRAINT custom_oauth_providers_jwks_uri_https CHECK (((jwks_uri IS NULL) OR (jwks_uri ~~ 'https://%'::text))),
    CONSTRAINT custom_oauth_providers_jwks_uri_length CHECK (((jwks_uri IS NULL) OR (char_length(jwks_uri) <= 2048))),
    CONSTRAINT custom_oauth_providers_name_length CHECK (((char_length(name) >= 1) AND (char_length(name) <= 100))),
    CONSTRAINT custom_oauth_providers_oauth2_requires_endpoints CHECK (((provider_type <> 'oauth2'::text) OR ((authorization_url IS NOT NULL) AND (token_url IS NOT NULL) AND (userinfo_url IS NOT NULL)))),
    CONSTRAINT custom_oauth_providers_oidc_discovery_url_https CHECK (((provider_type <> 'oidc'::text) OR (discovery_url IS NULL) OR (discovery_url ~~ 'https://%'::text))),
    CONSTRAINT custom_oauth_providers_oidc_issuer_https CHECK (((provider_type <> 'oidc'::text) OR (issuer IS NULL) OR (issuer ~~ 'https://%'::text))),
    CONSTRAINT custom_oauth_providers_oidc_requires_issuer CHECK (((provider_type <> 'oidc'::text) OR (issuer IS NOT NULL))),
    CONSTRAINT custom_oauth_providers_provider_type_check CHECK ((provider_type = ANY (ARRAY['oauth2'::text, 'oidc'::text]))),
    CONSTRAINT custom_oauth_providers_token_url_https CHECK (((token_url IS NULL) OR (token_url ~~ 'https://%'::text))),
    CONSTRAINT custom_oauth_providers_token_url_length CHECK (((token_url IS NULL) OR (char_length(token_url) <= 2048))),
    CONSTRAINT custom_oauth_providers_userinfo_url_https CHECK (((userinfo_url IS NULL) OR (userinfo_url ~~ 'https://%'::text))),
    CONSTRAINT custom_oauth_providers_userinfo_url_length CHECK (((userinfo_url IS NULL) OR (char_length(userinfo_url) <= 2048)))
);


ALTER TABLE auth.custom_oauth_providers OWNER TO supabase_auth_admin;

--
-- Name: flow_state; Type: TABLE; Schema: auth; Owner: supabase_auth_admin
--

CREATE TABLE auth.flow_state (
    id uuid NOT NULL,
    user_id uuid,
    auth_code text,
    code_challenge_method auth.code_challenge_method,
    code_challenge text,
    provider_type text NOT NULL,
    provider_access_token text,
    provider_refresh_token text,
    created_at timestamp with time zone,
    updated_at timestamp with time zone,
    authentication_method text NOT NULL,
    auth_code_issued_at timestamp with time zone,
    invite_token text,
    referrer text,
    oauth_client_state_id uuid,
    linking_target_id uuid,
    email_optional boolean DEFAULT false NOT NULL
);


ALTER TABLE auth.flow_state OWNER TO supabase_auth_admin;

--
-- Name: TABLE flow_state; Type: COMMENT; Schema: auth; Owner: supabase_auth_admin
--

COMMENT ON TABLE auth.flow_state IS 'Stores metadata for all OAuth/SSO login flows';


--
-- Name: identities; Type: TABLE; Schema: auth; Owner: supabase_auth_admin
--

CREATE TABLE auth.identities (
    provider_id text NOT NULL,
    user_id uuid NOT NULL,
    identity_data jsonb NOT NULL,
    provider text NOT NULL,
    last_sign_in_at timestamp with time zone,
    created_at timestamp with time zone,
    updated_at timestamp with time zone,
    email text GENERATED ALWAYS AS (lower((identity_data ->> 'email'::text))) STORED,
    id uuid DEFAULT gen_random_uuid() NOT NULL
);


ALTER TABLE auth.identities OWNER TO supabase_auth_admin;

--
-- Name: TABLE identities; Type: COMMENT; Schema: auth; Owner: supabase_auth_admin
--

COMMENT ON TABLE auth.identities IS 'Auth: Stores identities associated to a user.';


--
-- Name: COLUMN identities.email; Type: COMMENT; Schema: auth; Owner: supabase_auth_admin
--

COMMENT ON COLUMN auth.identities.email IS 'Auth: Email is a generated column that references the optional email property in the identity_data';


--
-- Name: instances; Type: TABLE; Schema: auth; Owner: supabase_auth_admin
--

CREATE TABLE auth.instances (
    id uuid NOT NULL,
    uuid uuid,
    raw_base_config text,
    created_at timestamp with time zone,
    updated_at timestamp with time zone
);


ALTER TABLE auth.instances OWNER TO supabase_auth_admin;

--
-- Name: TABLE instances; Type: COMMENT; Schema: auth; Owner: supabase_auth_admin
--

COMMENT ON TABLE auth.instances IS 'Auth: Manages users across multiple sites.';


--
-- Name: mfa_amr_claims; Type: TABLE; Schema: auth; Owner: supabase_auth_admin
--

CREATE TABLE auth.mfa_amr_claims (
    session_id uuid NOT NULL,
    created_at timestamp with time zone NOT NULL,
    updated_at timestamp with time zone NOT NULL,
    authentication_method text NOT NULL,
    id uuid NOT NULL
);


ALTER TABLE auth.mfa_amr_claims OWNER TO supabase_auth_admin;

--
-- Name: TABLE mfa_amr_claims; Type: COMMENT; Schema: auth; Owner: supabase_auth_admin
--

COMMENT ON TABLE auth.mfa_amr_claims IS 'auth: stores authenticator method reference claims for multi factor authentication';


--
-- Name: mfa_challenges; Type: TABLE; Schema: auth; Owner: supabase_auth_admin
--

CREATE TABLE auth.mfa_challenges (
    id uuid NOT NULL,
    factor_id uuid NOT NULL,
    created_at timestamp with time zone NOT NULL,
    verified_at timestamp with time zone,
    ip_address inet NOT NULL,
    otp_code text,
    web_authn_session_data jsonb
);


ALTER TABLE auth.mfa_challenges OWNER TO supabase_auth_admin;

--
-- Name: TABLE mfa_challenges; Type: COMMENT; Schema: auth; Owner: supabase_auth_admin
--

COMMENT ON TABLE auth.mfa_challenges IS 'auth: stores metadata about challenge requests made';


--
-- Name: mfa_factors; Type: TABLE; Schema: auth; Owner: supabase_auth_admin
--

CREATE TABLE auth.mfa_factors (
    id uuid NOT NULL,
    user_id uuid NOT NULL,
    friendly_name text,
    factor_type auth.factor_type NOT NULL,
    status auth.factor_status NOT NULL,
    created_at timestamp with time zone NOT NULL,
    updated_at timestamp with time zone NOT NULL,
    secret text,
    phone text,
    last_challenged_at timestamp with time zone,
    web_authn_credential jsonb,
    web_authn_aaguid uuid,
    last_webauthn_challenge_data jsonb
);


ALTER TABLE auth.mfa_factors OWNER TO supabase_auth_admin;

--
-- Name: TABLE mfa_factors; Type: COMMENT; Schema: auth; Owner: supabase_auth_admin
--

COMMENT ON TABLE auth.mfa_factors IS 'auth: stores metadata about factors';


--
-- Name: COLUMN mfa_factors.last_webauthn_challenge_data; Type: COMMENT; Schema: auth; Owner: supabase_auth_admin
--

COMMENT ON COLUMN auth.mfa_factors.last_webauthn_challenge_data IS 'Stores the latest WebAuthn challenge data including attestation/assertion for customer verification';


--
-- Name: mfa_recovery_code_sets; Type: TABLE; Schema: auth; Owner: supabase_auth_admin
--

CREATE TABLE auth.mfa_recovery_code_sets (
    id uuid NOT NULL,
    user_id uuid NOT NULL,
    mfa_factor_id uuid NOT NULL,
    failed_verification_count integer DEFAULT 0 NOT NULL,
    verification_locked_until timestamp with time zone,
    created_at timestamp with time zone DEFAULT now() NOT NULL,
    updated_at timestamp with time zone DEFAULT now() NOT NULL,
    CONSTRAINT mfa_recovery_code_sets_failed_verification_count_check CHECK ((failed_verification_count >= 0))
);


ALTER TABLE auth.mfa_recovery_code_sets OWNER TO supabase_auth_admin;

--
-- Name: mfa_recovery_codes; Type: TABLE; Schema: auth; Owner: supabase_auth_admin
--

CREATE TABLE auth.mfa_recovery_codes (
    id uuid NOT NULL,
    mfa_recovery_code_set_id uuid NOT NULL,
    code_hash text NOT NULL,
    consumed_at timestamp with time zone,
    created_at timestamp with time zone DEFAULT now() NOT NULL
);


ALTER TABLE auth.mfa_recovery_codes OWNER TO supabase_auth_admin;

--
-- Name: oauth_authorizations; Type: TABLE; Schema: auth; Owner: supabase_auth_admin
--

CREATE TABLE auth.oauth_authorizations (
    id uuid NOT NULL,
    authorization_id text NOT NULL,
    client_id uuid NOT NULL,
    user_id uuid,
    redirect_uri text NOT NULL,
    scope text NOT NULL,
    state text,
    resource text,
    code_challenge text,
    code_challenge_method auth.code_challenge_method,
    response_type auth.oauth_response_type DEFAULT 'code'::auth.oauth_response_type NOT NULL,
    status auth.oauth_authorization_status DEFAULT 'pending'::auth.oauth_authorization_status NOT NULL,
    authorization_code text,
    created_at timestamp with time zone DEFAULT now() NOT NULL,
    expires_at timestamp with time zone DEFAULT (now() + '00:03:00'::interval) NOT NULL,
    approved_at timestamp with time zone,
    nonce text,
    CONSTRAINT oauth_authorizations_authorization_code_length CHECK ((char_length(authorization_code) <= 255)),
    CONSTRAINT oauth_authorizations_code_challenge_length CHECK ((char_length(code_challenge) <= 128)),
    CONSTRAINT oauth_authorizations_expires_at_future CHECK ((expires_at > created_at)),
    CONSTRAINT oauth_authorizations_nonce_length CHECK ((char_length(nonce) <= 255)),
    CONSTRAINT oauth_authorizations_redirect_uri_length CHECK ((char_length(redirect_uri) <= 2048)),
    CONSTRAINT oauth_authorizations_resource_length CHECK ((char_length(resource) <= 2048)),
    CONSTRAINT oauth_authorizations_scope_length CHECK ((char_length(scope) <= 4096)),
    CONSTRAINT oauth_authorizations_state_length CHECK ((char_length(state) <= 4096))
);


ALTER TABLE auth.oauth_authorizations OWNER TO supabase_auth_admin;

--
-- Name: oauth_client_states; Type: TABLE; Schema: auth; Owner: supabase_auth_admin
--

CREATE TABLE auth.oauth_client_states (
    id uuid NOT NULL,
    provider_type text NOT NULL,
    code_verifier text,
    created_at timestamp with time zone NOT NULL
);


ALTER TABLE auth.oauth_client_states OWNER TO supabase_auth_admin;

--
-- Name: TABLE oauth_client_states; Type: COMMENT; Schema: auth; Owner: supabase_auth_admin
--

COMMENT ON TABLE auth.oauth_client_states IS 'Stores OAuth states for third-party provider authentication flows where Supabase acts as the OAuth client.';


--
-- Name: oauth_clients; Type: TABLE; Schema: auth; Owner: supabase_auth_admin
--

CREATE TABLE auth.oauth_clients (
    id uuid NOT NULL,
    client_secret_hash text,
    registration_type auth.oauth_registration_type NOT NULL,
    redirect_uris text NOT NULL,
    grant_types text NOT NULL,
    client_name text,
    client_uri text,
    logo_uri text,
    created_at timestamp with time zone DEFAULT now() NOT NULL,
    updated_at timestamp with time zone DEFAULT now() NOT NULL,
    deleted_at timestamp with time zone,
    client_type auth.oauth_client_type DEFAULT 'confidential'::auth.oauth_client_type NOT NULL,
    token_endpoint_auth_method text NOT NULL,
    CONSTRAINT oauth_clients_client_name_length CHECK ((char_length(client_name) <= 1024)),
    CONSTRAINT oauth_clients_client_uri_length CHECK ((char_length(client_uri) <= 2048)),
    CONSTRAINT oauth_clients_logo_uri_length CHECK ((char_length(logo_uri) <= 2048)),
    CONSTRAINT oauth_clients_token_endpoint_auth_method_check CHECK ((token_endpoint_auth_method = ANY (ARRAY['client_secret_basic'::text, 'client_secret_post'::text, 'none'::text])))
);


ALTER TABLE auth.oauth_clients OWNER TO supabase_auth_admin;

--
-- Name: oauth_consents; Type: TABLE; Schema: auth; Owner: supabase_auth_admin
--

CREATE TABLE auth.oauth_consents (
    id uuid NOT NULL,
    user_id uuid NOT NULL,
    client_id uuid NOT NULL,
    scopes text NOT NULL,
    granted_at timestamp with time zone DEFAULT now() NOT NULL,
    revoked_at timestamp with time zone,
    CONSTRAINT oauth_consents_revoked_after_granted CHECK (((revoked_at IS NULL) OR (revoked_at >= granted_at))),
    CONSTRAINT oauth_consents_scopes_length CHECK ((char_length(scopes) <= 2048)),
    CONSTRAINT oauth_consents_scopes_not_empty CHECK ((char_length(TRIM(BOTH FROM scopes)) > 0))
);


ALTER TABLE auth.oauth_consents OWNER TO supabase_auth_admin;

--
-- Name: one_time_tokens; Type: TABLE; Schema: auth; Owner: supabase_auth_admin
--

CREATE TABLE auth.one_time_tokens (
    id uuid NOT NULL,
    user_id uuid NOT NULL,
    token_type auth.one_time_token_type NOT NULL,
    token_hash text NOT NULL,
    relates_to text NOT NULL,
    created_at timestamp without time zone DEFAULT now() NOT NULL,
    updated_at timestamp without time zone DEFAULT now() NOT NULL,
    expires_at timestamp with time zone,
    CONSTRAINT one_time_tokens_token_hash_check CHECK ((char_length(token_hash) > 0))
);


ALTER TABLE auth.one_time_tokens OWNER TO supabase_auth_admin;

--
-- Name: refresh_tokens; Type: TABLE; Schema: auth; Owner: supabase_auth_admin
--

CREATE TABLE auth.refresh_tokens (
    instance_id uuid,
    id bigint NOT NULL,
    token character varying(255),
    user_id character varying(255),
    revoked boolean,
    created_at timestamp with time zone,
    updated_at timestamp with time zone,
    parent character varying(255),
    session_id uuid
);


ALTER TABLE auth.refresh_tokens OWNER TO supabase_auth_admin;

--
-- Name: TABLE refresh_tokens; Type: COMMENT; Schema: auth; Owner: supabase_auth_admin
--

COMMENT ON TABLE auth.refresh_tokens IS 'Auth: Store of tokens used to refresh JWT tokens once they expire.';


--
-- Name: refresh_tokens_id_seq; Type: SEQUENCE; Schema: auth; Owner: supabase_auth_admin
--

CREATE SEQUENCE auth.refresh_tokens_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE auth.refresh_tokens_id_seq OWNER TO supabase_auth_admin;

--
-- Name: refresh_tokens_id_seq; Type: SEQUENCE OWNED BY; Schema: auth; Owner: supabase_auth_admin
--

ALTER SEQUENCE auth.refresh_tokens_id_seq OWNED BY auth.refresh_tokens.id;


--
-- Name: saml_providers; Type: TABLE; Schema: auth; Owner: supabase_auth_admin
--

CREATE TABLE auth.saml_providers (
    id uuid NOT NULL,
    sso_provider_id uuid NOT NULL,
    entity_id text NOT NULL,
    metadata_xml text NOT NULL,
    metadata_url text,
    attribute_mapping jsonb,
    created_at timestamp with time zone,
    updated_at timestamp with time zone,
    name_id_format text,
    CONSTRAINT "entity_id not empty" CHECK ((char_length(entity_id) > 0)),
    CONSTRAINT "metadata_url not empty" CHECK (((metadata_url = NULL::text) OR (char_length(metadata_url) > 0))),
    CONSTRAINT "metadata_xml not empty" CHECK ((char_length(metadata_xml) > 0))
);


ALTER TABLE auth.saml_providers OWNER TO supabase_auth_admin;

--
-- Name: TABLE saml_providers; Type: COMMENT; Schema: auth; Owner: supabase_auth_admin
--

COMMENT ON TABLE auth.saml_providers IS 'Auth: Manages SAML Identity Provider connections.';


--
-- Name: saml_relay_states; Type: TABLE; Schema: auth; Owner: supabase_auth_admin
--

CREATE TABLE auth.saml_relay_states (
    id uuid NOT NULL,
    sso_provider_id uuid NOT NULL,
    request_id text NOT NULL,
    for_email text,
    redirect_to text,
    created_at timestamp with time zone,
    updated_at timestamp with time zone,
    flow_state_id uuid,
    CONSTRAINT "request_id not empty" CHECK ((char_length(request_id) > 0))
);


ALTER TABLE auth.saml_relay_states OWNER TO supabase_auth_admin;

--
-- Name: TABLE saml_relay_states; Type: COMMENT; Schema: auth; Owner: supabase_auth_admin
--

COMMENT ON TABLE auth.saml_relay_states IS 'Auth: Contains SAML Relay State information for each Service Provider initiated login.';


--
-- Name: schema_migrations; Type: TABLE; Schema: auth; Owner: supabase_auth_admin
--

CREATE TABLE auth.schema_migrations (
    version character varying(255) NOT NULL
);


ALTER TABLE auth.schema_migrations OWNER TO supabase_auth_admin;

--
-- Name: TABLE schema_migrations; Type: COMMENT; Schema: auth; Owner: supabase_auth_admin
--

COMMENT ON TABLE auth.schema_migrations IS 'Auth: Manages updates to the auth system.';


--
-- Name: scim_tokens; Type: TABLE; Schema: auth; Owner: supabase_auth_admin
--

CREATE TABLE auth.scim_tokens (
    id uuid NOT NULL,
    sso_provider_id uuid NOT NULL,
    token_hash text NOT NULL,
    prefix text NOT NULL,
    created_at timestamp with time zone DEFAULT now() NOT NULL,
    expires_at timestamp with time zone,
    revoked_at timestamp with time zone,
    last_used_at timestamp with time zone,
    CONSTRAINT scim_tokens_expires_at_future CHECK (((expires_at IS NULL) OR (expires_at > created_at))),
    CONSTRAINT scim_tokens_revoked_after_created CHECK (((revoked_at IS NULL) OR (revoked_at >= created_at))),
    CONSTRAINT scim_tokens_token_hash_check CHECK ((token_hash ~ '^[0-9a-f]{64}$'::text))
);


ALTER TABLE auth.scim_tokens OWNER TO supabase_auth_admin;

--
-- Name: scim_users; Type: TABLE; Schema: auth; Owner: supabase_auth_admin
--

CREATE TABLE auth.scim_users (
    id uuid NOT NULL,
    sso_provider_id uuid NOT NULL,
    user_id uuid,
    resource jsonb NOT NULL,
    user_name text GENERATED ALWAYS AS (lower((resource ->> 'userName'::text))) STORED NOT NULL,
    external_id text GENERATED ALWAYS AS ((resource ->> 'externalId'::text)) STORED,
    active boolean GENERATED ALWAYS AS (COALESCE(((resource ->> 'active'::text))::boolean, true)) STORED NOT NULL,
    created_at timestamp with time zone DEFAULT now() NOT NULL,
    updated_at timestamp with time zone DEFAULT now() NOT NULL,
    deleted_at timestamp with time zone
);


ALTER TABLE auth.scim_users OWNER TO supabase_auth_admin;

--
-- Name: sessions; Type: TABLE; Schema: auth; Owner: supabase_auth_admin
--

CREATE TABLE auth.sessions (
    id uuid NOT NULL,
    user_id uuid NOT NULL,
    created_at timestamp with time zone,
    updated_at timestamp with time zone,
    factor_id uuid,
    aal auth.aal_level,
    not_after timestamp with time zone,
    refreshed_at timestamp without time zone,
    user_agent text,
    ip inet,
    tag text,
    oauth_client_id uuid,
    refresh_token_hmac_key text,
    refresh_token_counter bigint,
    scopes text,
    CONSTRAINT sessions_scopes_length CHECK ((char_length(scopes) <= 4096))
);


ALTER TABLE auth.sessions OWNER TO supabase_auth_admin;

--
-- Name: TABLE sessions; Type: COMMENT; Schema: auth; Owner: supabase_auth_admin
--

COMMENT ON TABLE auth.sessions IS 'Auth: Stores session data associated to a user.';


--
-- Name: COLUMN sessions.not_after; Type: COMMENT; Schema: auth; Owner: supabase_auth_admin
--

COMMENT ON COLUMN auth.sessions.not_after IS 'Auth: Not after is a nullable column that contains a timestamp after which the session should be regarded as expired.';


--
-- Name: COLUMN sessions.refresh_token_hmac_key; Type: COMMENT; Schema: auth; Owner: supabase_auth_admin
--

COMMENT ON COLUMN auth.sessions.refresh_token_hmac_key IS 'Holds a HMAC-SHA256 key used to sign refresh tokens for this session.';


--
-- Name: COLUMN sessions.refresh_token_counter; Type: COMMENT; Schema: auth; Owner: supabase_auth_admin
--

COMMENT ON COLUMN auth.sessions.refresh_token_counter IS 'Holds the ID (counter) of the last issued refresh token.';


--
-- Name: sso_domains; Type: TABLE; Schema: auth; Owner: supabase_auth_admin
--

CREATE TABLE auth.sso_domains (
    id uuid NOT NULL,
    sso_provider_id uuid NOT NULL,
    domain text NOT NULL,
    created_at timestamp with time zone,
    updated_at timestamp with time zone,
    CONSTRAINT "domain not empty" CHECK ((char_length(domain) > 0))
);


ALTER TABLE auth.sso_domains OWNER TO supabase_auth_admin;

--
-- Name: TABLE sso_domains; Type: COMMENT; Schema: auth; Owner: supabase_auth_admin
--

COMMENT ON TABLE auth.sso_domains IS 'Auth: Manages SSO email address domain mapping to an SSO Identity Provider.';


--
-- Name: sso_providers; Type: TABLE; Schema: auth; Owner: supabase_auth_admin
--

CREATE TABLE auth.sso_providers (
    id uuid NOT NULL,
    resource_id text,
    created_at timestamp with time zone,
    updated_at timestamp with time zone,
    disabled boolean,
    CONSTRAINT "resource_id not empty" CHECK (((resource_id = NULL::text) OR (char_length(resource_id) > 0)))
);


ALTER TABLE auth.sso_providers OWNER TO supabase_auth_admin;

--
-- Name: TABLE sso_providers; Type: COMMENT; Schema: auth; Owner: supabase_auth_admin
--

COMMENT ON TABLE auth.sso_providers IS 'Auth: Manages SSO identity provider information; see saml_providers for SAML.';


--
-- Name: COLUMN sso_providers.resource_id; Type: COMMENT; Schema: auth; Owner: supabase_auth_admin
--

COMMENT ON COLUMN auth.sso_providers.resource_id IS 'Auth: Uniquely identifies a SSO provider according to a user-chosen resource ID (case insensitive), useful in infrastructure as code.';


--
-- Name: users; Type: TABLE; Schema: auth; Owner: supabase_auth_admin
--

CREATE TABLE auth.users (
    instance_id uuid,
    id uuid NOT NULL,
    aud character varying(255),
    role character varying(255),
    email character varying(255),
    encrypted_password character varying(255),
    email_confirmed_at timestamp with time zone,
    invited_at timestamp with time zone,
    confirmation_token character varying(255),
    confirmation_sent_at timestamp with time zone,
    recovery_token character varying(255),
    recovery_sent_at timestamp with time zone,
    email_change_token_new character varying(255),
    email_change character varying(255),
    email_change_sent_at timestamp with time zone,
    last_sign_in_at timestamp with time zone,
    raw_app_meta_data jsonb,
    raw_user_meta_data jsonb,
    is_super_admin boolean,
    created_at timestamp with time zone,
    updated_at timestamp with time zone,
    phone text DEFAULT NULL::character varying,
    phone_confirmed_at timestamp with time zone,
    phone_change text DEFAULT ''::character varying,
    phone_change_token character varying(255) DEFAULT ''::character varying,
    phone_change_sent_at timestamp with time zone,
    confirmed_at timestamp with time zone GENERATED ALWAYS AS (LEAST(email_confirmed_at, phone_confirmed_at)) STORED,
    email_change_token_current character varying(255) DEFAULT ''::character varying,
    email_change_confirm_status smallint DEFAULT 0,
    banned_until timestamp with time zone,
    reauthentication_token character varying(255) DEFAULT ''::character varying,
    reauthentication_sent_at timestamp with time zone,
    is_sso_user boolean DEFAULT false NOT NULL,
    deleted_at timestamp with time zone,
    is_anonymous boolean DEFAULT false NOT NULL,
    CONSTRAINT users_email_change_confirm_status_check CHECK (((email_change_confirm_status >= 0) AND (email_change_confirm_status <= 2)))
);


ALTER TABLE auth.users OWNER TO supabase_auth_admin;

--
-- Name: TABLE users; Type: COMMENT; Schema: auth; Owner: supabase_auth_admin
--

COMMENT ON TABLE auth.users IS 'Auth: Stores user login data within a secure schema.';


--
-- Name: COLUMN users.is_sso_user; Type: COMMENT; Schema: auth; Owner: supabase_auth_admin
--

COMMENT ON COLUMN auth.users.is_sso_user IS 'Auth: Set this column to true when the account comes from SSO. These accounts can have duplicate emails.';


--
-- Name: webauthn_challenges; Type: TABLE; Schema: auth; Owner: supabase_auth_admin
--

CREATE TABLE auth.webauthn_challenges (
    id uuid DEFAULT gen_random_uuid() NOT NULL,
    user_id uuid,
    challenge_type text NOT NULL,
    session_data jsonb NOT NULL,
    created_at timestamp with time zone DEFAULT now() NOT NULL,
    expires_at timestamp with time zone NOT NULL,
    CONSTRAINT webauthn_challenges_challenge_type_check CHECK ((challenge_type = ANY (ARRAY['signup'::text, 'registration'::text, 'authentication'::text])))
);


ALTER TABLE auth.webauthn_challenges OWNER TO supabase_auth_admin;

--
-- Name: webauthn_credentials; Type: TABLE; Schema: auth; Owner: supabase_auth_admin
--

CREATE TABLE auth.webauthn_credentials (
    id uuid DEFAULT gen_random_uuid() NOT NULL,
    user_id uuid NOT NULL,
    credential_id bytea NOT NULL,
    public_key bytea NOT NULL,
    attestation_type text DEFAULT ''::text NOT NULL,
    aaguid uuid,
    sign_count bigint DEFAULT 0 NOT NULL,
    transports jsonb DEFAULT '[]'::jsonb NOT NULL,
    backup_eligible boolean DEFAULT false NOT NULL,
    backed_up boolean DEFAULT false NOT NULL,
    friendly_name text DEFAULT ''::text NOT NULL,
    created_at timestamp with time zone DEFAULT now() NOT NULL,
    updated_at timestamp with time zone DEFAULT now() NOT NULL,
    last_used_at timestamp with time zone
);


ALTER TABLE auth.webauthn_credentials OWNER TO supabase_auth_admin;

--
-- Name: ai_usage_logs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.ai_usage_logs (
    id bigint NOT NULL,
    user_id bigint,
    conversation_id bigint,
    request_id uuid NOT NULL,
    operation character varying(32) NOT NULL,
    model character varying(64) NOT NULL,
    prompt_tokens integer,
    completion_tokens integer,
    total_tokens integer,
    latency_ms integer,
    chunks_retrieved smallint,
    success boolean DEFAULT true NOT NULL,
    error_code character varying(64),
    created_at timestamp(0) without time zone,
    cached_prompt_tokens integer
);


ALTER TABLE public.ai_usage_logs OWNER TO postgres;

--
-- Name: ai_usage_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.ai_usage_logs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.ai_usage_logs_id_seq OWNER TO postgres;

--
-- Name: ai_usage_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.ai_usage_logs_id_seq OWNED BY public.ai_usage_logs.id;


--
-- Name: backups; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.backups (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    type character varying(255) NOT NULL,
    file_name character varying(255) NOT NULL,
    file_path character varying(255) NOT NULL,
    file_size bigint,
    records_count bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT backups_type_check CHECK (((type)::text = ANY ((ARRAY['manual'::character varying, 'automatic'::character varying])::text[])))
);


ALTER TABLE public.backups OWNER TO postgres;

--
-- Name: backups_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.backups_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.backups_id_seq OWNER TO postgres;

--
-- Name: backups_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.backups_id_seq OWNED BY public.backups.id;


--
-- Name: cache; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache OWNER TO postgres;

--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache_locks OWNER TO postgres;

--
-- Name: chat_conversations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.chat_conversations (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    stud_file_id bigint,
    title character varying(160),
    active_context jsonb,
    message_count smallint DEFAULT '0'::smallint NOT NULL,
    last_message_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.chat_conversations OWNER TO postgres;

--
-- Name: chat_conversations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.chat_conversations_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.chat_conversations_id_seq OWNER TO postgres;

--
-- Name: chat_conversations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.chat_conversations_id_seq OWNED BY public.chat_conversations.id;


--
-- Name: chat_messages; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.chat_messages (
    id bigint NOT NULL,
    conversation_id bigint NOT NULL,
    role character varying(16) NOT NULL,
    content text NOT NULL,
    citations jsonb,
    retrieval_meta jsonb,
    model character varying(64),
    prompt_tokens integer,
    completion_tokens integer,
    latency_ms integer,
    created_at timestamp(0) without time zone,
    excluded_from_history boolean DEFAULT false NOT NULL,
    external_sources jsonb
);


ALTER TABLE public.chat_messages OWNER TO postgres;

--
-- Name: chat_messages_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.chat_messages_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.chat_messages_id_seq OWNER TO postgres;

--
-- Name: chat_messages_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.chat_messages_id_seq OWNED BY public.chat_messages.id;


--
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(255) NOT NULL,
    connection text NOT NULL,
    queue text NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.failed_jobs OWNER TO postgres;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.failed_jobs_id_seq OWNER TO postgres;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- Name: job_batches; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.job_batches (
    id character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    total_jobs integer NOT NULL,
    pending_jobs integer NOT NULL,
    failed_jobs integer NOT NULL,
    failed_job_ids text NOT NULL,
    options text,
    cancelled_at integer,
    created_at integer NOT NULL,
    finished_at integer
);


ALTER TABLE public.job_batches OWNER TO postgres;

--
-- Name: jobs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.jobs (
    id bigint NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    attempts smallint NOT NULL,
    reserved_at integer,
    available_at integer NOT NULL,
    created_at integer NOT NULL
);


ALTER TABLE public.jobs OWNER TO postgres;

--
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.jobs_id_seq OWNER TO postgres;

--
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- Name: keywords; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.keywords (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    display_name character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.keywords OWNER TO postgres;

--
-- Name: keywords_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.keywords_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.keywords_id_seq OWNER TO postgres;

--
-- Name: keywords_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.keywords_id_seq OWNED BY public.keywords.id;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


ALTER TABLE public.migrations OWNER TO postgres;

--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.migrations_id_seq OWNER TO postgres;

--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: notifications; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.notifications (
    id uuid NOT NULL,
    type character varying(255) NOT NULL,
    notifiable_type character varying(255) NOT NULL,
    notifiable_id bigint NOT NULL,
    data text NOT NULL,
    read_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.notifications OWNER TO postgres;

--
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp without time zone
);


ALTER TABLE public.password_reset_tokens OWNER TO postgres;

--
-- Name: research_access_requests; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.research_access_requests (
    id bigint NOT NULL,
    email character varying,
    created_at timestamp with time zone DEFAULT (now() AT TIME ZONE 'utc'::text),
    updated_at timestamp with time zone DEFAULT now(),
    status public."ENUM" DEFAULT 'pending'::public."ENUM" NOT NULL,
    reason_access text,
    stud_file_id bigint,
    name text
);


ALTER TABLE public.research_access_requests OWNER TO postgres;

--
-- Name: research_access_requests_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

ALTER TABLE public.research_access_requests ALTER COLUMN id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.research_access_requests_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: research_chunks; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.research_chunks (
    id bigint NOT NULL,
    stud_file_id bigint NOT NULL,
    chunk_index integer NOT NULL,
    section character varying(255),
    page_number integer,
    content text NOT NULL,
    token_count integer,
    content_hash character varying(64),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    embedding public.vector(1536),
    embedding_model character varying(64),
    embedding_version smallint DEFAULT '1'::smallint NOT NULL,
    content_tsv tsvector GENERATED ALWAYS AS (to_tsvector('english'::regconfig, content)) STORED
);


ALTER TABLE public.research_chunks OWNER TO postgres;

--
-- Name: research_chunks_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.research_chunks_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.research_chunks_id_seq OWNER TO postgres;

--
-- Name: research_chunks_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.research_chunks_id_seq OWNED BY public.research_chunks.id;


--
-- Name: research_feedback; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.research_feedback (
    id bigint NOT NULL,
    stud_file_id bigint NOT NULL,
    user_id bigint NOT NULL,
    feedback text NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.research_feedback OWNER TO postgres;

--
-- Name: research_feedback_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.research_feedback_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.research_feedback_id_seq OWNER TO postgres;

--
-- Name: research_feedback_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.research_feedback_id_seq OWNED BY public.research_feedback.id;


--
-- Name: sdgs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.sdgs (
    id bigint NOT NULL,
    number smallint NOT NULL,
    created_at timestamp without time zone,
    updated_at timestamp without time zone
);


ALTER TABLE public.sdgs OWNER TO postgres;

--
-- Name: sdgs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.sdgs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.sdgs_id_seq OWNER TO postgres;

--
-- Name: sdgs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.sdgs_id_seq OWNED BY public.sdgs.id;


--
-- Name: search_logs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.search_logs (
    id bigint NOT NULL,
    user_id bigint,
    keyword character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.search_logs OWNER TO postgres;

--
-- Name: search_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.search_logs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.search_logs_id_seq OWNER TO postgres;

--
-- Name: search_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.search_logs_id_seq OWNED BY public.search_logs.id;


--
-- Name: sessions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


ALTER TABLE public.sessions OWNER TO postgres;

--
-- Name: stud_file; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.stud_file (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    title character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    adviser character varying(255),
    "desc" text,
    file_path character varying(255) NOT NULL,
    auto_tag character varying(255),
    status character varying(255) DEFAULT 'pending'::character varying NOT NULL,
    visibility character varying(255) DEFAULT 'private'::character varying NOT NULL,
    requested_visibility character varying(255) DEFAULT 'private'::character varying NOT NULL,
    views integer DEFAULT 0 NOT NULL,
    downloads integer DEFAULT 0 NOT NULL,
    created_at timestamp without time zone,
    updated_at timestamp without time zone,
    publication_year smallint,
    adviser_id bigint,
    pdf_hash character varying(64),
    pdf_page_count smallint,
    extracted_abstract text,
    abstract_source character varying(255) DEFAULT 'manual'::character varying NOT NULL,
    extracted_sections jsonb,
    ai_analysis_status character varying(255) DEFAULT 'not_started'::character varying NOT NULL,
    ai_analysis_fingerprint character varying(255),
    ai_suggested_keywords jsonb,
    ai_suggested_sdgs jsonb,
    ai_model_used character varying(255),
    ai_analyzed_at timestamp(0) without time zone,
    search_index_status character varying(255) DEFAULT 'not_started'::character varying NOT NULL,
    search_indexed_at timestamp(0) without time zone,
    search_index_error text,
    instructor_feedback text,
    rejection_reason text,
    rejected_at timestamp with time zone,
    research_type text,
    admin_reject character varying,
    resubmit_count smallint DEFAULT '0'::smallint NOT NULL,
    extracted_references jsonb
);


ALTER TABLE public.stud_file OWNER TO postgres;

--
-- Name: stud_file_author; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.stud_file_author (
    id bigint NOT NULL,
    stud_file_id bigint NOT NULL,
    user_id bigint,
    role_label character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    author_name character varying(255),
    "position" smallint DEFAULT '0'::smallint NOT NULL
);


ALTER TABLE public.stud_file_author OWNER TO postgres;

--
-- Name: stud_file_author_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.stud_file_author_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.stud_file_author_id_seq OWNER TO postgres;

--
-- Name: stud_file_author_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.stud_file_author_id_seq OWNED BY public.stud_file_author.id;


--
-- Name: stud_file_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.stud_file_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.stud_file_id_seq OWNER TO postgres;

--
-- Name: stud_file_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.stud_file_id_seq OWNED BY public.stud_file.id;


--
-- Name: stud_file_keyword; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.stud_file_keyword (
    id bigint NOT NULL,
    stud_file_id bigint NOT NULL,
    keyword_id bigint NOT NULL,
    source character varying(255) DEFAULT 'manual'::character varying NOT NULL
);


ALTER TABLE public.stud_file_keyword OWNER TO postgres;

--
-- Name: stud_file_keyword_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.stud_file_keyword_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.stud_file_keyword_id_seq OWNER TO postgres;

--
-- Name: stud_file_keyword_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.stud_file_keyword_id_seq OWNED BY public.stud_file_keyword.id;


--
-- Name: stud_file_sdg; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.stud_file_sdg (
    id bigint NOT NULL,
    stud_file_id bigint NOT NULL,
    sdg_id bigint NOT NULL
);


ALTER TABLE public.stud_file_sdg OWNER TO postgres;

--
-- Name: stud_file_sdg_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.stud_file_sdg_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.stud_file_sdg_id_seq OWNER TO postgres;

--
-- Name: stud_file_sdg_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.stud_file_sdg_id_seq OWNED BY public.stud_file_sdg.id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    user_id character varying(255) NOT NULL,
    role character varying(255) NOT NULL,
    email character varying(250) NOT NULL,
    name character varying(255) NOT NULL,
    year_level character varying(255),
    account_status character varying(255) DEFAULT 'active'::character varying NOT NULL,
    password character varying(255) NOT NULL,
    remember_token character varying(100),
    created_at timestamp without time zone,
    updated_at timestamp without time zone,
    email_verification boolean,
    email_verified_at timestamp with time zone
);


ALTER TABLE public.users OWNER TO postgres;

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_id_seq OWNER TO postgres;

--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: messages; Type: TABLE; Schema: realtime; Owner: supabase_realtime_admin
--

CREATE TABLE realtime.messages (
    topic text NOT NULL,
    extension text NOT NULL,
    payload jsonb,
    event text,
    private boolean DEFAULT false,
    updated_at timestamp without time zone DEFAULT now() NOT NULL,
    inserted_at timestamp without time zone DEFAULT now() NOT NULL,
    id uuid DEFAULT gen_random_uuid() NOT NULL,
    binary_payload bytea,
    skip_broadcast boolean DEFAULT false NOT NULL
)
PARTITION BY RANGE (inserted_at);


ALTER TABLE realtime.messages OWNER TO supabase_realtime_admin;

--
-- Name: schema_migrations; Type: TABLE; Schema: realtime; Owner: supabase_admin
--

CREATE TABLE realtime.schema_migrations (
    version bigint NOT NULL,
    inserted_at timestamp(0) without time zone DEFAULT now()
);


ALTER TABLE realtime.schema_migrations OWNER TO supabase_admin;

--
-- Name: subscription; Type: TABLE; Schema: realtime; Owner: supabase_realtime_admin
--

CREATE TABLE realtime.subscription (
    id bigint NOT NULL,
    subscription_id uuid NOT NULL,
    entity regclass NOT NULL,
    filters realtime.user_defined_filter[] DEFAULT '{}'::realtime.user_defined_filter[] NOT NULL,
    claims jsonb NOT NULL,
    claims_role regrole GENERATED ALWAYS AS (realtime.to_regrole((claims ->> 'role'::text))) STORED NOT NULL,
    created_at timestamp without time zone DEFAULT timezone('utc'::text, now()) NOT NULL,
    action_filter text DEFAULT '*'::text,
    selected_columns text[],
    CONSTRAINT subscription_action_filter_check CHECK ((action_filter = ANY (ARRAY['*'::text, 'INSERT'::text, 'UPDATE'::text, 'DELETE'::text])))
);


ALTER TABLE realtime.subscription OWNER TO supabase_realtime_admin;

--
-- Name: subscription_id_seq; Type: SEQUENCE; Schema: realtime; Owner: supabase_realtime_admin
--

ALTER TABLE realtime.subscription ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    SEQUENCE NAME realtime.subscription_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: buckets; Type: TABLE; Schema: storage; Owner: supabase_storage_admin
--

CREATE TABLE storage.buckets (
    id text NOT NULL,
    name text NOT NULL,
    owner uuid,
    created_at timestamp with time zone DEFAULT now(),
    updated_at timestamp with time zone DEFAULT now(),
    public boolean DEFAULT false,
    avif_autodetection boolean DEFAULT false,
    file_size_limit bigint,
    allowed_mime_types text[],
    owner_id text,
    type storage.buckettype DEFAULT 'STANDARD'::storage.buckettype NOT NULL,
    versioning_status text DEFAULT 'DISABLED'::text NOT NULL,
    lifecycle_configuration jsonb,
    lifecycle_configuration_generation uuid,
    CONSTRAINT buckets_lifecycle_configuration_pair_check CHECK (((lifecycle_configuration IS NULL) = (lifecycle_configuration_generation IS NULL))),
    CONSTRAINT buckets_lifecycle_configuration_shape_check CHECK (((lifecycle_configuration IS NULL) OR ((jsonb_typeof(lifecycle_configuration) = 'object'::text) AND (lifecycle_configuration ? 'rules'::text) AND
CASE
    WHEN (jsonb_typeof((lifecycle_configuration -> 'rules'::text)) = 'array'::text) THEN ((jsonb_array_length((lifecycle_configuration -> 'rules'::text)) >= 1) AND (jsonb_array_length((lifecycle_configuration -> 'rules'::text)) <= 1000))
    ELSE false
END))),
    CONSTRAINT buckets_lifecycle_configuration_standard_only_check CHECK (((type = 'STANDARD'::storage.buckettype) OR ((lifecycle_configuration IS NULL) AND (lifecycle_configuration_generation IS NULL)))),
    CONSTRAINT buckets_versioning_dark_check CHECK ((versioning_status = 'DISABLED'::text)),
    CONSTRAINT buckets_versioning_standard_only_check CHECK (((type = 'STANDARD'::storage.buckettype) OR (versioning_status = 'DISABLED'::text))),
    CONSTRAINT buckets_versioning_status_check CHECK ((versioning_status = ANY (ARRAY['DISABLED'::text, 'ENABLED'::text, 'SUSPENDED'::text])))
);


ALTER TABLE storage.buckets OWNER TO supabase_storage_admin;

--
-- Name: COLUMN buckets.owner; Type: COMMENT; Schema: storage; Owner: supabase_storage_admin
--

COMMENT ON COLUMN storage.buckets.owner IS 'Field is deprecated, use owner_id instead';


--
-- Name: buckets_analytics; Type: TABLE; Schema: storage; Owner: supabase_storage_admin
--

CREATE TABLE storage.buckets_analytics (
    name text NOT NULL,
    type storage.buckettype DEFAULT 'ANALYTICS'::storage.buckettype NOT NULL,
    format text DEFAULT 'ICEBERG'::text NOT NULL,
    created_at timestamp with time zone DEFAULT now() NOT NULL,
    updated_at timestamp with time zone DEFAULT now() NOT NULL,
    id uuid DEFAULT gen_random_uuid() NOT NULL,
    deleted_at timestamp with time zone
);


ALTER TABLE storage.buckets_analytics OWNER TO supabase_storage_admin;

--
-- Name: buckets_vectors; Type: TABLE; Schema: storage; Owner: supabase_storage_admin
--

CREATE TABLE storage.buckets_vectors (
    id text NOT NULL,
    type storage.buckettype DEFAULT 'VECTOR'::storage.buckettype NOT NULL,
    created_at timestamp with time zone DEFAULT now() NOT NULL,
    updated_at timestamp with time zone DEFAULT now() NOT NULL
);


ALTER TABLE storage.buckets_vectors OWNER TO supabase_storage_admin;

--
-- Name: migrations; Type: TABLE; Schema: storage; Owner: supabase_storage_admin
--

CREATE TABLE storage.migrations (
    id integer NOT NULL,
    name character varying(100) NOT NULL,
    hash character varying(40) NOT NULL,
    executed_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE storage.migrations OWNER TO supabase_storage_admin;

--
-- Name: objects; Type: TABLE; Schema: storage; Owner: supabase_storage_admin
--

CREATE TABLE storage.objects (
    id uuid DEFAULT gen_random_uuid() NOT NULL,
    bucket_id text,
    name text,
    owner uuid,
    created_at timestamp with time zone DEFAULT now(),
    updated_at timestamp with time zone DEFAULT now(),
    last_accessed_at timestamp with time zone DEFAULT now(),
    metadata jsonb,
    path_tokens text[] GENERATED ALWAYS AS (string_to_array(name, '/'::text)) STORED,
    version text,
    owner_id text,
    user_metadata jsonb,
    archived_at timestamp with time zone,
    is_delete_marker boolean DEFAULT false NOT NULL,
    is_versioned boolean DEFAULT false NOT NULL
);


ALTER TABLE storage.objects OWNER TO supabase_storage_admin;

--
-- Name: COLUMN objects.owner; Type: COMMENT; Schema: storage; Owner: supabase_storage_admin
--

COMMENT ON COLUMN storage.objects.owner IS 'Field is deprecated, use owner_id instead';


--
-- Name: s3_multipart_uploads; Type: TABLE; Schema: storage; Owner: supabase_storage_admin
--

CREATE TABLE storage.s3_multipart_uploads (
    id text NOT NULL,
    in_progress_size bigint DEFAULT 0 NOT NULL,
    upload_signature text NOT NULL,
    bucket_id text NOT NULL,
    key text NOT NULL COLLATE pg_catalog."C",
    version text NOT NULL,
    owner_id text,
    created_at timestamp with time zone DEFAULT now() NOT NULL,
    user_metadata jsonb,
    metadata jsonb
);


ALTER TABLE storage.s3_multipart_uploads OWNER TO supabase_storage_admin;

--
-- Name: s3_multipart_uploads_parts; Type: TABLE; Schema: storage; Owner: supabase_storage_admin
--

CREATE TABLE storage.s3_multipart_uploads_parts (
    id uuid DEFAULT gen_random_uuid() NOT NULL,
    upload_id text NOT NULL,
    size bigint DEFAULT 0 NOT NULL,
    part_number integer NOT NULL,
    bucket_id text NOT NULL,
    key text NOT NULL COLLATE pg_catalog."C",
    etag text NOT NULL,
    owner_id text,
    version text NOT NULL,
    created_at timestamp with time zone DEFAULT now() NOT NULL
);


ALTER TABLE storage.s3_multipart_uploads_parts OWNER TO supabase_storage_admin;

--
-- Name: vector_indexes; Type: TABLE; Schema: storage; Owner: supabase_storage_admin
--

CREATE TABLE storage.vector_indexes (
    id text DEFAULT gen_random_uuid() NOT NULL,
    name text NOT NULL COLLATE pg_catalog."C",
    bucket_id text NOT NULL,
    data_type text NOT NULL,
    dimension integer NOT NULL,
    distance_metric text NOT NULL,
    metadata_configuration jsonb,
    created_at timestamp with time zone DEFAULT now() NOT NULL,
    updated_at timestamp with time zone DEFAULT now() NOT NULL
);


ALTER TABLE storage.vector_indexes OWNER TO supabase_storage_admin;

--
-- Name: schema_migrations; Type: TABLE; Schema: supabase_migrations; Owner: postgres
--

CREATE TABLE supabase_migrations.schema_migrations (
    version text NOT NULL,
    statements text[],
    name text,
    created_by text,
    idempotency_key text,
    rollback text[]
);


ALTER TABLE supabase_migrations.schema_migrations OWNER TO postgres;

--
-- Name: refresh_tokens id; Type: DEFAULT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.refresh_tokens ALTER COLUMN id SET DEFAULT nextval('auth.refresh_tokens_id_seq'::regclass);


--
-- Name: ai_usage_logs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ai_usage_logs ALTER COLUMN id SET DEFAULT nextval('public.ai_usage_logs_id_seq'::regclass);


--
-- Name: backups id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.backups ALTER COLUMN id SET DEFAULT nextval('public.backups_id_seq'::regclass);


--
-- Name: chat_conversations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.chat_conversations ALTER COLUMN id SET DEFAULT nextval('public.chat_conversations_id_seq'::regclass);


--
-- Name: chat_messages id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.chat_messages ALTER COLUMN id SET DEFAULT nextval('public.chat_messages_id_seq'::regclass);


--
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- Name: keywords id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.keywords ALTER COLUMN id SET DEFAULT nextval('public.keywords_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: research_chunks id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.research_chunks ALTER COLUMN id SET DEFAULT nextval('public.research_chunks_id_seq'::regclass);


--
-- Name: research_feedback id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.research_feedback ALTER COLUMN id SET DEFAULT nextval('public.research_feedback_id_seq'::regclass);


--
-- Name: sdgs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sdgs ALTER COLUMN id SET DEFAULT nextval('public.sdgs_id_seq'::regclass);


--
-- Name: search_logs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.search_logs ALTER COLUMN id SET DEFAULT nextval('public.search_logs_id_seq'::regclass);


--
-- Name: stud_file id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.stud_file ALTER COLUMN id SET DEFAULT nextval('public.stud_file_id_seq'::regclass);


--
-- Name: stud_file_author id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.stud_file_author ALTER COLUMN id SET DEFAULT nextval('public.stud_file_author_id_seq'::regclass);


--
-- Name: stud_file_keyword id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.stud_file_keyword ALTER COLUMN id SET DEFAULT nextval('public.stud_file_keyword_id_seq'::regclass);


--
-- Name: stud_file_sdg id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.stud_file_sdg ALTER COLUMN id SET DEFAULT nextval('public.stud_file_sdg_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Data for Name: audit_log_entries; Type: TABLE DATA; Schema: auth; Owner: supabase_auth_admin
--

COPY auth.audit_log_entries (instance_id, id, payload, created_at, ip_address) FROM stdin;
\.


--
-- Data for Name: custom_oauth_providers; Type: TABLE DATA; Schema: auth; Owner: supabase_auth_admin
--

COPY auth.custom_oauth_providers (id, provider_type, identifier, name, client_id, client_secret, acceptable_client_ids, scopes, pkce_enabled, attribute_mapping, authorization_params, enabled, email_optional, issuer, discovery_url, skip_nonce_check, cached_discovery, discovery_cached_at, authorization_url, token_url, userinfo_url, jwks_uri, created_at, updated_at, custom_claims_allowlist) FROM stdin;
\.


--
-- Data for Name: flow_state; Type: TABLE DATA; Schema: auth; Owner: supabase_auth_admin
--

COPY auth.flow_state (id, user_id, auth_code, code_challenge_method, code_challenge, provider_type, provider_access_token, provider_refresh_token, created_at, updated_at, authentication_method, auth_code_issued_at, invite_token, referrer, oauth_client_state_id, linking_target_id, email_optional) FROM stdin;
\.


--
-- Data for Name: identities; Type: TABLE DATA; Schema: auth; Owner: supabase_auth_admin
--

COPY auth.identities (provider_id, user_id, identity_data, provider, last_sign_in_at, created_at, updated_at, id) FROM stdin;
\.


--
-- Data for Name: instances; Type: TABLE DATA; Schema: auth; Owner: supabase_auth_admin
--

COPY auth.instances (id, uuid, raw_base_config, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: mfa_amr_claims; Type: TABLE DATA; Schema: auth; Owner: supabase_auth_admin
--

COPY auth.mfa_amr_claims (session_id, created_at, updated_at, authentication_method, id) FROM stdin;
\.


--
-- Data for Name: mfa_challenges; Type: TABLE DATA; Schema: auth; Owner: supabase_auth_admin
--

COPY auth.mfa_challenges (id, factor_id, created_at, verified_at, ip_address, otp_code, web_authn_session_data) FROM stdin;
\.


--
-- Data for Name: mfa_factors; Type: TABLE DATA; Schema: auth; Owner: supabase_auth_admin
--

COPY auth.mfa_factors (id, user_id, friendly_name, factor_type, status, created_at, updated_at, secret, phone, last_challenged_at, web_authn_credential, web_authn_aaguid, last_webauthn_challenge_data) FROM stdin;
\.


--
-- Data for Name: mfa_recovery_code_sets; Type: TABLE DATA; Schema: auth; Owner: supabase_auth_admin
--

COPY auth.mfa_recovery_code_sets (id, user_id, mfa_factor_id, failed_verification_count, verification_locked_until, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: mfa_recovery_codes; Type: TABLE DATA; Schema: auth; Owner: supabase_auth_admin
--

COPY auth.mfa_recovery_codes (id, mfa_recovery_code_set_id, code_hash, consumed_at, created_at) FROM stdin;
\.


--
-- Data for Name: oauth_authorizations; Type: TABLE DATA; Schema: auth; Owner: supabase_auth_admin
--

COPY auth.oauth_authorizations (id, authorization_id, client_id, user_id, redirect_uri, scope, state, resource, code_challenge, code_challenge_method, response_type, status, authorization_code, created_at, expires_at, approved_at, nonce) FROM stdin;
\.


--
-- Data for Name: oauth_client_states; Type: TABLE DATA; Schema: auth; Owner: supabase_auth_admin
--

COPY auth.oauth_client_states (id, provider_type, code_verifier, created_at) FROM stdin;
\.


--
-- Data for Name: oauth_clients; Type: TABLE DATA; Schema: auth; Owner: supabase_auth_admin
--

COPY auth.oauth_clients (id, client_secret_hash, registration_type, redirect_uris, grant_types, client_name, client_uri, logo_uri, created_at, updated_at, deleted_at, client_type, token_endpoint_auth_method) FROM stdin;
\.


--
-- Data for Name: oauth_consents; Type: TABLE DATA; Schema: auth; Owner: supabase_auth_admin
--

COPY auth.oauth_consents (id, user_id, client_id, scopes, granted_at, revoked_at) FROM stdin;
\.


--
-- Data for Name: one_time_tokens; Type: TABLE DATA; Schema: auth; Owner: supabase_auth_admin
--

COPY auth.one_time_tokens (id, user_id, token_type, token_hash, relates_to, created_at, updated_at, expires_at) FROM stdin;
\.


--
-- Data for Name: refresh_tokens; Type: TABLE DATA; Schema: auth; Owner: supabase_auth_admin
--

COPY auth.refresh_tokens (instance_id, id, token, user_id, revoked, created_at, updated_at, parent, session_id) FROM stdin;
\.


--
-- Data for Name: saml_providers; Type: TABLE DATA; Schema: auth; Owner: supabase_auth_admin
--

COPY auth.saml_providers (id, sso_provider_id, entity_id, metadata_xml, metadata_url, attribute_mapping, created_at, updated_at, name_id_format) FROM stdin;
\.


--
-- Data for Name: saml_relay_states; Type: TABLE DATA; Schema: auth; Owner: supabase_auth_admin
--

COPY auth.saml_relay_states (id, sso_provider_id, request_id, for_email, redirect_to, created_at, updated_at, flow_state_id) FROM stdin;
\.


--
-- Data for Name: schema_migrations; Type: TABLE DATA; Schema: auth; Owner: supabase_auth_admin
--

COPY auth.schema_migrations (version) FROM stdin;
20171026211738
20171026211808
20171026211834
20180103212743
20180108183307
20180119214651
20180125194653
00
20210710035447
20210722035447
20210730183235
20210909172000
20210927181326
20211122151130
20211124214934
20211202183645
20220114185221
20220114185340
20220224000811
20220323170000
20220429102000
20220531120530
20220614074223
20220811173540
20221003041349
20221003041400
20221011041400
20221020193600
20221021073300
20221021082433
20221027105023
20221114143122
20221114143410
20221125140132
20221208132122
20221215195500
20221215195800
20221215195900
20230116124310
20230116124412
20230131181311
20230322519590
20230402418590
20230411005111
20230508135423
20230523124323
20230818113222
20230914180801
20231027141322
20231114161723
20231117164230
20240115144230
20240214120130
20240306115329
20240314092811
20240427152123
20240612123726
20240729123726
20240802193726
20240806073726
20241009103726
20250717082212
20250731150234
20250804100000
20250901200500
20250903112500
20250904133000
20250925093508
20251007112900
20251104100000
20251111201300
20251201000000
20260115000000
20260121000000
20260219120000
20260302000000
20260625000000
20260821000000
20260821010000
20260824000000
20260824000001
20260831180000
\.


--
-- Data for Name: scim_tokens; Type: TABLE DATA; Schema: auth; Owner: supabase_auth_admin
--

COPY auth.scim_tokens (id, sso_provider_id, token_hash, prefix, created_at, expires_at, revoked_at, last_used_at) FROM stdin;
\.


--
-- Data for Name: scim_users; Type: TABLE DATA; Schema: auth; Owner: supabase_auth_admin
--

COPY auth.scim_users (id, sso_provider_id, user_id, resource, created_at, updated_at, deleted_at) FROM stdin;
\.


--
-- Data for Name: sessions; Type: TABLE DATA; Schema: auth; Owner: supabase_auth_admin
--

COPY auth.sessions (id, user_id, created_at, updated_at, factor_id, aal, not_after, refreshed_at, user_agent, ip, tag, oauth_client_id, refresh_token_hmac_key, refresh_token_counter, scopes) FROM stdin;
\.


--
-- Data for Name: sso_domains; Type: TABLE DATA; Schema: auth; Owner: supabase_auth_admin
--

COPY auth.sso_domains (id, sso_provider_id, domain, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: sso_providers; Type: TABLE DATA; Schema: auth; Owner: supabase_auth_admin
--

COPY auth.sso_providers (id, resource_id, created_at, updated_at, disabled) FROM stdin;
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: auth; Owner: supabase_auth_admin
--

COPY auth.users (instance_id, id, aud, role, email, encrypted_password, email_confirmed_at, invited_at, confirmation_token, confirmation_sent_at, recovery_token, recovery_sent_at, email_change_token_new, email_change, email_change_sent_at, last_sign_in_at, raw_app_meta_data, raw_user_meta_data, is_super_admin, created_at, updated_at, phone, phone_confirmed_at, phone_change, phone_change_token, phone_change_sent_at, email_change_token_current, email_change_confirm_status, banned_until, reauthentication_token, reauthentication_sent_at, is_sso_user, deleted_at, is_anonymous) FROM stdin;
\.


--
-- Data for Name: webauthn_challenges; Type: TABLE DATA; Schema: auth; Owner: supabase_auth_admin
--

COPY auth.webauthn_challenges (id, user_id, challenge_type, session_data, created_at, expires_at) FROM stdin;
\.


--
-- Data for Name: webauthn_credentials; Type: TABLE DATA; Schema: auth; Owner: supabase_auth_admin
--

COPY auth.webauthn_credentials (id, user_id, credential_id, public_key, attestation_type, aaguid, sign_count, transports, backup_eligible, backed_up, friendly_name, created_at, updated_at, last_used_at) FROM stdin;
\.


--
-- Data for Name: ai_usage_logs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.ai_usage_logs (id, user_id, conversation_id, request_id, operation, model, prompt_tokens, completion_tokens, total_tokens, latency_ms, chunks_retrieved, success, error_code, created_at, cached_prompt_tokens) FROM stdin;
81	2	34	92d0c696-8d42-49fc-aee7-bc2fdbe552a7	query_embedding	text-embedding-3-small	\N	\N	\N	6407	0	t	\N	2026-09-19 04:57:10	\N
82	2	34	3e1da7ef-beee-44a0-827f-a51fdfb88448	chat_answer	gpt-4o-mini	728	118	846	2981	0	t	\N	2026-09-19 05:06:20	\N
83	2	34	6fd1bef5-369e-40dd-9ced-8a48fcf9fd7f	query_embedding	text-embedding-3-small	\N	\N	\N	4005	0	t	\N	2026-09-19 05:08:10	\N
84	2	186	25f31739-71bb-458d-9bf4-167ca28231b5	catalog	gpt-4o-mini	981	68	1049	1779	1	t	\N	2026-09-19 06:07:33	\N
85	2	188	4c51a00f-1597-4c2d-8f79-1f6622631e56	query_embedding	text-embedding-3-small	\N	\N	\N	2326	0	t	\N	2026-09-19 06:12:57	\N
86	2	189	a81d62e7-9c05-4ca6-b9b0-3f2df3cd0f53	catalog	none	\N	\N	\N	\N	0	t	\N	2026-09-19 06:13:09	\N
87	2	189	c5d0a401-cfa7-46c7-a04a-6bda1fcdd236	catalog	gpt-4o-mini	1028	66	1094	1487	1	t	\N	2026-09-19 06:13:33	\N
88	2	190	5654176c-db9d-4de6-a8c9-6c2b7406ff47	chat_answer	gpt-4o-mini	678	111	789	3802	0	t	\N	2026-09-19 12:55:52	\N
89	2	195	88e649b3-df17-4ee7-a821-20b58264acc6	catalog	none	\N	\N	\N	\N	0	t	\N	2026-09-23 15:20:44	\N
90	2	195	130dcddf-bde1-4b0b-8817-234ba6274b1d	query_embedding	text-embedding-3-small	\N	\N	\N	7143	1	t	\N	2026-09-23 15:23:39	\N
91	2	195	130dcddf-bde1-4b0b-8817-234ba6274b1d	chat_answer	gpt-4o-mini	1260	43	1303	2144	1	t	\N	2026-09-23 15:23:43	\N
92	2	196	6a675e75-d7c4-422f-9f03-65d5f7b064ca	catalog	gpt-4o-mini	983	63	1046	3208	1	t	\N	2026-09-23 15:34:21	\N
93	2	197	516545f2-f3c7-4c76-aeb5-33eec59ead47	catalog	gpt-4o-mini	983	62	1045	2991	1	t	\N	2026-09-23 15:41:56	\N
94	2	198	6a4cb092-e3d1-4d03-8861-3daf2d8ca317	catalog	gpt-4o-mini	983	64	1047	4083	1	t	\N	2026-09-24 03:37:19	\N
95	2	\N	7ad0ae8b-8000-48ee-9138-186624a615e6	doc_embedding	text-embedding-3-small	4657	\N	4657	\N	13	t	\N	2026-09-25 18:47:03	\N
96	2	\N	62fbc817-a615-46a6-8ec8-5cfb03b6913a	doc_embedding	text-embedding-3-small	20962	\N	20962	\N	43	t	\N	2026-09-25 18:47:10	\N
97	4	\N	90e7da07-4f1d-4067-8605-b0055f59f4c3	doc_embedding	text-embedding-3-small	5766	\N	5766	\N	14	t	\N	2026-09-25 18:47:17	\N
98	4	\N	497214c2-2422-42fa-bfb2-282a07c8bf54	doc_embedding	text-embedding-3-small	4138	\N	4138	\N	11	t	\N	2026-09-25 18:47:23	\N
99	2	\N	9887aa93-4269-4929-8f13-dc48805e110f	doc_embedding	text-embedding-3-small	3829	\N	3829	\N	8	t	\N	2026-09-25 18:47:29	\N
100	2	352	c5389f54-c83f-46f2-a4ae-e003c355c74d	query_embedding	text-embedding-3-small	\N	\N	\N	13194	3	t	\N	2026-09-27 05:52:53	\N
101	2	352	c5389f54-c83f-46f2-a4ae-e003c355c74d	chat_answer	gpt-4o-mini	1857	223	2080	3958	3	t	\N	2026-09-27 05:52:58	\N
102	2	353	14ba0a7c-44e0-43f3-ba9c-c8ff62894f39	catalog	gpt-4o-mini	1312	192	1504	3122	3	t	\N	2026-09-27 06:04:19	\N
103	2	354	c17f577a-f3e4-4a9d-9e15-b6338e8c184c	query_embedding	text-embedding-3-small	\N	\N	\N	6075	5	t	\N	2026-09-27 06:07:09	\N
104	2	354	c17f577a-f3e4-4a9d-9e15-b6338e8c184c	chat_answer	gpt-4o-mini	2587	200	2787	2502	5	t	\N	2026-09-27 06:07:13	\N
105	2	355	d8691295-6121-43ae-b825-94e8f2cf38a6	catalog	gpt-4o-mini	1650	164	1814	3781	5	t	\N	2026-09-28 02:38:50	0
106	2	\N	94856220-7dbb-4653-affa-1bdbdf697e0a	metadata	gpt-4o-mini	444	69	513	5023	\N	t	\N	2026-09-28 03:25:15	0
107	3	356	147392ca-1f8d-4f98-ba6b-81be14eaeaf3	chat_answer	gpt-4o-mini	1620	173	1793	3698	2	t	\N	2026-09-28 03:33:35	0
108	2	\N	2ec60ce9-e157-492b-a540-8b6454967b56	metadata	gpt-4o-mini	442	69	511	4062	\N	t	\N	2026-09-28 09:22:17	0
109	2	\N	e9d84239-912e-4f95-a4c7-d645c12ee7a1	metadata	gpt-4o-mini	441	69	510	4639	\N	t	\N	2026-09-28 11:50:34	0
110	2	\N	608e65c1-5f0f-4583-ba66-0a199854b80a	metadata	gpt-4o-mini	441	65	506	6506	\N	t	\N	2026-09-28 14:06:05	0
111	2	\N	f167f27a-0163-4109-8882-67cf61707634	metadata	gpt-4o-mini	442	64	506	3158	\N	t	\N	2026-09-28 14:19:00	0
112	2	\N	bc64571f-db52-4a22-b938-8b36873093ae	metadata	gpt-4o-mini	441	66	507	3590	\N	t	\N	2026-09-28 14:57:57	0
113	2	\N	c657da69-4547-41a6-97f1-5f49564ecdbd	metadata	gpt-4o-mini	442	68	510	3493	\N	t	\N	2026-09-28 15:08:06	0
114	2	\N	cb2f9b59-90ab-485b-8fef-fece3a7e6e37	metadata	gpt-4o-mini	442	69	511	2546	\N	t	\N	2026-09-28 15:11:29	0
115	2	\N	9cf5eb00-274b-4126-a341-49d9ea3f2b56	metadata	gpt-4o-mini	442	69	511	5189	\N	t	\N	2026-09-28 17:18:51	0
116	2	\N	8c9355e3-37e7-4bb8-9fc1-a834e5ff14a1	metadata	gpt-4o-mini	442	68	510	5944	\N	t	\N	2026-09-29 02:14:44	0
117	2	357	97849ccc-5865-4301-b4f1-c72cff211b40	catalog	gpt-4o-mini	1353	107	1460	4193	3	t	\N	2026-09-29 03:24:04	0
118	2	\N	6674622e-d56c-4205-8f0d-c3bf72aa6d6c	metadata	gpt-4o-mini	441	68	509	3308	\N	t	\N	2026-09-29 05:09:30	0
119	3	358	4d80934e-6798-4b08-a84b-499b2e6e9689	catalog	gpt-4o-mini	1357	189	1546	5919	3	t	\N	2026-09-29 05:18:40	0
120	2	\N	1d59ed41-5e3a-45a3-8aae-4d763062615f	metadata	gpt-4o-mini	442	69	511	3269	\N	t	\N	2026-09-29 05:40:23	0
121	2	359	1d8d52be-4450-4393-8af1-cf4d5c910ae6	catalog	gpt-4o-mini	1353	107	1460	2722	3	t	\N	2026-09-29 05:48:38	0
122	2	\N	1fa934c7-9093-4dcb-b026-74eb031466e9	metadata	gpt-4o-mini	475	53	528	3816	\N	t	\N	2026-09-29 16:53:11	0
123	2	\N	7f737ef0-96e0-4fc1-a36c-0928bd97625f	metadata	gpt-4o-mini	485	57	542	2757	\N	t	\N	2026-09-29 17:07:47	0
124	2	\N	95f1a401-e98f-41e5-a90a-f2f9f52e6fa3	metadata	gpt-4o-mini	442	69	511	3202	\N	t	\N	2026-09-30 04:59:18	0
125	2	360	6b2fdacc-46fb-4eca-9f5c-77425631704c	catalog	gpt-4o-mini	1353	148	1501	4218	3	t	\N	2026-09-30 05:00:56	0
126	4	1	4e9195d3-6052-40a3-83b3-0bb43db7468a	catalog	none	\N	\N	\N	\N	0	t	\N	2026-10-01 13:33:26	\N
127	4	2	fa01f5da-32c6-4157-a2c8-f1dfdfa5631c	catalog	none	\N	\N	\N	\N	0	t	\N	2026-10-01 13:35:43	\N
128	4	3	8096cc25-1c0b-4d4b-95d4-87bcb658ce07	catalog	none	\N	\N	\N	\N	0	t	\N	2026-10-01 13:38:45	\N
129	4	3	e4dce650-2a8a-4be9-8382-ed03809b55cb	catalog	none	\N	\N	\N	\N	0	t	\N	2026-10-01 13:42:01	\N
130	2	5	73e1ef2b-2aa2-4f9d-99bc-24fb502b8a36	catalog	none	\N	\N	\N	\N	0	t	\N	2026-10-03 13:42:01	\N
131	2	6	9b097fe0-b50e-46a8-bbd6-55f29d4eeddc	catalog	none	\N	\N	\N	\N	0	t	\N	2026-10-03 13:46:58	\N
\.


--
-- Data for Name: backups; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.backups (id, name, type, file_name, file_path, file_size, records_count, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: cache; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cache (key, value, expiration) FROM stdin;
laravel-cache-admin_dashboard_stats:2026	a:8:{s:13:"totalResearch";i:5;s:13:"approvedCount";i:4;s:13:"rejectedCount";i:0;s:12:"pendingCount";i:0;s:10:"uploadData";a:12:{i:0;i:0;i:1;i:0;i:2;i:0;i:3;i:0;i:4;i:0;i:5;i:0;i:6;i:0;i:7;i:0;i:8;i:0;i:9;i:5;i:10;i:0;i:11;i:0;}s:12:"approvalData";a:12:{i:0;i:0;i:1;i:0;i:2;i:0;i:3;i:0;i:4;i:0;i:5;i:0;i:6;i:0;i:7;i:0;i:8;i:0;i:9;i:4;i:10;i:0;i:11;i:0;}s:8:"viewData";a:12:{i:0;i:0;i:1;i:0;i:2;i:0;i:3;i:0;i:4;i:0;i:5;i:0;i:6;i:0;i:7;i:0;i:8;i:0;i:9;i:4;i:10;i:0;i:11;i:0;}s:12:"downloadData";a:12:{i:0;i:0;i:1;i:0;i:2;i:0;i:3;i:0;i:4;i:0;i:5;i:0;i:6;i:0;i:7;i:0;i:8;i:0;i:9;i:1;i:10;i:0;i:11;i:0;}}	1791293864
\.


--
-- Data for Name: cache_locks; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cache_locks (key, owner, expiration) FROM stdin;
\.


--
-- Data for Name: chat_conversations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.chat_conversations (id, user_id, stud_file_id, title, active_context, message_count, last_message_at, created_at, updated_at) FROM stdin;
1	4	\N	What research is available about artificial intelligence?	\N	2	2026-10-01 13:33:27	2026-10-01 13:33:08	2026-10-01 13:33:27
2	4	\N	Which studies support SDG 4?	\N	2	2026-10-01 13:35:43	2026-10-01 13:35:28	2026-10-01 13:35:43
3	4	\N	What research is available about artificial intelligence?	\N	6	2026-10-01 13:42:02	2026-10-01 13:38:33	2026-10-01 13:42:02
4	4	\N	Research on the Application of Intelligent Technology in Doc...	\N	2	2026-10-01 13:56:59	2026-10-01 13:56:39	2026-10-01 13:56:59
5	2	\N	What research is available about artificial intelligence?	\N	2	2026-10-03 13:42:02	2026-10-03 13:41:33	2026-10-03 13:42:02
6	2	\N	Which studies support SDG 4?	\N	2	2026-10-03 13:46:59	2026-10-03 13:46:31	2026-10-03 13:46:59
\.


--
-- Data for Name: chat_messages; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.chat_messages (id, conversation_id, role, content, citations, retrieval_meta, model, prompt_tokens, completion_tokens, latency_ms, created_at, excluded_from_history, external_sources) FROM stdin;
1	1	user	What research is available about artificial intelligence?	\N	\N	\N	\N	\N	\N	2026-10-01 13:33:16	f	\N
2	1	assistant	The repository doesn't have any research you can access yet. Once submissions are approved and published, I can help you explore them.	\N	{"intent": "catalog", "filters": {"topic": "What research is available about artificial intelligence?", "author": null, "year_to": null, "is_broad": true, "year_from": null, "sdg_numbers": [], "is_comparison": false, "research_type": null, "stud_file_ids": []}, "skipped": "catalog_empty", "classifier": "catalog"}	\N	\N	\N	\N	2026-10-01 13:33:26	t	\N
3	2	user	Which studies support SDG 4?	\N	\N	\N	\N	\N	\N	2026-10-01 13:35:36	f	\N
4	2	assistant	I couldn't find any research you can access matching SDG 4. Try widening the year range, removing a filter, or asking about a broader topic.	\N	{"intent": "catalog", "filters": {"topic": "Which studies support ?", "author": null, "year_to": null, "is_broad": true, "year_from": null, "sdg_numbers": [4], "is_comparison": false, "research_type": null, "stud_file_ids": []}, "skipped": "catalog_empty", "classifier": "catalog"}	\N	\N	\N	\N	2026-10-01 13:35:43	t	\N
5	3	user	What research is available about artificial intelligence?	\N	\N	\N	\N	\N	\N	2026-10-01 13:38:43	f	\N
6	3	assistant	The repository doesn't have any research you can access yet. Once submissions are approved and published, I can help you explore them.	\N	{"intent": "catalog", "filters": {"topic": "What research is available about artificial intelligence?", "author": null, "year_to": null, "is_broad": true, "year_from": null, "sdg_numbers": [], "is_comparison": false, "research_type": null, "stud_file_ids": []}, "skipped": "catalog_empty", "classifier": "catalog"}	\N	\N	\N	\N	2026-10-01 13:38:46	t	\N
7	3	user	bugueyaknows study?	\N	\N	\N	\N	\N	\N	2026-10-01 13:40:56	f	\N
8	3	assistant	I couldn't find enough information in the research repository to answer that. Try a broader topic, different keywords, or browse the repository directly.	\N	{"intent": "research_qa", "classifier": "default", "no_evidence": true, "nearest_document_ids": []}	\N	\N	\N	\N	2026-10-01 13:41:04	t	\N
9	3	user	what's study available today?	\N	\N	\N	\N	\N	\N	2026-10-01 13:41:59	f	\N
10	3	assistant	The repository doesn't have any research you can access yet. Once submissions are approved and published, I can help you explore them.	\N	{"intent": "catalog", "filters": {"topic": "what's study available today?", "author": null, "year_to": null, "is_broad": true, "year_from": null, "sdg_numbers": [], "is_comparison": false, "research_type": null, "stud_file_ids": []}, "skipped": "catalog_empty", "classifier": "catalog"}	\N	\N	\N	\N	2026-10-01 13:42:01	t	\N
11	4	user	Research on the Application of Intelligent Technology in Document and Archives Management	\N	\N	\N	\N	\N	\N	2026-10-01 13:56:50	f	\N
12	4	assistant	I couldn't find enough information in the research repository to answer that. Try a broader topic, different keywords, or browse the repository directly.	\N	{"intent": "research_qa", "classifier": "default", "no_evidence": true, "nearest_document_ids": []}	\N	\N	\N	\N	2026-10-01 13:56:58	t	\N
13	5	user	What research is available about artificial intelligence?	\N	\N	\N	\N	\N	\N	2026-10-03 13:41:49	f	\N
14	5	assistant	The repository doesn't have any research you can access yet. Once submissions are approved and published, I can help you explore them.	\N	{"intent": "catalog", "filters": {"topic": "What research is available about artificial intelligence?", "author": null, "year_to": null, "is_broad": true, "year_from": null, "sdg_numbers": [], "is_comparison": false, "research_type": null, "stud_file_ids": []}, "skipped": "catalog_empty", "classifier": "catalog"}	\N	\N	\N	\N	2026-10-03 13:42:02	t	\N
15	6	user	Which studies support SDG 4?	\N	\N	\N	\N	\N	\N	2026-10-03 13:46:50	f	\N
16	6	assistant	I couldn't find any research you can access matching SDG 4. Try widening the year range, removing a filter, or asking about a broader topic.	\N	{"intent": "catalog", "filters": {"topic": "Which studies support ?", "author": null, "year_to": null, "is_broad": true, "year_from": null, "sdg_numbers": [4], "is_comparison": false, "research_type": null, "stud_file_ids": []}, "skipped": "catalog_empty", "classifier": "catalog"}	\N	\N	\N	\N	2026-10-03 13:46:59	t	\N
\.


--
-- Data for Name: failed_jobs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.failed_jobs (id, uuid, connection, queue, payload, exception, failed_at) FROM stdin;
\.


--
-- Data for Name: job_batches; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.job_batches (id, name, total_jobs, pending_jobs, failed_jobs, failed_job_ids, options, cancelled_at, created_at, finished_at) FROM stdin;
\.


--
-- Data for Name: jobs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.jobs (id, queue, payload, attempts, reserved_at, available_at, created_at) FROM stdin;
8	default	{"uuid":"0f1caa7b-6d8b-43ce-8c42-ccd85d5a9bd9","displayName":"App\\\\Jobs\\\\ProcessResearchDocument","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":3,"maxExceptions":null,"failOnTimeout":false,"backoff":"10,30,60","timeout":null,"retryUntil":null,"data":{"commandName":"App\\\\Jobs\\\\ProcessResearchDocument","command":"O:32:\\"App\\\\Jobs\\\\ProcessResearchDocument\\":1:{s:10:\\"studFileId\\";i:35;}"},"createdAt":1790176029,"delay":null}	0	\N	1790176029	1790176029
9	default	{"uuid":"dc486ea4-2ef6-4392-951e-39c4381e2e08","displayName":"App\\\\Jobs\\\\ProcessResearchDocument","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":3,"maxExceptions":null,"failOnTimeout":false,"backoff":"10,30,60","timeout":null,"retryUntil":null,"data":{"commandName":"App\\\\Jobs\\\\ProcessResearchDocument","command":"O:32:\\"App\\\\Jobs\\\\ProcessResearchDocument\\":1:{s:10:\\"studFileId\\";i:35;}"},"createdAt":1790178036,"delay":null}	0	\N	1790178036	1790178036
10	default	{"uuid":"8dd1fe07-d3e3-48af-ac42-e4b602900e8c","displayName":"App\\\\Jobs\\\\ProcessResearchDocument","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":3,"maxExceptions":null,"failOnTimeout":false,"backoff":"10,30,60","timeout":null,"retryUntil":null,"data":{"commandName":"App\\\\Jobs\\\\ProcessResearchDocument","command":"O:32:\\"App\\\\Jobs\\\\ProcessResearchDocument\\":1:{s:10:\\"studFileId\\";i:36;}"},"createdAt":1790238157,"delay":null}	0	\N	1790238157	1790238157
11	default	{"uuid":"f1d94131-ac1b-47c1-a65b-7ec6da58da45","displayName":"App\\\\Jobs\\\\ProcessResearchDocument","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":3,"maxExceptions":null,"failOnTimeout":false,"backoff":"10,30,60","timeout":null,"retryUntil":null,"data":{"commandName":"App\\\\Jobs\\\\ProcessResearchDocument","command":"O:32:\\"App\\\\Jobs\\\\ProcessResearchDocument\\":1:{s:10:\\"studFileId\\";i:37;}"},"createdAt":1790242196,"delay":null}	0	\N	1790242196	1790242196
12	default	{"uuid":"995f355f-be69-425f-a39b-a3730decc026","displayName":"App\\\\Jobs\\\\ProcessResearchDocument","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":3,"maxExceptions":null,"failOnTimeout":false,"backoff":"10,30,60","timeout":null,"retryUntil":null,"data":{"commandName":"App\\\\Jobs\\\\ProcessResearchDocument","command":"O:32:\\"App\\\\Jobs\\\\ProcessResearchDocument\\":1:{s:10:\\"studFileId\\";i:37;}"},"createdAt":1790261645,"delay":null}	0	\N	1790261645	1790261645
13	default	{"uuid":"1a122cdb-78ed-442c-9593-26ae021be549","displayName":"App\\\\Jobs\\\\ProcessResearchDocument","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":3,"maxExceptions":null,"failOnTimeout":false,"backoff":"10,30,60","timeout":null,"retryUntil":null,"data":{"commandName":"App\\\\Jobs\\\\ProcessResearchDocument","command":"O:32:\\"App\\\\Jobs\\\\ProcessResearchDocument\\":1:{s:10:\\"studFileId\\";i:38;}"},"createdAt":1790307772,"delay":null}	0	\N	1790307772	1790307772
14	default	{"uuid":"e29e16fd-680a-4a9d-bf94-aca274e79f07","displayName":"App\\\\Jobs\\\\ProcessResearchDocument","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":3,"maxExceptions":null,"failOnTimeout":false,"backoff":"10,30,60","timeout":null,"retryUntil":null,"data":{"commandName":"App\\\\Jobs\\\\ProcessResearchDocument","command":"O:32:\\"App\\\\Jobs\\\\ProcessResearchDocument\\":1:{s:10:\\"studFileId\\";i:39;}"},"createdAt":1790315353,"delay":null}	0	\N	1790315353	1790315353
15	default	{"uuid":"12e68c9f-e712-4a28-82fc-ec97673d25b4","displayName":"App\\\\Jobs\\\\ProcessResearchDocument","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":3,"maxExceptions":null,"failOnTimeout":false,"backoff":"10,30,60","timeout":null,"retryUntil":null,"data":{"commandName":"App\\\\Jobs\\\\ProcessResearchDocument","command":"O:32:\\"App\\\\Jobs\\\\ProcessResearchDocument\\":1:{s:10:\\"studFileId\\";i:41;}"},"createdAt":1790334506,"delay":null}	0	\N	1790334506	1790334506
16	default	{"uuid":"ca348fd1-518b-4c7b-9a52-c0a438782ade","displayName":"App\\\\Jobs\\\\ProcessResearchDocument","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":3,"maxExceptions":null,"failOnTimeout":false,"backoff":"10,30,60","timeout":null,"retryUntil":null,"data":{"commandName":"App\\\\Jobs\\\\ProcessResearchDocument","command":"O:32:\\"App\\\\Jobs\\\\ProcessResearchDocument\\":1:{s:10:\\"studFileId\\";i:42;}"},"createdAt":1790336051,"delay":null}	0	\N	1790336051	1790336051
17	default	{"uuid":"35ca3571-5623-49d5-8e46-51745afd8cc9","displayName":"App\\\\Jobs\\\\ProcessResearchDocument","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":3,"maxExceptions":null,"failOnTimeout":false,"backoff":"10,30,60","timeout":null,"retryUntil":null,"data":{"commandName":"App\\\\Jobs\\\\ProcessResearchDocument","command":"O:32:\\"App\\\\Jobs\\\\ProcessResearchDocument\\":1:{s:10:\\"studFileId\\";i:43;}"},"createdAt":1790337552,"delay":null}	0	\N	1790337552	1790337552
18	default	{"uuid":"0f85b705-c820-474c-ae6d-a6f86746ec62","displayName":"App\\\\Jobs\\\\ProcessResearchDocument","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":3,"maxExceptions":null,"failOnTimeout":false,"backoff":"10,30,60","timeout":null,"retryUntil":null,"data":{"commandName":"App\\\\Jobs\\\\ProcessResearchDocument","command":"O:32:\\"App\\\\Jobs\\\\ProcessResearchDocument\\":1:{s:10:\\"studFileId\\";i:44;}"},"createdAt":1790563028,"delay":null}	0	\N	1790563028	1790563028
19	default	{"uuid":"5fa77731-2f47-4b85-a660-23dad74df096","displayName":"App\\\\Jobs\\\\ProcessResearchDocument","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":3,"maxExceptions":null,"failOnTimeout":false,"backoff":"10,30,60","timeout":null,"retryUntil":null,"data":{"commandName":"App\\\\Jobs\\\\ProcessResearchDocument","command":"O:32:\\"App\\\\Jobs\\\\ProcessResearchDocument\\":1:{s:10:\\"studFileId\\";i:45;}"},"createdAt":1790564472,"delay":null}	0	\N	1790564472	1790564472
20	default	{"uuid":"05fca540-d771-4a71-a9ae-cf0c3fe820e7","displayName":"App\\\\Jobs\\\\ProcessResearchDocument","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":3,"maxExceptions":null,"failOnTimeout":false,"backoff":"10,30,60","timeout":null,"retryUntil":null,"data":{"commandName":"App\\\\Jobs\\\\ProcessResearchDocument","command":"O:32:\\"App\\\\Jobs\\\\ProcessResearchDocument\\":1:{s:10:\\"studFileId\\";i:46;}"},"createdAt":1790566019,"delay":null}	0	\N	1790566019	1790566019
21	default	{"uuid":"2580be8d-f74c-4257-bda7-ae9b20270272","displayName":"App\\\\Jobs\\\\ProcessResearchDocument","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":3,"maxExceptions":null,"failOnTimeout":false,"backoff":"10,30,60","timeout":null,"retryUntil":null,"data":{"commandName":"App\\\\Jobs\\\\ProcessResearchDocument","command":"O:32:\\"App\\\\Jobs\\\\ProcessResearchDocument\\":1:{s:10:\\"studFileId\\";i:47;}"},"createdAt":1790587372,"delay":null}	0	\N	1790587372	1790587372
22	default	{"uuid":"3233fb28-1b5e-4847-8f05-a047a2107ddd","displayName":"App\\\\Jobs\\\\ProcessResearchDocument","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":3,"maxExceptions":null,"failOnTimeout":false,"backoff":"10,30,60","timeout":null,"retryUntil":null,"data":{"commandName":"App\\\\Jobs\\\\ProcessResearchDocument","command":"O:32:\\"App\\\\Jobs\\\\ProcessResearchDocument\\":1:{s:10:\\"studFileId\\";i:48;}"},"createdAt":1790588174,"delay":null}	0	\N	1790588174	1790588174
23	default	{"uuid":"f9eac93f-4a2f-4e64-a594-610f67ffb435","displayName":"App\\\\Jobs\\\\ProcessResearchDocument","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":3,"maxExceptions":null,"failOnTimeout":false,"backoff":"10,30,60","timeout":null,"retryUntil":null,"data":{"commandName":"App\\\\Jobs\\\\ProcessResearchDocument","command":"O:32:\\"App\\\\Jobs\\\\ProcessResearchDocument\\":1:{s:10:\\"studFileId\\";i:49;}"},"createdAt":1790607526,"delay":null}	0	\N	1790607526	1790607526
24	default	{"uuid":"c7149a3c-0046-4e11-b35a-4c9ce41ecac9","displayName":"App\\\\Jobs\\\\ProcessResearchDocument","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":3,"maxExceptions":null,"failOnTimeout":false,"backoff":"10,30,60","timeout":null,"retryUntil":null,"data":{"commandName":"App\\\\Jobs\\\\ProcessResearchDocument","command":"O:32:\\"App\\\\Jobs\\\\ProcessResearchDocument\\":1:{s:10:\\"studFileId\\";i:50;}"},"createdAt":1790608131,"delay":null}	0	\N	1790608131	1790608131
25	default	{"uuid":"b6855d62-f451-4704-bf52-26e025c412c2","displayName":"App\\\\Jobs\\\\ProcessResearchDocument","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":3,"maxExceptions":null,"failOnTimeout":false,"backoff":"10,30,60","timeout":null,"retryUntil":null,"data":{"commandName":"App\\\\Jobs\\\\ProcessResearchDocument","command":"O:32:\\"App\\\\Jobs\\\\ProcessResearchDocument\\":1:{s:10:\\"studFileId\\";i:45;}"},"createdAt":1790612306,"delay":null}	0	\N	1790612306	1790612306
26	default	{"uuid":"f5c1a158-b5f1-4123-9aca-f0e299c35029","displayName":"App\\\\Jobs\\\\ProcessResearchDocument","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":3,"maxExceptions":null,"failOnTimeout":false,"backoff":"10,30,60","timeout":null,"retryUntil":null,"data":{"commandName":"App\\\\Jobs\\\\ProcessResearchDocument","command":"O:32:\\"App\\\\Jobs\\\\ProcessResearchDocument\\":1:{s:10:\\"studFileId\\";i:51;}"},"createdAt":1790615970,"delay":null}	0	\N	1790615970	1790615970
27	default	{"uuid":"8cc9fe0f-b2f5-461b-a566-b61cab3d2ae2","displayName":"App\\\\Jobs\\\\ProcessResearchDocument","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":3,"maxExceptions":null,"failOnTimeout":false,"backoff":"10,30,60","timeout":null,"retryUntil":null,"data":{"commandName":"App\\\\Jobs\\\\ProcessResearchDocument","command":"O:32:\\"App\\\\Jobs\\\\ProcessResearchDocument\\":1:{s:10:\\"studFileId\\";i:52;}"},"createdAt":1790658663,"delay":null}	0	\N	1790658663	1790658663
28	default	{"uuid":"4e0f084f-783a-4e0d-9337-241ae2369f5c","displayName":"App\\\\Jobs\\\\ProcessResearchDocument","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":3,"maxExceptions":null,"failOnTimeout":false,"backoff":"10,30,60","timeout":null,"retryUntil":null,"data":{"commandName":"App\\\\Jobs\\\\ProcessResearchDocument","command":"O:32:\\"App\\\\Jobs\\\\ProcessResearchDocument\\":1:{s:10:\\"studFileId\\";i:53;}"},"createdAt":1790660740,"delay":null}	0	\N	1790660740	1790660740
29	default	{"uuid":"fb8b5ba1-c43b-402e-b149-5d536a588316","displayName":"App\\\\Jobs\\\\ProcessResearchDocument","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":3,"maxExceptions":null,"failOnTimeout":false,"backoff":"10,30,60","timeout":null,"retryUntil":null,"data":{"commandName":"App\\\\Jobs\\\\ProcessResearchDocument","command":"O:32:\\"App\\\\Jobs\\\\ProcessResearchDocument\\":1:{s:10:\\"studFileId\\";i:54;}"},"createdAt":1790700601,"delay":null}	0	\N	1790700601	1790700601
30	default	{"uuid":"d5bf7e2a-fbbe-4b79-9b71-a228058cc9dc","displayName":"App\\\\Jobs\\\\ProcessResearchDocument","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":3,"maxExceptions":null,"failOnTimeout":false,"backoff":"10,30,60","timeout":null,"retryUntil":null,"data":{"commandName":"App\\\\Jobs\\\\ProcessResearchDocument","command":"O:32:\\"App\\\\Jobs\\\\ProcessResearchDocument\\":1:{s:10:\\"studFileId\\";i:55;}"},"createdAt":1790700818,"delay":null}	0	\N	1790700818	1790700818
31	default	{"uuid":"6d369cad-9263-456c-b9b0-0da158f0e2cc","displayName":"App\\\\Jobs\\\\ProcessResearchDocument","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":3,"maxExceptions":null,"failOnTimeout":false,"backoff":"10,30,60","timeout":null,"retryUntil":null,"data":{"commandName":"App\\\\Jobs\\\\ProcessResearchDocument","command":"O:32:\\"App\\\\Jobs\\\\ProcessResearchDocument\\":1:{s:10:\\"studFileId\\";i:56;}"},"createdAt":1790701738,"delay":null}	0	\N	1790701738	1790701738
32	default	{"uuid":"0c71987a-7908-4bea-a315-2fc4f272938d","displayName":"App\\\\Jobs\\\\ProcessResearchDocument","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":3,"maxExceptions":null,"failOnTimeout":false,"backoff":"10,30,60","timeout":null,"retryUntil":null,"data":{"commandName":"App\\\\Jobs\\\\ProcessResearchDocument","command":"O:32:\\"App\\\\Jobs\\\\ProcessResearchDocument\\":1:{s:10:\\"studFileId\\";i:57;}"},"createdAt":1790707045,"delay":null}	0	\N	1790707045	1790707045
33	default	{"uuid":"1f00287a-a591-4c45-9727-2491987dc0eb","displayName":"App\\\\Jobs\\\\ProcessResearchDocument","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":3,"maxExceptions":null,"failOnTimeout":false,"backoff":"10,30,60","timeout":null,"retryUntil":null,"data":{"commandName":"App\\\\Jobs\\\\ProcessResearchDocument","command":"O:32:\\"App\\\\Jobs\\\\ProcessResearchDocument\\":1:{s:10:\\"studFileId\\";i:58;}"},"createdAt":1790744391,"delay":null}	0	\N	1790744391	1790744391
34	default	{"uuid":"ce6565ac-8375-4078-9409-dff0d64488a3","displayName":"App\\\\Jobs\\\\ProcessResearchDocument","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":3,"maxExceptions":null,"failOnTimeout":false,"backoff":"10,30,60","timeout":null,"retryUntil":null,"data":{"commandName":"App\\\\Jobs\\\\ProcessResearchDocument","command":"O:32:\\"App\\\\Jobs\\\\ProcessResearchDocument\\":1:{s:10:\\"studFileId\\";i:1;}"},"createdAt":1790839511,"delay":null}	0	\N	1790839512	1790839512
35	default	{"uuid":"04265a1b-0b2e-4493-ad3f-7706dbe30dc2","displayName":"App\\\\Jobs\\\\ProcessResearchDocument","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":3,"maxExceptions":null,"failOnTimeout":false,"backoff":"10,30,60","timeout":null,"retryUntil":null,"data":{"commandName":"App\\\\Jobs\\\\ProcessResearchDocument","command":"O:32:\\"App\\\\Jobs\\\\ProcessResearchDocument\\":1:{s:10:\\"studFileId\\";i:2;}"},"createdAt":1790839789,"delay":null}	0	\N	1790839789	1790839789
36	default	{"uuid":"5f915660-8af6-435b-94fd-2886272e8340","displayName":"App\\\\Jobs\\\\ProcessResearchDocument","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":3,"maxExceptions":null,"failOnTimeout":false,"backoff":"10,30,60","timeout":null,"retryUntil":null,"data":{"commandName":"App\\\\Jobs\\\\ProcessResearchDocument","command":"O:32:\\"App\\\\Jobs\\\\ProcessResearchDocument\\":1:{s:10:\\"studFileId\\";i:3;}"},"createdAt":1790840524,"delay":null}	0	\N	1790840524	1790840524
37	default	{"uuid":"0f1b9883-e2a4-4055-8037-3dc800ce98cc","displayName":"App\\\\Jobs\\\\ProcessResearchDocument","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":3,"maxExceptions":null,"failOnTimeout":false,"backoff":"10,30,60","timeout":null,"retryUntil":null,"data":{"commandName":"App\\\\Jobs\\\\ProcessResearchDocument","command":"O:32:\\"App\\\\Jobs\\\\ProcessResearchDocument\\":1:{s:10:\\"studFileId\\";i:4;}"},"createdAt":1790840856,"delay":null}	0	\N	1790840856	1790840856
38	default	{"uuid":"45fc78a2-d78d-428a-89b5-d9a8701c4214","displayName":"App\\\\Jobs\\\\ProcessResearchDocument","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":3,"maxExceptions":null,"failOnTimeout":false,"backoff":"10,30,60","timeout":null,"retryUntil":null,"data":{"commandName":"App\\\\Jobs\\\\ProcessResearchDocument","command":"O:32:\\"App\\\\Jobs\\\\ProcessResearchDocument\\":1:{s:10:\\"studFileId\\";i:5;}"},"createdAt":1790865112,"delay":null}	0	\N	1790865113	1790865113
39	default	{"uuid":"4e315972-7aac-4933-922e-d1638eee0c26","displayName":"App\\\\Jobs\\\\ProcessResearchDocument","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":3,"maxExceptions":null,"failOnTimeout":false,"backoff":"10,30,60","timeout":null,"retryUntil":null,"data":{"commandName":"App\\\\Jobs\\\\ProcessResearchDocument","command":"O:32:\\"App\\\\Jobs\\\\ProcessResearchDocument\\":1:{s:10:\\"studFileId\\";i:1;}"},"createdAt":1791096067,"delay":null}	0	\N	1791096067	1791096067
40	default	{"uuid":"5b5c709f-e902-46d8-8633-1120d2b208e0","displayName":"App\\\\Jobs\\\\ProcessResearchDocument","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":3,"maxExceptions":null,"failOnTimeout":false,"backoff":"10,30,60","timeout":null,"retryUntil":null,"data":{"commandName":"App\\\\Jobs\\\\ProcessResearchDocument","command":"O:32:\\"App\\\\Jobs\\\\ProcessResearchDocument\\":1:{s:10:\\"studFileId\\";i:1;}"},"createdAt":1791096221,"delay":null}	0	\N	1791096221	1791096221
41	default	{"uuid":"c06c3576-b8b1-4fb7-91d8-e38adf2822ab","displayName":"App\\\\Jobs\\\\ProcessResearchDocument","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":3,"maxExceptions":null,"failOnTimeout":false,"backoff":"10,30,60","timeout":null,"retryUntil":null,"data":{"commandName":"App\\\\Jobs\\\\ProcessResearchDocument","command":"O:32:\\"App\\\\Jobs\\\\ProcessResearchDocument\\":1:{s:10:\\"studFileId\\";i:1;}"},"createdAt":1791101511,"delay":null}	0	\N	1791101511	1791101511
42	default	{"uuid":"bf544447-9f8e-47c2-ba29-e748a09b66a7","displayName":"App\\\\Jobs\\\\ProcessResearchDocument","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":3,"maxExceptions":null,"failOnTimeout":false,"backoff":"10,30,60","timeout":null,"retryUntil":null,"data":{"commandName":"App\\\\Jobs\\\\ProcessResearchDocument","command":"O:32:\\"App\\\\Jobs\\\\ProcessResearchDocument\\":1:{s:10:\\"studFileId\\";i:1;}"},"createdAt":1791101677,"delay":null}	0	\N	1791101677	1791101677
43	default	{"uuid":"15bd78b5-e745-41b2-b09c-6739860d8b13","displayName":"App\\\\Jobs\\\\ProcessResearchDocument","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":3,"maxExceptions":null,"failOnTimeout":false,"backoff":"10,30,60","timeout":null,"retryUntil":null,"data":{"commandName":"App\\\\Jobs\\\\ProcessResearchDocument","command":"O:32:\\"App\\\\Jobs\\\\ProcessResearchDocument\\":1:{s:10:\\"studFileId\\";i:1;}"},"createdAt":1791108663,"delay":null}	0	\N	1791108663	1791108663
44	default	{"uuid":"fff74958-c87b-4c10-b57e-6f7b0e6f0ab0","displayName":"App\\\\Jobs\\\\ProcessResearchDocument","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":3,"maxExceptions":null,"failOnTimeout":false,"backoff":"10,30,60","timeout":null,"retryUntil":null,"data":{"commandName":"App\\\\Jobs\\\\ProcessResearchDocument","command":"O:32:\\"App\\\\Jobs\\\\ProcessResearchDocument\\":1:{s:10:\\"studFileId\\";i:6;}"},"createdAt":1791109371,"delay":null}	0	\N	1791109371	1791109371
45	default	{"uuid":"dff77e03-ad4c-4d6e-b65d-9ee61ba85e67","displayName":"App\\\\Jobs\\\\ProcessResearchDocument","job":"Illuminate\\\\Queue\\\\CallQueuedHandler@call","maxTries":3,"maxExceptions":null,"failOnTimeout":false,"backoff":"10,30,60","timeout":null,"retryUntil":null,"data":{"commandName":"App\\\\Jobs\\\\ProcessResearchDocument","command":"O:32:\\"App\\\\Jobs\\\\ProcessResearchDocument\\":1:{s:10:\\"studFileId\\";i:7;}"},"createdAt":1791110959,"delay":null}	0	\N	1791110959	1791110959
\.


--
-- Data for Name: keywords; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.keywords (id, name, display_name, created_at, updated_at) FROM stdin;
10	document management system	document management system	2026-09-22 04:22:01	2026-09-22 04:22:01
11	electronic document management	electronic document management	2026-09-22 04:22:02	2026-09-22 04:22:02
12	file management system	file management system	2026-09-22 04:22:03	2026-09-22 04:22:03
13	electronic document management system	electronic document management system	2026-09-22 04:22:03	2026-09-22 04:22:03
14	web-based electronic document management system	web-based electronic document management system	2026-09-22 04:22:04	2026-09-22 04:22:04
15	web-based system	web-based system	2026-09-22 04:22:04	2026-09-22 04:22:04
16	web-based document management	web-based document management	2026-09-22 04:22:05	2026-09-22 04:22:05
17	artificial intelligence	Artificial Intelligence	2026-09-23 15:06:52	2026-09-23 15:06:52
18	learning efficiency	Learning Efficiency	2026-09-23 15:06:54	2026-09-23 15:06:54
19	academic performance	Academic Performance	2026-09-23 15:06:55	2026-09-23 15:06:55
20	student engagement	Student Engagement	2026-09-23 15:06:56	2026-09-23 15:06:56
21	ethical guidelines	Ethical Guidelines	2026-09-23 15:06:58	2026-09-23 15:06:58
22	critical thinking	Critical Thinking	2026-09-23 15:06:59	2026-09-23 15:06:59
23	data privacy	Data Privacy	2026-09-23 15:07:00	2026-09-23 15:07:00
24	educational environments	Educational Environments	2026-09-23 15:07:02	2026-09-23 15:07:02
25	empowering eight	empowering eight	2026-09-24 08:21:59	2026-09-24 08:21:59
26	repository	repository	2026-09-24 08:22:02	2026-09-24 08:22:02
27	research performance	research performance	2026-09-24 08:22:04	2026-09-24 08:22:04
28	students	students	2026-09-24 08:22:07	2026-09-24 08:22:07
29	information literacy	information literacy	2026-09-24 08:22:08	2026-09-24 08:22:08
30	web-based repository	web-based repository	2026-09-24 08:22:10	2026-09-24 08:22:10
31	student performance	student performance	2026-09-24 08:22:12	2026-09-24 08:22:12
32	covid-19 access	COVID-19 access	2026-09-24 08:22:13	2026-09-24 08:22:13
33	banten jaya university	Banten Jaya University	2026-09-24 08:22:15	2026-09-24 08:22:15
34	research methods	research methods	2026-09-24 08:22:18	2026-09-24 08:22:18
35	repository system	repository system	2026-09-24 08:22:20	2026-09-24 08:22:20
36	digital access	digital access	2026-09-24 08:22:22	2026-09-24 08:22:22
37	intelligent technology	intelligent technology	2026-09-24 09:29:24	2026-09-24 09:29:24
38	document management	document management	2026-09-24 09:29:27	2026-09-24 09:29:27
39	archives management	archives management	2026-09-24 09:29:30	2026-09-24 09:29:30
40	data security	data security	2026-09-24 09:29:33	2026-09-24 09:29:33
41	cloud computing	cloud computing	2026-09-24 09:29:35	2026-09-24 09:29:35
42	big data	big data	2026-09-24 09:29:38	2026-09-24 09:29:38
43	automated storage	automated storage	2026-09-24 09:29:40	2026-09-24 09:29:40
44	intelligent retrieval	intelligent retrieval	2026-09-24 09:29:41	2026-09-24 09:29:41
45	gps-based tracking system	GPS-Based Tracking System	2026-09-25 03:42:38	2026-09-25 03:42:38
46	passenger boat operations	Passenger Boat Operations	2026-09-25 03:42:39	2026-09-25 03:42:39
47	emergency alerts	Emergency Alerts	2026-09-25 03:42:42	2026-09-25 03:42:42
48	real-time monitoring	Real-Time Monitoring	2026-09-25 03:42:45	2026-09-25 03:42:45
49	database design	database design	2026-09-25 05:49:04	2026-09-25 05:49:04
50	development process	development process	2026-09-25 05:49:05	2026-09-25 05:49:05
51	promotes a safer work environment	Promotes a safer work environment	2026-09-25 05:49:07	2026-09-25 05:49:07
52	build passenger trust	build passenger trust	2026-09-25 05:49:09	2026-09-25 05:49:09
53	risk mitigation	Risk Mitigation	2026-09-25 11:07:04	2026-09-25 11:07:04
54	traders	traders	2026-09-25 11:07:07	2026-09-25 11:07:07
55	including accidents	including accidents	2026-09-25 11:07:09	2026-09-25 11:07:09
56	and transportation	and transportation	2026-09-25 11:34:02	2026-09-25 11:34:02
57	safety protocols	safety protocols	2026-09-25 11:58:59	2026-09-25 11:58:59
58	role-based access	role-based access	2026-09-25 11:59:01	2026-09-25 11:59:01
59	data visualization	data visualization	2026-09-25 11:59:03	2026-09-25 11:59:03
60	gps tracking	GPS tracking	2026-09-25 11:59:05	2026-09-25 11:59:05
61	supports csu’s goals in research and technology	Supports CSU’s goals in research and technology	2026-09-28 02:37:03	2026-09-28 02:37:03
62	user interface	user interface	2026-09-28 14:58:42	2026-09-28 14:58:42
63	community engagement	community engagement	2026-09-28 14:58:43	2026-09-28 14:58:43
64	stakeholder engagement	stakeholder engagement	2026-09-28 17:19:28	2026-09-28 17:19:28
65	maritime safety	Maritime Safety	2026-09-29 05:10:57	2026-09-29 05:10:57
66	metadata	metadata	2026-09-29 16:53:37	2026-09-29 16:53:37
67	covid-19 impact	COVID-19 impact	2026-09-29 17:08:56	2026-09-29 17:08:56
68	and user satisfaction	and user satisfaction	2026-09-29 18:37:23	2026-09-29 18:37:23
69	archives management.	Archives Management.	2026-10-01 07:42:01	2026-10-01 07:42:01
70	ai tools	AI Tools	2026-10-01 07:47:33	2026-10-01 07:47:33
71	technology-assisted learning	Technology-Assisted Learning	2026-10-01 07:47:34	2026-10-01 07:47:34
72	cherax quadricarinatus	Cherax quadricarinatus	2026-10-01 14:31:40	2026-10-01 14:31:40
73	growth performance	Growth Performance	2026-10-01 14:31:41	2026-10-01 14:31:41
74	diet	Diet	2026-10-01 14:31:43	2026-10-01 14:31:43
75	dsdsd	dsdsd	2026-10-04 10:22:48	2026-10-04 10:22:48
76	growth	growth	2026-10-04 10:49:16	2026-10-04 10:49:16
77	fish	fish	2026-10-04 10:49:16	2026-10-04 10:49:16
\.


--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.migrations (id, migration, batch) FROM stdin;
1	2026_07_12_045109_create_sessions_table	1
2	2026_07_12_045259_create_cache_table	1
3	2026_07_12_045942_create_users_table	1
4	2026_07_12_045957_create_password_reset_tokens_table	1
5	2026_07_12_050006_create_jobs_table	1
6	2026_07_12_050021_create_stud_file_table	1
7	2026_08_28_065859_add_requested_visibility_to_stud_file_table	2
8	2026_09_09_095915_create_sdgs_table	3
9	2026_09_09_102602_create_stud_file_sdg_table	4
10	2026_09_15_223530_add_email_and_account_status_to_users_table	5
11	2026_09_15_223531_add_status_visibility_views_to_stud_file_table	5
12	2026_09_15_230000_enable_pgvector_extension	6
13	2026_09_15_230001_add_research_metadata_to_stud_file_table	7
14	2026_09_15_230002_create_keywords_table	8
15	2026_09_15_230003_create_stud_file_author_table	9
16	2026_09_15_230004_create_research_chunks_table	10
17	2026_09_16_000000_add_content_hash_index_to_research_chunks_table	11
18	2026_09_16_000100_create_notifications_table	12
19	2026_09_16_000101_add_instructor_feedback_to_stud_file_table	13
20	2026_09_16_000102_add_author_name_to_stud_file_author_table	14
21	2026_09_18_000000_add_embedding_version_to_research_chunks_table	15
22	2026_09_18_000100_create_chat_conversations_table	16
23	2026_09_18_000101_create_chat_messages_table	16
24	2026_09_18_000102_create_ai_usage_logs_table	16
25	2026_09_18_000200_add_fulltext_to_research_chunks_table	17
26	2026_09_24_113104_create_research_feedback_table	18
27	2026_09_25_000000_add_cached_prompt_tokens_to_ai_usage_logs_table	19
28	2026_09_25_000100_add_excluded_from_history_to_chat_messages_table	19
29	2026_09_26_000000_add_external_sources_to_chat_messages_table	19
30	2026_09_27_000000_add_performance_indexes	20
31	2026_10_04_053122_add_resubmit_count_to_stud_file_table	21
32	2026_10_05_154415_create_search_logs_table	22
33	2026_10_06_132756_create_backups_table	23
\.


--
-- Data for Name: notifications; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.notifications (id, type, notifiable_type, notifiable_id, data, read_at, created_at, updated_at) FROM stdin;
4f6d1d08-1dfc-4f79-91d1-5dac225433f0	App\\Notifications\\ResearchReviewedByInstructor	App\\Models\\User	2	{"event":"research_reviewed_by_instructor","status":"approved","message":"Your adviser approved \\"The Effect of Artificial Intelligence Tool Usage on the Learning Efficiency of IT Students Complete 1\\" \\u2014 it's now with the admin for final review.","stud_file_id":35,"url":"http:\\/\\/127.0.0.1:8000\\/student?view=dashboard"}	\N	2026-09-23 15:47:45	2026-09-23 15:47:45
734342b9-2e62-4c22-81bc-734d2142362b	App\\Notifications\\ResearchSubmitted	App\\Models\\User	3	{"event":"research_submitted","message":"John Patrick Labinay submitted \\"Research Repository System\\" for your review.","stud_file_id":33,"url":"http:\\/\\/127.0.0.1:8000\\/instructor?view=dashboard"}	\N	2026-09-19 04:56:21	2026-09-19 04:56:21
d44cf2dd-ce12-4754-bc66-c0ee06da3918	App\\Notifications\\ResearchSubmitted	App\\Models\\User	3	{"event":"research_submitted","message":"John Patrick Labinay submitted \\"Web based Document Management System\\" for your review.","stud_file_id":34,"url":"http:\\/\\/127.0.0.1:8000\\/instructor?view=dashboard"}	\N	2026-09-22 04:22:09	2026-09-22 04:22:09
59a77363-e5a0-4d51-87da-f6e80cf81b85	App\\Notifications\\ResearchReviewedByInstructor	App\\Models\\User	2	{"event":"research_reviewed_by_instructor","status":"approved","message":"Your adviser approved \\"Web based Document Management System\\" \\u2014 it's now with the admin for final review.","stud_file_id":34,"url":"http:\\/\\/127.0.0.1:8000\\/student?view=dashboard"}	\N	2026-09-23 11:59:11	2026-09-23 11:59:11
d90473b1-cd6b-4ba4-ad24-568ab90d1c8a	App\\Notifications\\ResearchReviewedByAdmin	App\\Models\\User	2	{"event":"research_reviewed_by_admin","status":"approved","message":"Your research \\"Web based Document Management System\\" has been approved.","stud_file_id":34,"url":"http:\\/\\/127.0.0.1:8000\\/student?view=dashboard"}	\N	2026-09-23 12:01:28	2026-09-23 12:01:28
bd440bd0-f8d2-4fd0-9b1c-4f2a859cbd51	App\\Notifications\\ResearchReviewedByInstructor	App\\Models\\User	2	{"event":"research_reviewed_by_instructor","status":"approved","message":"Your adviser approved \\"Research Repository System\\" \\u2014 it's now with the admin for final review.","stud_file_id":33,"url":"http:\\/\\/127.0.0.1:8000\\/student?view=dashboard"}	\N	2026-09-23 12:08:38	2026-09-23 12:08:38
4ba08149-5c08-4313-a01e-e046f4098adb	App\\Notifications\\ResearchReviewedByAdmin	App\\Models\\User	2	{"event":"research_reviewed_by_admin","status":"approved","message":"Your research \\"Research Repository System\\" has been approved.","stud_file_id":33,"url":"http:\\/\\/127.0.0.1:8000\\/student?view=dashboard"}	\N	2026-09-23 12:30:19	2026-09-23 12:30:19
67cbb72a-4a95-4144-b60b-7ed3bdc3c08d	App\\Notifications\\ResearchSubmitted	App\\Models\\User	3	{"event":"research_submitted","message":"John Patrick Labinay submitted \\"The-Effect-of-Artificial-Intelligence-Tool-Usage-on-the-Learning-Efficiency-of-IT-Students-Complete-1\\" for your review.","stud_file_id":35,"url":"http:\\/\\/127.0.0.1:8000\\/instructor?view=dashboard"}	\N	2026-09-23 15:07:11	2026-09-23 15:07:11
c7d6584a-a45f-4b60-80e3-9b016170d6ca	App\\Notifications\\ResearchReviewedByInstructor	App\\Models\\User	2	{"event":"research_reviewed_by_instructor","status":"rejected","message":"Your adviser rejected \\"The-Effect-of-Artificial-Intelligence-Tool-Usage-on-the-Learning-Efficiency-of-IT-Students-Complete-1\\".","stud_file_id":35,"url":"http:\\/\\/127.0.0.1:8000\\/student?view=dashboard"}	\N	2026-09-23 15:36:42	2026-09-23 15:36:42
af976f7a-e3b6-4905-bf62-acf163d703d0	App\\Notifications\\ResearchSubmitted	App\\Models\\User	3	{"event":"research_submitted","message":"John Patrick Labinay submitted \\"The Effect of Artificial Intelligence Tool Usage on the Learning Efficiency of IT Students Complete 1\\" for your review.","stud_file_id":35,"url":"http:\\/\\/127.0.0.1:8000\\/instructor?view=dashboard"}	\N	2026-09-23 15:40:39	2026-09-23 15:40:39
8557ad38-88ea-48e2-a6a3-0c38a62acdb9	App\\Notifications\\ResearchReviewedByAdmin	App\\Models\\User	2	{"event":"research_reviewed_by_admin","status":"approved","message":"Your research \\"The Effect of Artificial Intelligence Tool Usage on the Learning Efficiency of IT Students Complete 1\\" has been approved.","stud_file_id":35,"url":"http:\\/\\/127.0.0.1:8000\\/student?view=dashboard"}	\N	2026-09-23 15:53:25	2026-09-23 15:53:25
717a3b1e-23aa-41d7-8f5f-2096462956e8	App\\Notifications\\ResearchFeedbackPosted	App\\Models\\User	2	{"event":"research_feedback_posted","message":"Your adviser left feedback on \\"The Effect of Artificial Intelligence Tool Usage on the Learning Efficiency of IT Students Complete 1\\": completed paper","stud_file_id":35,"url":"http:\\/\\/127.0.0.1:8000\\/student?view=dashboard"}	\N	2026-09-24 04:05:41	2026-09-24 04:05:41
9f9bcfc1-31ce-4594-b5e6-5872650426f5	App\\Notifications\\AccountCreated	App\\Models\\User	33	{"event":"account_created","message":"Welcome! Your account has been created.","url":"http:\\/\\/127.0.0.1:8000\\/instructor?view=dashboard"}	\N	2026-09-24 08:09:09	2026-09-24 08:09:09
b0233697-cbd3-4ed9-9846-9dcb56500f22	App\\Notifications\\ResearchSubmitted	App\\Models\\User	33	{"event":"research_submitted","message":"Marinette Peralta submitted \\"Empowering Eight Through Web Based Rep\\" for your review.","stud_file_id":36,"url":"http:\\/\\/127.0.0.1:8000\\/instructor?view=dashboard"}	\N	2026-09-24 08:22:39	2026-09-24 08:22:39
1facc0b7-94b0-4270-8e53-b6b7d54bcc0d	App\\Notifications\\ResearchSubmitted	App\\Models\\User	3	{"event":"research_submitted","message":"Marinette Peralta submitted \\"Document and Archive Management\\" for your review.","stud_file_id":37,"url":"http:\\/\\/127.0.0.1:8000\\/instructor?view=dashboard"}	\N	2026-09-24 09:29:59	2026-09-24 09:29:59
e801c252-fa15-4342-9881-114dac08ff00	App\\Notifications\\ResearchFeedbackPosted	App\\Models\\User	2	{"event":"research_feedback_posted","message":"Your adviser left feedback on \\"The Effect of Artificial Intelligence Tool Usage on the Learning Efficiency of IT Students Complete 1\\": Excellent work! The research topic is relevant and well-presented, and the paper follows a clear structure. The methodology is appropriate, and the discussion provides meaningful insights based on the study. Your hard work is reflected in the quality of the research. Keep it up!","stud_file_id":35,"url":"http:\\/\\/127.0.0.1:8000\\/student?view=dashboard"}	\N	2026-09-24 11:23:33	2026-09-24 11:23:33
ddfcda9b-ebef-41d5-973c-6ee44b0e739a	App\\Notifications\\ResearchFeedbackPosted	App\\Models\\User	2	{"event":"research_feedback_posted","message":"Your adviser left feedback on \\"The Effect of Artificial Intelligence Tool Usage on the Learning Efficiency of IT Students Complete 1\\": Excellent work! The research topic is relevant and well-presented, and the paper follows a clear structure. The methodology is appropriate, and the discussion provides meaningful insights based on the study. Your hard work is reflected in the quality of the research. Keep it up!","stud_file_id":35,"url":"http:\\/\\/127.0.0.1:8000\\/student?view=dashboard"}	\N	2026-09-24 11:33:20	2026-09-24 11:33:20
7475a65a-3eca-424f-879d-9a235ddc982f	App\\Notifications\\ResearchSubmitted	App\\Models\\User	3	{"event":"research_submitted","message":"Marinette Peralta submitted \\"Document and Archive Management\\" for your review.","stud_file_id":37,"url":"http:\\/\\/127.0.0.1:8000\\/instructor?view=dashboard"}	\N	2026-09-24 14:54:06	2026-09-24 14:54:06
d3c3b432-e9af-4cb8-8a9c-1f2aa5b0a31d	App\\Notifications\\ResearchReviewedByInstructor	App\\Models\\User	4	{"event":"research_reviewed_by_instructor","status":"approved","message":"Your adviser approved \\"Document and Archive Management\\" \\u2014 it's now with the admin for final review.","stud_file_id":37,"url":"http:\\/\\/127.0.0.1:8000\\/student?view=dashboard"}	2026-10-01 09:49:40	2026-09-24 09:33:55	2026-10-01 09:49:40
f69f13fd-8d65-4960-bf8a-40dd76326cd6	App\\Notifications\\ResearchSubmitted	App\\Models\\User	3	{"event":"research_submitted","message":"John Patrick Labinay submitted \\"thesis 4rt year\\" for your review.","stud_file_id":38,"url":"http:\\/\\/127.0.0.1:8000\\/instructor?view=dashboard"}	\N	2026-09-25 03:42:56	2026-09-25 03:42:56
de5d1a8e-cedc-4840-b32e-d7f764ea552e	App\\Notifications\\ResearchReviewedByInstructor	App\\Models\\User	2	{"event":"research_reviewed_by_instructor","status":"approved","message":"Your adviser approved \\"thesis 4rt year\\" \\u2014 it's now with the admin for final review.","stud_file_id":38,"url":"http:\\/\\/127.0.0.1:8000\\/student?view=dashboard"}	\N	2026-09-25 04:01:07	2026-09-25 04:01:07
9023a545-c846-45ec-ac1c-1bd9d0acebd8	App\\Notifications\\ResearchReviewedByAdmin	App\\Models\\User	2	{"event":"research_reviewed_by_admin","status":"approved","message":"Your research \\"thesis 4rt year\\" has been approved.","stud_file_id":38,"url":"http:\\/\\/127.0.0.1:8000\\/student?view=dashboard"}	\N	2026-09-25 04:03:36	2026-09-25 04:03:36
1398878d-1fec-4b90-a7d6-fbabc7f93a64	App\\Notifications\\ResearchSubmitted	App\\Models\\User	3	{"event":"research_submitted","message":"John Patrick Labinay submitted \\"thesissss\\" for your review.","stud_file_id":39,"url":"http:\\/\\/127.0.0.1:8123\\/instructor?view=dashboard"}	\N	2026-09-25 05:49:16	2026-09-25 05:49:16
a8276476-6655-4972-9149-b00f31f02ece	App\\Notifications\\ResearchSubmitted	App\\Models\\User	3	{"event":"research_submitted","message":"John Patrick Labinay submitted \\"new thesis paper\\" for your review.","stud_file_id":41,"url":"http:\\/\\/127.0.0.1:8123\\/instructor?view=dashboard"}	\N	2026-09-25 11:08:31	2026-09-25 11:08:31
c2e80c4c-11d2-41a3-aef6-034a2e982906	App\\Notifications\\ResearchReviewedByInstructor	App\\Models\\User	2	{"event":"research_reviewed_by_instructor","status":"approved","message":"Your adviser approved \\"new thesis paper\\" \\u2014 it's now with the admin for final review.","stud_file_id":41,"url":"http:\\/\\/127.0.0.1:8123\\/student?view=dashboard"}	\N	2026-09-25 11:13:58	2026-09-25 11:13:58
214e43de-a6d3-4105-b0cb-9b68512bb5cd	App\\Notifications\\ResearchReviewedByInstructor	App\\Models\\User	2	{"event":"research_reviewed_by_instructor","status":"rejected","message":"Your adviser rejected \\"new thesis paper\\".","stud_file_id":40,"url":"http:\\/\\/127.0.0.1:8123\\/student?view=dashboard"}	\N	2026-09-25 11:16:01	2026-09-25 11:16:01
d7a56267-fad7-4d89-bc09-b23917a7bad1	App\\Notifications\\ResearchReviewedByInstructor	App\\Models\\User	2	{"event":"research_reviewed_by_instructor","status":"rejected","message":"Your adviser rejected \\"thesissss\\".","stud_file_id":39,"url":"http:\\/\\/127.0.0.1:8123\\/student?view=dashboard"}	\N	2026-09-25 11:16:13	2026-09-25 11:16:13
257b0ec1-83f3-4041-947d-bddd457bc067	App\\Notifications\\ResearchSubmitted	App\\Models\\User	3	{"event":"research_submitted","message":"John Patrick Labinay submitted \\"GPS-Based Tracking System for Enhancing Risk Mitigation in Passenger Boat  Operations\\" for your review.","stud_file_id":42,"url":"http:\\/\\/127.0.0.1:8123\\/instructor?view=dashboard"}	\N	2026-09-25 11:34:15	2026-09-25 11:34:15
d05aa434-83bf-4175-8df3-50bdc770ffc5	App\\Notifications\\ResearchReviewedByInstructor	App\\Models\\User	2	{"event":"research_reviewed_by_instructor","status":"approved","message":"Your adviser approved \\"GPS-Based Tracking System for Enhancing Risk Mitigation in Passenger Boat  Operations\\" \\u2014 it's now with the admin for final review.","stud_file_id":42,"url":"http:\\/\\/127.0.0.1:8123\\/student?view=dashboard"}	\N	2026-09-25 11:38:56	2026-09-25 11:38:56
112d4fe4-7d20-4a89-a824-a53c2767c3c8	App\\Notifications\\ResearchReviewedByAdmin	App\\Models\\User	2	{"event":"research_reviewed_by_admin","status":"approved","message":"Your research \\"GPS-Based Tracking System for Enhancing Risk Mitigation in Passenger Boat  Operations\\" has been approved.","stud_file_id":42,"url":"http:\\/\\/127.0.0.1:8123\\/student?view=dashboard"}	\N	2026-09-25 11:42:29	2026-09-25 11:42:29
d937be8b-0296-45ac-98f5-ff15665f06f0	App\\Notifications\\ResearchSubmitted	App\\Models\\User	3	{"event":"research_submitted","message":"John Patrick Labinay submitted \\"GPS-Based Tracking System for Enhancing Risk Mitigation in Passenger Boat  Operations\\" for your review.","stud_file_id":43,"url":"http:\\/\\/127.0.0.1:8123\\/instructor?view=dashboard"}	\N	2026-09-25 11:59:15	2026-09-25 11:59:15
4741fce5-c36a-4621-a1ba-7cbff397ea76	App\\Notifications\\ResearchReviewedByInstructor	App\\Models\\User	2	{"event":"research_reviewed_by_instructor","status":"approved","message":"Your adviser approved \\"GPS-Based Tracking System for Enhancing Risk Mitigation in Passenger Boat  Operations\\" \\u2014 it's now with the admin for final review.","stud_file_id":43,"url":"http:\\/\\/127.0.0.1:8123\\/student?view=dashboard"}	\N	2026-09-25 12:05:06	2026-09-25 12:05:06
feb57fb1-f8f2-4682-a764-f9171d768767	App\\Notifications\\ResearchReviewedByAdmin	App\\Models\\User	2	{"event":"research_reviewed_by_admin","status":"approved","message":"Your research \\"GPS-Based Tracking System for Enhancing Risk Mitigation in Passenger Boat  Operations\\" has been approved.","stud_file_id":43,"url":"http:\\/\\/127.0.0.1:8123\\/student?view=dashboard"}	\N	2026-09-25 12:08:36	2026-09-25 12:08:36
d0e646f2-730c-48c3-9243-625486fe270f	App\\Notifications\\ResearchSubmitted	App\\Models\\User	3	{"event":"research_submitted","message":"Marinette Peralta submitted \\"Document and Archive Management\\" for your review.","stud_file_id":37,"url":"http:\\/\\/127.0.0.1:8123\\/instructor?view=dashboard"}	\N	2026-09-27 06:43:29	2026-09-27 06:43:29
86a1f220-e36a-4ec1-9f87-1402b39195b8	App\\Notifications\\ResearchSubmitted	App\\Models\\User	3	{"event":"research_submitted","message":"Marinette Peralta submitted \\"Document and Archive Management\\" for your review.","stud_file_id":37,"url":"http:\\/\\/127.0.0.1:8123\\/instructor?view=dashboard"}	\N	2026-09-27 07:28:01	2026-09-27 07:28:01
69e478b6-9f2e-417b-b947-7f77d71e39ce	App\\Notifications\\ResearchReviewedByInstructor	App\\Models\\User	4	{"event":"research_reviewed_by_instructor","status":"rejected","message":"Your adviser rejected \\"Document and Archive Management\\". Reason: [{\\"user_id\\":3,\\"feedback\\":\\"Excellent work! The research topic is relevant and well-presented, and the paper follows a clear structure. The methodology is appropriate, and the discussion provides meaningful insights based on the study. Your hard work is reflected in the quality of the research. Keep it up!\\",\\"created_at\\":\\"2026-09-24 11:53:52\\"}]","stud_file_id":37,"url":"http:\\/\\/127.0.0.1:8123\\/student?view=dashboard"}	2026-10-01 09:49:38	2026-09-27 06:52:25	2026-10-01 09:49:38
9b650342-839c-4d4c-b8f2-aae8abdb84d3	App\\Notifications\\ResearchReviewedByInstructor	App\\Models\\User	4	{"event":"research_reviewed_by_instructor","status":"rejected","message":"Your adviser rejected \\"Document and Archive Management\\". Reason: [{\\"user_id\\":3,\\"feedback\\":\\"Excellent work! The research topic is relevant and well-presented, and the paper follows a clear structure. The methodology is appropriate, and the discussion provides meaningful insights based on the study. Your hard work is reflected in the quality of the research. Keep it up!\\",\\"created_at\\":\\"2026-09-24 11:53:52\\"}]","stud_file_id":37,"url":"http:\\/\\/127.0.0.1:8123\\/student?view=dashboard"}	2026-10-01 09:49:38	2026-09-27 06:13:11	2026-10-01 09:49:38
3f1031e9-77d3-474f-8500-be56720fcf53	App\\Notifications\\ResearchSubmitted	App\\Models\\User	3	{"event":"research_submitted","message":"Marinette Peralta submitted \\"Document and Archive Management\\" for your review.","stud_file_id":37,"url":"http:\\/\\/127.0.0.1:8123\\/instructor?view=dashboard"}	\N	2026-09-27 09:24:45	2026-09-27 09:24:45
db4991dd-4e74-409d-a4a3-25ee7b0ae0a2	App\\Notifications\\ResearchSubmitted	App\\Models\\User	3	{"event":"research_submitted","message":"Marinette Peralta submitted \\"Document and Archive Management\\" for your review.","stud_file_id":37,"url":"http:\\/\\/127.0.0.1:8123\\/instructor?view=dashboard"}	\N	2026-09-28 00:41:09	2026-09-28 00:41:09
6d1c2751-56a2-4ab4-8b6d-e86513fdfb39	App\\Notifications\\ResearchSubmitted	App\\Models\\User	3	{"event":"research_submitted","message":"John Patrick Labinay submitted \\"Sample\\" for your review.","stud_file_id":44,"url":"http:\\/\\/127.0.0.1:8123\\/instructor?view=dashboard"}	\N	2026-09-28 02:37:11	2026-09-28 02:37:11
c54a9a21-7839-44e8-a6cd-a20f38be464b	App\\Notifications\\ResearchSubmitted	App\\Models\\User	3	{"event":"research_submitted","message":"John Patrick Labinay submitted \\"GPS\\" for your review.","stud_file_id":45,"url":"http:\\/\\/127.0.0.1:8123\\/instructor?view=dashboard"}	\N	2026-09-28 03:01:15	2026-09-28 03:01:15
01a4565c-0a9e-462d-8db4-d95329c240bd	App\\Notifications\\ResearchReviewedByInstructor	App\\Models\\User	2	{"event":"research_reviewed_by_instructor","status":"rejected","message":"Your adviser rejected \\"Sample\\".","stud_file_id":44,"url":"http:\\/\\/127.0.0.1:8123\\/student?view=dashboard"}	\N	2026-09-28 03:08:58	2026-09-28 03:08:58
46252fca-27fa-4a1b-8b4c-ac7881bb2776	App\\Notifications\\ResearchSubmitted	App\\Models\\User	3	{"event":"research_submitted","message":"John Patrick Labinay submitted \\"thesisss example\\" for your review.","stud_file_id":46,"url":"http:\\/\\/127.0.0.1:8123\\/instructor?view=dashboard"}	\N	2026-09-28 03:27:04	2026-09-28 03:27:04
6cb7f0e2-7998-4f0e-8919-6716e9802acf	App\\Notifications\\ResearchReviewedByInstructor	App\\Models\\User	2	{"event":"research_reviewed_by_instructor","status":"approved","message":"Your adviser approved \\"thesisss example\\" \\u2014 it's now with the admin for final review.","stud_file_id":46,"url":"http:\\/\\/127.0.0.1:8123\\/student?view=dashboard"}	\N	2026-09-28 03:29:48	2026-09-28 03:29:48
4387ddf0-1d9c-48b4-867d-feb5550d611a	App\\Notifications\\ResearchSubmitted	App\\Models\\User	3	{"event":"research_submitted","message":"John Patrick Labinay submitted \\"sapl\\" for your review.","stud_file_id":47,"url":"http:\\/\\/127.0.0.1:8123\\/instructor?view=dashboard"}	\N	2026-09-28 09:22:55	2026-09-28 09:22:55
524b124f-b878-40e3-9be1-3b903017a087	App\\Notifications\\ResearchSubmitted	App\\Models\\User	3	{"event":"research_submitted","message":"John Patrick Labinay submitted \\"ehhh\\" for your review.","stud_file_id":48,"url":"http:\\/\\/127.0.0.1:8123\\/instructor?view=dashboard"}	\N	2026-09-28 09:36:16	2026-09-28 09:36:16
95b17cc0-e32c-4526-b8b8-5cff322963c3	App\\Notifications\\ResearchSubmitted	App\\Models\\User	3	{"event":"research_submitted","message":"John Patrick Labinay submitted \\"sx\\" for your review.","stud_file_id":49,"url":"http:\\/\\/127.0.0.1:8123\\/instructor?view=dashboard"}	\N	2026-09-28 14:58:49	2026-09-28 14:58:49
3ff05035-09f1-47eb-9f44-4e85b01777b3	App\\Notifications\\ResearchSubmitted	App\\Models\\User	33	{"event":"research_submitted","message":"John Patrick Labinay submitted \\"sx\\" for your review.","stud_file_id":49,"url":"http:\\/\\/127.0.0.1:8123\\/instructor?view=dashboard"}	\N	2026-09-28 14:58:51	2026-09-28 14:58:51
163d75a0-05f1-4f40-b8ba-93e35bb842f5	App\\Notifications\\ResearchSubmitted	App\\Models\\User	3	{"event":"research_submitted","message":"John Patrick Labinay submitted \\"okoko\\" for your review.","stud_file_id":50,"url":"http:\\/\\/127.0.0.1:8123\\/instructor?view=dashboard"}	\N	2026-09-28 15:08:54	2026-09-28 15:08:54
07d7ba9c-590a-4e29-b9f4-14282794dc6c	App\\Notifications\\ResearchSubmitted	App\\Models\\User	33	{"event":"research_submitted","message":"John Patrick Labinay submitted \\"okoko\\" for your review.","stud_file_id":50,"url":"http:\\/\\/127.0.0.1:8123\\/instructor?view=dashboard"}	\N	2026-09-28 15:08:56	2026-09-28 15:08:56
2b49a22d-506c-4e7c-9b27-ed39e342a892	App\\Notifications\\ResearchReviewedByAdmin	App\\Models\\User	2	{"event":"research_reviewed_by_admin","status":"approved","message":"Your research \\"thesisss example\\" has been approved.","stud_file_id":46,"url":"http:\\/\\/127.0.0.1:8123\\/student?view=dashboard"}	\N	2026-09-28 15:57:30	2026-09-28 15:57:30
9856134a-f876-4845-ba88-d8321e6c0f1e	App\\Notifications\\ResearchReviewedByInstructor	App\\Models\\User	2	{"event":"research_reviewed_by_instructor","status":"approved","message":"Your adviser approved \\"GPS\\" \\u2014 it's now with the admin for final review.","stud_file_id":45,"url":"http:\\/\\/127.0.0.1:8123\\/student?view=dashboard"}	\N	2026-09-28 15:58:39	2026-09-28 15:58:39
c44f4b42-cf6d-4463-b5ef-9fb3b65d6985	App\\Notifications\\ResearchReviewedByInstructor	App\\Models\\User	2	{"event":"research_reviewed_by_instructor","status":"approved","message":"Your adviser approved \\"ehhh\\" \\u2014 it's now with the admin for final review.","stud_file_id":48,"url":"http:\\/\\/127.0.0.1:8123\\/student?view=dashboard"}	\N	2026-09-28 15:59:31	2026-09-28 15:59:31
00a6d140-9bb5-464a-ac2f-7c8cf7a8594f	App\\Notifications\\ResearchReviewedByInstructor	App\\Models\\User	2	{"event":"research_reviewed_by_instructor","status":"approved","message":"Your adviser approved \\"sx\\" \\u2014 it's now with the admin for final review.","stud_file_id":49,"url":"http:\\/\\/127.0.0.1:8123\\/student?view=dashboard"}	\N	2026-09-28 15:59:36	2026-09-28 15:59:36
93d71f49-6877-46fd-ab33-de01991dcdea	App\\Notifications\\ResearchReviewedByInstructor	App\\Models\\User	2	{"event":"research_reviewed_by_instructor","status":"rejected","message":"Your adviser rejected \\"sapl\\".","stud_file_id":47,"url":"http:\\/\\/127.0.0.1:8123\\/student?view=dashboard"}	\N	2026-09-28 16:00:57	2026-09-28 16:00:57
1ecce78e-45fe-4ba3-bdee-5a2e6d966dfc	App\\Notifications\\ResearchReviewedByAdmin	App\\Models\\User	2	{"event":"research_reviewed_by_admin","status":"rejected","message":"Your research \\"sx\\" has been rejected.","stud_file_id":49,"url":"http:\\/\\/127.0.0.1:8123\\/student?view=dashboard"}	\N	2026-09-28 16:02:47	2026-09-28 16:02:47
8d08750b-578c-45d1-b50b-e2a8760a7f5b	App\\Notifications\\ResearchReviewedByAdmin	App\\Models\\User	2	{"event":"research_reviewed_by_admin","status":"rejected","message":"Your research \\"ehhh\\" has been rejected.","stud_file_id":48,"url":"http:\\/\\/127.0.0.1:8123\\/student?view=dashboard"}	\N	2026-09-28 16:06:41	2026-09-28 16:06:41
58eca037-0a70-4aad-ad39-1e124ff00d18	App\\Notifications\\ResearchReviewedByInstructor	App\\Models\\User	4	{"event":"research_reviewed_by_instructor","status":"rejected","message":"Your adviser rejected \\"Document and Archive Management\\". Reason: [{\\"user_id\\":3,\\"feedback\\":\\"Excellent work! The research topic is relevant and well-presented, and the paper follows a clear structure. The methodology is appropriate, and the discussion provides meaningful insights based on the study. Your hard work is reflected in the quality of the research. Keep it up!\\",\\"created_at\\":\\"2026-09-24 11:53:52\\"}]","stud_file_id":37,"url":"http:\\/\\/127.0.0.1:8123\\/student?view=dashboard"}	2026-10-01 09:49:37	2026-09-27 09:27:06	2026-10-01 09:49:37
2cf04fcd-a9b9-42ea-ad24-2b922c2f996f	App\\Notifications\\ResearchReviewedByAdmin	App\\Models\\User	2	{"event":"research_reviewed_by_admin","status":"rejected","message":"Your research \\"GPS\\" has been rejected.","stud_file_id":45,"url":"http:\\/\\/127.0.0.1:8123\\/student?view=dashboard"}	\N	2026-09-28 16:10:31	2026-09-28 16:10:31
1e7ac611-a79d-4c01-8ecb-4ec53cbfa2db	App\\Notifications\\ResearchSubmitted	App\\Models\\User	3	{"event":"research_submitted","message":"John Patrick Labinay submitted \\"GPS\\" for your review.","stud_file_id":45,"url":"http:\\/\\/127.0.0.1:8123\\/instructor?view=dashboard"}	\N	2026-09-28 16:18:28	2026-09-28 16:18:28
5c7cfdc9-3c48-40a8-bd47-52c93a99148a	App\\Notifications\\ResearchReviewedByInstructor	App\\Models\\User	2	{"event":"research_reviewed_by_instructor","status":"approved","message":"Your adviser approved \\"GPS\\" \\u2014 it's now with the admin for final review.","stud_file_id":45,"url":"http:\\/\\/127.0.0.1:8123\\/student?view=dashboard"}	\N	2026-09-28 16:19:33	2026-09-28 16:19:33
bbf3e6f9-6897-44c5-93d6-5a4c3270f1f8	App\\Notifications\\ResearchReviewedByInstructor	App\\Models\\User	2	{"event":"research_reviewed_by_instructor","status":"approved","message":"Your adviser approved \\"okoko\\" \\u2014 it's now with the admin for final review.","stud_file_id":50,"url":"http:\\/\\/127.0.0.1:8123\\/student?view=dashboard"}	\N	2026-09-28 16:19:38	2026-09-28 16:19:38
bffcaeeb-c544-41d9-8097-287ed4918dc3	App\\Notifications\\ResearchReviewedByAdmin	App\\Models\\User	2	{"event":"research_reviewed_by_admin","status":"rejected","message":"Your research \\"GPS\\" has been rejected.","stud_file_id":45,"url":"http:\\/\\/127.0.0.1:8123\\/student?view=dashboard"}	\N	2026-09-28 16:21:10	2026-09-28 16:21:10
271bc540-b705-4c00-99da-eba9d257e302	App\\Notifications\\ResearchReviewedByAdmin	App\\Models\\User	2	{"event":"research_reviewed_by_admin","status":"rejected","message":"Your research \\"okoko\\" has been rejected.","stud_file_id":50,"url":"http:\\/\\/127.0.0.1:8123\\/student?view=dashboard"}	\N	2026-09-28 16:30:13	2026-09-28 16:30:13
ccf54b23-5ec9-4911-80dd-5281c5e6a6a7	App\\Notifications\\ResearchSubmitted	App\\Models\\User	3	{"event":"research_submitted","message":"John Patrick Labinay submitted \\"kokong\\" for your review.","stud_file_id":51,"url":"http:\\/\\/127.0.0.1:8123\\/instructor?view=dashboard"}	\N	2026-09-28 17:19:32	2026-09-28 17:19:32
988a6c37-eca4-4ee3-8967-8f08a51ef48c	App\\Notifications\\ResearchSubmitted	App\\Models\\User	33	{"event":"research_submitted","message":"John Patrick Labinay submitted \\"kokong\\" for your review.","stud_file_id":51,"url":"http:\\/\\/127.0.0.1:8123\\/instructor?view=dashboard"}	\N	2026-09-28 17:19:34	2026-09-28 17:19:34
534cbb2f-7426-4293-97ca-a64920eaf6ff	App\\Notifications\\ResearchReviewedByInstructor	App\\Models\\User	2	{"event":"research_reviewed_by_instructor","status":"approved","message":"Your adviser approved \\"kokong\\" \\u2014 it's now with the admin for final review.","stud_file_id":51,"url":"http:\\/\\/127.0.0.1:8123\\/student?view=dashboard"}	\N	2026-09-28 17:20:28	2026-09-28 17:20:28
2128f871-6ac2-4d3f-85d3-4a62f8cf2801	App\\Notifications\\ResearchReviewedByAdmin	App\\Models\\User	2	{"event":"research_reviewed_by_admin","status":"approved","message":"Your research \\"kokong\\" has been approved.","stud_file_id":51,"url":"http:\\/\\/127.0.0.1:8123\\/student?view=dashboard"}	\N	2026-09-28 17:21:42	2026-09-28 17:21:42
55fbb7b1-4520-4e05-acf5-328390efa7cb	App\\Notifications\\ResearchSubmitted	App\\Models\\User	3	{"event":"research_submitted","message":"John Patrick Labinay submitted \\"thesis\\" for your review.","stud_file_id":52,"url":"http:\\/\\/127.0.0.1:8123\\/instructor?view=dashboard"}	\N	2026-09-29 05:11:09	2026-09-29 05:11:09
ff1afbc5-2bcd-4f9e-a250-2f45f585cda6	App\\Notifications\\ResearchSubmitted	App\\Models\\User	33	{"event":"research_submitted","message":"John Patrick Labinay submitted \\"thesis\\" for your review.","stud_file_id":52,"url":"http:\\/\\/127.0.0.1:8123\\/instructor?view=dashboard"}	\N	2026-09-29 05:11:15	2026-09-29 05:11:15
2aab8716-9013-4d09-bfc6-37c401abd905	App\\Notifications\\ResearchReviewedByInstructor	App\\Models\\User	2	{"event":"research_reviewed_by_instructor","status":"approved","message":"Your adviser approved \\"thesis\\" \\u2014 it's now with the admin for final review.","stud_file_id":52,"url":"http:\\/\\/127.0.0.1:8123\\/student?view=dashboard"}	\N	2026-09-29 05:14:55	2026-09-29 05:14:55
aad1415e-5479-4e56-b9b1-979d8b5a7205	App\\Notifications\\ResearchSubmitted	App\\Models\\User	3	{"event":"research_submitted","message":"John Patrick Labinay submitted \\"gpsss\\" for your review.","stud_file_id":53,"url":"http:\\/\\/127.0.0.1:8123\\/instructor?view=dashboard"}	\N	2026-09-29 05:45:43	2026-09-29 05:45:43
1751e7a5-f739-4c42-8194-7fba86870684	App\\Notifications\\ResearchSubmitted	App\\Models\\User	33	{"event":"research_submitted","message":"John Patrick Labinay submitted \\"gpsss\\" for your review.","stud_file_id":53,"url":"http:\\/\\/127.0.0.1:8123\\/instructor?view=dashboard"}	\N	2026-09-29 05:45:48	2026-09-29 05:45:48
3100fc89-4112-423c-9af4-49dbbe4d2f62	App\\Notifications\\ResearchReviewedByAdmin	App\\Models\\User	2	{"event":"research_reviewed_by_admin","status":"approved","message":"Your research \\"thesis\\" has been approved.","stud_file_id":52,"url":"http:\\/\\/127.0.0.1:8123\\/student?view=dashboard"}	\N	2026-09-29 06:04:34	2026-09-29 06:04:34
3401bfc3-b9c3-45be-914d-c382d67af514	App\\Notifications\\ResearchSubmitted	App\\Models\\User	33	{"event":"research_submitted","message":"John Patrick Labinay submitted \\"public dpat\\" for your review.","stud_file_id":54,"url":"http:\\/\\/127.0.0.1:8123\\/instructor?view=dashboard"}	\N	2026-09-29 16:50:03	2026-09-29 16:50:03
4038a2de-7310-4ee3-976e-718c4dfa481f	App\\Notifications\\ResearchSubmitted	App\\Models\\User	3	{"event":"research_submitted","message":"John Patrick Labinay submitted \\"public dapat atuyen\\" for your review.","stud_file_id":55,"url":"http:\\/\\/127.0.0.1:8123\\/instructor?view=dashboard"}	\N	2026-09-29 16:53:40	2026-09-29 16:53:40
89af41f4-7e08-4d2d-b833-2bb286b55066	App\\Notifications\\ResearchReviewedByInstructor	App\\Models\\User	2	{"event":"research_reviewed_by_instructor","status":"approved","message":"Your adviser approved \\"public dapat atuyen\\" \\u2014 it's now with the admin for final review.","stud_file_id":55,"url":"http:\\/\\/127.0.0.1:8123\\/student?view=dashboard"}	\N	2026-09-29 16:54:35	2026-09-29 16:54:35
ead9d3d9-99d0-4e1e-b52e-0e90395adf67	App\\Notifications\\ResearchReviewedByAdmin	App\\Models\\User	2	{"event":"research_reviewed_by_admin","status":"approved","message":"Your research \\"public dapat atuyen\\" has been approved.","stud_file_id":55,"url":"http:\\/\\/127.0.0.1:8123\\/student?view=dashboard"}	\N	2026-09-29 16:55:33	2026-09-29 16:55:33
a7ed1e32-a8aa-4c25-bcaf-f80e61fcdbe4	App\\Notifications\\ResearchSubmitted	App\\Models\\User	3	{"event":"research_submitted","message":"John Patrick Labinay submitted \\"Empowering_Eight_Through_a_Web_Based_Rep_compressed\\" for your review.","stud_file_id":56,"url":"http:\\/\\/127.0.0.1:8123\\/instructor?view=dashboard"}	\N	2026-09-29 17:09:00	2026-09-29 17:09:00
c0a74c5c-8802-4fc8-b2f2-6c05d21f3e2c	App\\Notifications\\ResearchReviewedByInstructor	App\\Models\\User	2	{"event":"research_reviewed_by_instructor","status":"approved","message":"Your adviser approved \\"Empowering_Eight_Through_a_Web_Based_Rep_compressed\\" \\u2014 it's now with the admin for final review.","stud_file_id":56,"url":"http:\\/\\/127.0.0.1:8123\\/student?view=dashboard"}	\N	2026-09-29 17:10:13	2026-09-29 17:10:13
98e07236-0e4f-4675-b0f9-93a24ea7d0c2	App\\Notifications\\ResearchReviewedByAdmin	App\\Models\\User	2	{"event":"research_reviewed_by_admin","status":"approved","message":"Your research \\"Empowering_Eight_Through_a_Web_Based_Rep_compressed\\" has been approved.","stud_file_id":56,"url":"http:\\/\\/127.0.0.1:8123\\/student?view=dashboard"}	\N	2026-09-29 17:11:06	2026-09-29 17:11:06
dd658a9b-be8b-4da1-b185-e5dfea0ea47b	App\\Notifications\\ResearchSubmitted	App\\Models\\User	3	{"event":"research_submitted","message":"Marinette Peralta submitted \\"GPS-Based Tracking System for Enhancing Risk Mitigation in Passenger Boat\\" for your review.","stud_file_id":57,"url":"http:\\/\\/127.0.0.1:8123\\/instructor?view=dashboard"}	\N	2026-09-29 18:37:27	2026-09-29 18:37:27
a9b0de3e-e79b-433e-ab96-59457a447158	App\\Notifications\\ResearchSubmitted	App\\Models\\User	33	{"event":"research_submitted","message":"Marinette Peralta submitted \\"GPS-Based Tracking System for Enhancing Risk Mitigation in Passenger Boat\\" for your review.","stud_file_id":57,"url":"http:\\/\\/127.0.0.1:8123\\/instructor?view=dashboard"}	\N	2026-09-29 18:37:29	2026-09-29 18:37:29
a93bb864-c4df-4973-84ee-3cfc2dbae886	App\\Notifications\\ResearchSubmitted	App\\Models\\User	3	{"event":"research_submitted","message":"John Patrick Labinay submitted \\"thesis example\\" for your review.","stud_file_id":58,"url":"http:\\/\\/127.0.0.1:8123\\/instructor?view=dashboard"}	\N	2026-09-30 04:59:52	2026-09-30 04:59:52
b8b700b1-acec-4071-b463-2f88b3ffce66	App\\Notifications\\ResearchSubmitted	App\\Models\\User	33	{"event":"research_submitted","message":"John Patrick Labinay submitted \\"thesis example\\" for your review.","stud_file_id":58,"url":"http:\\/\\/127.0.0.1:8123\\/instructor?view=dashboard"}	\N	2026-09-30 04:59:54	2026-09-30 04:59:54
fe6d7ba4-234e-4b75-852c-3183eda6aa4a	App\\Notifications\\ResearchSubmitted	App\\Models\\User	3	{"event":"research_submitted","message":"John Patrick Labinay submitted \\"GPS-Based Tracking System for Enhancing Risk Mitigation in Passenger Boat\\" for your review.","stud_file_id":1,"url":"http:\\/\\/127.0.0.1:8123\\/instructor?view=dashboard"}	\N	2026-10-01 07:25:16	2026-10-01 07:25:16
c8d84d47-b946-4c41-b6a0-aee47cf649c1	App\\Notifications\\ResearchSubmitted	App\\Models\\User	33	{"event":"research_submitted","message":"John Patrick Labinay submitted \\"GPS-Based Tracking System for Enhancing Risk Mitigation in Passenger Boat\\" for your review.","stud_file_id":1,"url":"http:\\/\\/127.0.0.1:8123\\/instructor?view=dashboard"}	\N	2026-10-01 07:25:20	2026-10-01 07:25:20
81730f9e-7a63-4745-bdd7-754384c35364	App\\Notifications\\ResearchSubmitted	App\\Models\\User	3	{"event":"research_submitted","message":"John Patrick Labinay submitted \\"BugueyaKNOWS A Smart Food and Cultural Discovery Platform for Buguey\\" for your review.","stud_file_id":2,"url":"http:\\/\\/127.0.0.1:8123\\/instructor?view=dashboard"}	\N	2026-10-01 07:29:51	2026-10-01 07:29:51
ad4dd709-c906-4b43-b468-a32b8eb26d70	App\\Notifications\\ResearchSubmitted	App\\Models\\User	3	{"event":"research_submitted","message":"Marinette Peralta submitted \\"Research on the Application of Intelligent Technology in Document and Archives Management\\" for your review.","stud_file_id":3,"url":"http:\\/\\/127.0.0.1:8123\\/instructor?view=dashboard"}	\N	2026-10-01 07:42:07	2026-10-01 07:42:07
69d03be8-b18f-4683-bb48-782d82a93a69	App\\Notifications\\ResearchSubmitted	App\\Models\\User	33	{"event":"research_submitted","message":"Marinette Peralta submitted \\"Research on the Application of Intelligent Technology in Document and Archives Management\\" for your review.","stud_file_id":3,"url":"http:\\/\\/127.0.0.1:8123\\/instructor?view=dashboard"}	\N	2026-10-01 07:42:10	2026-10-01 07:42:10
6fd67134-ee5e-4b38-83ad-d5d3c85ce3d5	App\\Notifications\\ResearchSubmitted	App\\Models\\User	3	{"event":"research_submitted","message":"Marinette Peralta submitted \\"The Effect of Artificial Intelligence Tool Usage on the Learning Efficiency of IT Students\\" for your review.","stud_file_id":4,"url":"http:\\/\\/127.0.0.1:8123\\/instructor?view=dashboard"}	\N	2026-10-01 07:47:38	2026-10-01 07:47:38
8fd7be1f-627a-4f6e-8860-e561cb3be0e3	App\\Notifications\\ResearchSubmitted	App\\Models\\User	33	{"event":"research_submitted","message":"Marinette Peralta submitted \\"The Effect of Artificial Intelligence Tool Usage on the Learning Efficiency of IT Students\\" for your review.","stud_file_id":4,"url":"http:\\/\\/127.0.0.1:8123\\/instructor?view=dashboard"}	\N	2026-10-01 07:47:40	2026-10-01 07:47:40
e46b5428-dbc0-4021-b776-d4713dac182d	App\\Notifications\\AccountCreated	App\\Models\\User	39	{"event":"account_created","message":"Welcome! Your account has been created.","url":"http:\\/\\/127.0.0.1:8123\\/instructor?view=dashboard"}	\N	2026-10-01 08:00:00	2026-10-01 08:00:00
c2594625-16a7-4698-946f-66797e433800	App\\Notifications\\AccountCreated	App\\Models\\User	40	{"event":"account_created","message":"Welcome! Your account has been created.","url":"http:\\/\\/127.0.0.1:8123\\/instructor?view=dashboard"}	\N	2026-10-01 08:05:51	2026-10-01 08:05:51
7c0e3622-cf6d-4008-a8f5-04e62daf94e1	App\\Notifications\\AccountCreated	App\\Models\\User	41	{"event":"account_created","message":"Welcome! Your account has been created.","url":"http:\\/\\/127.0.0.1:8123\\/instructor?view=dashboard"}	\N	2026-10-01 08:42:07	2026-10-01 08:42:07
11bcd21a-1e14-424b-b780-92319368bcec	App\\Notifications\\AccountCreated	App\\Models\\User	42	{"event":"account_created","message":"Welcome! Your account has been created.","url":"http:\\/\\/127.0.0.1:8123\\/instructor?view=dashboard"}	\N	2026-10-01 08:48:02	2026-10-01 08:48:02
d88ccf57-20be-458f-89ce-c1926e3c72b3	App\\Notifications\\ResearchReviewedByInstructor	App\\Models\\User	2	{"event":"research_reviewed_by_instructor","status":"approved","message":"Your adviser approved \\"BugueyaKNOWS A Smart Food and Cultural Discovery Platform for Buguey\\" \\u2014 it's now with the admin for final review.","stud_file_id":2,"url":"http:\\/\\/127.0.0.1:8123\\/student?view=dashboard"}	\N	2026-10-01 08:52:40	2026-10-01 08:52:40
ceaea5bb-19ac-43d2-9c8e-1c99a5483d20	App\\Notifications\\ResearchReviewedByAdmin	App\\Models\\User	2	{"event":"research_reviewed_by_admin","status":"approved","message":"Your research \\"BugueyaKNOWS A Smart Food and Cultural Discovery Platform for Buguey\\" has been approved.","stud_file_id":2,"url":"http:\\/\\/127.0.0.1:8123\\/student?view=dashboard"}	\N	2026-10-01 08:56:22	2026-10-01 08:56:22
c4ac9b6d-6440-40c3-8eac-21dac2aa8c1d	App\\Notifications\\ResearchReviewedByAdmin	App\\Models\\User	4	{"event":"research_reviewed_by_admin","status":"approved","message":"Your research \\"The Effect of Artificial Intelligence Tool Usage on the Learning Efficiency of IT Students\\" has been approved.","stud_file_id":4,"url":"http:\\/\\/127.0.0.1:8123\\/student?view=dashboard"}	2026-10-01 09:49:35	2026-10-01 08:55:55	2026-10-01 09:49:35
5d958210-a1ee-4c58-accf-81c0063ff9b5	App\\Notifications\\ResearchReviewedByInstructor	App\\Models\\User	4	{"event":"research_reviewed_by_instructor","status":"approved","message":"Your adviser approved \\"The Effect of Artificial Intelligence Tool Usage on the Learning Efficiency of IT Students\\" \\u2014 it's now with the admin for final review.","stud_file_id":4,"url":"http:\\/\\/127.0.0.1:8123\\/student?view=dashboard"}	2026-10-01 09:49:35	2026-10-01 08:52:56	2026-10-01 09:49:35
1eda2585-6fb7-484f-be13-f30d79319e4c	App\\Notifications\\ResearchReviewedByAdmin	App\\Models\\User	4	{"event":"research_reviewed_by_admin","status":"approved","message":"Your research \\"GPS-Based Tracking System for Enhancing Risk Mitigation in Passenger Boat\\" has been approved.","stud_file_id":57,"url":"http:\\/\\/127.0.0.1:8123\\/student?view=dashboard"}	2026-10-01 09:49:36	2026-09-29 18:40:01	2026-10-01 09:49:36
a6026a22-796d-4bfd-ae9f-ad08713cb387	App\\Notifications\\ResearchReviewedByInstructor	App\\Models\\User	4	{"event":"research_reviewed_by_instructor","status":"approved","message":"Your adviser approved \\"GPS-Based Tracking System for Enhancing Risk Mitigation in Passenger Boat\\" \\u2014 it's now with the admin for final review.","stud_file_id":57,"url":"http:\\/\\/127.0.0.1:8123\\/student?view=dashboard"}	2026-10-01 09:49:36	2026-09-29 18:39:02	2026-10-01 09:49:36
ef02d5a9-94b4-48e0-9427-43d69f4917b5	App\\Notifications\\ResearchReviewedByInstructor	App\\Models\\User	4	{"event":"research_reviewed_by_instructor","status":"rejected","message":"Your adviser rejected \\"Document and Archive Management\\". Reason: [{\\"user_id\\":3,\\"feedback\\":\\"Excellent work! The research topic is relevant and well-presented, and the paper follows a clear structure. The methodology is appropriate, and the discussion provides meaningful insights based on the study. Your hard work is reflected in the quality of the research. Keep it up!\\",\\"created_at\\":\\"2026-09-24 11:53:52\\"}]","stud_file_id":37,"url":"http:\\/\\/127.0.0.1:8123\\/student?view=dashboard"}	2026-10-01 09:49:37	2026-09-28 00:44:35	2026-10-01 09:49:37
661f0077-b11a-488e-95c0-df0686a01b7d	App\\Notifications\\ResearchReviewedByInstructor	App\\Models\\User	4	{"event":"research_reviewed_by_instructor","status":"rejected","message":"Your adviser rejected \\"Document and Archive Management\\". Reason: [{\\"user_id\\":3,\\"feedback\\":\\"Excellent work! The research topic is relevant and well-presented, and the paper follows a clear structure. The methodology is appropriate, and the discussion provides meaningful insights based on the study. Your hard work is reflected in the quality of the research. Keep it up!\\",\\"created_at\\":\\"2026-09-24 11:53:52\\"}]","stud_file_id":37,"url":"http:\\/\\/127.0.0.1:8123\\/student?view=dashboard"}	2026-10-01 09:49:37	2026-09-27 07:33:22	2026-10-01 09:49:37
b9653461-bbc9-4660-a4ad-f44ccec493d6	App\\Notifications\\ResearchReviewedByAdmin	App\\Models\\User	4	{"event":"research_reviewed_by_admin","status":"rejected","message":"Your research \\"Document and Archive Management\\" has been rejected.","stud_file_id":37,"url":"http:\\/\\/127.0.0.1:8000\\/student?view=dashboard"}	2026-10-01 09:49:39	2026-09-24 09:41:38	2026-10-01 09:49:39
bfecdbfa-f20b-4874-be46-4a4f6cf816fa	App\\Notifications\\AccessRequestReceived	App\\Models\\User	2	{"type":"access_request","access_request_id":18,"stud_file_id":2,"research_title":"BugueyaKNOWS A Smart Food and Cultural Discovery Platform for Buguey","requester_email":"peraltamharinett@gmail.com","reason_access":"General Academic Learning","message":"Someone requested access to your research."}	\N	2026-10-01 10:31:53	2026-10-01 10:31:53
b6a06335-ee0e-43b3-b3dd-8113d55d4c8f	App\\Notifications\\AccessRequestReceived	App\\Models\\User	2	{"type":"access_request","access_request_id":19,"stud_file_id":2,"research_title":"BugueyaKNOWS A Smart Food and Cultural Discovery Platform for Buguey","requester_email":"peraltamharinett@gmail.com","reason_access":"General Academic Learning","message":"Someone requested access to your research."}	\N	2026-10-01 10:33:26	2026-10-01 10:33:26
df89671f-48bf-47fb-8b46-95b44bb72692	App\\Notifications\\AccessRequestReceived	App\\Models\\User	2	{"type":"access_request","access_request_id":20,"stud_file_id":2,"research_title":"BugueyaKNOWS A Smart Food and Cultural Discovery Platform for Buguey","requester_email":"jenniferlabinay2@gmail.com","reason_access":"Research Problem Identification","message":"Someone requested access to your research."}	\N	2026-10-01 10:44:08	2026-10-01 10:44:08
1d38086d-d201-42c0-85fe-9c159f77a1ba	App\\Notifications\\AccessRequestReceived	App\\Models\\User	2	{"type":"access_request","access_request_id":21,"stud_file_id":2,"research_title":"BugueyaKNOWS A Smart Food and Cultural Discovery Platform for Buguey","requester_email":"jenniferlabinay2@gmail.com","reason_access":"General Academic Learning","message":"Someone requested access to your research."}	\N	2026-10-01 10:46:14	2026-10-01 10:46:14
66d72422-c630-4e4c-bd23-f6d073b93d7e	App\\Notifications\\AccessRequestReceived	App\\Models\\User	2	{"type":"access_request","access_request_id":22,"stud_file_id":2,"research_title":"BugueyaKNOWS A Smart Food and Cultural Discovery Platform for Buguey","requester_email":"jenniferlabinay2@gmail.com","reason_access":"Research Problem Identification","message":"Someone requested access to your research."}	\N	2026-10-01 10:49:36	2026-10-01 10:49:36
6f3320ce-7e46-4be4-a39f-f2001fc5d4bb	App\\Notifications\\AccessRequestReceived	App\\Models\\User	2	{"type":"access_request","access_request_id":23,"stud_file_id":2,"research_title":"BugueyaKNOWS A Smart Food and Cultural Discovery Platform for Buguey","requester_email":"peraltamharinett@gmail.com","reason_access":"Research Problem Identification","message":"Someone requested access to your research."}	\N	2026-10-01 10:53:12	2026-10-01 10:53:12
3411d210-e4d4-473c-ba7e-41ea6be39d54	App\\Notifications\\ResearchSubmitted	App\\Models\\User	3	{"event":"research_submitted","message":"Marinette Peralta submitted \\"GROWTH PERFORMANCE Ofcherax Quadricarinatus (AUSTRALIAN RED CLAW CRAYFISH)(Martens, 1868)JUVENILES FED WITH DIFFERENT DIETS\\" for your review.","stud_file_id":5,"url":"http:\\/\\/127.0.0.1:8123\\/instructor?view=dashboard"}	\N	2026-10-01 14:31:58	2026-10-01 14:31:58
0fed8780-1aab-4ea2-833a-56a9c4c59c4b	App\\Notifications\\ResearchSubmitted	App\\Models\\User	42	{"event":"research_submitted","message":"Marinette Peralta submitted \\"GROWTH PERFORMANCE Ofcherax Quadricarinatus (AUSTRALIAN RED CLAW CRAYFISH)(Martens, 1868)JUVENILES FED WITH DIFFERENT DIETS\\" for your review.","stud_file_id":5,"url":"http:\\/\\/127.0.0.1:8123\\/instructor?view=dashboard"}	\N	2026-10-01 14:32:02	2026-10-01 14:32:02
d122283d-57cb-45f1-a974-20434a7c083c	App\\Notifications\\AccessRequestReceived	App\\Models\\User	4	{"type":"access_request","access_request_id":24,"stud_file_id":4,"research_title":"The Effect of Artificial Intelligence Tool Usage on the Learning Efficiency of IT Students","requester_email":"sagabaenianjorgedoblado@gmail.com","reason_access":"Research Problem Identification","message":"Someone requested access to your research."}	\N	2026-10-02 05:52:07	2026-10-02 05:52:07
84a95d01-9f80-4bcd-9f55-14130775d4a1	App\\Notifications\\AccessRequestReceived	App\\Models\\User	4	{"type":"access_request","access_request_id":25,"stud_file_id":4,"research_title":"The Effect of Artificial Intelligence Tool Usage on the Learning Efficiency of IT Students","requester_email":"jenniferlabinay2@gmail.com","reason_access":"Research Gap Identification","message":"Someone requested access to your research."}	\N	2026-10-02 06:01:10	2026-10-02 06:01:10
e1c5f9b7-23d0-47dc-be35-62cdaa9db7d1	App\\Notifications\\AccessRequestReceived	App\\Models\\User	4	{"type":"access_request","access_request_id":26,"stud_file_id":4,"research_title":"The Effect of Artificial Intelligence Tool Usage on the Learning Efficiency of IT Students","requester_email":"sagabaenianjorgedoblado@gmail.com","reason_access":"General Academic Learning","message":"Someone requested access to your research."}	\N	2026-10-02 08:18:45	2026-10-02 08:18:45
2d1c1a1b-3f65-4244-88ee-502db8f0cd41	App\\Notifications\\AccessRequestReceived	App\\Models\\User	2	{"type":"access_request","access_request_id":27,"stud_file_id":2,"research_title":"BugueyaKNOWS A Smart Food and Cultural Discovery Platform for Buguey","requester_email":"jenniferlabinay2@gmail.com","reason_access":"General Academic Learning","message":"Someone requested access to your research."}	\N	2026-10-02 12:43:59	2026-10-02 12:43:59
43a8f14c-91f3-488e-819b-533bc2c18c93	App\\Notifications\\AccessRequestReceived	App\\Models\\User	2	{"type":"access_request","access_request_id":28,"stud_file_id":2,"research_title":"BugueyaKNOWS A Smart Food and Cultural Discovery Platform for Buguey","requester_email":"johnpatrickalbeyeslabinay@gmail.com","reason_access":"Research Problem Identification","message":"Someone requested access to your research."}	\N	2026-10-02 14:18:03	2026-10-02 14:18:03
9ef68014-fc2e-4368-96b6-575821cdaae7	App\\Notifications\\AccessRequestReceived	App\\Models\\User	2	{"type":"access_request","access_request_id":29,"stud_file_id":2,"research_title":"BugueyaKNOWS A Smart Food and Cultural Discovery Platform for Buguey","requester_email":"johnpatrickalbeyeslabinay@gmail.com","reason_access":"Research Gap Identification","message":"Someone requested access to your research."}	\N	2026-10-02 14:25:40	2026-10-02 14:25:40
8cbbaaf4-3ebb-48d9-afdd-a5505e8a42ed	App\\Notifications\\AccessRequestReceived	App\\Models\\User	2	{"type":"access_request","access_request_id":30,"stud_file_id":2,"research_title":"BugueyaKNOWS A Smart Food and Cultural Discovery Platform for Buguey","requester_email":"johnpatrickalbeyeslabinay@gmail.com","reason_access":"Research Problem Identification","message":"Someone requested access to your research."}	\N	2026-10-02 15:00:07	2026-10-02 15:00:07
4bff0eb1-f61a-4aa7-a3ca-0219ecbb2c02	App\\Notifications\\AccessRequestReceived	App\\Models\\User	2	{"type":"access_request","access_request_id":31,"stud_file_id":2,"research_title":"BugueyaKNOWS A Smart Food and Cultural Discovery Platform for Buguey","requester_email":"johnpatrickalbeyeslabinay@gmail.com","reason_access":"Related Study Comparison","message":"Someone requested access to your research."}	\N	2026-10-02 15:12:45	2026-10-02 15:12:45
5f86049f-77da-4869-a823-b77b57ffa5da	App\\Notifications\\AccessRequestReceived	App\\Models\\User	2	{"type":"access_request","access_request_id":32,"stud_file_id":2,"research_title":"BugueyaKNOWS A Smart Food and Cultural Discovery Platform for Buguey","requester_email":"johnpatrickalbeyeslabinay@gmail.com","reason_access":"Related Study Comparison","message":"Someone requested access to your research."}	\N	2026-10-02 15:19:21	2026-10-02 15:19:21
f51c2d51-65bd-450e-9dab-3a92d0289128	App\\Notifications\\AccessRequestReceived	App\\Models\\User	2	{"type":"access_request","access_request_id":33,"stud_file_id":2,"research_title":"BugueyaKNOWS A Smart Food and Cultural Discovery Platform for Buguey","requester_email":"johnpatrickalbeyeslabinay@gmail.com","reason_access":"Research Gap Identification","message":"Someone requested access to your research."}	\N	2026-10-02 15:54:09	2026-10-02 15:54:09
c1041924-141b-42e5-9384-1167b54cbfa7	App\\Notifications\\AccessRequestReceived	App\\Models\\User	2	{"type":"access_request","access_request_id":34,"stud_file_id":2,"research_title":"BugueyaKNOWS A Smart Food and Cultural Discovery Platform for Buguey","requester_email":"johnpatrickalbeyeslabinay@gmail.com","reason_access":"Research Gap Identification","message":"Someone requested access to your research."}	\N	2026-10-02 15:59:14	2026-10-02 15:59:14
9e49acaf-7422-4edc-b6c3-7563cbb58f30	App\\Notifications\\AccessRequestReceived	App\\Models\\User	2	{"type":"access_request","access_request_id":35,"stud_file_id":2,"research_title":"BugueyaKNOWS A Smart Food and Cultural Discovery Platform for Buguey","requester_email":"johnpatrickalbeyeslabinay@gmail.com","reason_access":"Research Gap Identification","message":"Someone requested access to your research."}	\N	2026-10-02 16:02:00	2026-10-02 16:02:00
e0d45297-4208-42c4-9e48-5aff91cd4ba7	App\\Notifications\\AccessRequestReceived	App\\Models\\User	2	{"type":"access_request","access_request_id":36,"stud_file_id":2,"research_title":"BugueyaKNOWS A Smart Food and Cultural Discovery Platform for Buguey","requester_email":"johnpatrickalbeyeslabinay@gmail.com","reason_access":"Related Study Comparison","message":"Someone requested access to your research."}	\N	2026-10-02 16:05:39	2026-10-02 16:05:39
858b1c82-82b2-4a1b-bee6-e552aada808e	App\\Notifications\\AccessRequestReceived	App\\Models\\User	2	{"type":"access_request","access_request_id":37,"stud_file_id":2,"research_title":"BugueyaKNOWS A Smart Food and Cultural Discovery Platform for Buguey","requester_email":"johnpatrickalbeyeslabinay@gmail.com","reason_access":"Related Study Comparison","message":"Someone requested access to your research."}	\N	2026-10-02 16:14:51	2026-10-02 16:14:51
4c44157f-8235-486e-abb4-492a20864212	App\\Notifications\\AccessRequestReceived	App\\Models\\User	2	{"type":"access_request","access_request_id":38,"stud_file_id":2,"research_title":"BugueyaKNOWS A Smart Food and Cultural Discovery Platform for Buguey","requester_email":"johnpatrickalbeyeslabinay@gmail.com","reason_access":"Related Study Comparison","message":"Someone requested access to your research."}	\N	2026-10-02 16:22:01	2026-10-02 16:22:01
3753bd6b-2e46-4827-b7be-d4263fbf319a	App\\Notifications\\AccessRequestReceived	App\\Models\\User	2	{"type":"access_request","access_request_id":39,"stud_file_id":2,"research_title":"BugueyaKNOWS A Smart Food and Cultural Discovery Platform for Buguey","requester_email":"johnpatrickalbeyeslabinay@gmail.com","reason_access":"Research Gap Identification","message":"Someone requested access to your research."}	\N	2026-10-02 16:25:17	2026-10-02 16:25:17
ba37f798-e5e8-4545-9f0c-c43435b83dc0	App\\Notifications\\AccessRequestReceived	App\\Models\\User	2	{"type":"access_request","access_request_id":40,"stud_file_id":2,"research_title":"BugueyaKNOWS A Smart Food and Cultural Discovery Platform for Buguey","requester_email":"johnpatrickalbeyeslabinay@gmail.com","reason_access":"Research Gap Identification","message":"Someone requested access to your research."}	\N	2026-10-02 16:28:47	2026-10-02 16:28:47
e0d6f8fe-e985-40c2-af52-a361af4b8621	App\\Notifications\\AccessRequestReceived	App\\Models\\User	2	{"type":"access_request","access_request_id":41,"stud_file_id":2,"research_title":"BugueyaKNOWS A Smart Food and Cultural Discovery Platform for Buguey","requester_email":"johnpatrickalbeyeslabinay@gmail.com","reason_access":"Research Gap Identification","message":"Someone requested access to your research."}	\N	2026-10-02 16:31:52	2026-10-02 16:31:52
f5b7d4fc-bbe4-4adf-8381-3ed03e23980a	App\\Notifications\\AccessRequestReceived	App\\Models\\User	2	{"type":"access_request","access_request_id":42,"stud_file_id":2,"research_title":"BugueyaKNOWS A Smart Food and Cultural Discovery Platform for Buguey","requester_email":"johnpatrickalbeyeslabinay@gmail.com","reason_access":"Related Study Comparison","message":"Someone requested access to your research."}	\N	2026-10-02 16:44:21	2026-10-02 16:44:21
accbe7dd-f6d8-4c33-8626-88096388bf87	App\\Notifications\\AccessRequestReceived	App\\Models\\User	2	{"type":"access_request","access_request_id":43,"stud_file_id":2,"research_title":"BugueyaKNOWS A Smart Food and Cultural Discovery Platform for Buguey","requester_email":"johnpatrickalbeyeslabinay@gmail.com","reason_access":"Related Study Comparison","message":"Someone requested access to your research."}	\N	2026-10-02 16:50:45	2026-10-02 16:50:45
ab41052d-1606-4910-954d-b6b9a0bde3d0	App\\Notifications\\AccessRequestReceived	App\\Models\\User	4	{"type":"access_request","access_request_id":44,"stud_file_id":4,"research_title":"The Effect of Artificial Intelligence Tool Usage on the Learning Efficiency of IT Students","requester_email":"johnpatrickalbeyeslabinay@gmail.com","reason_access":"General Academic Learning","message":"Someone requested access to your research."}	\N	2026-10-03 02:55:24	2026-10-03 02:55:24
29086efc-6559-4ad3-bd3b-12d778199559	App\\Notifications\\AccessRequestReceived	App\\Models\\User	2	{"type":"access_request","access_request_id":45,"stud_file_id":2,"research_title":"BugueyaKNOWS A Smart Food and Cultural Discovery Platform for Buguey","requester_email":"peraltamharinett@gmail.com","reason_access":"Related Study Comparison","message":"Someone requested access to your research."}	\N	2026-10-03 03:16:21	2026-10-03 03:16:21
b3df6174-12c2-4c09-9dc9-c5303ec1a25a	App\\Notifications\\AccessRequestReceived	App\\Models\\User	2	{"type":"access_request","access_request_id":46,"stud_file_id":2,"research_title":"BugueyaKNOWS A Smart Food and Cultural Discovery Platform for Buguey","requester_email":"johnpatrickalbeyeslabinay@gmail.com","reason_access":"Related Study Comparison","message":"Someone requested access to your research."}	\N	2026-10-03 05:28:20	2026-10-03 05:28:20
a98ae114-9777-42c1-acf2-3d6af3ab6705	App\\Notifications\\AccessRequestReceived	App\\Models\\User	2	{"type":"access_request","access_request_id":47,"stud_file_id":2,"research_title":"BugueyaKNOWS A Smart Food and Cultural Discovery Platform for Buguey","requester_email":"johnpatrickalbeyeslabinay@gmail.com","reason_access":"Research Gap Identification","message":"Someone requested access to your research."}	\N	2026-10-03 05:47:14	2026-10-03 05:47:14
ced92e4b-c54e-4c46-be0c-68914619125d	App\\Notifications\\AccessRequestReceived	App\\Models\\User	2	{"type":"access_request","access_request_id":48,"stud_file_id":2,"research_title":"BugueyaKNOWS A Smart Food and Cultural Discovery Platform for Buguey","requester_email":"johnpatrickalbeyeslabinay@gmail.com","reason_access":"Research Gap Identification","message":"Someone requested access to your research."}	\N	2026-10-03 05:53:50	2026-10-03 05:53:50
4c542aba-b6dd-426d-9c36-bb4a459233a9	App\\Notifications\\AccountCreated	App\\Models\\User	43	{"event":"account_created","message":"Welcome! Your account has been created.","url":"http:\\/\\/127.0.0.1:8123\\/instructor?view=dashboard"}	\N	2026-10-03 08:02:28	2026-10-03 08:02:28
f9603443-9dcc-4d21-b934-9bf6b9cd9acf	App\\Notifications\\ResearchReviewedByInstructor	App\\Models\\User	4	{"event":"research_reviewed_by_instructor","status":"rejected","message":"Your adviser rejected \\"GROWTH PERFORMANCE Ofcherax Quadricarinatus (AUSTRALIAN RED CLAW CRAYFISH)(Martens, 1868)JUVENILES FED WITH DIFFERENT DIETS\\".","stud_file_id":5,"url":"http:\\/\\/127.0.0.1:8123\\/student?view=dashboard"}	\N	2026-10-03 09:45:56	2026-10-03 09:45:56
abb43906-ef2b-4312-aa69-a1a1d730cd7e	App\\Notifications\\ResearchReviewedByInstructor	App\\Models\\User	2	{"event":"research_reviewed_by_instructor","status":"rejected","message":"Your adviser rejected \\"GPS-Based Tracking System for Enhancing Risk Mitigation in Passenger Boat\\".","stud_file_id":1,"url":"http:\\/\\/127.0.0.1:8123\\/student?view=dashboard"}	\N	2026-10-04 06:35:20	2026-10-04 06:35:20
28b34083-d145-4a41-a60f-bab599c09b20	App\\Notifications\\ResearchSubmitted	App\\Models\\User	3	{"event":"research_submitted","message":"John Patrick Labinay submitted \\"GPS-Based Tracking System for Enhancing Risk Mitigation in Passenger Boat\\" for your review.","stud_file_id":1,"url":"http:\\/\\/127.0.0.1:8123\\/instructor?view=dashboard"}	\N	2026-10-04 06:41:09	2026-10-04 06:41:09
aaaeb7bb-638e-40ae-adaf-2253e1ea8fd0	App\\Notifications\\ResearchReviewedByInstructor	App\\Models\\User	2	{"event":"research_reviewed_by_instructor","status":"rejected","message":"Your adviser rejected \\"GPS-Based Tracking System for Enhancing Risk Mitigation in Passenger Boat\\".","stud_file_id":1,"url":"http:\\/\\/127.0.0.1:8123\\/student?view=dashboard"}	\N	2026-10-04 06:42:20	2026-10-04 06:42:20
39c37782-2f29-4cdd-b6a6-41c945b35269	App\\Notifications\\ResearchSubmitted	App\\Models\\User	3	{"event":"research_submitted","message":"John Patrick Labinay submitted \\"GPS-Based Tracking System for Enhancing Risk Mitigation in Passenger Boat\\" for your review.","stud_file_id":1,"url":"http:\\/\\/127.0.0.1:8123\\/instructor?view=dashboard"}	\N	2026-10-04 06:43:43	2026-10-04 06:43:43
38c69bb7-4514-416c-9b4b-10acafad9a75	App\\Notifications\\ResearchReviewedByInstructor	App\\Models\\User	2	{"event":"research_reviewed_by_instructor","status":"rejected","message":"Your adviser rejected \\"GPS-Based Tracking System for Enhancing Risk Mitigation in Passenger Boat\\".","stud_file_id":1,"url":"http:\\/\\/127.0.0.1:8123\\/student?view=dashboard"}	\N	2026-10-04 06:45:03	2026-10-04 06:45:03
675ca8cf-05fe-4521-90d5-4c633b8fdcce	App\\Notifications\\ResearchSubmitted	App\\Models\\User	3	{"event":"research_submitted","message":"John Patrick Labinay submitted \\"GPS-Based Tracking System for Enhancing Risk Mitigation in Passenger Boat\\" for your review.","stud_file_id":1,"url":"http:\\/\\/127.0.0.1:8123\\/instructor?view=dashboard"}	\N	2026-10-04 08:11:53	2026-10-04 08:11:53
16149abe-2b2d-4946-8b06-09e349df7408	App\\Notifications\\ResearchReviewedByInstructor	App\\Models\\User	2	{"event":"research_reviewed_by_instructor","status":"rejected","message":"Your adviser rejected \\"GPS-Based Tracking System for Enhancing Risk Mitigation in Passenger Boat\\".","stud_file_id":1,"url":"http:\\/\\/127.0.0.1:8123\\/student?view=dashboard"}	\N	2026-10-04 08:13:16	2026-10-04 08:13:16
b52fc5f7-88d8-4b1e-8d17-8e01bca082aa	App\\Notifications\\ResearchSubmitted	App\\Models\\User	3	{"event":"research_submitted","message":"John Patrick Labinay submitted \\"GPS-Based Tracking System for Enhancing Risk Mitigation in Passenger Boat\\" for your review.","stud_file_id":1,"url":"http:\\/\\/127.0.0.1:8123\\/instructor?view=dashboard"}	\N	2026-10-04 08:14:38	2026-10-04 08:14:38
b993071b-4fbc-48f2-9f3f-e6f3fc069f55	App\\Notifications\\ResearchReviewedByInstructor	App\\Models\\User	2	{"event":"research_reviewed_by_instructor","status":"rejected","message":"Your adviser rejected \\"GPS-Based Tracking System for Enhancing Risk Mitigation in Passenger Boat\\".","stud_file_id":1,"url":"http:\\/\\/127.0.0.1:8123\\/student?view=dashboard"}	\N	2026-10-04 08:16:13	2026-10-04 08:16:13
b21a8688-5643-4ed7-92f3-6ce38737fdb9	App\\Notifications\\ResearchSubmitted	App\\Models\\User	3	{"event":"research_submitted","message":"John Patrick Labinay submitted \\"GPS-Based Tracking System for Enhancing Risk Mitigation in Passenger Boat\\" for your review.","stud_file_id":1,"url":"http:\\/\\/127.0.0.1:8123\\/instructor?view=dashboard"}	\N	2026-10-04 10:11:05	2026-10-04 10:11:05
059060d9-39dd-4be9-9b42-c4a25a12044c	App\\Notifications\\ResearchReviewedByInstructor	App\\Models\\User	2	{"event":"research_reviewed_by_instructor","status":"approved","message":"Your adviser approved \\"GPS-Based Tracking System for Enhancing Risk Mitigation in Passenger Boat\\" \\u2014 it's now with the admin for final review.","stud_file_id":1,"url":"http:\\/\\/127.0.0.1:8123\\/student?view=dashboard"}	\N	2026-10-04 10:12:53	2026-10-04 10:12:53
e6d64061-fe5a-4258-a08f-bca0d50499a3	App\\Notifications\\ResearchReviewedByAdmin	App\\Models\\User	2	{"event":"research_reviewed_by_admin","status":"approved","message":"Your research \\"GPS-Based Tracking System for Enhancing Risk Mitigation in Passenger Boat\\" has been approved.","stud_file_id":1,"url":"http:\\/\\/127.0.0.1:8123\\/student?view=dashboard"}	\N	2026-10-04 10:16:12	2026-10-04 10:16:12
99e4e73e-91dd-46ce-9985-7691beeebf79	App\\Notifications\\ResearchSubmitted	App\\Models\\User	3	{"event":"research_submitted","message":"John Patrick Labinay submitted \\"GROWTH PERFORMANCE OFCherax quadricarinatus (AUSTRALIAN RED CLAW CRAYFISH)(Martens, 1868)JUVENILES FED WITH DIFFERENT DIETS\\" for your review.","stud_file_id":6,"url":"http:\\/\\/127.0.0.1:8123\\/instructor?view=dashboard"}	\N	2026-10-04 10:22:56	2026-10-04 10:22:56
12c5c10c-68fe-46c7-9a73-179888d18de2	App\\Notifications\\ResearchReviewedByInstructor	App\\Models\\User	2	{"event":"research_reviewed_by_instructor","status":"approved","message":"Your adviser approved \\"GROWTH PERFORMANCE OFCherax quadricarinatus (AUSTRALIAN RED CLAW CRAYFISH)(Martens, 1868)JUVENILES FED WITH DIFFERENT DIETS\\" \\u2014 it's now with the admin for final review.","stud_file_id":6,"url":"http:\\/\\/127.0.0.1:8123\\/student?view=dashboard"}	\N	2026-10-04 10:25:26	2026-10-04 10:25:26
3914294b-f561-4a8d-ac8d-4fe6a6a2b6a2	App\\Notifications\\ResearchReviewedByAdmin	App\\Models\\User	2	{"event":"research_reviewed_by_admin","status":"approved","message":"Your research \\"GROWTH PERFORMANCE OFCherax quadricarinatus (AUSTRALIAN RED CLAW CRAYFISH)(Martens, 1868)JUVENILES FED WITH DIFFERENT DIETS\\" has been approved.","stud_file_id":6,"url":"http:\\/\\/127.0.0.1:8123\\/student?view=dashboard"}	\N	2026-10-04 10:29:03	2026-10-04 10:29:03
32dbc854-8666-4f38-a68d-021114dbb20b	App\\Notifications\\ResearchSubmitted	App\\Models\\User	3	{"event":"research_submitted","message":"John Patrick Labinay submitted \\"GROWTH PERFORMANCE OFCherax quadricarinatus (AUSTRALIAN RED CLAW CRAYFISH)(Martens, 1868)JUVENILES FED WITH DIFFERENT DIETS\\" for your review.","stud_file_id":7,"url":"http:\\/\\/127.0.0.1:8123\\/instructor?view=dashboard"}	\N	2026-10-04 10:49:22	2026-10-04 10:49:22
e82cdc7a-0f6b-4e99-99ce-5719e7427915	App\\Notifications\\ResearchReviewedByInstructor	App\\Models\\User	2	{"event":"research_reviewed_by_instructor","status":"approved","message":"Your adviser approved \\"GROWTH PERFORMANCE OFCherax quadricarinatus (AUSTRALIAN RED CLAW CRAYFISH)(Martens, 1868)JUVENILES FED WITH DIFFERENT DIETS\\" \\u2014 it's now with the admin for final review.","stud_file_id":7,"url":"http:\\/\\/127.0.0.1:8123\\/student?view=dashboard"}	\N	2026-10-04 10:51:27	2026-10-04 10:51:27
83ace068-dcbf-451a-b968-fb07f72b40fb	App\\Notifications\\ResearchReviewedByAdmin	App\\Models\\User	2	{"event":"research_reviewed_by_admin","status":"approved","message":"Your research \\"GROWTH PERFORMANCE OFCherax quadricarinatus (AUSTRALIAN RED CLAW CRAYFISH)(Martens, 1868)JUVENILES FED WITH DIFFERENT DIETS\\" has been approved.","stud_file_id":7,"url":"http:\\/\\/127.0.0.1:8123\\/student?view=dashboard"}	\N	2026-10-04 10:53:27	2026-10-04 10:53:27
\.


--
-- Data for Name: password_reset_tokens; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.password_reset_tokens (email, token, created_at) FROM stdin;
\.


--
-- Data for Name: research_access_requests; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.research_access_requests (id, email, created_at, updated_at, status, reason_access, stud_file_id, name) FROM stdin;
44	johnpatrickalbeyeslabinay@gmail.com	2026-10-03 02:55:22+00	2026-10-03 03:28:03+00	approved	General Academic Learning	4	\N
45	peraltamharinett@gmail.com	2026-10-03 03:16:19+00	2026-10-03 04:56:57+00	approved	Related Study Comparison	2	\N
46	johnpatrickalbeyeslabinay@gmail.com	2026-10-03 05:28:17+00	2026-10-03 05:28:17+00	pending	Related Study Comparison	2	\N
48	johnpatrickalbeyeslabinay@gmail.com	2026-10-03 05:53:49+00	2026-10-03 05:54:36+00	approved	Research Gap Identification	2	\N
47	johnpatrickalbeyeslabinay@gmail.com	2026-10-03 05:47:13+00	2026-10-04 03:35:25+00	rejected	Research Gap Identification	2	\N
\.


--
-- Data for Name: research_chunks; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.research_chunks (id, stud_file_id, chunk_index, section, page_number, content, token_count, content_hash, created_at, updated_at, embedding, embedding_model, embedding_version) FROM stdin;
\.


--
-- Data for Name: research_feedback; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.research_feedback (id, stud_file_id, user_id, feedback, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: sdgs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.sdgs (id, number, created_at, updated_at) FROM stdin;
1	1	\N	\N
2	2	\N	\N
3	3	\N	\N
4	4	\N	\N
5	5	\N	\N
6	6	\N	\N
7	7	\N	\N
8	8	\N	\N
9	9	\N	\N
10	10	\N	\N
11	11	\N	\N
12	12	\N	\N
13	13	\N	\N
14	14	\N	\N
15	15	\N	\N
16	16	\N	\N
17	17	\N	\N
\.


--
-- Data for Name: search_logs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.search_logs (id, user_id, keyword, created_at, updated_at) FROM stdin;
19	\N	Artificial	2026-10-05 16:46:39	2026-10-05 16:46:39
20	\N	Tilapia	2026-10-05 16:47:16	2026-10-05 16:47:16
21	\N	Boat	2026-10-05 16:47:47	2026-10-05 16:47:47
22	\N	Intelligence	2026-10-05 16:48:40	2026-10-05 16:48:40
\.


--
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.sessions (id, user_id, ip_address, user_agent, payload, last_activity) FROM stdin;
SMGXiNRFqiRlQla2LnAFTpz1tgJIhvR6vhSqlUBM	1	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36	YTo0OntzOjY6Il90b2tlbiI7czo0MDoiWWdzV3RUNFl3V2tsTEUybnNxOGxpWUVTQzl2RXA5NnYxbXlBenFrQyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODEyMy9hZG1pbiI7czo1OiJyb3V0ZSI7czo1OiJhZG1pbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==	1791293821
PwFzphBFnxpywmEyWILqP1oAbQAPUhehuF4k3KxC	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.127.0 Chrome/148.0.7778.97 Electron/42.2.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoiSFZUVkxTTEdqMUdBMElCOThGVzl4RWplblFvS2lqdWljcXRSUmR2dyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODEyMyI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1791292074
\.


--
-- Data for Name: stud_file; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.stud_file (id, user_id, title, name, adviser, "desc", file_path, auto_tag, status, visibility, requested_visibility, views, downloads, created_at, updated_at, publication_year, adviser_id, pdf_hash, pdf_page_count, extracted_abstract, abstract_source, extracted_sections, ai_analysis_status, ai_analysis_fingerprint, ai_suggested_keywords, ai_suggested_sdgs, ai_model_used, ai_analyzed_at, search_index_status, search_indexed_at, search_index_error, instructor_feedback, rejection_reason, rejected_at, research_type, admin_reject, resubmit_count, extracted_references) FROM stdin;
3	4	Research on the Application of Intelligent Technology in Document and Archives Management	Marinette Peralta	Ian Jorge Sagabaen, Liezl P. Dela Cruz	The rapid advancement of intelligent technologies has transformed traditional approaches to document and archives management, providing organizations with more efficient methods for storing, organizing, retrieving, and preserving information. This study examines the application of intelligent technology in document and archives management, particularly its potential to improve accessibility, accuracy, efficiency, and information security. Intelligent technologies such as artificial intelligence, machine learning, optical character recognition, natural language processing, and automated classification can assist in identifying, categorizing, indexing, and retrieving large volumes of digital and physical records. The study focuses on how these technologies can reduce repetitive administrative tasks, minimize human error, and support faster access to relevant documents. It also considers challenges associated with implementation, including data privacy, cybersecurity, system integration, data quality, cost, and the need for personnel training. By examining the practical applications and potential benefits of intelligent technology, the study provides insights into how modern archives and records management practices can adapt to digital transformation. The findings may serve as a basis for organizations seeking to improve their document management processes while maintaining reliable, secure, and accessible archival information. Overall, the integration of intelligent technology presents opportunities for developing more organized, responsive, and technology-driven document and archives management systems.	research_files/c4668dc618cfa428ae840ce6ba94800981824084a714817d96c96e00755cee8e.pdf	research,application,intelligent,technology,document,archives,management	pending	private	private	1	0	2026-10-01 07:41:55	2026-10-01 13:54:36	2021	3	c4668dc618cfa428ae840ce6ba94800981824084a714817d96c96e00755cee8e	6	\N	manual	[{"end": 21188, "key": "introduction", "start": 1664, "heading": "Introduction", "page_number": 1}, {"end": 22418, "key": "conclusion", "start": 21188, "heading": "Conclusion", "page_number": 5}, {"end": 23343, "key": "references", "start": 22418, "heading": "References", "page_number": 6}]	not_started	\N	\N	\N	\N	\N	not_started	\N	\N	\N	\N	\N	\N	\N	0	\N
4	4	The Effect of Artificial Intelligence Tool Usage on the Learning Efficiency of IT Students	Marinette Peralta	Ian Jorge Sagabaen, Liezl P. Dela Cruz	The integration of Artificial Intelligence (AI) in education has transformed academic learning, \r\noffering both opportunities and challenges for students’ development. This study investigates \r\nthe impact of AI technologies on students’ learning processes and academic performance, with \r\na focus on their perceptions and the challenges associated with AI adoption. Conducted at the \r\nCagayan State University- Aparri Campus, this research involved second-year students who had \r\ndirect experience with AI-enhanced learning environments. Using purposive sampling, 179 \r\nparticipants were selected to ensure relevance. Data were collected through a structured \r\nquestionnaire comprising 11 items as follows: five closed-ended questions assessing \r\nperceptions, usage, and the effectiveness of AI tools; and four open-ended questions exploring \r\nexperiences, expectations, and concerns. Quantitative data were analyzed using frequency and \r\npercentage calculations responses were subjected to thematic analysis, incorporating vertical \r\n(individual responses) and horizontal (cross-dataset) approaches to ensure comprehensive \r\ntheme identification. The findings reveal that AI offers significant benefits, including \r\npersonalized learning, improved academic outcomes, and enhanced student engagement. \r\nHowever, challenges such as over-reliance on AI, diminished critical thinking skills, data privacy \r\nrisks, and academic dishonesty were also identified. The study underscores the necessity of a \r\nstructured framework for AI integration, supported by ethical guidelines, to maximize benefits \r\nwhile mitigating risks. In conclusion, while AI holds immense potential to enhance learning \r\nefficiency and academic performance, its successful implementation requires addressing \r\nconcerns related to accuracy, cognitive disengagement, and ethical implications. A balanced \r\napproach is essential to ensure equitable, effective, and responsible learning experiences in \r\nAI-enhanced educational environments. \r\nRevision 3 | July 06, 2025\r\nChapter 1	research_files/f64413a1843661e655527125dfb31a53139fde3c31a260e2e59c6a281c41544c.pdf	effect,artificial,intelligence,tool,usage,learning,efficiency,it,students	approved	public	private	3	1	2026-10-01 07:47:30	2026-10-03 04:43:18	2026	3	f64413a1843661e655527125dfb31a53139fde3c31a260e2e59c6a281c41544c	64	The integration of Artificial Intelligence (AI) in education has transformed academic learning, \noffering both opportunities and challenges for students’ development. This study investigates \nthe impact of AI technologies on students’ learning processes and academic performance, with \na focus on their perceptions and the challenges associated with AI adoption. Conducted at the \nCagayan State University- Aparri Campus, this research involved second-year students who had \ndirect experience with AI-enhanced learning environments. Using purposive sampling, 179 \nparticipants were selected to ensure relevance. Data were collected through a structured \nquestionnaire comprising 11 items as follows: five closed-ended questions assessing \nperceptions, usage, and the effectiveness of AI tools; and four open-ended questions exploring \nexperiences, expectations, and concerns. Quantitative data were analyzed using frequency and \npercentage calculations responses were subjected to thematic analysis, incorporating vertical \n(individual responses) and horizontal (cross-dataset) approaches to ensure comprehensive \ntheme identification. The findings reveal that AI offers significant benefits, including \npersonalized learning, improved academic outcomes, and enhanced student engagement. \nHowever, challenges such as over-reliance on AI, diminished critical thinking skills, data privacy \nrisks, and academic dishonesty were also identified. The study underscores the necessity of a \nstructured framework for AI integration, supported by ethical guidelines, to maximize benefits \nwhile mitigating risks. In conclusion, while AI holds immense potential to enhance learning \nefficiency and academic performance, its successful implementation requires addressing \nconcerns related to accuracy, cognitive disengagement, and ethical implications. A balanced \napproach is essential to ensure equitable, effective, and responsible learning experiences in \nAI-enhanced educational environments. \nRevision 3 | July 06, 2025\nChapter 1	manual	[{"end": 10462, "key": "abstract", "start": 8422, "heading": "ABSTRACT", "page_number": 8}, {"end": 10476, "key": "introduction", "start": 10462, "heading": "INTRODUCTION", "page_number": 9}, {"end": 66293, "key": "introduction", "start": 10476, "heading": "Background of the Study", "page_number": 9}, {"end": 72964, "key": "methodology", "start": 66293, "heading": "METHODOLOGY", "page_number": 38}, {"end": 87204, "key": "results", "start": 72964, "heading": "RESULTS AND DISCUSSION", "page_number": 44}, {"end": 89129, "key": "conclusion", "start": 87204, "heading": "Conclusion", "page_number": 55}, {"end": 99965, "key": "conclusion", "start": 89129, "heading": "Recommendations", "page_number": 56}]	not_started	\N	\N	\N	\N	\N	not_started	\N	\N	\N	\N	\N	\N	\N	0	\N
2	2	BugueyaKNOWS A Smart Food and Cultural Discovery Platform for Buguey	John Patrick Labinay	Ian Jorge Sagabaen	The Banten Jaya University Library repository system still used the conventional method of storing \r\nit on a shelf in the reference room, this made it difficult for students who were completing their final \r\nproject/thesis to access it online. Moreover, with the closure of the campus due to the COVID-19 pandemic, \r\nthe PSBB and PPKM were continued. This research made it easier for students to access library repository \r\ncollections wherever they are, measure the extent of students' literacy skills in utilizing information sources, \r\nand show the impact on their research performance. The data collection method uses a questionnaire, with \r\nstatistical analysis, a model for assessing student literacy skills with Empowering 8, which were a research \r\nmethod used to produce products and test the effectiveness of these products, with the Waterfall system \r\ndevelopment model, which consists of analysis, design, implementation, and testing. Metadata obtained \r\nfrom the repository application, distributing questionnaires to students taking Thesis/Final Project courses \r\nfrom each faculty. The literacy level of Banten Jaya University students is quite good, especially after the \r\nonline repository has been built, there is a significant correlation between student literacy levels and \r\nstudent performance results. A good literacy level was the basic capital in solving student problems while \r\ndoing their research, it is necessary to have a Research Information Literacy program for students that is \r\nintegrated with Research Methods courses so that student research performance increases in accessing the \r\nrepository that has been built.	research_files/c3dd1509c19bc6c3f1366d6b44d75b6115e3d3d45aa5d353e563eb8d7686fdcd.pdf	bugueyaknows,smart,food,cultural,discovery,platform,buguey	approved	public	private	0	0	2026-10-01 07:29:41	2026-10-01 08:56:20	2024	3	c3dd1509c19bc6c3f1366d6b44d75b6115e3d3d45aa5d353e563eb8d7686fdcd	8	The Banten Jaya University Library repository system still used the conventional method of storing \nit on a shelf in the reference room, this made it difficult for students who were completing their final \nproject/thesis to access it online. Moreover, with the closure of the campus due to the COVID-19 pandemic, \nthe PSBB and PPKM were continued. This research made it easier for students to access library repository \ncollections wherever they are, measure the extent of students' literacy skills in utilizing information sources, \nand show the impact on their research performance. The data collection method uses a questionnaire, with \nstatistical analysis, a model for assessing student literacy skills with Empowering 8, which were a research \nmethod used to produce products and test the effectiveness of these products, with the Waterfall system \ndevelopment model, which consists of analysis, design, implementation, and testing. Metadata obtained \nfrom the repository application, distributing questionnaires to students taking Thesis/Final Project courses \nfrom each faculty. The literacy level of Banten Jaya University students is quite good, especially after the \nonline repository has been built, there is a significant correlation between student literacy levels and \nstudent performance results. A good literacy level was the basic capital in solving student problems while \ndoing their research, it is necessary to have a Research Information Literacy program for students that is \nintegrated with Research Methods courses so that student research performance increases in accessing the \nrepository that has been built.	manual	[{"end": 2856, "key": "abstract", "start": 1129, "heading": "Abstract", "page_number": 1}, {"end": 16077, "key": "introduction", "start": 2856, "heading": "1. Introduction", "page_number": 1}, {"end": 26340, "key": "results", "start": 16077, "heading": "3. RESULTS AND DISCUSSION", "page_number": 4}, {"end": 27059, "key": "conclusion", "start": 26340, "heading": "4. Conclusion", "page_number": 7}, {"end": 32071, "key": "references", "start": 27059, "heading": "References", "page_number": 7}]	not_started	\N	\N	\N	\N	\N	not_started	\N	\N	\N	\N	\N	\N	\N	0	\N
1	2	GPS-Based Tracking System for Enhancing Risk Mitigation in Passenger Boat	John Patrick Labinay	Ian Jorge Sagabaen	SeawatchWeb is a GPS-based tracking system designed to enhance risk mitigation in \r\npassenger boat operations by addressing challenges such as inefficient manual tracking, \r\ndelayed emergency response, and limited access to real-time vessel data. The system provides \r\nreal-time GPS tracking, automated reporting, emergency alerts, and data visualization tools to \r\nimprove decision-making and operational efficiency. \r\nDeveloped using an agile methodology, SeawatchWeb underwent iterative phases, including \r\nplanning, system design, development, testing, and evaluation. It leverages modern web and \r\nmobile technologies to ensure a user-friendly interface and cross-device compatibility. Key \r\nstakeholders—passenger boat operators, maritime authorities, and local communities—benefit \r\nfrom improved safety measures, reliable monitoring, and streamlined emergency \r\ncommunication. Additionally, SeawatchWeb incorporates a role-based access system, allowing different user \r\ntypes—including boat operators, maritime authorities, and community members—to interact \r\nwith the platform based on their specific responsibilities and permissions. This structured \r\naccess ensures secure data handling while empowering stakeholders with the tools most \r\nrelevant to their roles. By mitigating risks and strengthening safety protocols, SeawatchWeb significantly enhances \r\nmaritime operations. Its data-driven approach supports informed decision-making, making it a \r\ncrucial tool for promoting maritime safety and sustainability.	research_files/2a6075e5b8de2cce0a1bacebfbadca850bca236a7d00a7db535b1ae38761d948.pdf	gpsbased,tracking,system,enhancing,risk,mitigation,passenger,boat	approved	public	private	0	0	2026-10-01 07:24:57	2026-10-04 10:16:05	2023	3	2a6075e5b8de2cce0a1bacebfbadca850bca236a7d00a7db535b1ae38761d948	12	SeawatchWeb is a GPS-based tracking system designed to enhance risk mitigation in \npassenger boat operations by addressing challenges such as inefficient manual tracking, \ndelayed emergency response, and limited access to real-time vessel data. The system provides \nreal-time GPS tracking, automated reporting, emergency alerts, and data visualization tools to \nimprove decision-making and operational efficiency. \nDeveloped using an agile methodology, SeawatchWeb underwent iterative phases, including \nplanning, system design, development, testing, and evaluation. It leverages modern web and \nmobile technologies to ensure a user-friendly interface and cross-device compatibility. Key \nstakeholders—passenger boat operators, maritime authorities, and local communities—benefit \nfrom improved safety measures, reliable monitoring, and streamlined emergency \ncommunication. Additionally, SeawatchWeb incorporates a role-based access system, allowing different user \ntypes—including boat operators, maritime authorities, and community members—to interact \nwith the platform based on their specific responsibilities and permissions. This structured \naccess ensures secure data handling while empowering stakeholders with the tools most \nrelevant to their roles. By mitigating risks and strengthening safety protocols, SeawatchWeb significantly enhances \nmaritime operations. Its data-driven approach supports informed decision-making, making it a \ncrucial tool for promoting maritime safety and sustainability.	manual	[{"end": 16642, "key": "abstract", "start": 380, "heading": "ABSTRACT", "page_number": 1}, {"end": 18511, "key": "references", "start": 16642, "heading": "REFERENCES", "page_number": 11}]	not_started	\N	\N	\N	\N	\N	not_started	\N	\N	\N	Missing required sections	2026-10-04 08:16:12+00	\N	\N	1	\N
7	2	GROWTH PERFORMANCE OFCherax quadricarinatus (AUSTRALIAN RED CLAW CRAYFISH)(Martens, 1868)JUVENILES FED WITH DIFFERENT DIETS	John Patrick Labinay	Ian Jorge Sagabaen	This study evaluated the growth performance of Cherax quadricarinatus (Australian red claw crayfish) juveniles fed with different diets. The study focused on determining how dietary treatments affect the growth and overall performance of juvenile red claw crayfish during the culture period. Growth performance was assessed based on relevant growth indicators, including weight gain, growth rate, and other production-related parameters. The juveniles were maintained under controlled culture conditions and provided with the designated diets throughout the experimental period. The results of the study provide information on the suitability of different diets for improving the growth performance of C. quadricarinatus juveniles. The findings may serve as a reference for selecting appropriate feeds and developing practical feeding strategies for the culture of Australian red claw crayfish.	research_files/622528cc66f31b81c5e8ddbc303b1747ac916a4a9d24cf04ee832b19c8625daf.pdf	growth,performance,ofcherax,quadricarinatus,australian,red,claw,crayfishmartens,1868juveniles,fed,different,diets	approved	public	private	0	0	2026-10-04 10:49:12	2026-10-04 10:53:22	2026	3	622528cc66f31b81c5e8ddbc303b1747ac916a4a9d24cf04ee832b19c8625daf	23	\N	manual	[{"end": 1516, "key": "introduction", "start": 1502, "heading": "INTRODUCTION", "page_number": 3}, {"end": 11741, "key": "introduction", "start": 1516, "heading": "1.1 Background of the study", "page_number": 3}, {"end": 22648, "key": "methodology", "start": 11741, "heading": "METHODOLOGY", "page_number": 9}, {"end": 29493, "key": "references", "start": 22648, "heading": "REFERENCES", "page_number": 18}]	not_started	\N	\N	\N	\N	\N	not_started	\N	\N	\N	\N	\N	\N	\N	0	["Aflalo, ED., Hoang, VH., Nguyen, Q. Lam, DM., Trinh, S. Raviv A. (2006).A novel two-step  procedure for mass production of all-mail populations of the Australian Red Claw Crayfish. Aquaculture. 256: 468-478.", "Afnan, SL., Zainudin, NA., Nor, SA., Saad, CR., (2020). Effects of cow skin meal as a protein  source on growth performance and nutrient utilizationof Red Claw Crayfish, Cherax quadricarintus (de Man, 1879). Aquaculture Nutrition, 26(6), 1457-1464.", "Bortolini, JL.,Alvarez,G., Rodriguez, A.(2007).On the presence of the Australian redclaw crayfish Cherax quadricarinatus, in Mexico. Biological Invasions. 9:615- 620.", "Curtis CM., Jones CM. (1995). Observations on monosex culture of redclaw crayfish Cherax quadricarinatus von Martens (Decapoda: Parastacidae) in earthen ponds. Journal of the World Aquaculture Society. (26)154-159.", "FAO. (2007). Fisheries and aquaculture statistics. FAO Yearbook. Available online: http://www.fao.org/fishery/publications/en Access date: 8/30/2010. Ford, A. T.", "2008. Can you feminise a crustacean? Aquatic Toxicology. 88: 316-321.", "El-Sayed, AF., Ismael, MF., Ahmed, MO.,El-Hameed, AA., (2015). Growth performance and feed utilization of Red Claw Crayfish Cherax quadricarintus (de Man, 1879) fed diets containing Cowskinmeal.  International Journal of Fisheries  and Aquatic Sciences, 9(6), 779-784.", "El-Hameed, AA., Ismael, MF., Ibrahim, ME., & Ahmed, MO., (2018). Suitability of  slaughter by-products as feed ingredients for Cherax quadricarintus (de Man,  1879): effects on water quality and  crayfish performance. Journal of the  World Aquaculture Society, 49(4), 549-561.", "Sahu, M., Jain, KC., & Misra, AK., (2002). Water stability of extruded pellets and its influence on feed-conversion ratio in Cherax quadricarintus (de Man) hatchery. A quaculture Nutrition, 8(4), 233-238.", "FAO. (2004). Nutritional requirements, feed formulation, and feeding practices for intensive culture of the Red Claw Crayfish Cherax quadricarintus: Reviews in Fisheries Science, 2(1), 1-82.", "Bostock, J., McAndrew, B., & Richards, R. (2010). Aquaculture: Revolution, evolution or  stagnation? International Journal of Sustainable Development & World Ecology,  17(3), 279-288.", "Dos Santos, MF.,(2021). Environmental microbiology in aquaculture:  The  role of water management. Aquaculture Research, 52(9), 4323-4332", "Emerenciano, MA., (2013). Biofloc technology: A new paradigm in  aquaculture. Journal of World Aquaculture Society", "FAO. (2010). Biosecurity and the Role of Responsible Fish Trade in Preventing Aquatic Animal Diseases. Food and Agriculture Organization of the United Nations.", "FAO. (2017). The State of World Fisheries and Aquaculture 2016. Food and Agriculture  Organization of the United Nations.", "Konga, EO., Afolabi, OD., Adedeji, OO., & Fagbenro, OA., (2020). Effect of different feed ingredients on growth performance and survival of Cherax quadricarintus post larvae. Nigerian Journal of Fisheries and Aquaculture,  8(1), 52- 62.", "Naylor, RL., Steinhauer, MW., Jorgensen, S., Sharples, J., & Friedlander, R.  (2009). Food production: Past, present, and future. Philosophical  Transactions  of the", "Royal  Society B: Biological Sciences, 360(1454), 307- 323.", "Heshmatfar, FM., (2023). Meal from Cow skin as an alternative  protein source for shrimp in aquaculture: Growth performance and nutrient  digestibility. Aquaculture Nutrition, 29(2), 467-479.", "Xu, X., & Pan, L. (2012). Development of sustainable aquaculture practices through alternative protein sources. Marine Biology Research, 8(5), 423-430. GANTTCHART Activities 2 0 2 5", "Month(2025) D E C J A N F E B M A R A P R M A Y J U N J U L A U G S E P O C T N O V D E C", "1. Proposal 1.1Brainstormingof Research Topic 1.2FormulationofThesis Title 1.3CompletionofChapter1 to 2 1.4 Thesis Consultation 1.5Acquirefeedbackfor the improvement of the study 1.6RevisionoftheThesis Proposal before defense 1.7ThesisProposalDefense 1.8 Preparation of the needed documents prior to experimentation 1.9Coordinationwiththe involve agencies and group 2.ConductExperiment 2.1 Purchase of Stocks Australian Red Claw Crayfish Juveniles. 2.2Purchase of Chicken wire net; fine mesh net; Nylon 70mm; Nails; PVC pipe; 2.3CollectingBamboos 2.4 Installation of net compartments 2.5 Purchase of Commercial Feeds (PO2) & Preparation ofCow Skin & Shrimp Meal 2.6Acquisitionof stocks 2.7 Acclimatization and Stocking 2.8 Feeds and feeding management 2.9 Routinelyactivities 2.10 Sampling 2.11MonitoringofWater Parameters 2.12 Growth and FCR Evaluation 2.13EvaluationofStocks Survival 2.14Statistical Analysis 2.15 Data Analysis and Interpretation 3.ThesisWriting 3.1 Adviser’s Preliminary Paper Critiquing 3.2 Adviser’s Final Paper Critiquing 4.Final Defense 4.1Tentative Schedule of Final Defense 4.2FinalizationofPaperfor Booking 4.3 Submission of book- bound manuscript Republic of the Philippines CAGAYAN STATE UNIVERSITY College of Fisheries and Aquatic Sciences Maura, Aparri, Cagayan March 7, 2025 ENGR. AUDY R. QUEBRAL., DPA Campus Executive Officer Cagayan State University- Aparri Campus Maura, Aparri, Cagayan Dear Sir, Warmest Greetings! We, the undersigned 3 rd year students of Cagayan State University -Aparri Campus, pursuing of Bachelor of Science in Fisheries from the College of Fisheries and Aquatic Sciences currently taking Thesis 1 this second semester will be conducting our undergraduate thesis study under the field of experimental aquaculture entitled “Growth Performance of Cherax quadricarinatus (Australian RedClaw Crayfish) (Martens, 1768) Juveniles fed with different diets”during this month of March until May 2025. In connection with this, we are writing this letter to ask for your permission to use the CSU-A Aquaculture NCII- Laboratory Area Breeding Tanks to conduct our study. We are hoping for your approval on this request. Thank you and we are looking for your positive response! Respectfully yours, LEIAN CHINE T. SALVADOR JOMARI M. ALBEYAS Student ResearcherStudent Researcher Noted and Further noted by: SALVADOR T. MIRANDA., MSc. QUIRINO G. PASCUA., PhD. Thesis Adviser                                    College Dean Approved by: ENGR. AUDY R. QUEBRAL., DPA. Campus Executive Officer"]
\.


--
-- Data for Name: stud_file_author; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.stud_file_author (id, stud_file_id, user_id, role_label, created_at, updated_at, author_name, "position") FROM stdin;
3	2	\N	\N	2026-10-01 07:29:43	2026-10-01 07:29:43	Juan Dela Cruz	0
4	2	\N	\N	2026-10-01 07:29:44	2026-10-01 07:29:44	Kevin John Garcia	1
5	3	\N	\N	2026-10-01 07:41:58	2026-10-01 07:41:58	Christian Paul Reyes	0
6	3	\N	\N	2026-10-01 07:41:59	2026-10-01 07:41:59	John Carlo Bautista	1
7	4	\N	\N	2026-10-01 07:47:31	2026-10-01 07:47:31	Gabriel James Flores	0
8	4	\N	\N	2026-10-01 07:47:32	2026-10-01 07:47:32	Angelica Mae Villanueva	1
19	1	4	\N	2026-10-04 10:10:58	2026-10-04 10:10:58	Marinette Peralta	0
20	1	\N	\N	2026-10-04 10:10:58	2026-10-04 10:10:58	Ian Sagabaen	1
22	7	4	\N	2026-10-04 10:49:15	2026-10-04 10:49:15	Marinette Peralta	0
\.


--
-- Data for Name: stud_file_keyword; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.stud_file_keyword (id, stud_file_id, keyword_id, source) FROM stdin;
5	2	26	extracted
6	2	25	extracted
7	2	28	extracted
8	2	27	extracted
9	3	37	manual
10	3	38	manual
11	3	69	manual
12	4	17	manual
13	4	70	manual
14	4	18	manual
15	4	71	manual
1	1	45	extracted
2	1	46	extracted
3	1	47	extracted
4	1	53	extracted
20	7	76	manual
21	7	77	manual
\.


--
-- Data for Name: stud_file_sdg; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.stud_file_sdg (id, stud_file_id, sdg_id) FROM stdin;
1	1	4
2	1	6
3	1	9
4	2	3
5	2	7
6	3	4
7	3	8
8	3	14
9	4	4
15	7	1
16	7	2
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.users (id, user_id, role, email, name, year_level, account_status, password, remember_token, created_at, updated_at, email_verification, email_verified_at) FROM stdin;
2	23-10210	student	johnpatrickalbeyeslabinay@gmail.com	John Patrick Labinay	4th year	active	$2y$12$7EuK6h04BE93Z67aKHbPD.Yh1cteFrmIUCfOoTo1qdXV8UpHxAfK.	\N	2026-08-30 00:05:56	2026-10-03 09:37:56	\N	2026-10-03 09:37:53+00
4	23-00001	student	peraltamharinett@gmail.com	Marinette Peralta	4th year	active	$2y$12$U57SNdMeXg0d424JQg1nG.VGEohwiFaj1IhxIjSAoqf2Zlo3VKRLu	\N	2026-09-01 03:00:01	2026-10-03 09:43:01	\N	2026-10-03 09:43:01+00
3	20-24680	instructor	sagabaenianjorgedoblado@gmail.com	Ian Jorge Sagabaen	\N	active	$2y$12$0yiWdW0DFsat99nsGd0bpeoAoQH8AEL8NMIJYVcUqlw.olwmaLVb6	\N	2026-08-30 00:17:22	2026-10-03 09:43:01	\N	2026-10-03 09:43:01+00
1	ADMIN-001	admin	admin@gmail.com	System Administrator	\N	active	$2y$12$s91nfBwYcBJa5vOqJUE0cOAqYHrvJ0N8kT6l9GR8TOHzjIflJoFSG	\N	2026-08-30 07:40:40	2026-10-03 09:43:01	\N	2026-10-03 09:43:01+00
41	00-00004	instructor	jenniferlabinay2@gmail.com	Jennifer Labinay	\N	active	$2y$12$pbIk6c7ILoJgfUWUnYcDkesRozWWKro/RpsfcCXvKrZbt5pCPIGtK	\N	2026-10-01 08:41:58	2026-10-03 09:43:01	\N	2026-10-03 09:43:01+00
42	04-0005	instructor	fisheriescsuadepartment@gmail.com	Liezl P. Dela Cruz	\N	active	$2y$12$LkBeYvm/zJsmDmOvabaF7.ka0qwuIcKhu1lHmc0LgqohX2ecGKjpO	\N	2026-10-01 08:47:58	2026-10-03 09:43:01	\N	2026-10-03 09:43:01+00
43	26-15390	instructor	jadeivanalbeyes@gmail.com	Jade Ivan Labinay	\N	active	$2y$12$uDc240bVbs22qxzACzCpM.KHomk38hktvqmSHQJHIz0lbYumVMXtq	\N	2026-10-03 08:02:20	2026-10-03 09:43:01	\N	2026-10-03 09:43:01+00
\.


--
-- Data for Name: schema_migrations; Type: TABLE DATA; Schema: realtime; Owner: supabase_admin
--

COPY realtime.schema_migrations (version, inserted_at) FROM stdin;
20211116024918	2026-09-15 14:12:45
20211116045059	2026-09-15 14:12:45
20211116050929	2026-09-15 14:12:45
20211116051442	2026-09-15 14:12:45
20211116212300	2026-09-15 14:12:45
20211116213355	2026-09-15 14:12:45
20211116213934	2026-09-15 14:12:45
20211116214523	2026-09-15 14:12:45
20211122062447	2026-09-15 14:12:45
20211124070109	2026-09-15 14:12:45
20211202204204	2026-09-15 14:12:45
20211202204605	2026-09-15 14:12:45
20211210212804	2026-09-15 14:12:45
20211228014915	2026-09-15 14:12:45
20220107221237	2026-09-15 14:12:45
20220228202821	2026-09-15 14:12:45
20220312004840	2026-09-15 14:12:45
20220603231003	2026-09-15 14:12:45
20220603232444	2026-09-15 14:12:45
20220615214548	2026-09-15 14:12:45
20220712093339	2026-09-15 14:12:45
20220908172859	2026-09-15 14:12:45
20220916233421	2026-09-15 14:12:45
20230119133233	2026-09-15 14:12:45
20230128025114	2026-09-15 14:12:45
20230128025212	2026-09-15 14:12:45
20230227211149	2026-09-15 14:12:45
20230228184745	2026-09-15 14:12:45
20230308225145	2026-09-15 14:12:45
20230328144023	2026-09-15 14:12:45
20231018144023	2026-09-15 14:12:45
20231204144023	2026-09-15 14:12:45
20231204144024	2026-09-15 14:12:45
20231204144025	2026-09-15 14:12:45
20240108234812	2026-09-15 14:12:45
20240109165339	2026-09-15 14:12:45
20240227174441	2026-09-15 14:12:45
20240311171622	2026-09-15 14:12:45
20240321100241	2026-09-15 14:12:45
20240401105812	2026-09-15 14:12:45
20240418121054	2026-09-15 14:12:45
20240523004032	2026-09-15 14:12:45
20240618124746	2026-09-15 14:12:45
20240801235015	2026-09-15 14:12:45
20240805133720	2026-09-15 14:12:45
20240827160934	2026-09-15 14:12:45
20240919163303	2026-09-15 14:12:45
20240919163305	2026-09-15 14:12:45
20241019105805	2026-09-15 14:12:45
20241030150047	2026-09-15 14:12:45
20241108114728	2026-09-15 14:12:45
20241121104152	2026-09-15 14:12:45
20241130184212	2026-09-15 14:12:45
20241220035512	2026-09-15 14:12:45
20241220123912	2026-09-15 14:12:45
20241224161212	2026-09-15 14:12:45
20250107150512	2026-09-15 14:12:45
20250110162412	2026-09-15 14:12:45
20250123174212	2026-09-15 14:12:45
20250128220012	2026-09-15 14:12:45
20250506224012	2026-09-15 14:12:45
20250523164012	2026-09-15 14:12:45
20250714121412	2026-09-15 14:12:45
20250905041441	2026-09-15 14:12:45
20251103001201	2026-09-15 14:12:45
20251120212548	2026-09-15 14:12:45
20251120215549	2026-09-15 14:12:45
20260218120000	2026-09-15 14:12:45
20260326120000	2026-09-15 14:12:45
20260514120000	2026-09-15 14:12:45
20260527120000	2026-09-15 14:12:45
20260528120000	2026-09-15 14:12:45
20260603120000	2026-09-15 14:12:45
20260605120000	2026-09-15 14:12:45
20260606110000	2026-09-15 14:12:45
20260616120000	2026-09-15 14:12:45
20260624120000	2026-09-15 14:12:45
20260626120000	2026-09-15 14:12:45
20260706120000	2026-09-15 14:12:45
20260707120000	2026-09-15 14:12:45
20260709120000	2026-09-15 14:12:45
20260714120000	2026-09-15 14:12:45
20260827120000	2026-09-16 13:18:36
20260914120000	2026-09-22 05:06:43
20260916120000	2026-09-22 05:06:44
20260922120000	2026-09-24 05:47:49
20260925120000	2026-09-30 04:38:09
20260928120000	2026-10-05 06:16:50
\.


--
-- Data for Name: subscription; Type: TABLE DATA; Schema: realtime; Owner: supabase_realtime_admin
--

COPY realtime.subscription (id, subscription_id, entity, filters, claims, created_at, action_filter, selected_columns) FROM stdin;
\.


--
-- Data for Name: buckets; Type: TABLE DATA; Schema: storage; Owner: supabase_storage_admin
--

COPY storage.buckets (id, name, owner, created_at, updated_at, public, avif_autodetection, file_size_limit, allowed_mime_types, owner_id, type, versioning_status, lifecycle_configuration, lifecycle_configuration_generation) FROM stdin;
research-files	research-files	\N	2026-09-16 13:28:23.072751+00	2026-09-16 13:28:23.072751+00	f	f	\N	\N	\N	STANDARD	DISABLED	\N	\N
\.


--
-- Data for Name: buckets_analytics; Type: TABLE DATA; Schema: storage; Owner: supabase_storage_admin
--

COPY storage.buckets_analytics (name, type, format, created_at, updated_at, id, deleted_at) FROM stdin;
\.


--
-- Data for Name: buckets_vectors; Type: TABLE DATA; Schema: storage; Owner: supabase_storage_admin
--

COPY storage.buckets_vectors (id, type, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: migrations; Type: TABLE DATA; Schema: storage; Owner: supabase_storage_admin
--

COPY storage.migrations (id, name, hash, executed_at) FROM stdin;
0	create-migrations-table	e18db593bcde2aca2a408c4d1100f6abba2195df	2026-09-15 13:43:55.553986
1	initialmigration	6ab16121fbaa08bbd11b712d05f358f9b555d777	2026-09-15 13:43:55.599782
2	storage-schema	f6a1fa2c93cbcd16d4e487b362e45fca157a8dbd	2026-09-15 13:43:55.60963
3	pathtoken-column	2cb1b0004b817b29d5b0a971af16bafeede4b70d	2026-09-15 13:43:55.642351
4	add-migrations-rls	427c5b63fe1c5937495d9c635c263ee7a5905058	2026-09-15 13:43:55.661095
5	add-size-functions	79e081a1455b63666c1294a440f8ad4b1e6a7f84	2026-09-15 13:43:55.67101
6	change-column-name-in-get-size	ded78e2f1b5d7e616117897e6443a925965b30d2	2026-09-15 13:43:55.681492
7	add-rls-to-buckets	e7e7f86adbc51049f341dfe8d30256c1abca17aa	2026-09-15 13:43:55.692059
8	add-public-to-buckets	fd670db39ed65f9d08b01db09d6202503ca2bab3	2026-09-15 13:43:55.70196
9	fix-search-function	af597a1b590c70519b464a4ab3be54490712796b	2026-09-15 13:43:55.712214
10	search-files-search-function	b595f05e92f7e91211af1bbfe9c6a13bb3391e16	2026-09-15 13:43:55.722581
11	add-trigger-to-auto-update-updated_at-column	7425bdb14366d1739fa8a18c83100636d74dcaa2	2026-09-15 13:43:55.73312
12	add-automatic-avif-detection-flag	8e92e1266eb29518b6a4c5313ab8f29dd0d08df9	2026-09-15 13:43:55.743809
13	add-bucket-custom-limits	cce962054138135cd9a8c4bcd531598684b25e7d	2026-09-15 13:43:55.754015
14	use-bytes-for-max-size	941c41b346f9802b411f06f30e972ad4744dad27	2026-09-15 13:43:55.764433
15	add-can-insert-object-function	934146bc38ead475f4ef4b555c524ee5d66799e5	2026-09-15 13:43:55.797259
16	add-version	76debf38d3fd07dcfc747ca49096457d95b1221b	2026-09-15 13:43:55.807478
17	drop-owner-foreign-key	f1cbb288f1b7a4c1eb8c38504b80ae2a0153d101	2026-09-15 13:43:55.817408
18	add_owner_id_column_deprecate_owner	e7a511b379110b08e2f214be852c35414749fe66	2026-09-15 13:43:55.829023
19	alter-default-value-objects-id	02e5e22a78626187e00d173dc45f58fa66a4f043	2026-09-15 13:43:55.84062
20	list-objects-with-delimiter	cd694ae708e51ba82bf012bba00caf4f3b6393b7	2026-09-15 13:43:55.852521
21	s3-multipart-uploads	8c804d4a566c40cd1e4cc5b3725a664a9303657f	2026-09-15 13:43:55.865339
22	s3-multipart-uploads-big-ints	9737dc258d2397953c9953d9b86920b8be0cdb73	2026-09-15 13:43:55.886948
23	optimize-search-function	9d7e604cddc4b56a5422dc68c9313f4a1b6f132c	2026-09-15 13:43:55.903154
24	operation-function	8312e37c2bf9e76bbe841aa5fda889206d2bf8aa	2026-09-15 13:43:55.913839
25	custom-metadata	d974c6057c3db1c1f847afa0e291e6165693b990	2026-09-15 13:43:55.923909
26	objects-prefixes	215cabcb7f78121892a5a2037a09fedf9a1ae322	2026-09-15 13:43:55.934151
27	search-v2	859ba38092ac96eb3964d83bf53ccc0b141663a6	2026-09-15 13:43:55.943943
28	object-bucket-name-sorting	c73a2b5b5d4041e39705814fd3a1b95502d38ce4	2026-09-15 13:43:55.953422
29	create-prefixes	ad2c1207f76703d11a9f9007f821620017a66c21	2026-09-15 13:43:55.96306
30	update-object-levels	2be814ff05c8252fdfdc7cfb4b7f5c7e17f0bed6	2026-09-15 13:43:55.972612
31	objects-level-index	b40367c14c3440ec75f19bbce2d71e914ddd3da0	2026-09-15 13:43:55.983145
32	backward-compatible-index-on-objects	e0c37182b0f7aee3efd823298fb3c76f1042c0f7	2026-09-15 13:43:55.993089
33	backward-compatible-index-on-prefixes	b480e99ed951e0900f033ec4eb34b5bdcb4e3d49	2026-09-15 13:43:56.003494
34	optimize-search-function-v1	ca80a3dc7bfef894df17108785ce29a7fc8ee456	2026-09-15 13:43:56.013749
35	add-insert-trigger-prefixes	458fe0ffd07ec53f5e3ce9df51bfdf4861929ccc	2026-09-15 13:43:56.023438
36	optimise-existing-functions	6ae5fca6af5c55abe95369cd4f93985d1814ca8f	2026-09-15 13:43:56.033141
37	add-bucket-name-length-trigger	3944135b4e3e8b22d6d4cbb568fe3b0b51df15c1	2026-09-15 13:43:56.042811
38	iceberg-catalog-flag-on-buckets	02716b81ceec9705aed84aa1501657095b32e5c5	2026-09-15 13:43:56.053296
39	add-search-v2-sort-support	6706c5f2928846abee18461279799ad12b279b78	2026-09-15 13:43:56.070877
40	fix-prefix-race-conditions-optimized	7ad69982ae2d372b21f48fc4829ae9752c518f6b	2026-09-15 13:43:56.080249
41	add-object-level-update-trigger	07fcf1a22165849b7a029deed059ffcde08d1ae0	2026-09-15 13:43:56.089645
42	rollback-prefix-triggers	771479077764adc09e2ea2043eb627503c034cd4	2026-09-15 13:43:56.099192
43	fix-object-level	84b35d6caca9d937478ad8a797491f38b8c2979f	2026-09-15 13:43:56.108759
44	vector-bucket-type	99c20c0ffd52bb1ff1f32fb992f3b351e3ef8fb3	2026-09-15 13:43:56.118608
45	vector-buckets	049e27196d77a7cb76497a85afae669d8b230953	2026-09-15 13:43:56.129244
46	buckets-objects-grants	fedeb96d60fefd8e02ab3ded9fbde05632f84aed	2026-09-15 13:43:56.14698
47	iceberg-table-metadata	649df56855c24d8b36dd4cc1aeb8251aa9ad42c2	2026-09-15 13:43:56.157105
48	iceberg-catalog-ids	e0e8b460c609b9999ccd0df9ad14294613eed939	2026-09-15 13:43:56.167006
49	buckets-objects-grants-postgres	072b1195d0d5a2f888af6b2302a1938dd94b8b3d	2026-09-15 13:43:56.189887
50	search-v2-optimised	6323ac4f850aa14e7387eb32102869578b5bd478	2026-09-15 13:43:56.20242
51	index-backward-compatible-search	2ee395d433f76e38bcd3856debaf6e0e5b674011	2026-09-15 13:43:56.421062
52	drop-not-used-indexes-and-functions	5cc44c8696749ac11dd0dc37f2a3802075f3a171	2026-09-15 13:43:56.424536
53	drop-index-lower-name	d0cb18777d9e2a98ebe0bc5cc7a42e57ebe41854	2026-09-15 13:43:56.441693
54	drop-index-object-level	6289e048b1472da17c31a7eba1ded625a6457e67	2026-09-15 13:43:56.447602
55	prevent-direct-deletes	262a4798d5e0f2e7c8970232e03ce8be695d5819	2026-09-15 13:43:56.45091
56	fix-optimized-search-function	b823ed1e418101032fa01374edc9a436e54e3ed4	2026-09-15 13:43:56.461851
57	s3-multipart-uploads-metadata	f127886e00d1b374fadbc7c6b31e09336aad5287	2026-09-15 13:43:56.473203
58	operation-ergonomics	00ca5d483b3fe0d522133d9002ccc5df98365120	2026-09-15 13:43:56.483248
59	drop-unused-functions	38456f13e39691c2bbb4b5151d0d1cdbabd4a8c4	2026-09-15 13:43:56.493922
60	optimize-existing-functions-again	db35e1c91a9201e59f4fef8d972c2f277d68b157	2026-09-15 13:43:56.504229
61	mark-filename-immutable	fe0096517ae9d60aaec1d110172ba9036dc66bb7	2026-09-15 13:43:56.514771
62	object-versioning-core	0b855f00ff3be0bfca91efee02a9858912491a9a	2026-09-15 13:43:56.525155
63	fix-search-name-relative-to-prefix	c7485e417624f795ce8bb2da21927f48e088904d	2026-09-15 13:43:56.538839
64	fix-search-by-timestamp-sqli	0af424ecd388a39bb1645184b222185a12149675	2026-09-15 13:43:56.549964
66	objects-current-version-index	191466c93aa2c46a00e36505577c5fcab8d7cb4b	2026-09-15 13:43:57.093234
67	objects-null-version-index	15bfe8c35b66642b6c78ba60060fa8793bd2207a	2026-09-15 13:43:57.106263
65	objects-key-version-index	da319c4b89ba800ce795d1b699f3a70675138058	2026-09-15 13:43:57.080517
68	bucket-lifecycle-configuration	3c08f6f889922f399519722a932b51007c11bebc	2026-09-22 04:22:04.478022
69	validate-bucket-lifecycle-constraints	4febacaaaa0e61e2b783bef081fe03a287e65eb3	2026-09-22 04:22:04.559019
70	list-objects-with-versions	5c17c3777616cd8d7b18b82835525fa3205af57b	2026-09-22 04:22:04.573559
71	objects-delete-marker-index	6d14858e66c66f8d6accf8a2630aefd1527fddba	2026-09-22 04:22:04.652413
72	drop-bucketid-objname-index	302beb09e1b469d7d4db19566f2389d280b64aa3	2026-09-22 04:22:04.674103
\.


--
-- Data for Name: objects; Type: TABLE DATA; Schema: storage; Owner: supabase_storage_admin
--

COPY storage.objects (id, bucket_id, name, owner, created_at, updated_at, last_accessed_at, metadata, version, owner_id, user_metadata, archived_at, is_delete_marker, is_versioned) FROM stdin;
07ce54c1-b77e-4833-bcf3-5d7b1867b3dd	research-files	research_files/79cfb108f8a847ac2a9f72c717b048e6e659eb81422a05da1ec0bd5444aee01b.pdf	\N	2026-09-19 04:56:19.967961+00	2026-09-19 04:56:19.967961+00	2026-09-19 04:56:19.967961+00	{"eTag": "\\"330d509e6d7fde3c719808461a6ecf81\\"", "size": 4482819, "mimetype": "application/pdf", "cacheControl": "no-cache", "lastModified": "2026-09-19T04:56:20.000Z", "contentLength": 4482819, "httpStatusCode": 200}	f3266075-73b3-4807-a771-2eb7e1ddfa0c	\N	{}	\N	f	f
90b1f901-0c8a-42cf-ba43-49c60856945c	research-files	research_files/dc6bbf80a3c534e7ccedd6d365e66f351d50a0d4a676e8117195e025fb30c142.pdf	\N	2026-09-22 04:21:59.45256+00	2026-09-22 04:21:59.45256+00	2026-09-22 04:21:59.45256+00	{"eTag": "\\"462348c05161a041f7a3c0e479d74576\\"", "size": 157658, "mimetype": "application/pdf", "cacheControl": "no-cache", "lastModified": "2026-09-22T04:22:00.000Z", "contentLength": 157658, "httpStatusCode": 200}	fe644ce1-9104-4f3c-b447-f0abba42f5fe	\N	{}	\N	f	f
ff3ae41c-692b-4ffd-8efd-7ce401f685fc	research-files	research_files/f64413a1843661e655527125dfb31a53139fde3c31a260e2e59c6a281c41544c.pdf	\N	2026-09-23 15:06:44.341987+00	2026-09-23 15:06:44.341987+00	2026-09-23 15:06:44.341987+00	{"eTag": "\\"021e566ad6ebf35c05f7f0ac10737102\\"", "size": 731076, "mimetype": "application/pdf", "cacheControl": "no-cache", "lastModified": "2026-09-23T15:06:45.000Z", "contentLength": 731076, "httpStatusCode": 200}	ebb887e7-c10e-4772-8f76-8f2323381add	\N	{}	\N	f	f
8a8248e9-8b41-4689-8de8-27953fdeee2f	research-files	research_files/c3dd1509c19bc6c3f1366d6b44d75b6115e3d3d45aa5d353e563eb8d7686fdcd.pdf	\N	2026-09-24 08:21:53.374747+00	2026-09-24 08:21:53.374747+00	2026-09-24 08:21:53.374747+00	{"eTag": "\\"d197553298537ea63d726cb064f13882\\"", "size": 197684, "mimetype": "application/pdf", "cacheControl": "no-cache", "lastModified": "2026-09-24T08:21:54.000Z", "contentLength": 197684, "httpStatusCode": 200}	0735837c-7ed0-4c8b-87f9-a64c9fba3970	\N	{}	\N	f	f
10ba3924-d0f1-41ef-9a49-4abe34d859d7	research-files	research_files/c4668dc618cfa428ae840ce6ba94800981824084a714817d96c96e00755cee8e.pdf	\N	2026-09-24 09:29:14.94006+00	2026-09-24 09:29:14.94006+00	2026-09-24 09:29:14.94006+00	{"eTag": "\\"77e823b2c3271f78c21097ea5d46c35a\\"", "size": 138127, "mimetype": "application/pdf", "cacheControl": "no-cache", "lastModified": "2026-09-24T09:29:15.000Z", "contentLength": 138127, "httpStatusCode": 200}	0b26b341-d7d8-4fe1-afad-d70e90509780	\N	{}	\N	f	f
4888e8cf-5428-4f27-a955-5b86ba484d0e	research-files	research_files/2a6075e5b8de2cce0a1bacebfbadca850bca236a7d00a7db535b1ae38761d948.pdf	\N	2026-09-25 03:42:26.407972+00	2026-09-25 03:42:26.407972+00	2026-09-25 03:42:26.407972+00	{"eTag": "\\"6422a413f7232530cdd5f82c5d79fab1\\"", "size": 260372, "mimetype": "application/pdf", "cacheControl": "no-cache", "lastModified": "2026-09-25T03:42:27.000Z", "contentLength": 260372, "httpStatusCode": 200}	821bbb81-3c3f-4cc1-8d82-4c4cf48fd852	\N	{}	\N	f	f
c197b399-69a4-4964-84d9-dde8e714e1e9	research-files	research_files/622528cc66f31b81c5e8ddbc303b1747ac916a4a9d24cf04ee832b19c8625daf.pdf	\N	2026-10-01 14:31:24.259038+00	2026-10-01 14:31:24.259038+00	2026-10-01 14:31:24.259038+00	{"eTag": "\\"039f26c05eb48c8ac8460f1d6f6ec40d\\"", "size": 451170, "mimetype": "application/pdf", "cacheControl": "no-cache", "lastModified": "2026-10-01T14:31:25.000Z", "contentLength": 451170, "httpStatusCode": 200}	8c9149bb-b8c9-4527-948c-f3647abbcbcf	\N	{}	\N	f	f
\.


--
-- Data for Name: s3_multipart_uploads; Type: TABLE DATA; Schema: storage; Owner: supabase_storage_admin
--

COPY storage.s3_multipart_uploads (id, in_progress_size, upload_signature, bucket_id, key, version, owner_id, created_at, user_metadata, metadata) FROM stdin;
\.


--
-- Data for Name: s3_multipart_uploads_parts; Type: TABLE DATA; Schema: storage; Owner: supabase_storage_admin
--

COPY storage.s3_multipart_uploads_parts (id, upload_id, size, part_number, bucket_id, key, etag, owner_id, version, created_at) FROM stdin;
\.


--
-- Data for Name: vector_indexes; Type: TABLE DATA; Schema: storage; Owner: supabase_storage_admin
--

COPY storage.vector_indexes (id, name, bucket_id, data_type, dimension, distance_metric, metadata_configuration, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: schema_migrations; Type: TABLE DATA; Schema: supabase_migrations; Owner: postgres
--

COPY supabase_migrations.schema_migrations (version, statements, name, created_by, idempotency_key, rollback) FROM stdin;
20260915143633	{"-- Laravel framework tables\nCREATE TABLE migrations (\n    id serial PRIMARY KEY,\n    migration varchar(255) NOT NULL,\n    batch integer NOT NULL\n);\n\nCREATE TABLE sessions (\n    id varchar(255) PRIMARY KEY,\n    user_id bigint NULL,\n    ip_address varchar(45) NULL,\n    user_agent text NULL,\n    payload text NOT NULL,\n    last_activity integer NOT NULL\n);\nCREATE INDEX sessions_user_id_index ON sessions (user_id);\nCREATE INDEX sessions_last_activity_index ON sessions (last_activity);\n\nCREATE TABLE cache (\n    key varchar(255) PRIMARY KEY,\n    value text NOT NULL,\n    expiration integer NOT NULL\n);\nCREATE INDEX cache_expiration_index ON cache (expiration);\n\nCREATE TABLE cache_locks (\n    key varchar(255) PRIMARY KEY,\n    owner varchar(255) NOT NULL,\n    expiration integer NOT NULL\n);\nCREATE INDEX cache_locks_expiration_index ON cache_locks (expiration);\n\nCREATE TABLE users (\n    id bigserial PRIMARY KEY,\n    user_id varchar(255) NOT NULL,\n    role varchar(255) NOT NULL,\n    email varchar(250) NOT NULL,\n    name varchar(255) NOT NULL,\n    year_level varchar(255) NULL,\n    account_status varchar(255) NOT NULL DEFAULT 'active',\n    password varchar(255) NOT NULL,\n    remember_token varchar(100) NULL,\n    created_at timestamp NULL,\n    updated_at timestamp NULL\n);\nCREATE UNIQUE INDEX users_user_id_unique ON users (user_id);\nCREATE UNIQUE INDEX users_email_unique ON users (email);\n\nCREATE TABLE password_reset_tokens (\n    email varchar(255) PRIMARY KEY,\n    token varchar(255) NOT NULL,\n    created_at timestamp NULL\n);\n\nCREATE TABLE jobs (\n    id bigserial PRIMARY KEY,\n    queue varchar(255) NOT NULL,\n    payload text NOT NULL,\n    attempts smallint NOT NULL,\n    reserved_at integer NULL,\n    available_at integer NOT NULL,\n    created_at integer NOT NULL\n);\nCREATE INDEX jobs_queue_reserved_at_index ON jobs (queue, reserved_at);\n\nCREATE TABLE job_batches (\n    id varchar(255) PRIMARY KEY,\n    name varchar(255) NOT NULL,\n    total_jobs integer NOT NULL,\n    pending_jobs integer NOT NULL,\n    failed_jobs integer NOT NULL,\n    failed_job_ids text NOT NULL,\n    options text NULL,\n    cancelled_at integer NULL,\n    created_at integer NOT NULL,\n    finished_at integer NULL\n);\n\nCREATE TABLE failed_jobs (\n    id bigserial PRIMARY KEY,\n    uuid varchar(255) NOT NULL UNIQUE,\n    connection text NOT NULL,\n    queue text NOT NULL,\n    payload text NOT NULL,\n    exception text NOT NULL,\n    failed_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP\n);\n\n-- App tables\nCREATE TABLE sdgs (\n    id bigserial PRIMARY KEY,\n    number smallint NOT NULL,\n    created_at timestamp NULL,\n    updated_at timestamp NULL\n);\nCREATE UNIQUE INDEX sdgs_number_unique ON sdgs (number);\n\nCREATE TABLE stud_file (\n    id bigserial PRIMARY KEY,\n    user_id bigint NOT NULL REFERENCES users (id) ON DELETE CASCADE,\n    title varchar(255) NOT NULL,\n    name varchar(255) NOT NULL,\n    adviser varchar(255) NULL,\n    \\"desc\\" text NULL,\n    file_path varchar(255) NOT NULL,\n    auto_tag varchar(255) NULL,\n    status varchar(255) NOT NULL DEFAULT 'pending',\n    visibility varchar(255) NOT NULL DEFAULT 'private',\n    requested_visibility varchar(255) NOT NULL DEFAULT 'private',\n    views integer NOT NULL DEFAULT 0,\n    downloads integer NOT NULL DEFAULT 0,\n    created_at timestamp NULL,\n    updated_at timestamp NULL\n);\nCREATE INDEX stud_file_user_id_foreign ON stud_file (user_id);\n\nCREATE TABLE stud_file_sdg (\n    id bigserial PRIMARY KEY,\n    stud_file_id bigint NOT NULL REFERENCES stud_file (id) ON DELETE CASCADE,\n    sdg_id bigint NOT NULL REFERENCES sdgs (id) ON DELETE CASCADE\n);\nCREATE UNIQUE INDEX stud_file_sdg_stud_file_id_sdg_id_unique ON stud_file_sdg (stud_file_id, sdg_id);\nCREATE INDEX stud_file_sdg_sdg_id_foreign ON stud_file_sdg (sdg_id);\n\n-- Mark Laravel migrations as already applied (schema above matches them 1:1)\nINSERT INTO migrations (migration, batch) VALUES\n('2026_07_12_045109_create_sessions_table', 1),\n('2026_07_12_045259_create_cache_table', 1),\n('2026_07_12_045942_create_users_table', 1),\n('2026_07_12_045957_create_password_reset_tokens_table', 1),\n('2026_07_12_050006_create_jobs_table', 1),\n('2026_07_12_050021_create_stud_file_table', 1),\n('2026_08_28_065859_add_requested_visibility_to_stud_file_table', 2),\n('2026_09_09_095915_create_sdgs_table', 3),\n('2026_09_09_102602_create_stud_file_sdg_table', 4),\n('2026_09_15_223530_add_email_and_account_status_to_users_table', 5),\n('2026_09_15_223531_add_status_visibility_views_to_stud_file_table', 5);\n"}	initial_schema_from_laravel_migrations	box.devvv@gmail.com	\N	\N
20260916132823	{"-- Private bucket for research PDFs, accessed exclusively by the Laravel\n-- backend via S3-protocol credentials (dedicated access key, generated in\n-- the dashboard under Storage > S3 Connection). End users never talk to\n-- Supabase Storage directly — every read/write goes through Laravel's own\n-- authorization logic (StudentController::authorizeFileAccess()), so this\n-- bucket only needs a single backend-scoped policy, not per-user RLS.\ninsert into storage.buckets (id, name, public)\nvalues ('research-files', 'research-files', false)\non conflict (id) do nothing;\n\ncreate policy \\"research-files backend full access\\"\non storage.objects\nfor all\nusing (bucket_id = 'research-files')\nwith check (bucket_id = 'research-files');\n"}	create_research_files_storage_bucket	box.devvv@gmail.com	\N	\N
20260927125305	{"create index if not exists stud_file_user_id_status_index on stud_file (user_id, status);\ncreate index if not exists stud_file_adviser_id_status_index on stud_file (adviser_id, status);\ncreate index if not exists stud_file_status_created_at_index on stud_file (status, created_at);\ncreate index if not exists stud_file_sdg_sdg_id_index on stud_file_sdg (sdg_id);\ncreate index if not exists stud_file_author_user_id_index on stud_file_author (user_id);\ninsert into migrations (migration, batch)\nselect '2026_09_27_000000_add_performance_indexes', 20\nwhere not exists (select 1 from migrations where migration = '2026_09_27_000000_add_performance_indexes');"}	add_performance_indexes	box.devvv@gmail.com	\N	\N
\.


--
-- Data for Name: secrets; Type: TABLE DATA; Schema: vault; Owner: supabase_admin
--

COPY vault.secrets (id, name, description, secret, key_id, nonce, created_at, updated_at) FROM stdin;
\.


--
-- Name: refresh_tokens_id_seq; Type: SEQUENCE SET; Schema: auth; Owner: supabase_auth_admin
--

SELECT pg_catalog.setval('auth.refresh_tokens_id_seq', 1, false);


--
-- Name: ai_usage_logs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.ai_usage_logs_id_seq', 131, true);


--
-- Name: backups_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.backups_id_seq', 1, false);


--
-- Name: chat_conversations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.chat_conversations_id_seq', 6, true);


--
-- Name: chat_messages_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.chat_messages_id_seq', 16, true);


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.failed_jobs_id_seq', 1, false);


--
-- Name: jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.jobs_id_seq', 45, true);


--
-- Name: keywords_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.keywords_id_seq', 77, true);


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.migrations_id_seq', 33, true);


--
-- Name: research_access_requests_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.research_access_requests_id_seq', 48, true);


--
-- Name: research_chunks_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.research_chunks_id_seq', 1, false);


--
-- Name: research_feedback_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.research_feedback_id_seq', 1, false);


--
-- Name: sdgs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.sdgs_id_seq', 17, true);


--
-- Name: search_logs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.search_logs_id_seq', 22, true);


--
-- Name: stud_file_author_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.stud_file_author_id_seq', 22, true);


--
-- Name: stud_file_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.stud_file_id_seq', 7, true);


--
-- Name: stud_file_keyword_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.stud_file_keyword_id_seq', 21, true);


--
-- Name: stud_file_sdg_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.stud_file_sdg_id_seq', 16, true);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.users_id_seq', 43, true);


--
-- Name: subscription_id_seq; Type: SEQUENCE SET; Schema: realtime; Owner: supabase_realtime_admin
--

SELECT pg_catalog.setval('realtime.subscription_id_seq', 1, false);


--
-- Name: mfa_amr_claims amr_id_pk; Type: CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.mfa_amr_claims
    ADD CONSTRAINT amr_id_pk PRIMARY KEY (id);


--
-- Name: audit_log_entries audit_log_entries_pkey; Type: CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.audit_log_entries
    ADD CONSTRAINT audit_log_entries_pkey PRIMARY KEY (id);


--
-- Name: custom_oauth_providers custom_oauth_providers_identifier_key; Type: CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.custom_oauth_providers
    ADD CONSTRAINT custom_oauth_providers_identifier_key UNIQUE (identifier);


--
-- Name: custom_oauth_providers custom_oauth_providers_pkey; Type: CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.custom_oauth_providers
    ADD CONSTRAINT custom_oauth_providers_pkey PRIMARY KEY (id);


--
-- Name: flow_state flow_state_pkey; Type: CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.flow_state
    ADD CONSTRAINT flow_state_pkey PRIMARY KEY (id);


--
-- Name: identities identities_pkey; Type: CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.identities
    ADD CONSTRAINT identities_pkey PRIMARY KEY (id);


--
-- Name: identities identities_provider_id_provider_unique; Type: CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.identities
    ADD CONSTRAINT identities_provider_id_provider_unique UNIQUE (provider_id, provider);


--
-- Name: instances instances_pkey; Type: CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.instances
    ADD CONSTRAINT instances_pkey PRIMARY KEY (id);


--
-- Name: mfa_amr_claims mfa_amr_claims_session_id_authentication_method_pkey; Type: CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.mfa_amr_claims
    ADD CONSTRAINT mfa_amr_claims_session_id_authentication_method_pkey UNIQUE (session_id, authentication_method);


--
-- Name: mfa_challenges mfa_challenges_pkey; Type: CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.mfa_challenges
    ADD CONSTRAINT mfa_challenges_pkey PRIMARY KEY (id);


--
-- Name: mfa_factors mfa_factors_last_challenged_at_key; Type: CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.mfa_factors
    ADD CONSTRAINT mfa_factors_last_challenged_at_key UNIQUE (last_challenged_at);


--
-- Name: mfa_factors mfa_factors_pkey; Type: CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.mfa_factors
    ADD CONSTRAINT mfa_factors_pkey PRIMARY KEY (id);


--
-- Name: mfa_recovery_code_sets mfa_recovery_code_sets_mfa_factor_id_key; Type: CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.mfa_recovery_code_sets
    ADD CONSTRAINT mfa_recovery_code_sets_mfa_factor_id_key UNIQUE (mfa_factor_id);


--
-- Name: mfa_recovery_code_sets mfa_recovery_code_sets_pkey; Type: CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.mfa_recovery_code_sets
    ADD CONSTRAINT mfa_recovery_code_sets_pkey PRIMARY KEY (id);


--
-- Name: mfa_recovery_code_sets mfa_recovery_code_sets_user_id_key; Type: CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.mfa_recovery_code_sets
    ADD CONSTRAINT mfa_recovery_code_sets_user_id_key UNIQUE (user_id);


--
-- Name: mfa_recovery_codes mfa_recovery_codes_pkey; Type: CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.mfa_recovery_codes
    ADD CONSTRAINT mfa_recovery_codes_pkey PRIMARY KEY (id);


--
-- Name: oauth_authorizations oauth_authorizations_authorization_code_key; Type: CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.oauth_authorizations
    ADD CONSTRAINT oauth_authorizations_authorization_code_key UNIQUE (authorization_code);


--
-- Name: oauth_authorizations oauth_authorizations_authorization_id_key; Type: CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.oauth_authorizations
    ADD CONSTRAINT oauth_authorizations_authorization_id_key UNIQUE (authorization_id);


--
-- Name: oauth_authorizations oauth_authorizations_pkey; Type: CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.oauth_authorizations
    ADD CONSTRAINT oauth_authorizations_pkey PRIMARY KEY (id);


--
-- Name: oauth_client_states oauth_client_states_pkey; Type: CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.oauth_client_states
    ADD CONSTRAINT oauth_client_states_pkey PRIMARY KEY (id);


--
-- Name: oauth_clients oauth_clients_pkey; Type: CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.oauth_clients
    ADD CONSTRAINT oauth_clients_pkey PRIMARY KEY (id);


--
-- Name: oauth_consents oauth_consents_pkey; Type: CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.oauth_consents
    ADD CONSTRAINT oauth_consents_pkey PRIMARY KEY (id);


--
-- Name: oauth_consents oauth_consents_user_client_unique; Type: CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.oauth_consents
    ADD CONSTRAINT oauth_consents_user_client_unique UNIQUE (user_id, client_id);


--
-- Name: one_time_tokens one_time_tokens_pkey; Type: CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.one_time_tokens
    ADD CONSTRAINT one_time_tokens_pkey PRIMARY KEY (id);


--
-- Name: refresh_tokens refresh_tokens_pkey; Type: CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.refresh_tokens
    ADD CONSTRAINT refresh_tokens_pkey PRIMARY KEY (id);


--
-- Name: refresh_tokens refresh_tokens_token_unique; Type: CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.refresh_tokens
    ADD CONSTRAINT refresh_tokens_token_unique UNIQUE (token);


--
-- Name: saml_providers saml_providers_entity_id_key; Type: CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.saml_providers
    ADD CONSTRAINT saml_providers_entity_id_key UNIQUE (entity_id);


--
-- Name: saml_providers saml_providers_pkey; Type: CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.saml_providers
    ADD CONSTRAINT saml_providers_pkey PRIMARY KEY (id);


--
-- Name: saml_relay_states saml_relay_states_pkey; Type: CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.saml_relay_states
    ADD CONSTRAINT saml_relay_states_pkey PRIMARY KEY (id);


--
-- Name: schema_migrations schema_migrations_pkey; Type: CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.schema_migrations
    ADD CONSTRAINT schema_migrations_pkey PRIMARY KEY (version);


--
-- Name: scim_tokens scim_tokens_pkey; Type: CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.scim_tokens
    ADD CONSTRAINT scim_tokens_pkey PRIMARY KEY (id);


--
-- Name: scim_users scim_users_pkey; Type: CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.scim_users
    ADD CONSTRAINT scim_users_pkey PRIMARY KEY (id);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: sso_domains sso_domains_pkey; Type: CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.sso_domains
    ADD CONSTRAINT sso_domains_pkey PRIMARY KEY (id);


--
-- Name: sso_providers sso_providers_pkey; Type: CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.sso_providers
    ADD CONSTRAINT sso_providers_pkey PRIMARY KEY (id);


--
-- Name: users users_phone_key; Type: CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.users
    ADD CONSTRAINT users_phone_key UNIQUE (phone);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: webauthn_challenges webauthn_challenges_pkey; Type: CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.webauthn_challenges
    ADD CONSTRAINT webauthn_challenges_pkey PRIMARY KEY (id);


--
-- Name: webauthn_credentials webauthn_credentials_pkey; Type: CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.webauthn_credentials
    ADD CONSTRAINT webauthn_credentials_pkey PRIMARY KEY (id);


--
-- Name: ai_usage_logs ai_usage_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ai_usage_logs
    ADD CONSTRAINT ai_usage_logs_pkey PRIMARY KEY (id);


--
-- Name: backups backups_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.backups
    ADD CONSTRAINT backups_pkey PRIMARY KEY (id);


--
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: chat_conversations chat_conversations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.chat_conversations
    ADD CONSTRAINT chat_conversations_pkey PRIMARY KEY (id);


--
-- Name: chat_messages chat_messages_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.chat_messages
    ADD CONSTRAINT chat_messages_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_uuid_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_key UNIQUE (uuid);


--
-- Name: job_batches job_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.job_batches
    ADD CONSTRAINT job_batches_pkey PRIMARY KEY (id);


--
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- Name: keywords keywords_name_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.keywords
    ADD CONSTRAINT keywords_name_unique UNIQUE (name);


--
-- Name: keywords keywords_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.keywords
    ADD CONSTRAINT keywords_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: notifications notifications_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT notifications_pkey PRIMARY KEY (id);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- Name: research_access_requests research_access_requests_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.research_access_requests
    ADD CONSTRAINT research_access_requests_pkey PRIMARY KEY (id);


--
-- Name: research_chunks research_chunks_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.research_chunks
    ADD CONSTRAINT research_chunks_pkey PRIMARY KEY (id);


--
-- Name: research_feedback research_feedback_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.research_feedback
    ADD CONSTRAINT research_feedback_pkey PRIMARY KEY (id);


--
-- Name: sdgs sdgs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sdgs
    ADD CONSTRAINT sdgs_pkey PRIMARY KEY (id);


--
-- Name: search_logs search_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.search_logs
    ADD CONSTRAINT search_logs_pkey PRIMARY KEY (id);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: stud_file_author stud_file_author_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.stud_file_author
    ADD CONSTRAINT stud_file_author_pkey PRIMARY KEY (id);


--
-- Name: stud_file_keyword stud_file_keyword_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.stud_file_keyword
    ADD CONSTRAINT stud_file_keyword_pkey PRIMARY KEY (id);


--
-- Name: stud_file_keyword stud_file_keyword_stud_file_id_keyword_id_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.stud_file_keyword
    ADD CONSTRAINT stud_file_keyword_stud_file_id_keyword_id_unique UNIQUE (stud_file_id, keyword_id);


--
-- Name: stud_file stud_file_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.stud_file
    ADD CONSTRAINT stud_file_pkey PRIMARY KEY (id);


--
-- Name: stud_file_sdg stud_file_sdg_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.stud_file_sdg
    ADD CONSTRAINT stud_file_sdg_pkey PRIMARY KEY (id);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: messages messages_payload_exclusive; Type: CHECK CONSTRAINT; Schema: realtime; Owner: supabase_realtime_admin
--

ALTER TABLE realtime.messages
    ADD CONSTRAINT messages_payload_exclusive CHECK (((payload IS NULL) OR (binary_payload IS NULL))) NOT VALID;


--
-- Name: messages messages_pkey; Type: CONSTRAINT; Schema: realtime; Owner: supabase_realtime_admin
--

ALTER TABLE ONLY realtime.messages
    ADD CONSTRAINT messages_pkey PRIMARY KEY (id, inserted_at);


--
-- Name: subscription pk_subscription; Type: CONSTRAINT; Schema: realtime; Owner: supabase_realtime_admin
--

ALTER TABLE ONLY realtime.subscription
    ADD CONSTRAINT pk_subscription PRIMARY KEY (id);


--
-- Name: schema_migrations schema_migrations_pkey; Type: CONSTRAINT; Schema: realtime; Owner: supabase_admin
--

ALTER TABLE ONLY realtime.schema_migrations
    ADD CONSTRAINT schema_migrations_pkey PRIMARY KEY (version);


--
-- Name: buckets_analytics buckets_analytics_pkey; Type: CONSTRAINT; Schema: storage; Owner: supabase_storage_admin
--

ALTER TABLE ONLY storage.buckets_analytics
    ADD CONSTRAINT buckets_analytics_pkey PRIMARY KEY (id);


--
-- Name: buckets buckets_pkey; Type: CONSTRAINT; Schema: storage; Owner: supabase_storage_admin
--

ALTER TABLE ONLY storage.buckets
    ADD CONSTRAINT buckets_pkey PRIMARY KEY (id);


--
-- Name: buckets_vectors buckets_vectors_pkey; Type: CONSTRAINT; Schema: storage; Owner: supabase_storage_admin
--

ALTER TABLE ONLY storage.buckets_vectors
    ADD CONSTRAINT buckets_vectors_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_name_key; Type: CONSTRAINT; Schema: storage; Owner: supabase_storage_admin
--

ALTER TABLE ONLY storage.migrations
    ADD CONSTRAINT migrations_name_key UNIQUE (name);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: storage; Owner: supabase_storage_admin
--

ALTER TABLE ONLY storage.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: objects objects_pkey; Type: CONSTRAINT; Schema: storage; Owner: supabase_storage_admin
--

ALTER TABLE ONLY storage.objects
    ADD CONSTRAINT objects_pkey PRIMARY KEY (id);


--
-- Name: s3_multipart_uploads_parts s3_multipart_uploads_parts_pkey; Type: CONSTRAINT; Schema: storage; Owner: supabase_storage_admin
--

ALTER TABLE ONLY storage.s3_multipart_uploads_parts
    ADD CONSTRAINT s3_multipart_uploads_parts_pkey PRIMARY KEY (id);


--
-- Name: s3_multipart_uploads s3_multipart_uploads_pkey; Type: CONSTRAINT; Schema: storage; Owner: supabase_storage_admin
--

ALTER TABLE ONLY storage.s3_multipart_uploads
    ADD CONSTRAINT s3_multipart_uploads_pkey PRIMARY KEY (id);


--
-- Name: vector_indexes vector_indexes_pkey; Type: CONSTRAINT; Schema: storage; Owner: supabase_storage_admin
--

ALTER TABLE ONLY storage.vector_indexes
    ADD CONSTRAINT vector_indexes_pkey PRIMARY KEY (id);


--
-- Name: schema_migrations schema_migrations_idempotency_key_key; Type: CONSTRAINT; Schema: supabase_migrations; Owner: postgres
--

ALTER TABLE ONLY supabase_migrations.schema_migrations
    ADD CONSTRAINT schema_migrations_idempotency_key_key UNIQUE (idempotency_key);


--
-- Name: schema_migrations schema_migrations_pkey; Type: CONSTRAINT; Schema: supabase_migrations; Owner: postgres
--

ALTER TABLE ONLY supabase_migrations.schema_migrations
    ADD CONSTRAINT schema_migrations_pkey PRIMARY KEY (version);


--
-- Name: audit_logs_instance_id_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX audit_logs_instance_id_idx ON auth.audit_log_entries USING btree (instance_id);


--
-- Name: confirmation_token_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE UNIQUE INDEX confirmation_token_idx ON auth.users USING btree (confirmation_token) WHERE ((confirmation_token)::text !~ '^[0-9 ]*$'::text);


--
-- Name: custom_oauth_providers_created_at_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX custom_oauth_providers_created_at_idx ON auth.custom_oauth_providers USING btree (created_at);


--
-- Name: custom_oauth_providers_enabled_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX custom_oauth_providers_enabled_idx ON auth.custom_oauth_providers USING btree (enabled);


--
-- Name: custom_oauth_providers_identifier_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX custom_oauth_providers_identifier_idx ON auth.custom_oauth_providers USING btree (identifier);


--
-- Name: custom_oauth_providers_provider_type_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX custom_oauth_providers_provider_type_idx ON auth.custom_oauth_providers USING btree (provider_type);


--
-- Name: email_change_token_current_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE UNIQUE INDEX email_change_token_current_idx ON auth.users USING btree (email_change_token_current) WHERE ((email_change_token_current)::text !~ '^[0-9 ]*$'::text);


--
-- Name: email_change_token_new_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE UNIQUE INDEX email_change_token_new_idx ON auth.users USING btree (email_change_token_new) WHERE ((email_change_token_new)::text !~ '^[0-9 ]*$'::text);


--
-- Name: factor_id_created_at_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX factor_id_created_at_idx ON auth.mfa_factors USING btree (user_id, created_at);


--
-- Name: flow_state_created_at_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX flow_state_created_at_idx ON auth.flow_state USING btree (created_at DESC);


--
-- Name: identities_email_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX identities_email_idx ON auth.identities USING btree (email text_pattern_ops);


--
-- Name: INDEX identities_email_idx; Type: COMMENT; Schema: auth; Owner: supabase_auth_admin
--

COMMENT ON INDEX auth.identities_email_idx IS 'Auth: Ensures indexed queries on the email column';


--
-- Name: identities_user_id_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX identities_user_id_idx ON auth.identities USING btree (user_id);


--
-- Name: idx_auth_code; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX idx_auth_code ON auth.flow_state USING btree (auth_code);


--
-- Name: idx_oauth_client_states_created_at; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX idx_oauth_client_states_created_at ON auth.oauth_client_states USING btree (created_at);


--
-- Name: idx_user_id_auth_method; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX idx_user_id_auth_method ON auth.flow_state USING btree (user_id, authentication_method);


--
-- Name: idx_users_created_at_desc; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX idx_users_created_at_desc ON auth.users USING btree (created_at DESC);


--
-- Name: idx_users_email; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX idx_users_email ON auth.users USING btree (email);


--
-- Name: idx_users_last_sign_in_at_desc; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX idx_users_last_sign_in_at_desc ON auth.users USING btree (last_sign_in_at DESC);


--
-- Name: idx_users_name; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX idx_users_name ON auth.users USING btree (((raw_user_meta_data ->> 'name'::text))) WHERE ((raw_user_meta_data ->> 'name'::text) IS NOT NULL);


--
-- Name: mfa_challenge_created_at_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX mfa_challenge_created_at_idx ON auth.mfa_challenges USING btree (created_at DESC);


--
-- Name: mfa_factors_user_friendly_name_unique; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE UNIQUE INDEX mfa_factors_user_friendly_name_unique ON auth.mfa_factors USING btree (friendly_name, user_id) WHERE (TRIM(BOTH FROM friendly_name) <> ''::text);


--
-- Name: mfa_factors_user_id_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX mfa_factors_user_id_idx ON auth.mfa_factors USING btree (user_id);


--
-- Name: mfa_recovery_codes_set_id_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX mfa_recovery_codes_set_id_idx ON auth.mfa_recovery_codes USING btree (mfa_recovery_code_set_id);


--
-- Name: oauth_auth_pending_exp_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX oauth_auth_pending_exp_idx ON auth.oauth_authorizations USING btree (expires_at) WHERE (status = 'pending'::auth.oauth_authorization_status);


--
-- Name: oauth_clients_deleted_at_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX oauth_clients_deleted_at_idx ON auth.oauth_clients USING btree (deleted_at);


--
-- Name: oauth_consents_active_client_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX oauth_consents_active_client_idx ON auth.oauth_consents USING btree (client_id) WHERE (revoked_at IS NULL);


--
-- Name: oauth_consents_active_user_client_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX oauth_consents_active_user_client_idx ON auth.oauth_consents USING btree (user_id, client_id) WHERE (revoked_at IS NULL);


--
-- Name: oauth_consents_user_order_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX oauth_consents_user_order_idx ON auth.oauth_consents USING btree (user_id, granted_at DESC);


--
-- Name: one_time_tokens_relates_to_hash_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX one_time_tokens_relates_to_hash_idx ON auth.one_time_tokens USING hash (relates_to);


--
-- Name: one_time_tokens_token_hash_hash_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX one_time_tokens_token_hash_hash_idx ON auth.one_time_tokens USING hash (token_hash);


--
-- Name: one_time_tokens_user_id_token_type_key; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE UNIQUE INDEX one_time_tokens_user_id_token_type_key ON auth.one_time_tokens USING btree (user_id, token_type);


--
-- Name: reauthentication_token_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE UNIQUE INDEX reauthentication_token_idx ON auth.users USING btree (reauthentication_token) WHERE ((reauthentication_token)::text !~ '^[0-9 ]*$'::text);


--
-- Name: recovery_token_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE UNIQUE INDEX recovery_token_idx ON auth.users USING btree (recovery_token) WHERE ((recovery_token)::text !~ '^[0-9 ]*$'::text);


--
-- Name: refresh_tokens_instance_id_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX refresh_tokens_instance_id_idx ON auth.refresh_tokens USING btree (instance_id);


--
-- Name: refresh_tokens_instance_id_user_id_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX refresh_tokens_instance_id_user_id_idx ON auth.refresh_tokens USING btree (instance_id, user_id);


--
-- Name: refresh_tokens_parent_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX refresh_tokens_parent_idx ON auth.refresh_tokens USING btree (parent);


--
-- Name: refresh_tokens_session_id_revoked_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX refresh_tokens_session_id_revoked_idx ON auth.refresh_tokens USING btree (session_id, revoked);


--
-- Name: refresh_tokens_updated_at_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX refresh_tokens_updated_at_idx ON auth.refresh_tokens USING btree (updated_at DESC);


--
-- Name: saml_providers_sso_provider_id_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX saml_providers_sso_provider_id_idx ON auth.saml_providers USING btree (sso_provider_id);


--
-- Name: saml_relay_states_created_at_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX saml_relay_states_created_at_idx ON auth.saml_relay_states USING btree (created_at DESC);


--
-- Name: saml_relay_states_for_email_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX saml_relay_states_for_email_idx ON auth.saml_relay_states USING btree (for_email);


--
-- Name: saml_relay_states_sso_provider_id_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX saml_relay_states_sso_provider_id_idx ON auth.saml_relay_states USING btree (sso_provider_id);


--
-- Name: scim_tokens_expires_at_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX scim_tokens_expires_at_idx ON auth.scim_tokens USING btree (expires_at);


--
-- Name: scim_tokens_revoked_at_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX scim_tokens_revoked_at_idx ON auth.scim_tokens USING btree (revoked_at);


--
-- Name: scim_tokens_sso_provider_id_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX scim_tokens_sso_provider_id_idx ON auth.scim_tokens USING btree (sso_provider_id);


--
-- Name: scim_tokens_token_hash_key; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE UNIQUE INDEX scim_tokens_token_hash_key ON auth.scim_tokens USING btree (token_hash);


--
-- Name: scim_users_created_at_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX scim_users_created_at_idx ON auth.scim_users USING btree (sso_provider_id, created_at, id) WHERE (deleted_at IS NULL);


--
-- Name: scim_users_deleted_at_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX scim_users_deleted_at_idx ON auth.scim_users USING btree (deleted_at);


--
-- Name: scim_users_external_id_key; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE UNIQUE INDEX scim_users_external_id_key ON auth.scim_users USING btree (sso_provider_id, external_id) WHERE ((external_id IS NOT NULL) AND (deleted_at IS NULL));


--
-- Name: scim_users_id_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX scim_users_id_idx ON auth.scim_users USING btree (sso_provider_id, id) WHERE (deleted_at IS NULL);


--
-- Name: scim_users_sso_provider_id_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX scim_users_sso_provider_id_idx ON auth.scim_users USING btree (sso_provider_id);


--
-- Name: scim_users_updated_at_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX scim_users_updated_at_idx ON auth.scim_users USING btree (sso_provider_id, updated_at, id) WHERE (deleted_at IS NULL);


--
-- Name: scim_users_user_id_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX scim_users_user_id_idx ON auth.scim_users USING btree (user_id);


--
-- Name: scim_users_user_name_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX scim_users_user_name_idx ON auth.scim_users USING btree (sso_provider_id, user_name COLLATE "C", id) WHERE (deleted_at IS NULL);


--
-- Name: scim_users_user_name_key; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE UNIQUE INDEX scim_users_user_name_key ON auth.scim_users USING btree (sso_provider_id, user_name) WHERE (deleted_at IS NULL);


--
-- Name: sessions_not_after_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX sessions_not_after_idx ON auth.sessions USING btree (not_after DESC);


--
-- Name: sessions_oauth_client_id_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX sessions_oauth_client_id_idx ON auth.sessions USING btree (oauth_client_id);


--
-- Name: sessions_user_id_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX sessions_user_id_idx ON auth.sessions USING btree (user_id);


--
-- Name: sso_domains_domain_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE UNIQUE INDEX sso_domains_domain_idx ON auth.sso_domains USING btree (lower(domain));


--
-- Name: sso_domains_sso_provider_id_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX sso_domains_sso_provider_id_idx ON auth.sso_domains USING btree (sso_provider_id);


--
-- Name: sso_providers_resource_id_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE UNIQUE INDEX sso_providers_resource_id_idx ON auth.sso_providers USING btree (lower(resource_id));


--
-- Name: sso_providers_resource_id_pattern_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX sso_providers_resource_id_pattern_idx ON auth.sso_providers USING btree (resource_id text_pattern_ops);


--
-- Name: unique_phone_factor_per_user; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE UNIQUE INDEX unique_phone_factor_per_user ON auth.mfa_factors USING btree (user_id, phone);


--
-- Name: user_id_created_at_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX user_id_created_at_idx ON auth.sessions USING btree (user_id, created_at);


--
-- Name: users_email_partial_key; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE UNIQUE INDEX users_email_partial_key ON auth.users USING btree (email) WHERE (is_sso_user = false);


--
-- Name: INDEX users_email_partial_key; Type: COMMENT; Schema: auth; Owner: supabase_auth_admin
--

COMMENT ON INDEX auth.users_email_partial_key IS 'Auth: A partial unique index that applies only when is_sso_user is false';


--
-- Name: users_instance_id_email_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX users_instance_id_email_idx ON auth.users USING btree (instance_id, lower((email)::text));


--
-- Name: users_instance_id_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX users_instance_id_idx ON auth.users USING btree (instance_id);


--
-- Name: users_is_anonymous_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX users_is_anonymous_idx ON auth.users USING btree (is_anonymous);


--
-- Name: webauthn_challenges_expires_at_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX webauthn_challenges_expires_at_idx ON auth.webauthn_challenges USING btree (expires_at);


--
-- Name: webauthn_challenges_user_id_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX webauthn_challenges_user_id_idx ON auth.webauthn_challenges USING btree (user_id);


--
-- Name: webauthn_credentials_credential_id_key; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE UNIQUE INDEX webauthn_credentials_credential_id_key ON auth.webauthn_credentials USING btree (credential_id);


--
-- Name: webauthn_credentials_user_id_idx; Type: INDEX; Schema: auth; Owner: supabase_auth_admin
--

CREATE INDEX webauthn_credentials_user_id_idx ON auth.webauthn_credentials USING btree (user_id);


--
-- Name: ai_usage_logs_created_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX ai_usage_logs_created_at_index ON public.ai_usage_logs USING btree (created_at);


--
-- Name: ai_usage_logs_user_id_created_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX ai_usage_logs_user_id_created_at_index ON public.ai_usage_logs USING btree (user_id, created_at);


--
-- Name: cache_expiration_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX cache_expiration_index ON public.cache USING btree (expiration);


--
-- Name: cache_locks_expiration_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX cache_locks_expiration_index ON public.cache_locks USING btree (expiration);


--
-- Name: chat_conversations_user_id_last_message_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX chat_conversations_user_id_last_message_at_index ON public.chat_conversations USING btree (user_id, last_message_at);


--
-- Name: chat_messages_conversation_id_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX chat_messages_conversation_id_id_index ON public.chat_messages USING btree (conversation_id, id);


--
-- Name: chat_messages_history_idx; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX chat_messages_history_idx ON public.chat_messages USING btree (conversation_id, excluded_from_history, id);


--
-- Name: jobs_queue_reserved_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX jobs_queue_reserved_at_index ON public.jobs USING btree (queue, reserved_at);


--
-- Name: notifications_notifiable_type_notifiable_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX notifications_notifiable_type_notifiable_id_index ON public.notifications USING btree (notifiable_type, notifiable_id);


--
-- Name: research_chunks_content_hash_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX research_chunks_content_hash_index ON public.research_chunks USING btree (content_hash);


--
-- Name: research_chunks_content_tsv_idx; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX research_chunks_content_tsv_idx ON public.research_chunks USING gin (content_tsv);


--
-- Name: research_chunks_embedding_hnsw_idx; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX research_chunks_embedding_hnsw_idx ON public.research_chunks USING hnsw (embedding public.vector_cosine_ops);


--
-- Name: research_chunks_embedding_ver_idx; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX research_chunks_embedding_ver_idx ON public.research_chunks USING btree (embedding_version, embedding_model);


--
-- Name: research_chunks_stud_file_id_chunk_index_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX research_chunks_stud_file_id_chunk_index_index ON public.research_chunks USING btree (stud_file_id, chunk_index);


--
-- Name: sdgs_number_unique; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX sdgs_number_unique ON public.sdgs USING btree (number);


--
-- Name: search_logs_keyword_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX search_logs_keyword_index ON public.search_logs USING btree (keyword);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- Name: stud_file_adviser_id_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX stud_file_adviser_id_status_index ON public.stud_file USING btree (adviser_id, status);


--
-- Name: stud_file_author_user_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX stud_file_author_user_id_index ON public.stud_file_author USING btree (user_id);


--
-- Name: stud_file_pdf_hash_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX stud_file_pdf_hash_index ON public.stud_file USING btree (pdf_hash);


--
-- Name: stud_file_sdg_sdg_id_foreign; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX stud_file_sdg_sdg_id_foreign ON public.stud_file_sdg USING btree (sdg_id);


--
-- Name: stud_file_sdg_sdg_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX stud_file_sdg_sdg_id_index ON public.stud_file_sdg USING btree (sdg_id);


--
-- Name: stud_file_sdg_stud_file_id_sdg_id_unique; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX stud_file_sdg_stud_file_id_sdg_id_unique ON public.stud_file_sdg USING btree (stud_file_id, sdg_id);


--
-- Name: stud_file_status_created_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX stud_file_status_created_at_index ON public.stud_file USING btree (status, created_at);


--
-- Name: stud_file_user_id_foreign; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX stud_file_user_id_foreign ON public.stud_file USING btree (user_id);


--
-- Name: stud_file_user_id_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX stud_file_user_id_status_index ON public.stud_file USING btree (user_id, status);


--
-- Name: users_email_unique; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX users_email_unique ON public.users USING btree (email);


--
-- Name: users_user_id_unique; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX users_user_id_unique ON public.users USING btree (user_id);


--
-- Name: ix_realtime_subscription_entity; Type: INDEX; Schema: realtime; Owner: supabase_realtime_admin
--

CREATE INDEX ix_realtime_subscription_entity ON realtime.subscription USING btree (entity);


--
-- Name: messages_inserted_at_topic_index; Type: INDEX; Schema: realtime; Owner: supabase_realtime_admin
--

CREATE INDEX messages_inserted_at_topic_index ON ONLY realtime.messages USING btree (inserted_at DESC, topic) WHERE ((extension = 'broadcast'::text) AND (private IS TRUE));


--
-- Name: subscription_subscription_id_entity_filters_action_filter_selec; Type: INDEX; Schema: realtime; Owner: supabase_realtime_admin
--

CREATE UNIQUE INDEX subscription_subscription_id_entity_filters_action_filter_selec ON realtime.subscription USING btree (subscription_id, entity, filters, action_filter, COALESCE(selected_columns, '{}'::text[]));


--
-- Name: bname; Type: INDEX; Schema: storage; Owner: supabase_storage_admin
--

CREATE UNIQUE INDEX bname ON storage.buckets USING btree (name);


--
-- Name: buckets_analytics_unique_name_idx; Type: INDEX; Schema: storage; Owner: supabase_storage_admin
--

CREATE UNIQUE INDEX buckets_analytics_unique_name_idx ON storage.buckets_analytics USING btree (name) WHERE (deleted_at IS NULL);


--
-- Name: idx_multipart_uploads_list; Type: INDEX; Schema: storage; Owner: supabase_storage_admin
--

CREATE INDEX idx_multipart_uploads_list ON storage.s3_multipart_uploads USING btree (bucket_id, key, created_at);


--
-- Name: idx_objects_bucket_id_name; Type: INDEX; Schema: storage; Owner: supabase_storage_admin
--

CREATE INDEX idx_objects_bucket_id_name ON storage.objects USING btree (bucket_id, name COLLATE "C");


--
-- Name: idx_objects_bucket_id_name_lower; Type: INDEX; Schema: storage; Owner: supabase_storage_admin
--

CREATE INDEX idx_objects_bucket_id_name_lower ON storage.objects USING btree (bucket_id, lower(name) COLLATE "C");


--
-- Name: idx_objects_current_version; Type: INDEX; Schema: storage; Owner: supabase_storage_admin
--

CREATE UNIQUE INDEX idx_objects_current_version ON storage.objects USING btree (bucket_id, name COLLATE "C") WHERE (archived_at IS NULL);


--
-- Name: idx_objects_delete_markers; Type: INDEX; Schema: storage; Owner: supabase_storage_admin
--

CREATE INDEX idx_objects_delete_markers ON storage.objects USING btree (bucket_id, name COLLATE "C") WHERE is_delete_marker;


--
-- Name: idx_objects_null_version; Type: INDEX; Schema: storage; Owner: supabase_storage_admin
--

CREATE UNIQUE INDEX idx_objects_null_version ON storage.objects USING btree (bucket_id, name COLLATE "C") WHERE (NOT is_versioned);


--
-- Name: name_prefix_search; Type: INDEX; Schema: storage; Owner: supabase_storage_admin
--

CREATE INDEX name_prefix_search ON storage.objects USING btree (name text_pattern_ops);


--
-- Name: objects_bucket_id_name_version_key; Type: INDEX; Schema: storage; Owner: supabase_storage_admin
--

CREATE UNIQUE INDEX objects_bucket_id_name_version_key ON storage.objects USING btree (bucket_id, name COLLATE "C", version) NULLS NOT DISTINCT;


--
-- Name: vector_indexes_name_bucket_id_idx; Type: INDEX; Schema: storage; Owner: supabase_storage_admin
--

CREATE UNIQUE INDEX vector_indexes_name_bucket_id_idx ON storage.vector_indexes USING btree (name, bucket_id);


--
-- Name: subscription tr_check_filters; Type: TRIGGER; Schema: realtime; Owner: supabase_realtime_admin
--

CREATE TRIGGER tr_check_filters BEFORE INSERT OR UPDATE ON realtime.subscription FOR EACH ROW EXECUTE FUNCTION realtime.subscription_check_filters();


--
-- Name: buckets enforce_bucket_name_length_trigger; Type: TRIGGER; Schema: storage; Owner: supabase_storage_admin
--

CREATE TRIGGER enforce_bucket_name_length_trigger BEFORE INSERT OR UPDATE OF name ON storage.buckets FOR EACH ROW EXECUTE FUNCTION storage.enforce_bucket_name_length();


--
-- Name: buckets protect_bucket_control_insert; Type: TRIGGER; Schema: storage; Owner: supabase_storage_admin
--

CREATE TRIGGER protect_bucket_control_insert BEFORE INSERT ON storage.buckets FOR EACH ROW EXECUTE FUNCTION storage.protect_bucket_control_columns('service_role');


--
-- Name: buckets protect_bucket_control_update; Type: TRIGGER; Schema: storage; Owner: supabase_storage_admin
--

CREATE TRIGGER protect_bucket_control_update BEFORE UPDATE OF lifecycle_configuration, lifecycle_configuration_generation ON storage.buckets FOR EACH ROW EXECUTE FUNCTION storage.protect_bucket_control_columns();


--
-- Name: buckets protect_bucket_control_update_role; Type: TRIGGER; Schema: storage; Owner: supabase_storage_admin
--

CREATE TRIGGER protect_bucket_control_update_role AFTER UPDATE OF lifecycle_configuration, lifecycle_configuration_generation ON storage.buckets FOR EACH ROW EXECUTE FUNCTION storage.enforce_bucket_lifecycle_service_role('service_role');


--
-- Name: buckets protect_buckets_delete; Type: TRIGGER; Schema: storage; Owner: supabase_storage_admin
--

CREATE TRIGGER protect_buckets_delete BEFORE DELETE ON storage.buckets FOR EACH STATEMENT EXECUTE FUNCTION storage.protect_delete();


--
-- Name: objects protect_objects_delete; Type: TRIGGER; Schema: storage; Owner: supabase_storage_admin
--

CREATE TRIGGER protect_objects_delete BEFORE DELETE ON storage.objects FOR EACH STATEMENT EXECUTE FUNCTION storage.protect_delete();


--
-- Name: objects update_objects_updated_at; Type: TRIGGER; Schema: storage; Owner: supabase_storage_admin
--

CREATE TRIGGER update_objects_updated_at BEFORE UPDATE ON storage.objects FOR EACH ROW EXECUTE FUNCTION storage.update_updated_at_column();


--
-- Name: identities identities_user_id_fkey; Type: FK CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.identities
    ADD CONSTRAINT identities_user_id_fkey FOREIGN KEY (user_id) REFERENCES auth.users(id) ON DELETE CASCADE;


--
-- Name: mfa_amr_claims mfa_amr_claims_session_id_fkey; Type: FK CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.mfa_amr_claims
    ADD CONSTRAINT mfa_amr_claims_session_id_fkey FOREIGN KEY (session_id) REFERENCES auth.sessions(id) ON DELETE CASCADE;


--
-- Name: mfa_challenges mfa_challenges_auth_factor_id_fkey; Type: FK CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.mfa_challenges
    ADD CONSTRAINT mfa_challenges_auth_factor_id_fkey FOREIGN KEY (factor_id) REFERENCES auth.mfa_factors(id) ON DELETE CASCADE;


--
-- Name: mfa_factors mfa_factors_user_id_fkey; Type: FK CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.mfa_factors
    ADD CONSTRAINT mfa_factors_user_id_fkey FOREIGN KEY (user_id) REFERENCES auth.users(id) ON DELETE CASCADE;


--
-- Name: mfa_recovery_code_sets mfa_recovery_code_sets_mfa_factor_id_fkey; Type: FK CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.mfa_recovery_code_sets
    ADD CONSTRAINT mfa_recovery_code_sets_mfa_factor_id_fkey FOREIGN KEY (mfa_factor_id) REFERENCES auth.mfa_factors(id) ON DELETE CASCADE;


--
-- Name: mfa_recovery_code_sets mfa_recovery_code_sets_user_id_fkey; Type: FK CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.mfa_recovery_code_sets
    ADD CONSTRAINT mfa_recovery_code_sets_user_id_fkey FOREIGN KEY (user_id) REFERENCES auth.users(id) ON DELETE CASCADE;


--
-- Name: mfa_recovery_codes mfa_recovery_codes_mfa_recovery_code_set_id_fkey; Type: FK CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.mfa_recovery_codes
    ADD CONSTRAINT mfa_recovery_codes_mfa_recovery_code_set_id_fkey FOREIGN KEY (mfa_recovery_code_set_id) REFERENCES auth.mfa_recovery_code_sets(id) ON DELETE CASCADE;


--
-- Name: oauth_authorizations oauth_authorizations_client_id_fkey; Type: FK CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.oauth_authorizations
    ADD CONSTRAINT oauth_authorizations_client_id_fkey FOREIGN KEY (client_id) REFERENCES auth.oauth_clients(id) ON DELETE CASCADE;


--
-- Name: oauth_authorizations oauth_authorizations_user_id_fkey; Type: FK CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.oauth_authorizations
    ADD CONSTRAINT oauth_authorizations_user_id_fkey FOREIGN KEY (user_id) REFERENCES auth.users(id) ON DELETE CASCADE;


--
-- Name: oauth_consents oauth_consents_client_id_fkey; Type: FK CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.oauth_consents
    ADD CONSTRAINT oauth_consents_client_id_fkey FOREIGN KEY (client_id) REFERENCES auth.oauth_clients(id) ON DELETE CASCADE;


--
-- Name: oauth_consents oauth_consents_user_id_fkey; Type: FK CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.oauth_consents
    ADD CONSTRAINT oauth_consents_user_id_fkey FOREIGN KEY (user_id) REFERENCES auth.users(id) ON DELETE CASCADE;


--
-- Name: one_time_tokens one_time_tokens_user_id_fkey; Type: FK CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.one_time_tokens
    ADD CONSTRAINT one_time_tokens_user_id_fkey FOREIGN KEY (user_id) REFERENCES auth.users(id) ON DELETE CASCADE;


--
-- Name: refresh_tokens refresh_tokens_session_id_fkey; Type: FK CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.refresh_tokens
    ADD CONSTRAINT refresh_tokens_session_id_fkey FOREIGN KEY (session_id) REFERENCES auth.sessions(id) ON DELETE CASCADE;


--
-- Name: saml_providers saml_providers_sso_provider_id_fkey; Type: FK CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.saml_providers
    ADD CONSTRAINT saml_providers_sso_provider_id_fkey FOREIGN KEY (sso_provider_id) REFERENCES auth.sso_providers(id) ON DELETE CASCADE;


--
-- Name: saml_relay_states saml_relay_states_flow_state_id_fkey; Type: FK CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.saml_relay_states
    ADD CONSTRAINT saml_relay_states_flow_state_id_fkey FOREIGN KEY (flow_state_id) REFERENCES auth.flow_state(id) ON DELETE CASCADE;


--
-- Name: saml_relay_states saml_relay_states_sso_provider_id_fkey; Type: FK CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.saml_relay_states
    ADD CONSTRAINT saml_relay_states_sso_provider_id_fkey FOREIGN KEY (sso_provider_id) REFERENCES auth.sso_providers(id) ON DELETE CASCADE;


--
-- Name: scim_tokens scim_tokens_sso_provider_id_fkey; Type: FK CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.scim_tokens
    ADD CONSTRAINT scim_tokens_sso_provider_id_fkey FOREIGN KEY (sso_provider_id) REFERENCES auth.sso_providers(id) ON DELETE CASCADE;


--
-- Name: scim_users scim_users_sso_provider_id_fkey; Type: FK CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.scim_users
    ADD CONSTRAINT scim_users_sso_provider_id_fkey FOREIGN KEY (sso_provider_id) REFERENCES auth.sso_providers(id) ON DELETE CASCADE;


--
-- Name: scim_users scim_users_user_id_fkey; Type: FK CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.scim_users
    ADD CONSTRAINT scim_users_user_id_fkey FOREIGN KEY (user_id) REFERENCES auth.users(id) ON DELETE SET NULL;


--
-- Name: sessions sessions_oauth_client_id_fkey; Type: FK CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.sessions
    ADD CONSTRAINT sessions_oauth_client_id_fkey FOREIGN KEY (oauth_client_id) REFERENCES auth.oauth_clients(id) ON DELETE CASCADE;


--
-- Name: sessions sessions_user_id_fkey; Type: FK CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.sessions
    ADD CONSTRAINT sessions_user_id_fkey FOREIGN KEY (user_id) REFERENCES auth.users(id) ON DELETE CASCADE;


--
-- Name: sso_domains sso_domains_sso_provider_id_fkey; Type: FK CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.sso_domains
    ADD CONSTRAINT sso_domains_sso_provider_id_fkey FOREIGN KEY (sso_provider_id) REFERENCES auth.sso_providers(id) ON DELETE CASCADE;


--
-- Name: webauthn_challenges webauthn_challenges_user_id_fkey; Type: FK CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.webauthn_challenges
    ADD CONSTRAINT webauthn_challenges_user_id_fkey FOREIGN KEY (user_id) REFERENCES auth.users(id) ON DELETE CASCADE;


--
-- Name: webauthn_credentials webauthn_credentials_user_id_fkey; Type: FK CONSTRAINT; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE ONLY auth.webauthn_credentials
    ADD CONSTRAINT webauthn_credentials_user_id_fkey FOREIGN KEY (user_id) REFERENCES auth.users(id) ON DELETE CASCADE;


--
-- Name: ai_usage_logs ai_usage_logs_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.ai_usage_logs
    ADD CONSTRAINT ai_usage_logs_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: chat_conversations chat_conversations_stud_file_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.chat_conversations
    ADD CONSTRAINT chat_conversations_stud_file_id_foreign FOREIGN KEY (stud_file_id) REFERENCES public.stud_file(id) ON DELETE CASCADE;


--
-- Name: chat_conversations chat_conversations_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.chat_conversations
    ADD CONSTRAINT chat_conversations_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: chat_messages chat_messages_conversation_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.chat_messages
    ADD CONSTRAINT chat_messages_conversation_id_foreign FOREIGN KEY (conversation_id) REFERENCES public.chat_conversations(id) ON DELETE CASCADE;


--
-- Name: research_chunks research_chunks_stud_file_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.research_chunks
    ADD CONSTRAINT research_chunks_stud_file_id_foreign FOREIGN KEY (stud_file_id) REFERENCES public.stud_file(id) ON DELETE CASCADE;


--
-- Name: research_feedback research_feedback_stud_file_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.research_feedback
    ADD CONSTRAINT research_feedback_stud_file_id_foreign FOREIGN KEY (stud_file_id) REFERENCES public.stud_file(id) ON DELETE CASCADE;


--
-- Name: research_feedback research_feedback_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.research_feedback
    ADD CONSTRAINT research_feedback_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: search_logs search_logs_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.search_logs
    ADD CONSTRAINT search_logs_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: stud_file stud_file_adviser_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.stud_file
    ADD CONSTRAINT stud_file_adviser_id_foreign FOREIGN KEY (adviser_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: stud_file_author stud_file_author_stud_file_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.stud_file_author
    ADD CONSTRAINT stud_file_author_stud_file_id_foreign FOREIGN KEY (stud_file_id) REFERENCES public.stud_file(id) ON DELETE CASCADE;


--
-- Name: stud_file_author stud_file_author_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.stud_file_author
    ADD CONSTRAINT stud_file_author_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: stud_file_keyword stud_file_keyword_keyword_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.stud_file_keyword
    ADD CONSTRAINT stud_file_keyword_keyword_id_foreign FOREIGN KEY (keyword_id) REFERENCES public.keywords(id) ON DELETE CASCADE;


--
-- Name: stud_file_keyword stud_file_keyword_stud_file_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.stud_file_keyword
    ADD CONSTRAINT stud_file_keyword_stud_file_id_foreign FOREIGN KEY (stud_file_id) REFERENCES public.stud_file(id) ON DELETE CASCADE;


--
-- Name: stud_file_sdg stud_file_sdg_sdg_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.stud_file_sdg
    ADD CONSTRAINT stud_file_sdg_sdg_id_fkey FOREIGN KEY (sdg_id) REFERENCES public.sdgs(id) ON DELETE CASCADE;


--
-- Name: stud_file_sdg stud_file_sdg_stud_file_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.stud_file_sdg
    ADD CONSTRAINT stud_file_sdg_stud_file_id_fkey FOREIGN KEY (stud_file_id) REFERENCES public.stud_file(id) ON DELETE CASCADE;


--
-- Name: stud_file stud_file_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.stud_file
    ADD CONSTRAINT stud_file_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: objects objects_bucketId_fkey; Type: FK CONSTRAINT; Schema: storage; Owner: supabase_storage_admin
--

ALTER TABLE ONLY storage.objects
    ADD CONSTRAINT "objects_bucketId_fkey" FOREIGN KEY (bucket_id) REFERENCES storage.buckets(id);


--
-- Name: s3_multipart_uploads s3_multipart_uploads_bucket_id_fkey; Type: FK CONSTRAINT; Schema: storage; Owner: supabase_storage_admin
--

ALTER TABLE ONLY storage.s3_multipart_uploads
    ADD CONSTRAINT s3_multipart_uploads_bucket_id_fkey FOREIGN KEY (bucket_id) REFERENCES storage.buckets(id);


--
-- Name: s3_multipart_uploads_parts s3_multipart_uploads_parts_bucket_id_fkey; Type: FK CONSTRAINT; Schema: storage; Owner: supabase_storage_admin
--

ALTER TABLE ONLY storage.s3_multipart_uploads_parts
    ADD CONSTRAINT s3_multipart_uploads_parts_bucket_id_fkey FOREIGN KEY (bucket_id) REFERENCES storage.buckets(id);


--
-- Name: s3_multipart_uploads_parts s3_multipart_uploads_parts_upload_id_fkey; Type: FK CONSTRAINT; Schema: storage; Owner: supabase_storage_admin
--

ALTER TABLE ONLY storage.s3_multipart_uploads_parts
    ADD CONSTRAINT s3_multipart_uploads_parts_upload_id_fkey FOREIGN KEY (upload_id) REFERENCES storage.s3_multipart_uploads(id) ON DELETE CASCADE;


--
-- Name: vector_indexes vector_indexes_bucket_id_fkey; Type: FK CONSTRAINT; Schema: storage; Owner: supabase_storage_admin
--

ALTER TABLE ONLY storage.vector_indexes
    ADD CONSTRAINT vector_indexes_bucket_id_fkey FOREIGN KEY (bucket_id) REFERENCES storage.buckets_vectors(id);


--
-- Name: audit_log_entries; Type: ROW SECURITY; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE auth.audit_log_entries ENABLE ROW LEVEL SECURITY;

--
-- Name: flow_state; Type: ROW SECURITY; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE auth.flow_state ENABLE ROW LEVEL SECURITY;

--
-- Name: identities; Type: ROW SECURITY; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE auth.identities ENABLE ROW LEVEL SECURITY;

--
-- Name: instances; Type: ROW SECURITY; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE auth.instances ENABLE ROW LEVEL SECURITY;

--
-- Name: mfa_amr_claims; Type: ROW SECURITY; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE auth.mfa_amr_claims ENABLE ROW LEVEL SECURITY;

--
-- Name: mfa_challenges; Type: ROW SECURITY; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE auth.mfa_challenges ENABLE ROW LEVEL SECURITY;

--
-- Name: mfa_factors; Type: ROW SECURITY; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE auth.mfa_factors ENABLE ROW LEVEL SECURITY;

--
-- Name: one_time_tokens; Type: ROW SECURITY; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE auth.one_time_tokens ENABLE ROW LEVEL SECURITY;

--
-- Name: refresh_tokens; Type: ROW SECURITY; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE auth.refresh_tokens ENABLE ROW LEVEL SECURITY;

--
-- Name: saml_providers; Type: ROW SECURITY; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE auth.saml_providers ENABLE ROW LEVEL SECURITY;

--
-- Name: saml_relay_states; Type: ROW SECURITY; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE auth.saml_relay_states ENABLE ROW LEVEL SECURITY;

--
-- Name: schema_migrations; Type: ROW SECURITY; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE auth.schema_migrations ENABLE ROW LEVEL SECURITY;

--
-- Name: sessions; Type: ROW SECURITY; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE auth.sessions ENABLE ROW LEVEL SECURITY;

--
-- Name: sso_domains; Type: ROW SECURITY; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE auth.sso_domains ENABLE ROW LEVEL SECURITY;

--
-- Name: sso_providers; Type: ROW SECURITY; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE auth.sso_providers ENABLE ROW LEVEL SECURITY;

--
-- Name: users; Type: ROW SECURITY; Schema: auth; Owner: supabase_auth_admin
--

ALTER TABLE auth.users ENABLE ROW LEVEL SECURITY;

--
-- Name: ai_usage_logs; Type: ROW SECURITY; Schema: public; Owner: postgres
--

ALTER TABLE public.ai_usage_logs ENABLE ROW LEVEL SECURITY;

--
-- Name: cache; Type: ROW SECURITY; Schema: public; Owner: postgres
--

ALTER TABLE public.cache ENABLE ROW LEVEL SECURITY;

--
-- Name: cache_locks; Type: ROW SECURITY; Schema: public; Owner: postgres
--

ALTER TABLE public.cache_locks ENABLE ROW LEVEL SECURITY;

--
-- Name: chat_conversations; Type: ROW SECURITY; Schema: public; Owner: postgres
--

ALTER TABLE public.chat_conversations ENABLE ROW LEVEL SECURITY;

--
-- Name: chat_messages; Type: ROW SECURITY; Schema: public; Owner: postgres
--

ALTER TABLE public.chat_messages ENABLE ROW LEVEL SECURITY;

--
-- Name: failed_jobs; Type: ROW SECURITY; Schema: public; Owner: postgres
--

ALTER TABLE public.failed_jobs ENABLE ROW LEVEL SECURITY;

--
-- Name: job_batches; Type: ROW SECURITY; Schema: public; Owner: postgres
--

ALTER TABLE public.job_batches ENABLE ROW LEVEL SECURITY;

--
-- Name: jobs; Type: ROW SECURITY; Schema: public; Owner: postgres
--

ALTER TABLE public.jobs ENABLE ROW LEVEL SECURITY;

--
-- Name: keywords; Type: ROW SECURITY; Schema: public; Owner: postgres
--

ALTER TABLE public.keywords ENABLE ROW LEVEL SECURITY;

--
-- Name: migrations; Type: ROW SECURITY; Schema: public; Owner: postgres
--

ALTER TABLE public.migrations ENABLE ROW LEVEL SECURITY;

--
-- Name: notifications; Type: ROW SECURITY; Schema: public; Owner: postgres
--

ALTER TABLE public.notifications ENABLE ROW LEVEL SECURITY;

--
-- Name: password_reset_tokens; Type: ROW SECURITY; Schema: public; Owner: postgres
--

ALTER TABLE public.password_reset_tokens ENABLE ROW LEVEL SECURITY;

--
-- Name: research_access_requests; Type: ROW SECURITY; Schema: public; Owner: postgres
--

ALTER TABLE public.research_access_requests ENABLE ROW LEVEL SECURITY;

--
-- Name: research_chunks; Type: ROW SECURITY; Schema: public; Owner: postgres
--

ALTER TABLE public.research_chunks ENABLE ROW LEVEL SECURITY;

--
-- Name: sdgs; Type: ROW SECURITY; Schema: public; Owner: postgres
--

ALTER TABLE public.sdgs ENABLE ROW LEVEL SECURITY;

--
-- Name: sessions; Type: ROW SECURITY; Schema: public; Owner: postgres
--

ALTER TABLE public.sessions ENABLE ROW LEVEL SECURITY;

--
-- Name: stud_file; Type: ROW SECURITY; Schema: public; Owner: postgres
--

ALTER TABLE public.stud_file ENABLE ROW LEVEL SECURITY;

--
-- Name: stud_file_author; Type: ROW SECURITY; Schema: public; Owner: postgres
--

ALTER TABLE public.stud_file_author ENABLE ROW LEVEL SECURITY;

--
-- Name: stud_file_keyword; Type: ROW SECURITY; Schema: public; Owner: postgres
--

ALTER TABLE public.stud_file_keyword ENABLE ROW LEVEL SECURITY;

--
-- Name: stud_file_sdg; Type: ROW SECURITY; Schema: public; Owner: postgres
--

ALTER TABLE public.stud_file_sdg ENABLE ROW LEVEL SECURITY;

--
-- Name: users; Type: ROW SECURITY; Schema: public; Owner: postgres
--

ALTER TABLE public.users ENABLE ROW LEVEL SECURITY;

--
-- Name: messages; Type: ROW SECURITY; Schema: realtime; Owner: supabase_realtime_admin
--

ALTER TABLE realtime.messages ENABLE ROW LEVEL SECURITY;

--
-- Name: buckets; Type: ROW SECURITY; Schema: storage; Owner: supabase_storage_admin
--

ALTER TABLE storage.buckets ENABLE ROW LEVEL SECURITY;

--
-- Name: buckets_analytics; Type: ROW SECURITY; Schema: storage; Owner: supabase_storage_admin
--

ALTER TABLE storage.buckets_analytics ENABLE ROW LEVEL SECURITY;

--
-- Name: buckets_vectors; Type: ROW SECURITY; Schema: storage; Owner: supabase_storage_admin
--

ALTER TABLE storage.buckets_vectors ENABLE ROW LEVEL SECURITY;

--
-- Name: migrations; Type: ROW SECURITY; Schema: storage; Owner: supabase_storage_admin
--

ALTER TABLE storage.migrations ENABLE ROW LEVEL SECURITY;

--
-- Name: objects; Type: ROW SECURITY; Schema: storage; Owner: supabase_storage_admin
--

ALTER TABLE storage.objects ENABLE ROW LEVEL SECURITY;

--
-- Name: s3_multipart_uploads; Type: ROW SECURITY; Schema: storage; Owner: supabase_storage_admin
--

ALTER TABLE storage.s3_multipart_uploads ENABLE ROW LEVEL SECURITY;

--
-- Name: s3_multipart_uploads_parts; Type: ROW SECURITY; Schema: storage; Owner: supabase_storage_admin
--

ALTER TABLE storage.s3_multipart_uploads_parts ENABLE ROW LEVEL SECURITY;

--
-- Name: vector_indexes; Type: ROW SECURITY; Schema: storage; Owner: supabase_storage_admin
--

ALTER TABLE storage.vector_indexes ENABLE ROW LEVEL SECURITY;

--
-- Name: supabase_realtime; Type: PUBLICATION; Schema: -; Owner: postgres
--

CREATE PUBLICATION supabase_realtime WITH (publish = 'insert, update, delete, truncate');


ALTER PUBLICATION supabase_realtime OWNER TO postgres;

--
-- Name: SCHEMA auth; Type: ACL; Schema: -; Owner: supabase_admin
--

GRANT USAGE ON SCHEMA auth TO anon;
GRANT USAGE ON SCHEMA auth TO authenticated;
GRANT USAGE ON SCHEMA auth TO service_role;
GRANT ALL ON SCHEMA auth TO supabase_auth_admin;
GRANT ALL ON SCHEMA auth TO dashboard_user;
GRANT USAGE ON SCHEMA auth TO postgres;


--
-- Name: SCHEMA extensions; Type: ACL; Schema: -; Owner: postgres
--

GRANT USAGE ON SCHEMA extensions TO anon;
GRANT USAGE ON SCHEMA extensions TO authenticated;
GRANT USAGE ON SCHEMA extensions TO service_role;
GRANT ALL ON SCHEMA extensions TO dashboard_user;


--
-- Name: SCHEMA public; Type: ACL; Schema: -; Owner: pg_database_owner
--

GRANT USAGE ON SCHEMA public TO postgres;
GRANT USAGE ON SCHEMA public TO anon;
GRANT USAGE ON SCHEMA public TO authenticated;
GRANT USAGE ON SCHEMA public TO service_role;


--
-- Name: SCHEMA realtime; Type: ACL; Schema: -; Owner: supabase_admin
--

GRANT USAGE ON SCHEMA realtime TO postgres WITH GRANT OPTION;
GRANT USAGE ON SCHEMA realtime TO anon;
GRANT USAGE ON SCHEMA realtime TO service_role;
GRANT ALL ON SCHEMA realtime TO supabase_realtime_admin;
GRANT USAGE ON SCHEMA realtime TO authenticated;


--
-- Name: SCHEMA storage; Type: ACL; Schema: -; Owner: supabase_admin
--

GRANT USAGE ON SCHEMA storage TO postgres WITH GRANT OPTION;
GRANT USAGE ON SCHEMA storage TO anon;
GRANT USAGE ON SCHEMA storage TO authenticated;
GRANT USAGE ON SCHEMA storage TO service_role;
GRANT ALL ON SCHEMA storage TO supabase_storage_admin WITH GRANT OPTION;
GRANT ALL ON SCHEMA storage TO dashboard_user;


--
-- Name: SCHEMA vault; Type: ACL; Schema: -; Owner: supabase_admin
--

GRANT USAGE ON SCHEMA vault TO postgres WITH GRANT OPTION;
GRANT USAGE ON SCHEMA vault TO service_role;


--
-- Name: FUNCTION halfvec_in(cstring, oid, integer); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.halfvec_in(cstring, oid, integer) TO postgres;
GRANT ALL ON FUNCTION public.halfvec_in(cstring, oid, integer) TO anon;
GRANT ALL ON FUNCTION public.halfvec_in(cstring, oid, integer) TO authenticated;
GRANT ALL ON FUNCTION public.halfvec_in(cstring, oid, integer) TO service_role;


--
-- Name: FUNCTION halfvec_out(public.halfvec); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.halfvec_out(public.halfvec) TO postgres;
GRANT ALL ON FUNCTION public.halfvec_out(public.halfvec) TO anon;
GRANT ALL ON FUNCTION public.halfvec_out(public.halfvec) TO authenticated;
GRANT ALL ON FUNCTION public.halfvec_out(public.halfvec) TO service_role;


--
-- Name: FUNCTION halfvec_recv(internal, oid, integer); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.halfvec_recv(internal, oid, integer) TO postgres;
GRANT ALL ON FUNCTION public.halfvec_recv(internal, oid, integer) TO anon;
GRANT ALL ON FUNCTION public.halfvec_recv(internal, oid, integer) TO authenticated;
GRANT ALL ON FUNCTION public.halfvec_recv(internal, oid, integer) TO service_role;


--
-- Name: FUNCTION halfvec_send(public.halfvec); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.halfvec_send(public.halfvec) TO postgres;
GRANT ALL ON FUNCTION public.halfvec_send(public.halfvec) TO anon;
GRANT ALL ON FUNCTION public.halfvec_send(public.halfvec) TO authenticated;
GRANT ALL ON FUNCTION public.halfvec_send(public.halfvec) TO service_role;


--
-- Name: FUNCTION halfvec_typmod_in(cstring[]); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.halfvec_typmod_in(cstring[]) TO postgres;
GRANT ALL ON FUNCTION public.halfvec_typmod_in(cstring[]) TO anon;
GRANT ALL ON FUNCTION public.halfvec_typmod_in(cstring[]) TO authenticated;
GRANT ALL ON FUNCTION public.halfvec_typmod_in(cstring[]) TO service_role;


--
-- Name: FUNCTION sparsevec_in(cstring, oid, integer); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.sparsevec_in(cstring, oid, integer) TO postgres;
GRANT ALL ON FUNCTION public.sparsevec_in(cstring, oid, integer) TO anon;
GRANT ALL ON FUNCTION public.sparsevec_in(cstring, oid, integer) TO authenticated;
GRANT ALL ON FUNCTION public.sparsevec_in(cstring, oid, integer) TO service_role;


--
-- Name: FUNCTION sparsevec_out(public.sparsevec); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.sparsevec_out(public.sparsevec) TO postgres;
GRANT ALL ON FUNCTION public.sparsevec_out(public.sparsevec) TO anon;
GRANT ALL ON FUNCTION public.sparsevec_out(public.sparsevec) TO authenticated;
GRANT ALL ON FUNCTION public.sparsevec_out(public.sparsevec) TO service_role;


--
-- Name: FUNCTION sparsevec_recv(internal, oid, integer); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.sparsevec_recv(internal, oid, integer) TO postgres;
GRANT ALL ON FUNCTION public.sparsevec_recv(internal, oid, integer) TO anon;
GRANT ALL ON FUNCTION public.sparsevec_recv(internal, oid, integer) TO authenticated;
GRANT ALL ON FUNCTION public.sparsevec_recv(internal, oid, integer) TO service_role;


--
-- Name: FUNCTION sparsevec_send(public.sparsevec); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.sparsevec_send(public.sparsevec) TO postgres;
GRANT ALL ON FUNCTION public.sparsevec_send(public.sparsevec) TO anon;
GRANT ALL ON FUNCTION public.sparsevec_send(public.sparsevec) TO authenticated;
GRANT ALL ON FUNCTION public.sparsevec_send(public.sparsevec) TO service_role;


--
-- Name: FUNCTION sparsevec_typmod_in(cstring[]); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.sparsevec_typmod_in(cstring[]) TO postgres;
GRANT ALL ON FUNCTION public.sparsevec_typmod_in(cstring[]) TO anon;
GRANT ALL ON FUNCTION public.sparsevec_typmod_in(cstring[]) TO authenticated;
GRANT ALL ON FUNCTION public.sparsevec_typmod_in(cstring[]) TO service_role;


--
-- Name: FUNCTION vector_in(cstring, oid, integer); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.vector_in(cstring, oid, integer) TO postgres;
GRANT ALL ON FUNCTION public.vector_in(cstring, oid, integer) TO anon;
GRANT ALL ON FUNCTION public.vector_in(cstring, oid, integer) TO authenticated;
GRANT ALL ON FUNCTION public.vector_in(cstring, oid, integer) TO service_role;


--
-- Name: FUNCTION vector_out(public.vector); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.vector_out(public.vector) TO postgres;
GRANT ALL ON FUNCTION public.vector_out(public.vector) TO anon;
GRANT ALL ON FUNCTION public.vector_out(public.vector) TO authenticated;
GRANT ALL ON FUNCTION public.vector_out(public.vector) TO service_role;


--
-- Name: FUNCTION vector_recv(internal, oid, integer); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.vector_recv(internal, oid, integer) TO postgres;
GRANT ALL ON FUNCTION public.vector_recv(internal, oid, integer) TO anon;
GRANT ALL ON FUNCTION public.vector_recv(internal, oid, integer) TO authenticated;
GRANT ALL ON FUNCTION public.vector_recv(internal, oid, integer) TO service_role;


--
-- Name: FUNCTION vector_send(public.vector); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.vector_send(public.vector) TO postgres;
GRANT ALL ON FUNCTION public.vector_send(public.vector) TO anon;
GRANT ALL ON FUNCTION public.vector_send(public.vector) TO authenticated;
GRANT ALL ON FUNCTION public.vector_send(public.vector) TO service_role;


--
-- Name: FUNCTION vector_typmod_in(cstring[]); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.vector_typmod_in(cstring[]) TO postgres;
GRANT ALL ON FUNCTION public.vector_typmod_in(cstring[]) TO anon;
GRANT ALL ON FUNCTION public.vector_typmod_in(cstring[]) TO authenticated;
GRANT ALL ON FUNCTION public.vector_typmod_in(cstring[]) TO service_role;


--
-- Name: FUNCTION array_to_halfvec(real[], integer, boolean); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.array_to_halfvec(real[], integer, boolean) TO postgres;
GRANT ALL ON FUNCTION public.array_to_halfvec(real[], integer, boolean) TO anon;
GRANT ALL ON FUNCTION public.array_to_halfvec(real[], integer, boolean) TO authenticated;
GRANT ALL ON FUNCTION public.array_to_halfvec(real[], integer, boolean) TO service_role;


--
-- Name: FUNCTION array_to_sparsevec(real[], integer, boolean); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.array_to_sparsevec(real[], integer, boolean) TO postgres;
GRANT ALL ON FUNCTION public.array_to_sparsevec(real[], integer, boolean) TO anon;
GRANT ALL ON FUNCTION public.array_to_sparsevec(real[], integer, boolean) TO authenticated;
GRANT ALL ON FUNCTION public.array_to_sparsevec(real[], integer, boolean) TO service_role;


--
-- Name: FUNCTION array_to_vector(real[], integer, boolean); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.array_to_vector(real[], integer, boolean) TO postgres;
GRANT ALL ON FUNCTION public.array_to_vector(real[], integer, boolean) TO anon;
GRANT ALL ON FUNCTION public.array_to_vector(real[], integer, boolean) TO authenticated;
GRANT ALL ON FUNCTION public.array_to_vector(real[], integer, boolean) TO service_role;


--
-- Name: FUNCTION array_to_halfvec(double precision[], integer, boolean); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.array_to_halfvec(double precision[], integer, boolean) TO postgres;
GRANT ALL ON FUNCTION public.array_to_halfvec(double precision[], integer, boolean) TO anon;
GRANT ALL ON FUNCTION public.array_to_halfvec(double precision[], integer, boolean) TO authenticated;
GRANT ALL ON FUNCTION public.array_to_halfvec(double precision[], integer, boolean) TO service_role;


--
-- Name: FUNCTION array_to_sparsevec(double precision[], integer, boolean); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.array_to_sparsevec(double precision[], integer, boolean) TO postgres;
GRANT ALL ON FUNCTION public.array_to_sparsevec(double precision[], integer, boolean) TO anon;
GRANT ALL ON FUNCTION public.array_to_sparsevec(double precision[], integer, boolean) TO authenticated;
GRANT ALL ON FUNCTION public.array_to_sparsevec(double precision[], integer, boolean) TO service_role;


--
-- Name: FUNCTION array_to_vector(double precision[], integer, boolean); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.array_to_vector(double precision[], integer, boolean) TO postgres;
GRANT ALL ON FUNCTION public.array_to_vector(double precision[], integer, boolean) TO anon;
GRANT ALL ON FUNCTION public.array_to_vector(double precision[], integer, boolean) TO authenticated;
GRANT ALL ON FUNCTION public.array_to_vector(double precision[], integer, boolean) TO service_role;


--
-- Name: FUNCTION array_to_halfvec(integer[], integer, boolean); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.array_to_halfvec(integer[], integer, boolean) TO postgres;
GRANT ALL ON FUNCTION public.array_to_halfvec(integer[], integer, boolean) TO anon;
GRANT ALL ON FUNCTION public.array_to_halfvec(integer[], integer, boolean) TO authenticated;
GRANT ALL ON FUNCTION public.array_to_halfvec(integer[], integer, boolean) TO service_role;


--
-- Name: FUNCTION array_to_sparsevec(integer[], integer, boolean); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.array_to_sparsevec(integer[], integer, boolean) TO postgres;
GRANT ALL ON FUNCTION public.array_to_sparsevec(integer[], integer, boolean) TO anon;
GRANT ALL ON FUNCTION public.array_to_sparsevec(integer[], integer, boolean) TO authenticated;
GRANT ALL ON FUNCTION public.array_to_sparsevec(integer[], integer, boolean) TO service_role;


--
-- Name: FUNCTION array_to_vector(integer[], integer, boolean); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.array_to_vector(integer[], integer, boolean) TO postgres;
GRANT ALL ON FUNCTION public.array_to_vector(integer[], integer, boolean) TO anon;
GRANT ALL ON FUNCTION public.array_to_vector(integer[], integer, boolean) TO authenticated;
GRANT ALL ON FUNCTION public.array_to_vector(integer[], integer, boolean) TO service_role;


--
-- Name: FUNCTION array_to_halfvec(numeric[], integer, boolean); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.array_to_halfvec(numeric[], integer, boolean) TO postgres;
GRANT ALL ON FUNCTION public.array_to_halfvec(numeric[], integer, boolean) TO anon;
GRANT ALL ON FUNCTION public.array_to_halfvec(numeric[], integer, boolean) TO authenticated;
GRANT ALL ON FUNCTION public.array_to_halfvec(numeric[], integer, boolean) TO service_role;


--
-- Name: FUNCTION array_to_sparsevec(numeric[], integer, boolean); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.array_to_sparsevec(numeric[], integer, boolean) TO postgres;
GRANT ALL ON FUNCTION public.array_to_sparsevec(numeric[], integer, boolean) TO anon;
GRANT ALL ON FUNCTION public.array_to_sparsevec(numeric[], integer, boolean) TO authenticated;
GRANT ALL ON FUNCTION public.array_to_sparsevec(numeric[], integer, boolean) TO service_role;


--
-- Name: FUNCTION array_to_vector(numeric[], integer, boolean); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.array_to_vector(numeric[], integer, boolean) TO postgres;
GRANT ALL ON FUNCTION public.array_to_vector(numeric[], integer, boolean) TO anon;
GRANT ALL ON FUNCTION public.array_to_vector(numeric[], integer, boolean) TO authenticated;
GRANT ALL ON FUNCTION public.array_to_vector(numeric[], integer, boolean) TO service_role;


--
-- Name: FUNCTION halfvec_to_float4(public.halfvec, integer, boolean); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.halfvec_to_float4(public.halfvec, integer, boolean) TO postgres;
GRANT ALL ON FUNCTION public.halfvec_to_float4(public.halfvec, integer, boolean) TO anon;
GRANT ALL ON FUNCTION public.halfvec_to_float4(public.halfvec, integer, boolean) TO authenticated;
GRANT ALL ON FUNCTION public.halfvec_to_float4(public.halfvec, integer, boolean) TO service_role;


--
-- Name: FUNCTION halfvec(public.halfvec, integer, boolean); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.halfvec(public.halfvec, integer, boolean) TO postgres;
GRANT ALL ON FUNCTION public.halfvec(public.halfvec, integer, boolean) TO anon;
GRANT ALL ON FUNCTION public.halfvec(public.halfvec, integer, boolean) TO authenticated;
GRANT ALL ON FUNCTION public.halfvec(public.halfvec, integer, boolean) TO service_role;


--
-- Name: FUNCTION halfvec_to_sparsevec(public.halfvec, integer, boolean); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.halfvec_to_sparsevec(public.halfvec, integer, boolean) TO postgres;
GRANT ALL ON FUNCTION public.halfvec_to_sparsevec(public.halfvec, integer, boolean) TO anon;
GRANT ALL ON FUNCTION public.halfvec_to_sparsevec(public.halfvec, integer, boolean) TO authenticated;
GRANT ALL ON FUNCTION public.halfvec_to_sparsevec(public.halfvec, integer, boolean) TO service_role;


--
-- Name: FUNCTION halfvec_to_vector(public.halfvec, integer, boolean); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.halfvec_to_vector(public.halfvec, integer, boolean) TO postgres;
GRANT ALL ON FUNCTION public.halfvec_to_vector(public.halfvec, integer, boolean) TO anon;
GRANT ALL ON FUNCTION public.halfvec_to_vector(public.halfvec, integer, boolean) TO authenticated;
GRANT ALL ON FUNCTION public.halfvec_to_vector(public.halfvec, integer, boolean) TO service_role;


--
-- Name: FUNCTION sparsevec_to_halfvec(public.sparsevec, integer, boolean); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.sparsevec_to_halfvec(public.sparsevec, integer, boolean) TO postgres;
GRANT ALL ON FUNCTION public.sparsevec_to_halfvec(public.sparsevec, integer, boolean) TO anon;
GRANT ALL ON FUNCTION public.sparsevec_to_halfvec(public.sparsevec, integer, boolean) TO authenticated;
GRANT ALL ON FUNCTION public.sparsevec_to_halfvec(public.sparsevec, integer, boolean) TO service_role;


--
-- Name: FUNCTION sparsevec(public.sparsevec, integer, boolean); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.sparsevec(public.sparsevec, integer, boolean) TO postgres;
GRANT ALL ON FUNCTION public.sparsevec(public.sparsevec, integer, boolean) TO anon;
GRANT ALL ON FUNCTION public.sparsevec(public.sparsevec, integer, boolean) TO authenticated;
GRANT ALL ON FUNCTION public.sparsevec(public.sparsevec, integer, boolean) TO service_role;


--
-- Name: FUNCTION sparsevec_to_vector(public.sparsevec, integer, boolean); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.sparsevec_to_vector(public.sparsevec, integer, boolean) TO postgres;
GRANT ALL ON FUNCTION public.sparsevec_to_vector(public.sparsevec, integer, boolean) TO anon;
GRANT ALL ON FUNCTION public.sparsevec_to_vector(public.sparsevec, integer, boolean) TO authenticated;
GRANT ALL ON FUNCTION public.sparsevec_to_vector(public.sparsevec, integer, boolean) TO service_role;


--
-- Name: FUNCTION vector_to_float4(public.vector, integer, boolean); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.vector_to_float4(public.vector, integer, boolean) TO postgres;
GRANT ALL ON FUNCTION public.vector_to_float4(public.vector, integer, boolean) TO anon;
GRANT ALL ON FUNCTION public.vector_to_float4(public.vector, integer, boolean) TO authenticated;
GRANT ALL ON FUNCTION public.vector_to_float4(public.vector, integer, boolean) TO service_role;


--
-- Name: FUNCTION vector_to_halfvec(public.vector, integer, boolean); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.vector_to_halfvec(public.vector, integer, boolean) TO postgres;
GRANT ALL ON FUNCTION public.vector_to_halfvec(public.vector, integer, boolean) TO anon;
GRANT ALL ON FUNCTION public.vector_to_halfvec(public.vector, integer, boolean) TO authenticated;
GRANT ALL ON FUNCTION public.vector_to_halfvec(public.vector, integer, boolean) TO service_role;


--
-- Name: FUNCTION vector_to_sparsevec(public.vector, integer, boolean); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.vector_to_sparsevec(public.vector, integer, boolean) TO postgres;
GRANT ALL ON FUNCTION public.vector_to_sparsevec(public.vector, integer, boolean) TO anon;
GRANT ALL ON FUNCTION public.vector_to_sparsevec(public.vector, integer, boolean) TO authenticated;
GRANT ALL ON FUNCTION public.vector_to_sparsevec(public.vector, integer, boolean) TO service_role;


--
-- Name: FUNCTION vector(public.vector, integer, boolean); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.vector(public.vector, integer, boolean) TO postgres;
GRANT ALL ON FUNCTION public.vector(public.vector, integer, boolean) TO anon;
GRANT ALL ON FUNCTION public.vector(public.vector, integer, boolean) TO authenticated;
GRANT ALL ON FUNCTION public.vector(public.vector, integer, boolean) TO service_role;


--
-- Name: FUNCTION email(); Type: ACL; Schema: auth; Owner: supabase_auth_admin
--

GRANT ALL ON FUNCTION auth.email() TO dashboard_user;


--
-- Name: FUNCTION jwt(); Type: ACL; Schema: auth; Owner: supabase_auth_admin
--

GRANT ALL ON FUNCTION auth.jwt() TO postgres;
GRANT ALL ON FUNCTION auth.jwt() TO dashboard_user;


--
-- Name: FUNCTION role(); Type: ACL; Schema: auth; Owner: supabase_auth_admin
--

GRANT ALL ON FUNCTION auth.role() TO dashboard_user;


--
-- Name: FUNCTION uid(); Type: ACL; Schema: auth; Owner: supabase_auth_admin
--

GRANT ALL ON FUNCTION auth.uid() TO dashboard_user;


--
-- Name: FUNCTION armor(bytea); Type: ACL; Schema: extensions; Owner: postgres
--

REVOKE ALL ON FUNCTION extensions.armor(bytea) FROM postgres;
GRANT ALL ON FUNCTION extensions.armor(bytea) TO postgres WITH GRANT OPTION;
GRANT ALL ON FUNCTION extensions.armor(bytea) TO dashboard_user;


--
-- Name: FUNCTION armor(bytea, text[], text[]); Type: ACL; Schema: extensions; Owner: postgres
--

REVOKE ALL ON FUNCTION extensions.armor(bytea, text[], text[]) FROM postgres;
GRANT ALL ON FUNCTION extensions.armor(bytea, text[], text[]) TO postgres WITH GRANT OPTION;
GRANT ALL ON FUNCTION extensions.armor(bytea, text[], text[]) TO dashboard_user;


--
-- Name: FUNCTION crypt(text, text); Type: ACL; Schema: extensions; Owner: postgres
--

REVOKE ALL ON FUNCTION extensions.crypt(text, text) FROM postgres;
GRANT ALL ON FUNCTION extensions.crypt(text, text) TO postgres WITH GRANT OPTION;
GRANT ALL ON FUNCTION extensions.crypt(text, text) TO dashboard_user;


--
-- Name: FUNCTION dearmor(text); Type: ACL; Schema: extensions; Owner: postgres
--

REVOKE ALL ON FUNCTION extensions.dearmor(text) FROM postgres;
GRANT ALL ON FUNCTION extensions.dearmor(text) TO postgres WITH GRANT OPTION;
GRANT ALL ON FUNCTION extensions.dearmor(text) TO dashboard_user;


--
-- Name: FUNCTION decrypt(bytea, bytea, text); Type: ACL; Schema: extensions; Owner: postgres
--

REVOKE ALL ON FUNCTION extensions.decrypt(bytea, bytea, text) FROM postgres;
GRANT ALL ON FUNCTION extensions.decrypt(bytea, bytea, text) TO postgres WITH GRANT OPTION;
GRANT ALL ON FUNCTION extensions.decrypt(bytea, bytea, text) TO dashboard_user;


--
-- Name: FUNCTION decrypt_iv(bytea, bytea, bytea, text); Type: ACL; Schema: extensions; Owner: postgres
--

REVOKE ALL ON FUNCTION extensions.decrypt_iv(bytea, bytea, bytea, text) FROM postgres;
GRANT ALL ON FUNCTION extensions.decrypt_iv(bytea, bytea, bytea, text) TO postgres WITH GRANT OPTION;
GRANT ALL ON FUNCTION extensions.decrypt_iv(bytea, bytea, bytea, text) TO dashboard_user;


--
-- Name: FUNCTION digest(bytea, text); Type: ACL; Schema: extensions; Owner: postgres
--

REVOKE ALL ON FUNCTION extensions.digest(bytea, text) FROM postgres;
GRANT ALL ON FUNCTION extensions.digest(bytea, text) TO postgres WITH GRANT OPTION;
GRANT ALL ON FUNCTION extensions.digest(bytea, text) TO dashboard_user;


--
-- Name: FUNCTION digest(text, text); Type: ACL; Schema: extensions; Owner: postgres
--

REVOKE ALL ON FUNCTION extensions.digest(text, text) FROM postgres;
GRANT ALL ON FUNCTION extensions.digest(text, text) TO postgres WITH GRANT OPTION;
GRANT ALL ON FUNCTION extensions.digest(text, text) TO dashboard_user;


--
-- Name: FUNCTION encrypt(bytea, bytea, text); Type: ACL; Schema: extensions; Owner: postgres
--

REVOKE ALL ON FUNCTION extensions.encrypt(bytea, bytea, text) FROM postgres;
GRANT ALL ON FUNCTION extensions.encrypt(bytea, bytea, text) TO postgres WITH GRANT OPTION;
GRANT ALL ON FUNCTION extensions.encrypt(bytea, bytea, text) TO dashboard_user;


--
-- Name: FUNCTION encrypt_iv(bytea, bytea, bytea, text); Type: ACL; Schema: extensions; Owner: postgres
--

REVOKE ALL ON FUNCTION extensions.encrypt_iv(bytea, bytea, bytea, text) FROM postgres;
GRANT ALL ON FUNCTION extensions.encrypt_iv(bytea, bytea, bytea, text) TO postgres WITH GRANT OPTION;
GRANT ALL ON FUNCTION extensions.encrypt_iv(bytea, bytea, bytea, text) TO dashboard_user;


--
-- Name: FUNCTION gen_random_bytes(integer); Type: ACL; Schema: extensions; Owner: postgres
--

REVOKE ALL ON FUNCTION extensions.gen_random_bytes(integer) FROM postgres;
GRANT ALL ON FUNCTION extensions.gen_random_bytes(integer) TO postgres WITH GRANT OPTION;
GRANT ALL ON FUNCTION extensions.gen_random_bytes(integer) TO dashboard_user;


--
-- Name: FUNCTION gen_random_uuid(); Type: ACL; Schema: extensions; Owner: postgres
--

REVOKE ALL ON FUNCTION extensions.gen_random_uuid() FROM postgres;
GRANT ALL ON FUNCTION extensions.gen_random_uuid() TO postgres WITH GRANT OPTION;
GRANT ALL ON FUNCTION extensions.gen_random_uuid() TO dashboard_user;


--
-- Name: FUNCTION gen_salt(text); Type: ACL; Schema: extensions; Owner: postgres
--

REVOKE ALL ON FUNCTION extensions.gen_salt(text) FROM postgres;
GRANT ALL ON FUNCTION extensions.gen_salt(text) TO postgres WITH GRANT OPTION;
GRANT ALL ON FUNCTION extensions.gen_salt(text) TO dashboard_user;


--
-- Name: FUNCTION gen_salt(text, integer); Type: ACL; Schema: extensions; Owner: postgres
--

REVOKE ALL ON FUNCTION extensions.gen_salt(text, integer) FROM postgres;
GRANT ALL ON FUNCTION extensions.gen_salt(text, integer) TO postgres WITH GRANT OPTION;
GRANT ALL ON FUNCTION extensions.gen_salt(text, integer) TO dashboard_user;


--
-- Name: FUNCTION grant_pg_cron_access(); Type: ACL; Schema: extensions; Owner: supabase_admin
--

REVOKE ALL ON FUNCTION extensions.grant_pg_cron_access() FROM supabase_admin;
GRANT ALL ON FUNCTION extensions.grant_pg_cron_access() TO supabase_admin WITH GRANT OPTION;
GRANT ALL ON FUNCTION extensions.grant_pg_cron_access() TO dashboard_user;


--
-- Name: FUNCTION grant_pg_graphql_access(); Type: ACL; Schema: extensions; Owner: supabase_admin
--

GRANT ALL ON FUNCTION extensions.grant_pg_graphql_access() TO postgres WITH GRANT OPTION;


--
-- Name: FUNCTION grant_pg_net_access(); Type: ACL; Schema: extensions; Owner: supabase_admin
--

REVOKE ALL ON FUNCTION extensions.grant_pg_net_access() FROM supabase_admin;
GRANT ALL ON FUNCTION extensions.grant_pg_net_access() TO supabase_admin WITH GRANT OPTION;
GRANT ALL ON FUNCTION extensions.grant_pg_net_access() TO dashboard_user;


--
-- Name: FUNCTION hmac(bytea, bytea, text); Type: ACL; Schema: extensions; Owner: postgres
--

REVOKE ALL ON FUNCTION extensions.hmac(bytea, bytea, text) FROM postgres;
GRANT ALL ON FUNCTION extensions.hmac(bytea, bytea, text) TO postgres WITH GRANT OPTION;
GRANT ALL ON FUNCTION extensions.hmac(bytea, bytea, text) TO dashboard_user;


--
-- Name: FUNCTION hmac(text, text, text); Type: ACL; Schema: extensions; Owner: postgres
--

REVOKE ALL ON FUNCTION extensions.hmac(text, text, text) FROM postgres;
GRANT ALL ON FUNCTION extensions.hmac(text, text, text) TO postgres WITH GRANT OPTION;
GRANT ALL ON FUNCTION extensions.hmac(text, text, text) TO dashboard_user;


--
-- Name: FUNCTION pg_stat_statements(showtext boolean, OUT userid oid, OUT dbid oid, OUT toplevel boolean, OUT queryid bigint, OUT query text, OUT plans bigint, OUT total_plan_time double precision, OUT min_plan_time double precision, OUT max_plan_time double precision, OUT mean_plan_time double precision, OUT stddev_plan_time double precision, OUT calls bigint, OUT total_exec_time double precision, OUT min_exec_time double precision, OUT max_exec_time double precision, OUT mean_exec_time double precision, OUT stddev_exec_time double precision, OUT rows bigint, OUT shared_blks_hit bigint, OUT shared_blks_read bigint, OUT shared_blks_dirtied bigint, OUT shared_blks_written bigint, OUT local_blks_hit bigint, OUT local_blks_read bigint, OUT local_blks_dirtied bigint, OUT local_blks_written bigint, OUT temp_blks_read bigint, OUT temp_blks_written bigint, OUT shared_blk_read_time double precision, OUT shared_blk_write_time double precision, OUT local_blk_read_time double precision, OUT local_blk_write_time double precision, OUT temp_blk_read_time double precision, OUT temp_blk_write_time double precision, OUT wal_records bigint, OUT wal_fpi bigint, OUT wal_bytes numeric, OUT jit_functions bigint, OUT jit_generation_time double precision, OUT jit_inlining_count bigint, OUT jit_inlining_time double precision, OUT jit_optimization_count bigint, OUT jit_optimization_time double precision, OUT jit_emission_count bigint, OUT jit_emission_time double precision, OUT jit_deform_count bigint, OUT jit_deform_time double precision, OUT stats_since timestamp with time zone, OUT minmax_stats_since timestamp with time zone); Type: ACL; Schema: extensions; Owner: postgres
--

REVOKE ALL ON FUNCTION extensions.pg_stat_statements(showtext boolean, OUT userid oid, OUT dbid oid, OUT toplevel boolean, OUT queryid bigint, OUT query text, OUT plans bigint, OUT total_plan_time double precision, OUT min_plan_time double precision, OUT max_plan_time double precision, OUT mean_plan_time double precision, OUT stddev_plan_time double precision, OUT calls bigint, OUT total_exec_time double precision, OUT min_exec_time double precision, OUT max_exec_time double precision, OUT mean_exec_time double precision, OUT stddev_exec_time double precision, OUT rows bigint, OUT shared_blks_hit bigint, OUT shared_blks_read bigint, OUT shared_blks_dirtied bigint, OUT shared_blks_written bigint, OUT local_blks_hit bigint, OUT local_blks_read bigint, OUT local_blks_dirtied bigint, OUT local_blks_written bigint, OUT temp_blks_read bigint, OUT temp_blks_written bigint, OUT shared_blk_read_time double precision, OUT shared_blk_write_time double precision, OUT local_blk_read_time double precision, OUT local_blk_write_time double precision, OUT temp_blk_read_time double precision, OUT temp_blk_write_time double precision, OUT wal_records bigint, OUT wal_fpi bigint, OUT wal_bytes numeric, OUT jit_functions bigint, OUT jit_generation_time double precision, OUT jit_inlining_count bigint, OUT jit_inlining_time double precision, OUT jit_optimization_count bigint, OUT jit_optimization_time double precision, OUT jit_emission_count bigint, OUT jit_emission_time double precision, OUT jit_deform_count bigint, OUT jit_deform_time double precision, OUT stats_since timestamp with time zone, OUT minmax_stats_since timestamp with time zone) FROM postgres;
GRANT ALL ON FUNCTION extensions.pg_stat_statements(showtext boolean, OUT userid oid, OUT dbid oid, OUT toplevel boolean, OUT queryid bigint, OUT query text, OUT plans bigint, OUT total_plan_time double precision, OUT min_plan_time double precision, OUT max_plan_time double precision, OUT mean_plan_time double precision, OUT stddev_plan_time double precision, OUT calls bigint, OUT total_exec_time double precision, OUT min_exec_time double precision, OUT max_exec_time double precision, OUT mean_exec_time double precision, OUT stddev_exec_time double precision, OUT rows bigint, OUT shared_blks_hit bigint, OUT shared_blks_read bigint, OUT shared_blks_dirtied bigint, OUT shared_blks_written bigint, OUT local_blks_hit bigint, OUT local_blks_read bigint, OUT local_blks_dirtied bigint, OUT local_blks_written bigint, OUT temp_blks_read bigint, OUT temp_blks_written bigint, OUT shared_blk_read_time double precision, OUT shared_blk_write_time double precision, OUT local_blk_read_time double precision, OUT local_blk_write_time double precision, OUT temp_blk_read_time double precision, OUT temp_blk_write_time double precision, OUT wal_records bigint, OUT wal_fpi bigint, OUT wal_bytes numeric, OUT jit_functions bigint, OUT jit_generation_time double precision, OUT jit_inlining_count bigint, OUT jit_inlining_time double precision, OUT jit_optimization_count bigint, OUT jit_optimization_time double precision, OUT jit_emission_count bigint, OUT jit_emission_time double precision, OUT jit_deform_count bigint, OUT jit_deform_time double precision, OUT stats_since timestamp with time zone, OUT minmax_stats_since timestamp with time zone) TO postgres WITH GRANT OPTION;
GRANT ALL ON FUNCTION extensions.pg_stat_statements(showtext boolean, OUT userid oid, OUT dbid oid, OUT toplevel boolean, OUT queryid bigint, OUT query text, OUT plans bigint, OUT total_plan_time double precision, OUT min_plan_time double precision, OUT max_plan_time double precision, OUT mean_plan_time double precision, OUT stddev_plan_time double precision, OUT calls bigint, OUT total_exec_time double precision, OUT min_exec_time double precision, OUT max_exec_time double precision, OUT mean_exec_time double precision, OUT stddev_exec_time double precision, OUT rows bigint, OUT shared_blks_hit bigint, OUT shared_blks_read bigint, OUT shared_blks_dirtied bigint, OUT shared_blks_written bigint, OUT local_blks_hit bigint, OUT local_blks_read bigint, OUT local_blks_dirtied bigint, OUT local_blks_written bigint, OUT temp_blks_read bigint, OUT temp_blks_written bigint, OUT shared_blk_read_time double precision, OUT shared_blk_write_time double precision, OUT local_blk_read_time double precision, OUT local_blk_write_time double precision, OUT temp_blk_read_time double precision, OUT temp_blk_write_time double precision, OUT wal_records bigint, OUT wal_fpi bigint, OUT wal_bytes numeric, OUT jit_functions bigint, OUT jit_generation_time double precision, OUT jit_inlining_count bigint, OUT jit_inlining_time double precision, OUT jit_optimization_count bigint, OUT jit_optimization_time double precision, OUT jit_emission_count bigint, OUT jit_emission_time double precision, OUT jit_deform_count bigint, OUT jit_deform_time double precision, OUT stats_since timestamp with time zone, OUT minmax_stats_since timestamp with time zone) TO dashboard_user;


--
-- Name: FUNCTION pg_stat_statements_info(OUT dealloc bigint, OUT stats_reset timestamp with time zone); Type: ACL; Schema: extensions; Owner: postgres
--

REVOKE ALL ON FUNCTION extensions.pg_stat_statements_info(OUT dealloc bigint, OUT stats_reset timestamp with time zone) FROM postgres;
GRANT ALL ON FUNCTION extensions.pg_stat_statements_info(OUT dealloc bigint, OUT stats_reset timestamp with time zone) TO postgres WITH GRANT OPTION;
GRANT ALL ON FUNCTION extensions.pg_stat_statements_info(OUT dealloc bigint, OUT stats_reset timestamp with time zone) TO dashboard_user;


--
-- Name: FUNCTION pg_stat_statements_reset(userid oid, dbid oid, queryid bigint, minmax_only boolean); Type: ACL; Schema: extensions; Owner: postgres
--

REVOKE ALL ON FUNCTION extensions.pg_stat_statements_reset(userid oid, dbid oid, queryid bigint, minmax_only boolean) FROM postgres;
GRANT ALL ON FUNCTION extensions.pg_stat_statements_reset(userid oid, dbid oid, queryid bigint, minmax_only boolean) TO postgres WITH GRANT OPTION;
GRANT ALL ON FUNCTION extensions.pg_stat_statements_reset(userid oid, dbid oid, queryid bigint, minmax_only boolean) TO dashboard_user;


--
-- Name: FUNCTION pgp_armor_headers(text, OUT key text, OUT value text); Type: ACL; Schema: extensions; Owner: postgres
--

REVOKE ALL ON FUNCTION extensions.pgp_armor_headers(text, OUT key text, OUT value text) FROM postgres;
GRANT ALL ON FUNCTION extensions.pgp_armor_headers(text, OUT key text, OUT value text) TO postgres WITH GRANT OPTION;
GRANT ALL ON FUNCTION extensions.pgp_armor_headers(text, OUT key text, OUT value text) TO dashboard_user;


--
-- Name: FUNCTION pgp_key_id(bytea); Type: ACL; Schema: extensions; Owner: postgres
--

REVOKE ALL ON FUNCTION extensions.pgp_key_id(bytea) FROM postgres;
GRANT ALL ON FUNCTION extensions.pgp_key_id(bytea) TO postgres WITH GRANT OPTION;
GRANT ALL ON FUNCTION extensions.pgp_key_id(bytea) TO dashboard_user;


--
-- Name: FUNCTION pgp_pub_decrypt(bytea, bytea); Type: ACL; Schema: extensions; Owner: postgres
--

REVOKE ALL ON FUNCTION extensions.pgp_pub_decrypt(bytea, bytea) FROM postgres;
GRANT ALL ON FUNCTION extensions.pgp_pub_decrypt(bytea, bytea) TO postgres WITH GRANT OPTION;
GRANT ALL ON FUNCTION extensions.pgp_pub_decrypt(bytea, bytea) TO dashboard_user;


--
-- Name: FUNCTION pgp_pub_decrypt(bytea, bytea, text); Type: ACL; Schema: extensions; Owner: postgres
--

REVOKE ALL ON FUNCTION extensions.pgp_pub_decrypt(bytea, bytea, text) FROM postgres;
GRANT ALL ON FUNCTION extensions.pgp_pub_decrypt(bytea, bytea, text) TO postgres WITH GRANT OPTION;
GRANT ALL ON FUNCTION extensions.pgp_pub_decrypt(bytea, bytea, text) TO dashboard_user;


--
-- Name: FUNCTION pgp_pub_decrypt(bytea, bytea, text, text); Type: ACL; Schema: extensions; Owner: postgres
--

REVOKE ALL ON FUNCTION extensions.pgp_pub_decrypt(bytea, bytea, text, text) FROM postgres;
GRANT ALL ON FUNCTION extensions.pgp_pub_decrypt(bytea, bytea, text, text) TO postgres WITH GRANT OPTION;
GRANT ALL ON FUNCTION extensions.pgp_pub_decrypt(bytea, bytea, text, text) TO dashboard_user;


--
-- Name: FUNCTION pgp_pub_decrypt_bytea(bytea, bytea); Type: ACL; Schema: extensions; Owner: postgres
--

REVOKE ALL ON FUNCTION extensions.pgp_pub_decrypt_bytea(bytea, bytea) FROM postgres;
GRANT ALL ON FUNCTION extensions.pgp_pub_decrypt_bytea(bytea, bytea) TO postgres WITH GRANT OPTION;
GRANT ALL ON FUNCTION extensions.pgp_pub_decrypt_bytea(bytea, bytea) TO dashboard_user;


--
-- Name: FUNCTION pgp_pub_decrypt_bytea(bytea, bytea, text); Type: ACL; Schema: extensions; Owner: postgres
--

REVOKE ALL ON FUNCTION extensions.pgp_pub_decrypt_bytea(bytea, bytea, text) FROM postgres;
GRANT ALL ON FUNCTION extensions.pgp_pub_decrypt_bytea(bytea, bytea, text) TO postgres WITH GRANT OPTION;
GRANT ALL ON FUNCTION extensions.pgp_pub_decrypt_bytea(bytea, bytea, text) TO dashboard_user;


--
-- Name: FUNCTION pgp_pub_decrypt_bytea(bytea, bytea, text, text); Type: ACL; Schema: extensions; Owner: postgres
--

REVOKE ALL ON FUNCTION extensions.pgp_pub_decrypt_bytea(bytea, bytea, text, text) FROM postgres;
GRANT ALL ON FUNCTION extensions.pgp_pub_decrypt_bytea(bytea, bytea, text, text) TO postgres WITH GRANT OPTION;
GRANT ALL ON FUNCTION extensions.pgp_pub_decrypt_bytea(bytea, bytea, text, text) TO dashboard_user;


--
-- Name: FUNCTION pgp_pub_encrypt(text, bytea); Type: ACL; Schema: extensions; Owner: postgres
--

REVOKE ALL ON FUNCTION extensions.pgp_pub_encrypt(text, bytea) FROM postgres;
GRANT ALL ON FUNCTION extensions.pgp_pub_encrypt(text, bytea) TO postgres WITH GRANT OPTION;
GRANT ALL ON FUNCTION extensions.pgp_pub_encrypt(text, bytea) TO dashboard_user;


--
-- Name: FUNCTION pgp_pub_encrypt(text, bytea, text); Type: ACL; Schema: extensions; Owner: postgres
--

REVOKE ALL ON FUNCTION extensions.pgp_pub_encrypt(text, bytea, text) FROM postgres;
GRANT ALL ON FUNCTION extensions.pgp_pub_encrypt(text, bytea, text) TO postgres WITH GRANT OPTION;
GRANT ALL ON FUNCTION extensions.pgp_pub_encrypt(text, bytea, text) TO dashboard_user;


--
-- Name: FUNCTION pgp_pub_encrypt_bytea(bytea, bytea); Type: ACL; Schema: extensions; Owner: postgres
--

REVOKE ALL ON FUNCTION extensions.pgp_pub_encrypt_bytea(bytea, bytea) FROM postgres;
GRANT ALL ON FUNCTION extensions.pgp_pub_encrypt_bytea(bytea, bytea) TO postgres WITH GRANT OPTION;
GRANT ALL ON FUNCTION extensions.pgp_pub_encrypt_bytea(bytea, bytea) TO dashboard_user;


--
-- Name: FUNCTION pgp_pub_encrypt_bytea(bytea, bytea, text); Type: ACL; Schema: extensions; Owner: postgres
--

REVOKE ALL ON FUNCTION extensions.pgp_pub_encrypt_bytea(bytea, bytea, text) FROM postgres;
GRANT ALL ON FUNCTION extensions.pgp_pub_encrypt_bytea(bytea, bytea, text) TO postgres WITH GRANT OPTION;
GRANT ALL ON FUNCTION extensions.pgp_pub_encrypt_bytea(bytea, bytea, text) TO dashboard_user;


--
-- Name: FUNCTION pgp_sym_decrypt(bytea, text); Type: ACL; Schema: extensions; Owner: postgres
--

REVOKE ALL ON FUNCTION extensions.pgp_sym_decrypt(bytea, text) FROM postgres;
GRANT ALL ON FUNCTION extensions.pgp_sym_decrypt(bytea, text) TO postgres WITH GRANT OPTION;
GRANT ALL ON FUNCTION extensions.pgp_sym_decrypt(bytea, text) TO dashboard_user;


--
-- Name: FUNCTION pgp_sym_decrypt(bytea, text, text); Type: ACL; Schema: extensions; Owner: postgres
--

REVOKE ALL ON FUNCTION extensions.pgp_sym_decrypt(bytea, text, text) FROM postgres;
GRANT ALL ON FUNCTION extensions.pgp_sym_decrypt(bytea, text, text) TO postgres WITH GRANT OPTION;
GRANT ALL ON FUNCTION extensions.pgp_sym_decrypt(bytea, text, text) TO dashboard_user;


--
-- Name: FUNCTION pgp_sym_decrypt_bytea(bytea, text); Type: ACL; Schema: extensions; Owner: postgres
--

REVOKE ALL ON FUNCTION extensions.pgp_sym_decrypt_bytea(bytea, text) FROM postgres;
GRANT ALL ON FUNCTION extensions.pgp_sym_decrypt_bytea(bytea, text) TO postgres WITH GRANT OPTION;
GRANT ALL ON FUNCTION extensions.pgp_sym_decrypt_bytea(bytea, text) TO dashboard_user;


--
-- Name: FUNCTION pgp_sym_decrypt_bytea(bytea, text, text); Type: ACL; Schema: extensions; Owner: postgres
--

REVOKE ALL ON FUNCTION extensions.pgp_sym_decrypt_bytea(bytea, text, text) FROM postgres;
GRANT ALL ON FUNCTION extensions.pgp_sym_decrypt_bytea(bytea, text, text) TO postgres WITH GRANT OPTION;
GRANT ALL ON FUNCTION extensions.pgp_sym_decrypt_bytea(bytea, text, text) TO dashboard_user;


--
-- Name: FUNCTION pgp_sym_encrypt(text, text); Type: ACL; Schema: extensions; Owner: postgres
--

REVOKE ALL ON FUNCTION extensions.pgp_sym_encrypt(text, text) FROM postgres;
GRANT ALL ON FUNCTION extensions.pgp_sym_encrypt(text, text) TO postgres WITH GRANT OPTION;
GRANT ALL ON FUNCTION extensions.pgp_sym_encrypt(text, text) TO dashboard_user;


--
-- Name: FUNCTION pgp_sym_encrypt(text, text, text); Type: ACL; Schema: extensions; Owner: postgres
--

REVOKE ALL ON FUNCTION extensions.pgp_sym_encrypt(text, text, text) FROM postgres;
GRANT ALL ON FUNCTION extensions.pgp_sym_encrypt(text, text, text) TO postgres WITH GRANT OPTION;
GRANT ALL ON FUNCTION extensions.pgp_sym_encrypt(text, text, text) TO dashboard_user;


--
-- Name: FUNCTION pgp_sym_encrypt_bytea(bytea, text); Type: ACL; Schema: extensions; Owner: postgres
--

REVOKE ALL ON FUNCTION extensions.pgp_sym_encrypt_bytea(bytea, text) FROM postgres;
GRANT ALL ON FUNCTION extensions.pgp_sym_encrypt_bytea(bytea, text) TO postgres WITH GRANT OPTION;
GRANT ALL ON FUNCTION extensions.pgp_sym_encrypt_bytea(bytea, text) TO dashboard_user;


--
-- Name: FUNCTION pgp_sym_encrypt_bytea(bytea, text, text); Type: ACL; Schema: extensions; Owner: postgres
--

REVOKE ALL ON FUNCTION extensions.pgp_sym_encrypt_bytea(bytea, text, text) FROM postgres;
GRANT ALL ON FUNCTION extensions.pgp_sym_encrypt_bytea(bytea, text, text) TO postgres WITH GRANT OPTION;
GRANT ALL ON FUNCTION extensions.pgp_sym_encrypt_bytea(bytea, text, text) TO dashboard_user;


--
-- Name: FUNCTION pgrst_ddl_watch(); Type: ACL; Schema: extensions; Owner: supabase_admin
--

GRANT ALL ON FUNCTION extensions.pgrst_ddl_watch() TO postgres WITH GRANT OPTION;


--
-- Name: FUNCTION pgrst_drop_watch(); Type: ACL; Schema: extensions; Owner: supabase_admin
--

GRANT ALL ON FUNCTION extensions.pgrst_drop_watch() TO postgres WITH GRANT OPTION;


--
-- Name: FUNCTION set_graphql_placeholder(); Type: ACL; Schema: extensions; Owner: supabase_admin
--

GRANT ALL ON FUNCTION extensions.set_graphql_placeholder() TO postgres WITH GRANT OPTION;


--
-- Name: FUNCTION uuid_generate_v1(); Type: ACL; Schema: extensions; Owner: postgres
--

REVOKE ALL ON FUNCTION extensions.uuid_generate_v1() FROM postgres;
GRANT ALL ON FUNCTION extensions.uuid_generate_v1() TO postgres WITH GRANT OPTION;
GRANT ALL ON FUNCTION extensions.uuid_generate_v1() TO dashboard_user;


--
-- Name: FUNCTION uuid_generate_v1mc(); Type: ACL; Schema: extensions; Owner: postgres
--

REVOKE ALL ON FUNCTION extensions.uuid_generate_v1mc() FROM postgres;
GRANT ALL ON FUNCTION extensions.uuid_generate_v1mc() TO postgres WITH GRANT OPTION;
GRANT ALL ON FUNCTION extensions.uuid_generate_v1mc() TO dashboard_user;


--
-- Name: FUNCTION uuid_generate_v3(namespace uuid, name text); Type: ACL; Schema: extensions; Owner: postgres
--

REVOKE ALL ON FUNCTION extensions.uuid_generate_v3(namespace uuid, name text) FROM postgres;
GRANT ALL ON FUNCTION extensions.uuid_generate_v3(namespace uuid, name text) TO postgres WITH GRANT OPTION;
GRANT ALL ON FUNCTION extensions.uuid_generate_v3(namespace uuid, name text) TO dashboard_user;


--
-- Name: FUNCTION uuid_generate_v4(); Type: ACL; Schema: extensions; Owner: postgres
--

REVOKE ALL ON FUNCTION extensions.uuid_generate_v4() FROM postgres;
GRANT ALL ON FUNCTION extensions.uuid_generate_v4() TO postgres WITH GRANT OPTION;
GRANT ALL ON FUNCTION extensions.uuid_generate_v4() TO dashboard_user;


--
-- Name: FUNCTION uuid_generate_v5(namespace uuid, name text); Type: ACL; Schema: extensions; Owner: postgres
--

REVOKE ALL ON FUNCTION extensions.uuid_generate_v5(namespace uuid, name text) FROM postgres;
GRANT ALL ON FUNCTION extensions.uuid_generate_v5(namespace uuid, name text) TO postgres WITH GRANT OPTION;
GRANT ALL ON FUNCTION extensions.uuid_generate_v5(namespace uuid, name text) TO dashboard_user;


--
-- Name: FUNCTION uuid_nil(); Type: ACL; Schema: extensions; Owner: postgres
--

REVOKE ALL ON FUNCTION extensions.uuid_nil() FROM postgres;
GRANT ALL ON FUNCTION extensions.uuid_nil() TO postgres WITH GRANT OPTION;
GRANT ALL ON FUNCTION extensions.uuid_nil() TO dashboard_user;


--
-- Name: FUNCTION uuid_ns_dns(); Type: ACL; Schema: extensions; Owner: postgres
--

REVOKE ALL ON FUNCTION extensions.uuid_ns_dns() FROM postgres;
GRANT ALL ON FUNCTION extensions.uuid_ns_dns() TO postgres WITH GRANT OPTION;
GRANT ALL ON FUNCTION extensions.uuid_ns_dns() TO dashboard_user;


--
-- Name: FUNCTION uuid_ns_oid(); Type: ACL; Schema: extensions; Owner: postgres
--

REVOKE ALL ON FUNCTION extensions.uuid_ns_oid() FROM postgres;
GRANT ALL ON FUNCTION extensions.uuid_ns_oid() TO postgres WITH GRANT OPTION;
GRANT ALL ON FUNCTION extensions.uuid_ns_oid() TO dashboard_user;


--
-- Name: FUNCTION uuid_ns_url(); Type: ACL; Schema: extensions; Owner: postgres
--

REVOKE ALL ON FUNCTION extensions.uuid_ns_url() FROM postgres;
GRANT ALL ON FUNCTION extensions.uuid_ns_url() TO postgres WITH GRANT OPTION;
GRANT ALL ON FUNCTION extensions.uuid_ns_url() TO dashboard_user;


--
-- Name: FUNCTION uuid_ns_x500(); Type: ACL; Schema: extensions; Owner: postgres
--

REVOKE ALL ON FUNCTION extensions.uuid_ns_x500() FROM postgres;
GRANT ALL ON FUNCTION extensions.uuid_ns_x500() TO postgres WITH GRANT OPTION;
GRANT ALL ON FUNCTION extensions.uuid_ns_x500() TO dashboard_user;


--
-- Name: FUNCTION graphql("operationName" text, query text, variables jsonb, extensions jsonb); Type: ACL; Schema: graphql_public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION graphql_public.graphql("operationName" text, query text, variables jsonb, extensions jsonb) TO postgres;
GRANT ALL ON FUNCTION graphql_public.graphql("operationName" text, query text, variables jsonb, extensions jsonb) TO anon;
GRANT ALL ON FUNCTION graphql_public.graphql("operationName" text, query text, variables jsonb, extensions jsonb) TO authenticated;
GRANT ALL ON FUNCTION graphql_public.graphql("operationName" text, query text, variables jsonb, extensions jsonb) TO service_role;


--
-- Name: FUNCTION pg_reload_conf(); Type: ACL; Schema: pg_catalog; Owner: supabase_admin
--

GRANT ALL ON FUNCTION pg_catalog.pg_reload_conf() TO postgres WITH GRANT OPTION;


--
-- Name: FUNCTION get_auth(p_usename text); Type: ACL; Schema: pgbouncer; Owner: supabase_admin
--

REVOKE ALL ON FUNCTION pgbouncer.get_auth(p_usename text) FROM PUBLIC;
GRANT ALL ON FUNCTION pgbouncer.get_auth(p_usename text) TO pgbouncer;


--
-- Name: FUNCTION binary_quantize(public.halfvec); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.binary_quantize(public.halfvec) TO postgres;
GRANT ALL ON FUNCTION public.binary_quantize(public.halfvec) TO anon;
GRANT ALL ON FUNCTION public.binary_quantize(public.halfvec) TO authenticated;
GRANT ALL ON FUNCTION public.binary_quantize(public.halfvec) TO service_role;


--
-- Name: FUNCTION binary_quantize(public.vector); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.binary_quantize(public.vector) TO postgres;
GRANT ALL ON FUNCTION public.binary_quantize(public.vector) TO anon;
GRANT ALL ON FUNCTION public.binary_quantize(public.vector) TO authenticated;
GRANT ALL ON FUNCTION public.binary_quantize(public.vector) TO service_role;


--
-- Name: FUNCTION cosine_distance(public.halfvec, public.halfvec); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.cosine_distance(public.halfvec, public.halfvec) TO postgres;
GRANT ALL ON FUNCTION public.cosine_distance(public.halfvec, public.halfvec) TO anon;
GRANT ALL ON FUNCTION public.cosine_distance(public.halfvec, public.halfvec) TO authenticated;
GRANT ALL ON FUNCTION public.cosine_distance(public.halfvec, public.halfvec) TO service_role;


--
-- Name: FUNCTION cosine_distance(public.sparsevec, public.sparsevec); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.cosine_distance(public.sparsevec, public.sparsevec) TO postgres;
GRANT ALL ON FUNCTION public.cosine_distance(public.sparsevec, public.sparsevec) TO anon;
GRANT ALL ON FUNCTION public.cosine_distance(public.sparsevec, public.sparsevec) TO authenticated;
GRANT ALL ON FUNCTION public.cosine_distance(public.sparsevec, public.sparsevec) TO service_role;


--
-- Name: FUNCTION cosine_distance(public.vector, public.vector); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.cosine_distance(public.vector, public.vector) TO postgres;
GRANT ALL ON FUNCTION public.cosine_distance(public.vector, public.vector) TO anon;
GRANT ALL ON FUNCTION public.cosine_distance(public.vector, public.vector) TO authenticated;
GRANT ALL ON FUNCTION public.cosine_distance(public.vector, public.vector) TO service_role;


--
-- Name: FUNCTION halfvec_accum(double precision[], public.halfvec); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.halfvec_accum(double precision[], public.halfvec) TO postgres;
GRANT ALL ON FUNCTION public.halfvec_accum(double precision[], public.halfvec) TO anon;
GRANT ALL ON FUNCTION public.halfvec_accum(double precision[], public.halfvec) TO authenticated;
GRANT ALL ON FUNCTION public.halfvec_accum(double precision[], public.halfvec) TO service_role;


--
-- Name: FUNCTION halfvec_add(public.halfvec, public.halfvec); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.halfvec_add(public.halfvec, public.halfvec) TO postgres;
GRANT ALL ON FUNCTION public.halfvec_add(public.halfvec, public.halfvec) TO anon;
GRANT ALL ON FUNCTION public.halfvec_add(public.halfvec, public.halfvec) TO authenticated;
GRANT ALL ON FUNCTION public.halfvec_add(public.halfvec, public.halfvec) TO service_role;


--
-- Name: FUNCTION halfvec_avg(double precision[]); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.halfvec_avg(double precision[]) TO postgres;
GRANT ALL ON FUNCTION public.halfvec_avg(double precision[]) TO anon;
GRANT ALL ON FUNCTION public.halfvec_avg(double precision[]) TO authenticated;
GRANT ALL ON FUNCTION public.halfvec_avg(double precision[]) TO service_role;


--
-- Name: FUNCTION halfvec_cmp(public.halfvec, public.halfvec); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.halfvec_cmp(public.halfvec, public.halfvec) TO postgres;
GRANT ALL ON FUNCTION public.halfvec_cmp(public.halfvec, public.halfvec) TO anon;
GRANT ALL ON FUNCTION public.halfvec_cmp(public.halfvec, public.halfvec) TO authenticated;
GRANT ALL ON FUNCTION public.halfvec_cmp(public.halfvec, public.halfvec) TO service_role;


--
-- Name: FUNCTION halfvec_combine(double precision[], double precision[]); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.halfvec_combine(double precision[], double precision[]) TO postgres;
GRANT ALL ON FUNCTION public.halfvec_combine(double precision[], double precision[]) TO anon;
GRANT ALL ON FUNCTION public.halfvec_combine(double precision[], double precision[]) TO authenticated;
GRANT ALL ON FUNCTION public.halfvec_combine(double precision[], double precision[]) TO service_role;


--
-- Name: FUNCTION halfvec_concat(public.halfvec, public.halfvec); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.halfvec_concat(public.halfvec, public.halfvec) TO postgres;
GRANT ALL ON FUNCTION public.halfvec_concat(public.halfvec, public.halfvec) TO anon;
GRANT ALL ON FUNCTION public.halfvec_concat(public.halfvec, public.halfvec) TO authenticated;
GRANT ALL ON FUNCTION public.halfvec_concat(public.halfvec, public.halfvec) TO service_role;


--
-- Name: FUNCTION halfvec_eq(public.halfvec, public.halfvec); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.halfvec_eq(public.halfvec, public.halfvec) TO postgres;
GRANT ALL ON FUNCTION public.halfvec_eq(public.halfvec, public.halfvec) TO anon;
GRANT ALL ON FUNCTION public.halfvec_eq(public.halfvec, public.halfvec) TO authenticated;
GRANT ALL ON FUNCTION public.halfvec_eq(public.halfvec, public.halfvec) TO service_role;


--
-- Name: FUNCTION halfvec_ge(public.halfvec, public.halfvec); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.halfvec_ge(public.halfvec, public.halfvec) TO postgres;
GRANT ALL ON FUNCTION public.halfvec_ge(public.halfvec, public.halfvec) TO anon;
GRANT ALL ON FUNCTION public.halfvec_ge(public.halfvec, public.halfvec) TO authenticated;
GRANT ALL ON FUNCTION public.halfvec_ge(public.halfvec, public.halfvec) TO service_role;


--
-- Name: FUNCTION halfvec_gt(public.halfvec, public.halfvec); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.halfvec_gt(public.halfvec, public.halfvec) TO postgres;
GRANT ALL ON FUNCTION public.halfvec_gt(public.halfvec, public.halfvec) TO anon;
GRANT ALL ON FUNCTION public.halfvec_gt(public.halfvec, public.halfvec) TO authenticated;
GRANT ALL ON FUNCTION public.halfvec_gt(public.halfvec, public.halfvec) TO service_role;


--
-- Name: FUNCTION halfvec_l2_squared_distance(public.halfvec, public.halfvec); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.halfvec_l2_squared_distance(public.halfvec, public.halfvec) TO postgres;
GRANT ALL ON FUNCTION public.halfvec_l2_squared_distance(public.halfvec, public.halfvec) TO anon;
GRANT ALL ON FUNCTION public.halfvec_l2_squared_distance(public.halfvec, public.halfvec) TO authenticated;
GRANT ALL ON FUNCTION public.halfvec_l2_squared_distance(public.halfvec, public.halfvec) TO service_role;


--
-- Name: FUNCTION halfvec_le(public.halfvec, public.halfvec); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.halfvec_le(public.halfvec, public.halfvec) TO postgres;
GRANT ALL ON FUNCTION public.halfvec_le(public.halfvec, public.halfvec) TO anon;
GRANT ALL ON FUNCTION public.halfvec_le(public.halfvec, public.halfvec) TO authenticated;
GRANT ALL ON FUNCTION public.halfvec_le(public.halfvec, public.halfvec) TO service_role;


--
-- Name: FUNCTION halfvec_lt(public.halfvec, public.halfvec); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.halfvec_lt(public.halfvec, public.halfvec) TO postgres;
GRANT ALL ON FUNCTION public.halfvec_lt(public.halfvec, public.halfvec) TO anon;
GRANT ALL ON FUNCTION public.halfvec_lt(public.halfvec, public.halfvec) TO authenticated;
GRANT ALL ON FUNCTION public.halfvec_lt(public.halfvec, public.halfvec) TO service_role;


--
-- Name: FUNCTION halfvec_mul(public.halfvec, public.halfvec); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.halfvec_mul(public.halfvec, public.halfvec) TO postgres;
GRANT ALL ON FUNCTION public.halfvec_mul(public.halfvec, public.halfvec) TO anon;
GRANT ALL ON FUNCTION public.halfvec_mul(public.halfvec, public.halfvec) TO authenticated;
GRANT ALL ON FUNCTION public.halfvec_mul(public.halfvec, public.halfvec) TO service_role;


--
-- Name: FUNCTION halfvec_ne(public.halfvec, public.halfvec); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.halfvec_ne(public.halfvec, public.halfvec) TO postgres;
GRANT ALL ON FUNCTION public.halfvec_ne(public.halfvec, public.halfvec) TO anon;
GRANT ALL ON FUNCTION public.halfvec_ne(public.halfvec, public.halfvec) TO authenticated;
GRANT ALL ON FUNCTION public.halfvec_ne(public.halfvec, public.halfvec) TO service_role;


--
-- Name: FUNCTION halfvec_negative_inner_product(public.halfvec, public.halfvec); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.halfvec_negative_inner_product(public.halfvec, public.halfvec) TO postgres;
GRANT ALL ON FUNCTION public.halfvec_negative_inner_product(public.halfvec, public.halfvec) TO anon;
GRANT ALL ON FUNCTION public.halfvec_negative_inner_product(public.halfvec, public.halfvec) TO authenticated;
GRANT ALL ON FUNCTION public.halfvec_negative_inner_product(public.halfvec, public.halfvec) TO service_role;


--
-- Name: FUNCTION halfvec_spherical_distance(public.halfvec, public.halfvec); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.halfvec_spherical_distance(public.halfvec, public.halfvec) TO postgres;
GRANT ALL ON FUNCTION public.halfvec_spherical_distance(public.halfvec, public.halfvec) TO anon;
GRANT ALL ON FUNCTION public.halfvec_spherical_distance(public.halfvec, public.halfvec) TO authenticated;
GRANT ALL ON FUNCTION public.halfvec_spherical_distance(public.halfvec, public.halfvec) TO service_role;


--
-- Name: FUNCTION halfvec_sub(public.halfvec, public.halfvec); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.halfvec_sub(public.halfvec, public.halfvec) TO postgres;
GRANT ALL ON FUNCTION public.halfvec_sub(public.halfvec, public.halfvec) TO anon;
GRANT ALL ON FUNCTION public.halfvec_sub(public.halfvec, public.halfvec) TO authenticated;
GRANT ALL ON FUNCTION public.halfvec_sub(public.halfvec, public.halfvec) TO service_role;


--
-- Name: FUNCTION hamming_distance(bit, bit); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.hamming_distance(bit, bit) TO postgres;
GRANT ALL ON FUNCTION public.hamming_distance(bit, bit) TO anon;
GRANT ALL ON FUNCTION public.hamming_distance(bit, bit) TO authenticated;
GRANT ALL ON FUNCTION public.hamming_distance(bit, bit) TO service_role;


--
-- Name: FUNCTION hnsw_bit_support(internal); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.hnsw_bit_support(internal) TO postgres;
GRANT ALL ON FUNCTION public.hnsw_bit_support(internal) TO anon;
GRANT ALL ON FUNCTION public.hnsw_bit_support(internal) TO authenticated;
GRANT ALL ON FUNCTION public.hnsw_bit_support(internal) TO service_role;


--
-- Name: FUNCTION hnsw_halfvec_support(internal); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.hnsw_halfvec_support(internal) TO postgres;
GRANT ALL ON FUNCTION public.hnsw_halfvec_support(internal) TO anon;
GRANT ALL ON FUNCTION public.hnsw_halfvec_support(internal) TO authenticated;
GRANT ALL ON FUNCTION public.hnsw_halfvec_support(internal) TO service_role;


--
-- Name: FUNCTION hnsw_sparsevec_support(internal); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.hnsw_sparsevec_support(internal) TO postgres;
GRANT ALL ON FUNCTION public.hnsw_sparsevec_support(internal) TO anon;
GRANT ALL ON FUNCTION public.hnsw_sparsevec_support(internal) TO authenticated;
GRANT ALL ON FUNCTION public.hnsw_sparsevec_support(internal) TO service_role;


--
-- Name: FUNCTION hnswhandler(internal); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.hnswhandler(internal) TO postgres;
GRANT ALL ON FUNCTION public.hnswhandler(internal) TO anon;
GRANT ALL ON FUNCTION public.hnswhandler(internal) TO authenticated;
GRANT ALL ON FUNCTION public.hnswhandler(internal) TO service_role;


--
-- Name: FUNCTION inner_product(public.halfvec, public.halfvec); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.inner_product(public.halfvec, public.halfvec) TO postgres;
GRANT ALL ON FUNCTION public.inner_product(public.halfvec, public.halfvec) TO anon;
GRANT ALL ON FUNCTION public.inner_product(public.halfvec, public.halfvec) TO authenticated;
GRANT ALL ON FUNCTION public.inner_product(public.halfvec, public.halfvec) TO service_role;


--
-- Name: FUNCTION inner_product(public.sparsevec, public.sparsevec); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.inner_product(public.sparsevec, public.sparsevec) TO postgres;
GRANT ALL ON FUNCTION public.inner_product(public.sparsevec, public.sparsevec) TO anon;
GRANT ALL ON FUNCTION public.inner_product(public.sparsevec, public.sparsevec) TO authenticated;
GRANT ALL ON FUNCTION public.inner_product(public.sparsevec, public.sparsevec) TO service_role;


--
-- Name: FUNCTION inner_product(public.vector, public.vector); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.inner_product(public.vector, public.vector) TO postgres;
GRANT ALL ON FUNCTION public.inner_product(public.vector, public.vector) TO anon;
GRANT ALL ON FUNCTION public.inner_product(public.vector, public.vector) TO authenticated;
GRANT ALL ON FUNCTION public.inner_product(public.vector, public.vector) TO service_role;


--
-- Name: FUNCTION ivfflat_bit_support(internal); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.ivfflat_bit_support(internal) TO postgres;
GRANT ALL ON FUNCTION public.ivfflat_bit_support(internal) TO anon;
GRANT ALL ON FUNCTION public.ivfflat_bit_support(internal) TO authenticated;
GRANT ALL ON FUNCTION public.ivfflat_bit_support(internal) TO service_role;


--
-- Name: FUNCTION ivfflat_halfvec_support(internal); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.ivfflat_halfvec_support(internal) TO postgres;
GRANT ALL ON FUNCTION public.ivfflat_halfvec_support(internal) TO anon;
GRANT ALL ON FUNCTION public.ivfflat_halfvec_support(internal) TO authenticated;
GRANT ALL ON FUNCTION public.ivfflat_halfvec_support(internal) TO service_role;


--
-- Name: FUNCTION ivfflathandler(internal); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.ivfflathandler(internal) TO postgres;
GRANT ALL ON FUNCTION public.ivfflathandler(internal) TO anon;
GRANT ALL ON FUNCTION public.ivfflathandler(internal) TO authenticated;
GRANT ALL ON FUNCTION public.ivfflathandler(internal) TO service_role;


--
-- Name: FUNCTION jaccard_distance(bit, bit); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.jaccard_distance(bit, bit) TO postgres;
GRANT ALL ON FUNCTION public.jaccard_distance(bit, bit) TO anon;
GRANT ALL ON FUNCTION public.jaccard_distance(bit, bit) TO authenticated;
GRANT ALL ON FUNCTION public.jaccard_distance(bit, bit) TO service_role;


--
-- Name: FUNCTION l1_distance(public.halfvec, public.halfvec); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.l1_distance(public.halfvec, public.halfvec) TO postgres;
GRANT ALL ON FUNCTION public.l1_distance(public.halfvec, public.halfvec) TO anon;
GRANT ALL ON FUNCTION public.l1_distance(public.halfvec, public.halfvec) TO authenticated;
GRANT ALL ON FUNCTION public.l1_distance(public.halfvec, public.halfvec) TO service_role;


--
-- Name: FUNCTION l1_distance(public.sparsevec, public.sparsevec); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.l1_distance(public.sparsevec, public.sparsevec) TO postgres;
GRANT ALL ON FUNCTION public.l1_distance(public.sparsevec, public.sparsevec) TO anon;
GRANT ALL ON FUNCTION public.l1_distance(public.sparsevec, public.sparsevec) TO authenticated;
GRANT ALL ON FUNCTION public.l1_distance(public.sparsevec, public.sparsevec) TO service_role;


--
-- Name: FUNCTION l1_distance(public.vector, public.vector); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.l1_distance(public.vector, public.vector) TO postgres;
GRANT ALL ON FUNCTION public.l1_distance(public.vector, public.vector) TO anon;
GRANT ALL ON FUNCTION public.l1_distance(public.vector, public.vector) TO authenticated;
GRANT ALL ON FUNCTION public.l1_distance(public.vector, public.vector) TO service_role;


--
-- Name: FUNCTION l2_distance(public.halfvec, public.halfvec); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.l2_distance(public.halfvec, public.halfvec) TO postgres;
GRANT ALL ON FUNCTION public.l2_distance(public.halfvec, public.halfvec) TO anon;
GRANT ALL ON FUNCTION public.l2_distance(public.halfvec, public.halfvec) TO authenticated;
GRANT ALL ON FUNCTION public.l2_distance(public.halfvec, public.halfvec) TO service_role;


--
-- Name: FUNCTION l2_distance(public.sparsevec, public.sparsevec); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.l2_distance(public.sparsevec, public.sparsevec) TO postgres;
GRANT ALL ON FUNCTION public.l2_distance(public.sparsevec, public.sparsevec) TO anon;
GRANT ALL ON FUNCTION public.l2_distance(public.sparsevec, public.sparsevec) TO authenticated;
GRANT ALL ON FUNCTION public.l2_distance(public.sparsevec, public.sparsevec) TO service_role;


--
-- Name: FUNCTION l2_distance(public.vector, public.vector); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.l2_distance(public.vector, public.vector) TO postgres;
GRANT ALL ON FUNCTION public.l2_distance(public.vector, public.vector) TO anon;
GRANT ALL ON FUNCTION public.l2_distance(public.vector, public.vector) TO authenticated;
GRANT ALL ON FUNCTION public.l2_distance(public.vector, public.vector) TO service_role;


--
-- Name: FUNCTION l2_norm(public.halfvec); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.l2_norm(public.halfvec) TO postgres;
GRANT ALL ON FUNCTION public.l2_norm(public.halfvec) TO anon;
GRANT ALL ON FUNCTION public.l2_norm(public.halfvec) TO authenticated;
GRANT ALL ON FUNCTION public.l2_norm(public.halfvec) TO service_role;


--
-- Name: FUNCTION l2_norm(public.sparsevec); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.l2_norm(public.sparsevec) TO postgres;
GRANT ALL ON FUNCTION public.l2_norm(public.sparsevec) TO anon;
GRANT ALL ON FUNCTION public.l2_norm(public.sparsevec) TO authenticated;
GRANT ALL ON FUNCTION public.l2_norm(public.sparsevec) TO service_role;


--
-- Name: FUNCTION l2_normalize(public.halfvec); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.l2_normalize(public.halfvec) TO postgres;
GRANT ALL ON FUNCTION public.l2_normalize(public.halfvec) TO anon;
GRANT ALL ON FUNCTION public.l2_normalize(public.halfvec) TO authenticated;
GRANT ALL ON FUNCTION public.l2_normalize(public.halfvec) TO service_role;


--
-- Name: FUNCTION l2_normalize(public.sparsevec); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.l2_normalize(public.sparsevec) TO postgres;
GRANT ALL ON FUNCTION public.l2_normalize(public.sparsevec) TO anon;
GRANT ALL ON FUNCTION public.l2_normalize(public.sparsevec) TO authenticated;
GRANT ALL ON FUNCTION public.l2_normalize(public.sparsevec) TO service_role;


--
-- Name: FUNCTION l2_normalize(public.vector); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.l2_normalize(public.vector) TO postgres;
GRANT ALL ON FUNCTION public.l2_normalize(public.vector) TO anon;
GRANT ALL ON FUNCTION public.l2_normalize(public.vector) TO authenticated;
GRANT ALL ON FUNCTION public.l2_normalize(public.vector) TO service_role;


--
-- Name: FUNCTION sparsevec_cmp(public.sparsevec, public.sparsevec); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.sparsevec_cmp(public.sparsevec, public.sparsevec) TO postgres;
GRANT ALL ON FUNCTION public.sparsevec_cmp(public.sparsevec, public.sparsevec) TO anon;
GRANT ALL ON FUNCTION public.sparsevec_cmp(public.sparsevec, public.sparsevec) TO authenticated;
GRANT ALL ON FUNCTION public.sparsevec_cmp(public.sparsevec, public.sparsevec) TO service_role;


--
-- Name: FUNCTION sparsevec_eq(public.sparsevec, public.sparsevec); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.sparsevec_eq(public.sparsevec, public.sparsevec) TO postgres;
GRANT ALL ON FUNCTION public.sparsevec_eq(public.sparsevec, public.sparsevec) TO anon;
GRANT ALL ON FUNCTION public.sparsevec_eq(public.sparsevec, public.sparsevec) TO authenticated;
GRANT ALL ON FUNCTION public.sparsevec_eq(public.sparsevec, public.sparsevec) TO service_role;


--
-- Name: FUNCTION sparsevec_ge(public.sparsevec, public.sparsevec); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.sparsevec_ge(public.sparsevec, public.sparsevec) TO postgres;
GRANT ALL ON FUNCTION public.sparsevec_ge(public.sparsevec, public.sparsevec) TO anon;
GRANT ALL ON FUNCTION public.sparsevec_ge(public.sparsevec, public.sparsevec) TO authenticated;
GRANT ALL ON FUNCTION public.sparsevec_ge(public.sparsevec, public.sparsevec) TO service_role;


--
-- Name: FUNCTION sparsevec_gt(public.sparsevec, public.sparsevec); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.sparsevec_gt(public.sparsevec, public.sparsevec) TO postgres;
GRANT ALL ON FUNCTION public.sparsevec_gt(public.sparsevec, public.sparsevec) TO anon;
GRANT ALL ON FUNCTION public.sparsevec_gt(public.sparsevec, public.sparsevec) TO authenticated;
GRANT ALL ON FUNCTION public.sparsevec_gt(public.sparsevec, public.sparsevec) TO service_role;


--
-- Name: FUNCTION sparsevec_l2_squared_distance(public.sparsevec, public.sparsevec); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.sparsevec_l2_squared_distance(public.sparsevec, public.sparsevec) TO postgres;
GRANT ALL ON FUNCTION public.sparsevec_l2_squared_distance(public.sparsevec, public.sparsevec) TO anon;
GRANT ALL ON FUNCTION public.sparsevec_l2_squared_distance(public.sparsevec, public.sparsevec) TO authenticated;
GRANT ALL ON FUNCTION public.sparsevec_l2_squared_distance(public.sparsevec, public.sparsevec) TO service_role;


--
-- Name: FUNCTION sparsevec_le(public.sparsevec, public.sparsevec); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.sparsevec_le(public.sparsevec, public.sparsevec) TO postgres;
GRANT ALL ON FUNCTION public.sparsevec_le(public.sparsevec, public.sparsevec) TO anon;
GRANT ALL ON FUNCTION public.sparsevec_le(public.sparsevec, public.sparsevec) TO authenticated;
GRANT ALL ON FUNCTION public.sparsevec_le(public.sparsevec, public.sparsevec) TO service_role;


--
-- Name: FUNCTION sparsevec_lt(public.sparsevec, public.sparsevec); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.sparsevec_lt(public.sparsevec, public.sparsevec) TO postgres;
GRANT ALL ON FUNCTION public.sparsevec_lt(public.sparsevec, public.sparsevec) TO anon;
GRANT ALL ON FUNCTION public.sparsevec_lt(public.sparsevec, public.sparsevec) TO authenticated;
GRANT ALL ON FUNCTION public.sparsevec_lt(public.sparsevec, public.sparsevec) TO service_role;


--
-- Name: FUNCTION sparsevec_ne(public.sparsevec, public.sparsevec); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.sparsevec_ne(public.sparsevec, public.sparsevec) TO postgres;
GRANT ALL ON FUNCTION public.sparsevec_ne(public.sparsevec, public.sparsevec) TO anon;
GRANT ALL ON FUNCTION public.sparsevec_ne(public.sparsevec, public.sparsevec) TO authenticated;
GRANT ALL ON FUNCTION public.sparsevec_ne(public.sparsevec, public.sparsevec) TO service_role;


--
-- Name: FUNCTION sparsevec_negative_inner_product(public.sparsevec, public.sparsevec); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.sparsevec_negative_inner_product(public.sparsevec, public.sparsevec) TO postgres;
GRANT ALL ON FUNCTION public.sparsevec_negative_inner_product(public.sparsevec, public.sparsevec) TO anon;
GRANT ALL ON FUNCTION public.sparsevec_negative_inner_product(public.sparsevec, public.sparsevec) TO authenticated;
GRANT ALL ON FUNCTION public.sparsevec_negative_inner_product(public.sparsevec, public.sparsevec) TO service_role;


--
-- Name: FUNCTION subvector(public.halfvec, integer, integer); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.subvector(public.halfvec, integer, integer) TO postgres;
GRANT ALL ON FUNCTION public.subvector(public.halfvec, integer, integer) TO anon;
GRANT ALL ON FUNCTION public.subvector(public.halfvec, integer, integer) TO authenticated;
GRANT ALL ON FUNCTION public.subvector(public.halfvec, integer, integer) TO service_role;


--
-- Name: FUNCTION subvector(public.vector, integer, integer); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.subvector(public.vector, integer, integer) TO postgres;
GRANT ALL ON FUNCTION public.subvector(public.vector, integer, integer) TO anon;
GRANT ALL ON FUNCTION public.subvector(public.vector, integer, integer) TO authenticated;
GRANT ALL ON FUNCTION public.subvector(public.vector, integer, integer) TO service_role;


--
-- Name: FUNCTION vector_accum(double precision[], public.vector); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.vector_accum(double precision[], public.vector) TO postgres;
GRANT ALL ON FUNCTION public.vector_accum(double precision[], public.vector) TO anon;
GRANT ALL ON FUNCTION public.vector_accum(double precision[], public.vector) TO authenticated;
GRANT ALL ON FUNCTION public.vector_accum(double precision[], public.vector) TO service_role;


--
-- Name: FUNCTION vector_add(public.vector, public.vector); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.vector_add(public.vector, public.vector) TO postgres;
GRANT ALL ON FUNCTION public.vector_add(public.vector, public.vector) TO anon;
GRANT ALL ON FUNCTION public.vector_add(public.vector, public.vector) TO authenticated;
GRANT ALL ON FUNCTION public.vector_add(public.vector, public.vector) TO service_role;


--
-- Name: FUNCTION vector_avg(double precision[]); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.vector_avg(double precision[]) TO postgres;
GRANT ALL ON FUNCTION public.vector_avg(double precision[]) TO anon;
GRANT ALL ON FUNCTION public.vector_avg(double precision[]) TO authenticated;
GRANT ALL ON FUNCTION public.vector_avg(double precision[]) TO service_role;


--
-- Name: FUNCTION vector_cmp(public.vector, public.vector); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.vector_cmp(public.vector, public.vector) TO postgres;
GRANT ALL ON FUNCTION public.vector_cmp(public.vector, public.vector) TO anon;
GRANT ALL ON FUNCTION public.vector_cmp(public.vector, public.vector) TO authenticated;
GRANT ALL ON FUNCTION public.vector_cmp(public.vector, public.vector) TO service_role;


--
-- Name: FUNCTION vector_combine(double precision[], double precision[]); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.vector_combine(double precision[], double precision[]) TO postgres;
GRANT ALL ON FUNCTION public.vector_combine(double precision[], double precision[]) TO anon;
GRANT ALL ON FUNCTION public.vector_combine(double precision[], double precision[]) TO authenticated;
GRANT ALL ON FUNCTION public.vector_combine(double precision[], double precision[]) TO service_role;


--
-- Name: FUNCTION vector_concat(public.vector, public.vector); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.vector_concat(public.vector, public.vector) TO postgres;
GRANT ALL ON FUNCTION public.vector_concat(public.vector, public.vector) TO anon;
GRANT ALL ON FUNCTION public.vector_concat(public.vector, public.vector) TO authenticated;
GRANT ALL ON FUNCTION public.vector_concat(public.vector, public.vector) TO service_role;


--
-- Name: FUNCTION vector_dims(public.halfvec); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.vector_dims(public.halfvec) TO postgres;
GRANT ALL ON FUNCTION public.vector_dims(public.halfvec) TO anon;
GRANT ALL ON FUNCTION public.vector_dims(public.halfvec) TO authenticated;
GRANT ALL ON FUNCTION public.vector_dims(public.halfvec) TO service_role;


--
-- Name: FUNCTION vector_dims(public.vector); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.vector_dims(public.vector) TO postgres;
GRANT ALL ON FUNCTION public.vector_dims(public.vector) TO anon;
GRANT ALL ON FUNCTION public.vector_dims(public.vector) TO authenticated;
GRANT ALL ON FUNCTION public.vector_dims(public.vector) TO service_role;


--
-- Name: FUNCTION vector_eq(public.vector, public.vector); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.vector_eq(public.vector, public.vector) TO postgres;
GRANT ALL ON FUNCTION public.vector_eq(public.vector, public.vector) TO anon;
GRANT ALL ON FUNCTION public.vector_eq(public.vector, public.vector) TO authenticated;
GRANT ALL ON FUNCTION public.vector_eq(public.vector, public.vector) TO service_role;


--
-- Name: FUNCTION vector_ge(public.vector, public.vector); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.vector_ge(public.vector, public.vector) TO postgres;
GRANT ALL ON FUNCTION public.vector_ge(public.vector, public.vector) TO anon;
GRANT ALL ON FUNCTION public.vector_ge(public.vector, public.vector) TO authenticated;
GRANT ALL ON FUNCTION public.vector_ge(public.vector, public.vector) TO service_role;


--
-- Name: FUNCTION vector_gt(public.vector, public.vector); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.vector_gt(public.vector, public.vector) TO postgres;
GRANT ALL ON FUNCTION public.vector_gt(public.vector, public.vector) TO anon;
GRANT ALL ON FUNCTION public.vector_gt(public.vector, public.vector) TO authenticated;
GRANT ALL ON FUNCTION public.vector_gt(public.vector, public.vector) TO service_role;


--
-- Name: FUNCTION vector_l2_squared_distance(public.vector, public.vector); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.vector_l2_squared_distance(public.vector, public.vector) TO postgres;
GRANT ALL ON FUNCTION public.vector_l2_squared_distance(public.vector, public.vector) TO anon;
GRANT ALL ON FUNCTION public.vector_l2_squared_distance(public.vector, public.vector) TO authenticated;
GRANT ALL ON FUNCTION public.vector_l2_squared_distance(public.vector, public.vector) TO service_role;


--
-- Name: FUNCTION vector_le(public.vector, public.vector); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.vector_le(public.vector, public.vector) TO postgres;
GRANT ALL ON FUNCTION public.vector_le(public.vector, public.vector) TO anon;
GRANT ALL ON FUNCTION public.vector_le(public.vector, public.vector) TO authenticated;
GRANT ALL ON FUNCTION public.vector_le(public.vector, public.vector) TO service_role;


--
-- Name: FUNCTION vector_lt(public.vector, public.vector); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.vector_lt(public.vector, public.vector) TO postgres;
GRANT ALL ON FUNCTION public.vector_lt(public.vector, public.vector) TO anon;
GRANT ALL ON FUNCTION public.vector_lt(public.vector, public.vector) TO authenticated;
GRANT ALL ON FUNCTION public.vector_lt(public.vector, public.vector) TO service_role;


--
-- Name: FUNCTION vector_mul(public.vector, public.vector); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.vector_mul(public.vector, public.vector) TO postgres;
GRANT ALL ON FUNCTION public.vector_mul(public.vector, public.vector) TO anon;
GRANT ALL ON FUNCTION public.vector_mul(public.vector, public.vector) TO authenticated;
GRANT ALL ON FUNCTION public.vector_mul(public.vector, public.vector) TO service_role;


--
-- Name: FUNCTION vector_ne(public.vector, public.vector); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.vector_ne(public.vector, public.vector) TO postgres;
GRANT ALL ON FUNCTION public.vector_ne(public.vector, public.vector) TO anon;
GRANT ALL ON FUNCTION public.vector_ne(public.vector, public.vector) TO authenticated;
GRANT ALL ON FUNCTION public.vector_ne(public.vector, public.vector) TO service_role;


--
-- Name: FUNCTION vector_negative_inner_product(public.vector, public.vector); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.vector_negative_inner_product(public.vector, public.vector) TO postgres;
GRANT ALL ON FUNCTION public.vector_negative_inner_product(public.vector, public.vector) TO anon;
GRANT ALL ON FUNCTION public.vector_negative_inner_product(public.vector, public.vector) TO authenticated;
GRANT ALL ON FUNCTION public.vector_negative_inner_product(public.vector, public.vector) TO service_role;


--
-- Name: FUNCTION vector_norm(public.vector); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.vector_norm(public.vector) TO postgres;
GRANT ALL ON FUNCTION public.vector_norm(public.vector) TO anon;
GRANT ALL ON FUNCTION public.vector_norm(public.vector) TO authenticated;
GRANT ALL ON FUNCTION public.vector_norm(public.vector) TO service_role;


--
-- Name: FUNCTION vector_spherical_distance(public.vector, public.vector); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.vector_spherical_distance(public.vector, public.vector) TO postgres;
GRANT ALL ON FUNCTION public.vector_spherical_distance(public.vector, public.vector) TO anon;
GRANT ALL ON FUNCTION public.vector_spherical_distance(public.vector, public.vector) TO authenticated;
GRANT ALL ON FUNCTION public.vector_spherical_distance(public.vector, public.vector) TO service_role;


--
-- Name: FUNCTION vector_sub(public.vector, public.vector); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.vector_sub(public.vector, public.vector) TO postgres;
GRANT ALL ON FUNCTION public.vector_sub(public.vector, public.vector) TO anon;
GRANT ALL ON FUNCTION public.vector_sub(public.vector, public.vector) TO authenticated;
GRANT ALL ON FUNCTION public.vector_sub(public.vector, public.vector) TO service_role;


--
-- Name: FUNCTION apply_rls(wal jsonb, max_record_bytes integer); Type: ACL; Schema: realtime; Owner: supabase_realtime_admin
--

GRANT ALL ON FUNCTION realtime.apply_rls(wal jsonb, max_record_bytes integer) TO postgres;
GRANT ALL ON FUNCTION realtime.apply_rls(wal jsonb, max_record_bytes integer) TO dashboard_user;
GRANT ALL ON FUNCTION realtime.apply_rls(wal jsonb, max_record_bytes integer) TO anon;
GRANT ALL ON FUNCTION realtime.apply_rls(wal jsonb, max_record_bytes integer) TO authenticated;
GRANT ALL ON FUNCTION realtime.apply_rls(wal jsonb, max_record_bytes integer) TO service_role;


--
-- Name: FUNCTION broadcast_changes(topic_name text, event_name text, operation text, table_name text, table_schema text, new record, old record, level text); Type: ACL; Schema: realtime; Owner: supabase_realtime_admin
--

GRANT ALL ON FUNCTION realtime.broadcast_changes(topic_name text, event_name text, operation text, table_name text, table_schema text, new record, old record, level text) TO postgres;
GRANT ALL ON FUNCTION realtime.broadcast_changes(topic_name text, event_name text, operation text, table_name text, table_schema text, new record, old record, level text) TO dashboard_user;


--
-- Name: FUNCTION build_prepared_statement_sql(prepared_statement_name text, entity regclass, columns realtime.wal_column[]); Type: ACL; Schema: realtime; Owner: supabase_realtime_admin
--

GRANT ALL ON FUNCTION realtime.build_prepared_statement_sql(prepared_statement_name text, entity regclass, columns realtime.wal_column[]) TO postgres;
GRANT ALL ON FUNCTION realtime.build_prepared_statement_sql(prepared_statement_name text, entity regclass, columns realtime.wal_column[]) TO dashboard_user;
GRANT ALL ON FUNCTION realtime.build_prepared_statement_sql(prepared_statement_name text, entity regclass, columns realtime.wal_column[]) TO anon;
GRANT ALL ON FUNCTION realtime.build_prepared_statement_sql(prepared_statement_name text, entity regclass, columns realtime.wal_column[]) TO authenticated;
GRANT ALL ON FUNCTION realtime.build_prepared_statement_sql(prepared_statement_name text, entity regclass, columns realtime.wal_column[]) TO service_role;


--
-- Name: FUNCTION "cast"(val text, type_ regtype); Type: ACL; Schema: realtime; Owner: supabase_realtime_admin
--

GRANT ALL ON FUNCTION realtime."cast"(val text, type_ regtype) TO postgres;
GRANT ALL ON FUNCTION realtime."cast"(val text, type_ regtype) TO dashboard_user;
GRANT ALL ON FUNCTION realtime."cast"(val text, type_ regtype) TO anon;
GRANT ALL ON FUNCTION realtime."cast"(val text, type_ regtype) TO authenticated;
GRANT ALL ON FUNCTION realtime."cast"(val text, type_ regtype) TO service_role;


--
-- Name: FUNCTION check_equality_op(op realtime.equality_op, type_ regtype, val_1 text, val_2 text); Type: ACL; Schema: realtime; Owner: supabase_realtime_admin
--

GRANT ALL ON FUNCTION realtime.check_equality_op(op realtime.equality_op, type_ regtype, val_1 text, val_2 text) TO postgres;
GRANT ALL ON FUNCTION realtime.check_equality_op(op realtime.equality_op, type_ regtype, val_1 text, val_2 text) TO dashboard_user;
GRANT ALL ON FUNCTION realtime.check_equality_op(op realtime.equality_op, type_ regtype, val_1 text, val_2 text) TO anon;
GRANT ALL ON FUNCTION realtime.check_equality_op(op realtime.equality_op, type_ regtype, val_1 text, val_2 text) TO authenticated;
GRANT ALL ON FUNCTION realtime.check_equality_op(op realtime.equality_op, type_ regtype, val_1 text, val_2 text) TO service_role;


--
-- Name: FUNCTION check_equality_op(op realtime.equality_op, type_ regtype, val_1 text, val_2 text, negate boolean); Type: ACL; Schema: realtime; Owner: supabase_realtime_admin
--

GRANT ALL ON FUNCTION realtime.check_equality_op(op realtime.equality_op, type_ regtype, val_1 text, val_2 text, negate boolean) TO postgres;
GRANT ALL ON FUNCTION realtime.check_equality_op(op realtime.equality_op, type_ regtype, val_1 text, val_2 text, negate boolean) TO dashboard_user;
GRANT ALL ON FUNCTION realtime.check_equality_op(op realtime.equality_op, type_ regtype, val_1 text, val_2 text, negate boolean) TO anon;
GRANT ALL ON FUNCTION realtime.check_equality_op(op realtime.equality_op, type_ regtype, val_1 text, val_2 text, negate boolean) TO authenticated;
GRANT ALL ON FUNCTION realtime.check_equality_op(op realtime.equality_op, type_ regtype, val_1 text, val_2 text, negate boolean) TO service_role;


--
-- Name: FUNCTION is_visible_through_filters(columns realtime.wal_column[], filters realtime.user_defined_filter[]); Type: ACL; Schema: realtime; Owner: supabase_realtime_admin
--

GRANT ALL ON FUNCTION realtime.is_visible_through_filters(columns realtime.wal_column[], filters realtime.user_defined_filter[]) TO postgres;
GRANT ALL ON FUNCTION realtime.is_visible_through_filters(columns realtime.wal_column[], filters realtime.user_defined_filter[]) TO dashboard_user;
GRANT ALL ON FUNCTION realtime.is_visible_through_filters(columns realtime.wal_column[], filters realtime.user_defined_filter[]) TO anon;
GRANT ALL ON FUNCTION realtime.is_visible_through_filters(columns realtime.wal_column[], filters realtime.user_defined_filter[]) TO authenticated;
GRANT ALL ON FUNCTION realtime.is_visible_through_filters(columns realtime.wal_column[], filters realtime.user_defined_filter[]) TO service_role;


--
-- Name: FUNCTION list_changes(publication name, slot_name name, max_changes integer, max_record_bytes integer); Type: ACL; Schema: realtime; Owner: supabase_realtime_admin
--

GRANT ALL ON FUNCTION realtime.list_changes(publication name, slot_name name, max_changes integer, max_record_bytes integer) TO postgres;
GRANT ALL ON FUNCTION realtime.list_changes(publication name, slot_name name, max_changes integer, max_record_bytes integer) TO dashboard_user;


--
-- Name: FUNCTION list_changes_sync(publication name, slot_name name, max_changes integer, max_record_bytes integer); Type: ACL; Schema: realtime; Owner: supabase_realtime_admin
--

GRANT ALL ON FUNCTION realtime.list_changes_sync(publication name, slot_name name, max_changes integer, max_record_bytes integer) TO postgres;
GRANT ALL ON FUNCTION realtime.list_changes_sync(publication name, slot_name name, max_changes integer, max_record_bytes integer) TO dashboard_user;


--
-- Name: FUNCTION quote_wal2json(entity regclass); Type: ACL; Schema: realtime; Owner: supabase_realtime_admin
--

GRANT ALL ON FUNCTION realtime.quote_wal2json(entity regclass) TO postgres;
GRANT ALL ON FUNCTION realtime.quote_wal2json(entity regclass) TO dashboard_user;
GRANT ALL ON FUNCTION realtime.quote_wal2json(entity regclass) TO anon;
GRANT ALL ON FUNCTION realtime.quote_wal2json(entity regclass) TO authenticated;
GRANT ALL ON FUNCTION realtime.quote_wal2json(entity regclass) TO service_role;


--
-- Name: FUNCTION send(payload jsonb, event text, topic text, private boolean); Type: ACL; Schema: realtime; Owner: supabase_realtime_admin
--

GRANT ALL ON FUNCTION realtime.send(payload jsonb, event text, topic text, private boolean) TO postgres;
GRANT ALL ON FUNCTION realtime.send(payload jsonb, event text, topic text, private boolean) TO dashboard_user;


--
-- Name: FUNCTION send_binary(payload bytea, event text, topic text, private boolean); Type: ACL; Schema: realtime; Owner: supabase_realtime_admin
--

GRANT ALL ON FUNCTION realtime.send_binary(payload bytea, event text, topic text, private boolean) TO postgres;
GRANT ALL ON FUNCTION realtime.send_binary(payload bytea, event text, topic text, private boolean) TO dashboard_user;


--
-- Name: FUNCTION settled_changes(slot_name name, max_changes integer, VARIADIC opts text[]); Type: ACL; Schema: realtime; Owner: supabase_realtime_admin
--

GRANT ALL ON FUNCTION realtime.settled_changes(slot_name name, max_changes integer, VARIADIC opts text[]) TO postgres;
GRANT ALL ON FUNCTION realtime.settled_changes(slot_name name, max_changes integer, VARIADIC opts text[]) TO dashboard_user;


--
-- Name: FUNCTION subscription_check_filters(); Type: ACL; Schema: realtime; Owner: supabase_realtime_admin
--

GRANT ALL ON FUNCTION realtime.subscription_check_filters() TO postgres;
GRANT ALL ON FUNCTION realtime.subscription_check_filters() TO dashboard_user;
GRANT ALL ON FUNCTION realtime.subscription_check_filters() TO anon;
GRANT ALL ON FUNCTION realtime.subscription_check_filters() TO authenticated;
GRANT ALL ON FUNCTION realtime.subscription_check_filters() TO service_role;


--
-- Name: FUNCTION to_regrole(role_name text); Type: ACL; Schema: realtime; Owner: supabase_realtime_admin
--

GRANT ALL ON FUNCTION realtime.to_regrole(role_name text) TO postgres;
GRANT ALL ON FUNCTION realtime.to_regrole(role_name text) TO dashboard_user;
GRANT ALL ON FUNCTION realtime.to_regrole(role_name text) TO anon;
GRANT ALL ON FUNCTION realtime.to_regrole(role_name text) TO authenticated;
GRANT ALL ON FUNCTION realtime.to_regrole(role_name text) TO service_role;


--
-- Name: FUNCTION topic(); Type: ACL; Schema: realtime; Owner: supabase_realtime_admin
--

GRANT ALL ON FUNCTION realtime.topic() TO postgres;
GRANT ALL ON FUNCTION realtime.topic() TO dashboard_user;


--
-- Name: FUNCTION wal2json_escape_identifier(name text); Type: ACL; Schema: realtime; Owner: supabase_realtime_admin
--

GRANT ALL ON FUNCTION realtime.wal2json_escape_identifier(name text) TO postgres;
GRANT ALL ON FUNCTION realtime.wal2json_escape_identifier(name text) TO dashboard_user;


--
-- Name: FUNCTION _crypto_aead_det_decrypt(message bytea, additional bytea, key_id bigint, context bytea, nonce bytea); Type: ACL; Schema: vault; Owner: supabase_admin
--

GRANT ALL ON FUNCTION vault._crypto_aead_det_decrypt(message bytea, additional bytea, key_id bigint, context bytea, nonce bytea) TO postgres WITH GRANT OPTION;
GRANT ALL ON FUNCTION vault._crypto_aead_det_decrypt(message bytea, additional bytea, key_id bigint, context bytea, nonce bytea) TO service_role;


--
-- Name: FUNCTION create_secret(new_secret text, new_name text, new_description text, new_key_id uuid); Type: ACL; Schema: vault; Owner: supabase_admin
--

GRANT ALL ON FUNCTION vault.create_secret(new_secret text, new_name text, new_description text, new_key_id uuid) TO postgres WITH GRANT OPTION;
GRANT ALL ON FUNCTION vault.create_secret(new_secret text, new_name text, new_description text, new_key_id uuid) TO service_role;


--
-- Name: FUNCTION update_secret(secret_id uuid, new_secret text, new_name text, new_description text, new_key_id uuid); Type: ACL; Schema: vault; Owner: supabase_admin
--

GRANT ALL ON FUNCTION vault.update_secret(secret_id uuid, new_secret text, new_name text, new_description text, new_key_id uuid) TO postgres WITH GRANT OPTION;
GRANT ALL ON FUNCTION vault.update_secret(secret_id uuid, new_secret text, new_name text, new_description text, new_key_id uuid) TO service_role;


--
-- Name: FUNCTION avg(public.halfvec); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.avg(public.halfvec) TO postgres;
GRANT ALL ON FUNCTION public.avg(public.halfvec) TO anon;
GRANT ALL ON FUNCTION public.avg(public.halfvec) TO authenticated;
GRANT ALL ON FUNCTION public.avg(public.halfvec) TO service_role;


--
-- Name: FUNCTION avg(public.vector); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.avg(public.vector) TO postgres;
GRANT ALL ON FUNCTION public.avg(public.vector) TO anon;
GRANT ALL ON FUNCTION public.avg(public.vector) TO authenticated;
GRANT ALL ON FUNCTION public.avg(public.vector) TO service_role;


--
-- Name: FUNCTION sum(public.halfvec); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.sum(public.halfvec) TO postgres;
GRANT ALL ON FUNCTION public.sum(public.halfvec) TO anon;
GRANT ALL ON FUNCTION public.sum(public.halfvec) TO authenticated;
GRANT ALL ON FUNCTION public.sum(public.halfvec) TO service_role;


--
-- Name: FUNCTION sum(public.vector); Type: ACL; Schema: public; Owner: supabase_admin
--

GRANT ALL ON FUNCTION public.sum(public.vector) TO postgres;
GRANT ALL ON FUNCTION public.sum(public.vector) TO anon;
GRANT ALL ON FUNCTION public.sum(public.vector) TO authenticated;
GRANT ALL ON FUNCTION public.sum(public.vector) TO service_role;


--
-- Name: TABLE audit_log_entries; Type: ACL; Schema: auth; Owner: supabase_auth_admin
--

GRANT ALL ON TABLE auth.audit_log_entries TO dashboard_user;
GRANT INSERT,REFERENCES,DELETE,TRIGGER,TRUNCATE,MAINTAIN,UPDATE ON TABLE auth.audit_log_entries TO postgres;
GRANT SELECT ON TABLE auth.audit_log_entries TO postgres WITH GRANT OPTION;


--
-- Name: TABLE custom_oauth_providers; Type: ACL; Schema: auth; Owner: supabase_auth_admin
--

GRANT ALL ON TABLE auth.custom_oauth_providers TO postgres;
GRANT ALL ON TABLE auth.custom_oauth_providers TO dashboard_user;


--
-- Name: TABLE flow_state; Type: ACL; Schema: auth; Owner: supabase_auth_admin
--

GRANT INSERT,REFERENCES,DELETE,TRIGGER,TRUNCATE,MAINTAIN,UPDATE ON TABLE auth.flow_state TO postgres;
GRANT SELECT ON TABLE auth.flow_state TO postgres WITH GRANT OPTION;
GRANT ALL ON TABLE auth.flow_state TO dashboard_user;


--
-- Name: TABLE identities; Type: ACL; Schema: auth; Owner: supabase_auth_admin
--

GRANT INSERT,REFERENCES,DELETE,TRIGGER,TRUNCATE,MAINTAIN,UPDATE ON TABLE auth.identities TO postgres;
GRANT SELECT ON TABLE auth.identities TO postgres WITH GRANT OPTION;
GRANT ALL ON TABLE auth.identities TO dashboard_user;


--
-- Name: TABLE instances; Type: ACL; Schema: auth; Owner: supabase_auth_admin
--

GRANT ALL ON TABLE auth.instances TO dashboard_user;
GRANT INSERT,REFERENCES,DELETE,TRIGGER,TRUNCATE,MAINTAIN,UPDATE ON TABLE auth.instances TO postgres;
GRANT SELECT ON TABLE auth.instances TO postgres WITH GRANT OPTION;


--
-- Name: TABLE mfa_amr_claims; Type: ACL; Schema: auth; Owner: supabase_auth_admin
--

GRANT INSERT,REFERENCES,DELETE,TRIGGER,TRUNCATE,MAINTAIN,UPDATE ON TABLE auth.mfa_amr_claims TO postgres;
GRANT SELECT ON TABLE auth.mfa_amr_claims TO postgres WITH GRANT OPTION;
GRANT ALL ON TABLE auth.mfa_amr_claims TO dashboard_user;


--
-- Name: TABLE mfa_challenges; Type: ACL; Schema: auth; Owner: supabase_auth_admin
--

GRANT INSERT,REFERENCES,DELETE,TRIGGER,TRUNCATE,MAINTAIN,UPDATE ON TABLE auth.mfa_challenges TO postgres;
GRANT SELECT ON TABLE auth.mfa_challenges TO postgres WITH GRANT OPTION;
GRANT ALL ON TABLE auth.mfa_challenges TO dashboard_user;


--
-- Name: TABLE mfa_factors; Type: ACL; Schema: auth; Owner: supabase_auth_admin
--

GRANT INSERT,REFERENCES,DELETE,TRIGGER,TRUNCATE,MAINTAIN,UPDATE ON TABLE auth.mfa_factors TO postgres;
GRANT SELECT ON TABLE auth.mfa_factors TO postgres WITH GRANT OPTION;
GRANT ALL ON TABLE auth.mfa_factors TO dashboard_user;


--
-- Name: TABLE mfa_recovery_code_sets; Type: ACL; Schema: auth; Owner: supabase_auth_admin
--

GRANT ALL ON TABLE auth.mfa_recovery_code_sets TO postgres;
GRANT ALL ON TABLE auth.mfa_recovery_code_sets TO dashboard_user;


--
-- Name: TABLE mfa_recovery_codes; Type: ACL; Schema: auth; Owner: supabase_auth_admin
--

GRANT ALL ON TABLE auth.mfa_recovery_codes TO postgres;
GRANT ALL ON TABLE auth.mfa_recovery_codes TO dashboard_user;


--
-- Name: TABLE oauth_authorizations; Type: ACL; Schema: auth; Owner: supabase_auth_admin
--

GRANT ALL ON TABLE auth.oauth_authorizations TO postgres;
GRANT ALL ON TABLE auth.oauth_authorizations TO dashboard_user;


--
-- Name: TABLE oauth_client_states; Type: ACL; Schema: auth; Owner: supabase_auth_admin
--

GRANT ALL ON TABLE auth.oauth_client_states TO postgres;
GRANT ALL ON TABLE auth.oauth_client_states TO dashboard_user;


--
-- Name: TABLE oauth_clients; Type: ACL; Schema: auth; Owner: supabase_auth_admin
--

GRANT ALL ON TABLE auth.oauth_clients TO postgres;
GRANT ALL ON TABLE auth.oauth_clients TO dashboard_user;


--
-- Name: TABLE oauth_consents; Type: ACL; Schema: auth; Owner: supabase_auth_admin
--

GRANT ALL ON TABLE auth.oauth_consents TO postgres;
GRANT ALL ON TABLE auth.oauth_consents TO dashboard_user;


--
-- Name: TABLE one_time_tokens; Type: ACL; Schema: auth; Owner: supabase_auth_admin
--

GRANT INSERT,REFERENCES,DELETE,TRIGGER,TRUNCATE,MAINTAIN,UPDATE ON TABLE auth.one_time_tokens TO postgres;
GRANT SELECT ON TABLE auth.one_time_tokens TO postgres WITH GRANT OPTION;
GRANT ALL ON TABLE auth.one_time_tokens TO dashboard_user;


--
-- Name: TABLE refresh_tokens; Type: ACL; Schema: auth; Owner: supabase_auth_admin
--

GRANT ALL ON TABLE auth.refresh_tokens TO dashboard_user;
GRANT INSERT,REFERENCES,DELETE,TRIGGER,TRUNCATE,MAINTAIN,UPDATE ON TABLE auth.refresh_tokens TO postgres;
GRANT SELECT ON TABLE auth.refresh_tokens TO postgres WITH GRANT OPTION;


--
-- Name: SEQUENCE refresh_tokens_id_seq; Type: ACL; Schema: auth; Owner: supabase_auth_admin
--

GRANT ALL ON SEQUENCE auth.refresh_tokens_id_seq TO dashboard_user;
GRANT ALL ON SEQUENCE auth.refresh_tokens_id_seq TO postgres;


--
-- Name: TABLE saml_providers; Type: ACL; Schema: auth; Owner: supabase_auth_admin
--

GRANT INSERT,REFERENCES,DELETE,TRIGGER,TRUNCATE,MAINTAIN,UPDATE ON TABLE auth.saml_providers TO postgres;
GRANT SELECT ON TABLE auth.saml_providers TO postgres WITH GRANT OPTION;
GRANT ALL ON TABLE auth.saml_providers TO dashboard_user;


--
-- Name: TABLE saml_relay_states; Type: ACL; Schema: auth; Owner: supabase_auth_admin
--

GRANT INSERT,REFERENCES,DELETE,TRIGGER,TRUNCATE,MAINTAIN,UPDATE ON TABLE auth.saml_relay_states TO postgres;
GRANT SELECT ON TABLE auth.saml_relay_states TO postgres WITH GRANT OPTION;
GRANT ALL ON TABLE auth.saml_relay_states TO dashboard_user;


--
-- Name: TABLE schema_migrations; Type: ACL; Schema: auth; Owner: supabase_auth_admin
--

GRANT SELECT ON TABLE auth.schema_migrations TO postgres WITH GRANT OPTION;


--
-- Name: TABLE scim_tokens; Type: ACL; Schema: auth; Owner: supabase_auth_admin
--

GRANT ALL ON TABLE auth.scim_tokens TO postgres;
GRANT ALL ON TABLE auth.scim_tokens TO dashboard_user;


--
-- Name: TABLE scim_users; Type: ACL; Schema: auth; Owner: supabase_auth_admin
--

GRANT ALL ON TABLE auth.scim_users TO postgres;
GRANT ALL ON TABLE auth.scim_users TO dashboard_user;


--
-- Name: TABLE sessions; Type: ACL; Schema: auth; Owner: supabase_auth_admin
--

GRANT INSERT,REFERENCES,DELETE,TRIGGER,TRUNCATE,MAINTAIN,UPDATE ON TABLE auth.sessions TO postgres;
GRANT SELECT ON TABLE auth.sessions TO postgres WITH GRANT OPTION;
GRANT ALL ON TABLE auth.sessions TO dashboard_user;


--
-- Name: TABLE sso_domains; Type: ACL; Schema: auth; Owner: supabase_auth_admin
--

GRANT INSERT,REFERENCES,DELETE,TRIGGER,TRUNCATE,MAINTAIN,UPDATE ON TABLE auth.sso_domains TO postgres;
GRANT SELECT ON TABLE auth.sso_domains TO postgres WITH GRANT OPTION;
GRANT ALL ON TABLE auth.sso_domains TO dashboard_user;


--
-- Name: TABLE sso_providers; Type: ACL; Schema: auth; Owner: supabase_auth_admin
--

GRANT INSERT,REFERENCES,DELETE,TRIGGER,TRUNCATE,MAINTAIN,UPDATE ON TABLE auth.sso_providers TO postgres;
GRANT SELECT ON TABLE auth.sso_providers TO postgres WITH GRANT OPTION;
GRANT ALL ON TABLE auth.sso_providers TO dashboard_user;


--
-- Name: TABLE users; Type: ACL; Schema: auth; Owner: supabase_auth_admin
--

GRANT ALL ON TABLE auth.users TO dashboard_user;
GRANT INSERT,REFERENCES,DELETE,TRIGGER,TRUNCATE,MAINTAIN,UPDATE ON TABLE auth.users TO postgres;
GRANT SELECT ON TABLE auth.users TO postgres WITH GRANT OPTION;


--
-- Name: TABLE webauthn_challenges; Type: ACL; Schema: auth; Owner: supabase_auth_admin
--

GRANT ALL ON TABLE auth.webauthn_challenges TO postgres;
GRANT ALL ON TABLE auth.webauthn_challenges TO dashboard_user;


--
-- Name: TABLE webauthn_credentials; Type: ACL; Schema: auth; Owner: supabase_auth_admin
--

GRANT ALL ON TABLE auth.webauthn_credentials TO postgres;
GRANT ALL ON TABLE auth.webauthn_credentials TO dashboard_user;


--
-- Name: TABLE pg_stat_statements; Type: ACL; Schema: extensions; Owner: postgres
--

REVOKE ALL ON TABLE extensions.pg_stat_statements FROM postgres;
GRANT ALL ON TABLE extensions.pg_stat_statements TO postgres WITH GRANT OPTION;
GRANT ALL ON TABLE extensions.pg_stat_statements TO dashboard_user;


--
-- Name: TABLE pg_stat_statements_info; Type: ACL; Schema: extensions; Owner: postgres
--

REVOKE ALL ON TABLE extensions.pg_stat_statements_info FROM postgres;
GRANT ALL ON TABLE extensions.pg_stat_statements_info TO postgres WITH GRANT OPTION;
GRANT ALL ON TABLE extensions.pg_stat_statements_info TO dashboard_user;


--
-- Name: TABLE ai_usage_logs; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON TABLE public.ai_usage_logs TO service_role;


--
-- Name: SEQUENCE ai_usage_logs_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.ai_usage_logs_id_seq TO anon;
GRANT ALL ON SEQUENCE public.ai_usage_logs_id_seq TO authenticated;
GRANT ALL ON SEQUENCE public.ai_usage_logs_id_seq TO service_role;


--
-- Name: TABLE backups; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON TABLE public.backups TO anon;
GRANT ALL ON TABLE public.backups TO authenticated;
GRANT ALL ON TABLE public.backups TO service_role;


--
-- Name: SEQUENCE backups_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.backups_id_seq TO anon;
GRANT ALL ON SEQUENCE public.backups_id_seq TO authenticated;
GRANT ALL ON SEQUENCE public.backups_id_seq TO service_role;


--
-- Name: TABLE cache; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON TABLE public.cache TO service_role;


--
-- Name: TABLE cache_locks; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON TABLE public.cache_locks TO service_role;


--
-- Name: TABLE chat_conversations; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON TABLE public.chat_conversations TO service_role;


--
-- Name: SEQUENCE chat_conversations_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.chat_conversations_id_seq TO anon;
GRANT ALL ON SEQUENCE public.chat_conversations_id_seq TO authenticated;
GRANT ALL ON SEQUENCE public.chat_conversations_id_seq TO service_role;


--
-- Name: TABLE chat_messages; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON TABLE public.chat_messages TO service_role;


--
-- Name: SEQUENCE chat_messages_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.chat_messages_id_seq TO anon;
GRANT ALL ON SEQUENCE public.chat_messages_id_seq TO authenticated;
GRANT ALL ON SEQUENCE public.chat_messages_id_seq TO service_role;


--
-- Name: TABLE failed_jobs; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON TABLE public.failed_jobs TO service_role;


--
-- Name: SEQUENCE failed_jobs_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.failed_jobs_id_seq TO anon;
GRANT ALL ON SEQUENCE public.failed_jobs_id_seq TO authenticated;
GRANT ALL ON SEQUENCE public.failed_jobs_id_seq TO service_role;


--
-- Name: TABLE job_batches; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON TABLE public.job_batches TO service_role;


--
-- Name: TABLE jobs; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON TABLE public.jobs TO service_role;


--
-- Name: SEQUENCE jobs_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.jobs_id_seq TO anon;
GRANT ALL ON SEQUENCE public.jobs_id_seq TO authenticated;
GRANT ALL ON SEQUENCE public.jobs_id_seq TO service_role;


--
-- Name: TABLE keywords; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON TABLE public.keywords TO service_role;


--
-- Name: SEQUENCE keywords_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.keywords_id_seq TO anon;
GRANT ALL ON SEQUENCE public.keywords_id_seq TO authenticated;
GRANT ALL ON SEQUENCE public.keywords_id_seq TO service_role;


--
-- Name: TABLE migrations; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON TABLE public.migrations TO service_role;


--
-- Name: SEQUENCE migrations_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.migrations_id_seq TO anon;
GRANT ALL ON SEQUENCE public.migrations_id_seq TO authenticated;
GRANT ALL ON SEQUENCE public.migrations_id_seq TO service_role;


--
-- Name: TABLE notifications; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON TABLE public.notifications TO service_role;


--
-- Name: TABLE password_reset_tokens; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON TABLE public.password_reset_tokens TO service_role;


--
-- Name: TABLE research_access_requests; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON TABLE public.research_access_requests TO anon;
GRANT ALL ON TABLE public.research_access_requests TO authenticated;
GRANT ALL ON TABLE public.research_access_requests TO service_role;


--
-- Name: SEQUENCE research_access_requests_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.research_access_requests_id_seq TO anon;
GRANT ALL ON SEQUENCE public.research_access_requests_id_seq TO authenticated;
GRANT ALL ON SEQUENCE public.research_access_requests_id_seq TO service_role;


--
-- Name: TABLE research_chunks; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON TABLE public.research_chunks TO service_role;


--
-- Name: SEQUENCE research_chunks_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.research_chunks_id_seq TO anon;
GRANT ALL ON SEQUENCE public.research_chunks_id_seq TO authenticated;
GRANT ALL ON SEQUENCE public.research_chunks_id_seq TO service_role;


--
-- Name: TABLE research_feedback; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON TABLE public.research_feedback TO anon;
GRANT ALL ON TABLE public.research_feedback TO authenticated;
GRANT ALL ON TABLE public.research_feedback TO service_role;


--
-- Name: SEQUENCE research_feedback_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.research_feedback_id_seq TO anon;
GRANT ALL ON SEQUENCE public.research_feedback_id_seq TO authenticated;
GRANT ALL ON SEQUENCE public.research_feedback_id_seq TO service_role;


--
-- Name: TABLE sdgs; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON TABLE public.sdgs TO service_role;


--
-- Name: SEQUENCE sdgs_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.sdgs_id_seq TO anon;
GRANT ALL ON SEQUENCE public.sdgs_id_seq TO authenticated;
GRANT ALL ON SEQUENCE public.sdgs_id_seq TO service_role;


--
-- Name: TABLE search_logs; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON TABLE public.search_logs TO anon;
GRANT ALL ON TABLE public.search_logs TO authenticated;
GRANT ALL ON TABLE public.search_logs TO service_role;


--
-- Name: SEQUENCE search_logs_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.search_logs_id_seq TO anon;
GRANT ALL ON SEQUENCE public.search_logs_id_seq TO authenticated;
GRANT ALL ON SEQUENCE public.search_logs_id_seq TO service_role;


--
-- Name: TABLE sessions; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON TABLE public.sessions TO service_role;


--
-- Name: TABLE stud_file; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON TABLE public.stud_file TO service_role;


--
-- Name: TABLE stud_file_author; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON TABLE public.stud_file_author TO service_role;


--
-- Name: SEQUENCE stud_file_author_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.stud_file_author_id_seq TO anon;
GRANT ALL ON SEQUENCE public.stud_file_author_id_seq TO authenticated;
GRANT ALL ON SEQUENCE public.stud_file_author_id_seq TO service_role;


--
-- Name: SEQUENCE stud_file_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.stud_file_id_seq TO anon;
GRANT ALL ON SEQUENCE public.stud_file_id_seq TO authenticated;
GRANT ALL ON SEQUENCE public.stud_file_id_seq TO service_role;


--
-- Name: TABLE stud_file_keyword; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON TABLE public.stud_file_keyword TO service_role;


--
-- Name: SEQUENCE stud_file_keyword_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.stud_file_keyword_id_seq TO anon;
GRANT ALL ON SEQUENCE public.stud_file_keyword_id_seq TO authenticated;
GRANT ALL ON SEQUENCE public.stud_file_keyword_id_seq TO service_role;


--
-- Name: TABLE stud_file_sdg; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON TABLE public.stud_file_sdg TO service_role;


--
-- Name: SEQUENCE stud_file_sdg_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.stud_file_sdg_id_seq TO anon;
GRANT ALL ON SEQUENCE public.stud_file_sdg_id_seq TO authenticated;
GRANT ALL ON SEQUENCE public.stud_file_sdg_id_seq TO service_role;


--
-- Name: TABLE users; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON TABLE public.users TO service_role;


--
-- Name: SEQUENCE users_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT ALL ON SEQUENCE public.users_id_seq TO anon;
GRANT ALL ON SEQUENCE public.users_id_seq TO authenticated;
GRANT ALL ON SEQUENCE public.users_id_seq TO service_role;


--
-- Name: TABLE messages; Type: ACL; Schema: realtime; Owner: supabase_realtime_admin
--

GRANT REFERENCES,DELETE,TRIGGER,TRUNCATE,MAINTAIN,UPDATE ON TABLE realtime.messages TO postgres;
GRANT SELECT,INSERT ON TABLE realtime.messages TO postgres WITH GRANT OPTION;
GRANT ALL ON TABLE realtime.messages TO dashboard_user;
GRANT SELECT,INSERT,UPDATE ON TABLE realtime.messages TO anon;
GRANT SELECT,INSERT,UPDATE ON TABLE realtime.messages TO authenticated;
GRANT SELECT,INSERT,UPDATE ON TABLE realtime.messages TO service_role;


--
-- Name: TABLE schema_migrations; Type: ACL; Schema: realtime; Owner: supabase_admin
--

GRANT ALL ON TABLE realtime.schema_migrations TO postgres;
GRANT ALL ON TABLE realtime.schema_migrations TO dashboard_user;


--
-- Name: TABLE subscription; Type: ACL; Schema: realtime; Owner: supabase_realtime_admin
--

GRANT ALL ON TABLE realtime.subscription TO postgres;
GRANT ALL ON TABLE realtime.subscription TO dashboard_user;
GRANT SELECT ON TABLE realtime.subscription TO anon;
GRANT SELECT ON TABLE realtime.subscription TO authenticated;
GRANT SELECT ON TABLE realtime.subscription TO service_role;


--
-- Name: SEQUENCE subscription_id_seq; Type: ACL; Schema: realtime; Owner: supabase_realtime_admin
--

GRANT ALL ON SEQUENCE realtime.subscription_id_seq TO postgres;
GRANT ALL ON SEQUENCE realtime.subscription_id_seq TO dashboard_user;
GRANT USAGE ON SEQUENCE realtime.subscription_id_seq TO anon;
GRANT USAGE ON SEQUENCE realtime.subscription_id_seq TO authenticated;
GRANT USAGE ON SEQUENCE realtime.subscription_id_seq TO service_role;


--
-- Name: TABLE buckets; Type: ACL; Schema: storage; Owner: supabase_storage_admin
--

REVOKE ALL ON TABLE storage.buckets FROM supabase_storage_admin;
GRANT ALL ON TABLE storage.buckets TO supabase_storage_admin WITH GRANT OPTION;
GRANT ALL ON TABLE storage.buckets TO service_role;
GRANT ALL ON TABLE storage.buckets TO authenticated;
GRANT ALL ON TABLE storage.buckets TO anon;
GRANT ALL ON TABLE storage.buckets TO postgres WITH GRANT OPTION;


--
-- Name: TABLE buckets_analytics; Type: ACL; Schema: storage; Owner: supabase_storage_admin
--

GRANT ALL ON TABLE storage.buckets_analytics TO service_role;
GRANT ALL ON TABLE storage.buckets_analytics TO authenticated;
GRANT ALL ON TABLE storage.buckets_analytics TO anon;


--
-- Name: TABLE buckets_vectors; Type: ACL; Schema: storage; Owner: supabase_storage_admin
--

GRANT SELECT ON TABLE storage.buckets_vectors TO service_role;
GRANT SELECT ON TABLE storage.buckets_vectors TO authenticated;
GRANT SELECT ON TABLE storage.buckets_vectors TO anon;


--
-- Name: TABLE objects; Type: ACL; Schema: storage; Owner: supabase_storage_admin
--

REVOKE ALL ON TABLE storage.objects FROM supabase_storage_admin;
GRANT ALL ON TABLE storage.objects TO supabase_storage_admin WITH GRANT OPTION;
GRANT ALL ON TABLE storage.objects TO service_role;
GRANT ALL ON TABLE storage.objects TO authenticated;
GRANT ALL ON TABLE storage.objects TO anon;
GRANT ALL ON TABLE storage.objects TO postgres WITH GRANT OPTION;


--
-- Name: TABLE s3_multipart_uploads; Type: ACL; Schema: storage; Owner: supabase_storage_admin
--

GRANT ALL ON TABLE storage.s3_multipart_uploads TO service_role;
GRANT SELECT ON TABLE storage.s3_multipart_uploads TO authenticated;
GRANT SELECT ON TABLE storage.s3_multipart_uploads TO anon;


--
-- Name: TABLE s3_multipart_uploads_parts; Type: ACL; Schema: storage; Owner: supabase_storage_admin
--

GRANT ALL ON TABLE storage.s3_multipart_uploads_parts TO service_role;
GRANT SELECT ON TABLE storage.s3_multipart_uploads_parts TO authenticated;
GRANT SELECT ON TABLE storage.s3_multipart_uploads_parts TO anon;


--
-- Name: TABLE vector_indexes; Type: ACL; Schema: storage; Owner: supabase_storage_admin
--

GRANT SELECT ON TABLE storage.vector_indexes TO service_role;
GRANT SELECT ON TABLE storage.vector_indexes TO authenticated;
GRANT SELECT ON TABLE storage.vector_indexes TO anon;


--
-- Name: TABLE secrets; Type: ACL; Schema: vault; Owner: supabase_admin
--

GRANT SELECT,REFERENCES,DELETE,TRUNCATE ON TABLE vault.secrets TO postgres WITH GRANT OPTION;
GRANT SELECT,DELETE ON TABLE vault.secrets TO service_role;


--
-- Name: TABLE decrypted_secrets; Type: ACL; Schema: vault; Owner: supabase_admin
--

GRANT SELECT,REFERENCES,DELETE,TRUNCATE ON TABLE vault.decrypted_secrets TO postgres WITH GRANT OPTION;
GRANT SELECT,DELETE ON TABLE vault.decrypted_secrets TO service_role;


--
-- Name: DEFAULT PRIVILEGES FOR SEQUENCES; Type: DEFAULT ACL; Schema: auth; Owner: supabase_auth_admin
--

ALTER DEFAULT PRIVILEGES FOR ROLE supabase_auth_admin IN SCHEMA auth GRANT ALL ON SEQUENCES TO postgres;
ALTER DEFAULT PRIVILEGES FOR ROLE supabase_auth_admin IN SCHEMA auth GRANT ALL ON SEQUENCES TO dashboard_user;


--
-- Name: DEFAULT PRIVILEGES FOR FUNCTIONS; Type: DEFAULT ACL; Schema: auth; Owner: supabase_auth_admin
--

ALTER DEFAULT PRIVILEGES FOR ROLE supabase_auth_admin IN SCHEMA auth GRANT ALL ON FUNCTIONS TO postgres;
ALTER DEFAULT PRIVILEGES FOR ROLE supabase_auth_admin IN SCHEMA auth GRANT ALL ON FUNCTIONS TO dashboard_user;


--
-- Name: DEFAULT PRIVILEGES FOR TABLES; Type: DEFAULT ACL; Schema: auth; Owner: supabase_auth_admin
--

ALTER DEFAULT PRIVILEGES FOR ROLE supabase_auth_admin IN SCHEMA auth GRANT ALL ON TABLES TO postgres;
ALTER DEFAULT PRIVILEGES FOR ROLE supabase_auth_admin IN SCHEMA auth GRANT ALL ON TABLES TO dashboard_user;


--
-- Name: DEFAULT PRIVILEGES FOR SEQUENCES; Type: DEFAULT ACL; Schema: extensions; Owner: supabase_admin
--

ALTER DEFAULT PRIVILEGES FOR ROLE supabase_admin IN SCHEMA extensions GRANT ALL ON SEQUENCES TO postgres WITH GRANT OPTION;


--
-- Name: DEFAULT PRIVILEGES FOR FUNCTIONS; Type: DEFAULT ACL; Schema: extensions; Owner: supabase_admin
--

ALTER DEFAULT PRIVILEGES FOR ROLE supabase_admin IN SCHEMA extensions GRANT ALL ON FUNCTIONS TO postgres WITH GRANT OPTION;


--
-- Name: DEFAULT PRIVILEGES FOR TABLES; Type: DEFAULT ACL; Schema: extensions; Owner: supabase_admin
--

ALTER DEFAULT PRIVILEGES FOR ROLE supabase_admin IN SCHEMA extensions GRANT ALL ON TABLES TO postgres WITH GRANT OPTION;


--
-- Name: DEFAULT PRIVILEGES FOR SEQUENCES; Type: DEFAULT ACL; Schema: graphql; Owner: supabase_admin
--

ALTER DEFAULT PRIVILEGES FOR ROLE supabase_admin IN SCHEMA graphql GRANT ALL ON SEQUENCES TO postgres;
ALTER DEFAULT PRIVILEGES FOR ROLE supabase_admin IN SCHEMA graphql GRANT ALL ON SEQUENCES TO anon;
ALTER DEFAULT PRIVILEGES FOR ROLE supabase_admin IN SCHEMA graphql GRANT ALL ON SEQUENCES TO authenticated;
ALTER DEFAULT PRIVILEGES FOR ROLE supabase_admin IN SCHEMA graphql GRANT ALL ON SEQUENCES TO service_role;


--
-- Name: DEFAULT PRIVILEGES FOR FUNCTIONS; Type: DEFAULT ACL; Schema: graphql; Owner: supabase_admin
--

ALTER DEFAULT PRIVILEGES FOR ROLE supabase_admin IN SCHEMA graphql GRANT ALL ON FUNCTIONS TO postgres;
ALTER DEFAULT PRIVILEGES FOR ROLE supabase_admin IN SCHEMA graphql GRANT ALL ON FUNCTIONS TO anon;
ALTER DEFAULT PRIVILEGES FOR ROLE supabase_admin IN SCHEMA graphql GRANT ALL ON FUNCTIONS TO authenticated;
ALTER DEFAULT PRIVILEGES FOR ROLE supabase_admin IN SCHEMA graphql GRANT ALL ON FUNCTIONS TO service_role;


--
-- Name: DEFAULT PRIVILEGES FOR TABLES; Type: DEFAULT ACL; Schema: graphql; Owner: supabase_admin
--

ALTER DEFAULT PRIVILEGES FOR ROLE supabase_admin IN SCHEMA graphql GRANT ALL ON TABLES TO postgres;
ALTER DEFAULT PRIVILEGES FOR ROLE supabase_admin IN SCHEMA graphql GRANT ALL ON TABLES TO anon;
ALTER DEFAULT PRIVILEGES FOR ROLE supabase_admin IN SCHEMA graphql GRANT ALL ON TABLES TO authenticated;
ALTER DEFAULT PRIVILEGES FOR ROLE supabase_admin IN SCHEMA graphql GRANT ALL ON TABLES TO service_role;


--
-- Name: DEFAULT PRIVILEGES FOR SEQUENCES; Type: DEFAULT ACL; Schema: graphql_public; Owner: supabase_admin
--

ALTER DEFAULT PRIVILEGES FOR ROLE supabase_admin IN SCHEMA graphql_public GRANT ALL ON SEQUENCES TO postgres;
ALTER DEFAULT PRIVILEGES FOR ROLE supabase_admin IN SCHEMA graphql_public GRANT ALL ON SEQUENCES TO anon;
ALTER DEFAULT PRIVILEGES FOR ROLE supabase_admin IN SCHEMA graphql_public GRANT ALL ON SEQUENCES TO authenticated;
ALTER DEFAULT PRIVILEGES FOR ROLE supabase_admin IN SCHEMA graphql_public GRANT ALL ON SEQUENCES TO service_role;


--
-- Name: DEFAULT PRIVILEGES FOR FUNCTIONS; Type: DEFAULT ACL; Schema: graphql_public; Owner: supabase_admin
--

ALTER DEFAULT PRIVILEGES FOR ROLE supabase_admin IN SCHEMA graphql_public GRANT ALL ON FUNCTIONS TO postgres;
ALTER DEFAULT PRIVILEGES FOR ROLE supabase_admin IN SCHEMA graphql_public GRANT ALL ON FUNCTIONS TO anon;
ALTER DEFAULT PRIVILEGES FOR ROLE supabase_admin IN SCHEMA graphql_public GRANT ALL ON FUNCTIONS TO authenticated;
ALTER DEFAULT PRIVILEGES FOR ROLE supabase_admin IN SCHEMA graphql_public GRANT ALL ON FUNCTIONS TO service_role;


--
-- Name: DEFAULT PRIVILEGES FOR TABLES; Type: DEFAULT ACL; Schema: graphql_public; Owner: supabase_admin
--

ALTER DEFAULT PRIVILEGES FOR ROLE supabase_admin IN SCHEMA graphql_public GRANT ALL ON TABLES TO postgres;
ALTER DEFAULT PRIVILEGES FOR ROLE supabase_admin IN SCHEMA graphql_public GRANT ALL ON TABLES TO anon;
ALTER DEFAULT PRIVILEGES FOR ROLE supabase_admin IN SCHEMA graphql_public GRANT ALL ON TABLES TO authenticated;
ALTER DEFAULT PRIVILEGES FOR ROLE supabase_admin IN SCHEMA graphql_public GRANT ALL ON TABLES TO service_role;


--
-- Name: DEFAULT PRIVILEGES FOR SEQUENCES; Type: DEFAULT ACL; Schema: public; Owner: postgres
--

ALTER DEFAULT PRIVILEGES FOR ROLE postgres IN SCHEMA public GRANT ALL ON SEQUENCES TO postgres;
ALTER DEFAULT PRIVILEGES FOR ROLE postgres IN SCHEMA public GRANT ALL ON SEQUENCES TO anon;
ALTER DEFAULT PRIVILEGES FOR ROLE postgres IN SCHEMA public GRANT ALL ON SEQUENCES TO authenticated;
ALTER DEFAULT PRIVILEGES FOR ROLE postgres IN SCHEMA public GRANT ALL ON SEQUENCES TO service_role;


--
-- Name: DEFAULT PRIVILEGES FOR SEQUENCES; Type: DEFAULT ACL; Schema: public; Owner: supabase_admin
--

ALTER DEFAULT PRIVILEGES FOR ROLE supabase_admin IN SCHEMA public GRANT ALL ON SEQUENCES TO postgres;
ALTER DEFAULT PRIVILEGES FOR ROLE supabase_admin IN SCHEMA public GRANT ALL ON SEQUENCES TO anon;
ALTER DEFAULT PRIVILEGES FOR ROLE supabase_admin IN SCHEMA public GRANT ALL ON SEQUENCES TO authenticated;
ALTER DEFAULT PRIVILEGES FOR ROLE supabase_admin IN SCHEMA public GRANT ALL ON SEQUENCES TO service_role;


--
-- Name: DEFAULT PRIVILEGES FOR FUNCTIONS; Type: DEFAULT ACL; Schema: public; Owner: postgres
--

ALTER DEFAULT PRIVILEGES FOR ROLE postgres IN SCHEMA public GRANT ALL ON FUNCTIONS TO postgres;
ALTER DEFAULT PRIVILEGES FOR ROLE postgres IN SCHEMA public GRANT ALL ON FUNCTIONS TO anon;
ALTER DEFAULT PRIVILEGES FOR ROLE postgres IN SCHEMA public GRANT ALL ON FUNCTIONS TO authenticated;
ALTER DEFAULT PRIVILEGES FOR ROLE postgres IN SCHEMA public GRANT ALL ON FUNCTIONS TO service_role;


--
-- Name: DEFAULT PRIVILEGES FOR FUNCTIONS; Type: DEFAULT ACL; Schema: public; Owner: supabase_admin
--

ALTER DEFAULT PRIVILEGES FOR ROLE supabase_admin IN SCHEMA public GRANT ALL ON FUNCTIONS TO postgres;
ALTER DEFAULT PRIVILEGES FOR ROLE supabase_admin IN SCHEMA public GRANT ALL ON FUNCTIONS TO anon;
ALTER DEFAULT PRIVILEGES FOR ROLE supabase_admin IN SCHEMA public GRANT ALL ON FUNCTIONS TO authenticated;
ALTER DEFAULT PRIVILEGES FOR ROLE supabase_admin IN SCHEMA public GRANT ALL ON FUNCTIONS TO service_role;


--
-- Name: DEFAULT PRIVILEGES FOR TABLES; Type: DEFAULT ACL; Schema: public; Owner: postgres
--

ALTER DEFAULT PRIVILEGES FOR ROLE postgres IN SCHEMA public GRANT ALL ON TABLES TO postgres;
ALTER DEFAULT PRIVILEGES FOR ROLE postgres IN SCHEMA public GRANT ALL ON TABLES TO anon;
ALTER DEFAULT PRIVILEGES FOR ROLE postgres IN SCHEMA public GRANT ALL ON TABLES TO authenticated;
ALTER DEFAULT PRIVILEGES FOR ROLE postgres IN SCHEMA public GRANT ALL ON TABLES TO service_role;


--
-- Name: DEFAULT PRIVILEGES FOR TABLES; Type: DEFAULT ACL; Schema: public; Owner: supabase_admin
--

ALTER DEFAULT PRIVILEGES FOR ROLE supabase_admin IN SCHEMA public GRANT ALL ON TABLES TO postgres;
ALTER DEFAULT PRIVILEGES FOR ROLE supabase_admin IN SCHEMA public GRANT ALL ON TABLES TO anon;
ALTER DEFAULT PRIVILEGES FOR ROLE supabase_admin IN SCHEMA public GRANT ALL ON TABLES TO authenticated;
ALTER DEFAULT PRIVILEGES FOR ROLE supabase_admin IN SCHEMA public GRANT ALL ON TABLES TO service_role;


--
-- Name: DEFAULT PRIVILEGES FOR SEQUENCES; Type: DEFAULT ACL; Schema: realtime; Owner: supabase_admin
--

ALTER DEFAULT PRIVILEGES FOR ROLE supabase_admin IN SCHEMA realtime GRANT ALL ON SEQUENCES TO postgres;
ALTER DEFAULT PRIVILEGES FOR ROLE supabase_admin IN SCHEMA realtime GRANT ALL ON SEQUENCES TO dashboard_user;


--
-- Name: DEFAULT PRIVILEGES FOR FUNCTIONS; Type: DEFAULT ACL; Schema: realtime; Owner: supabase_admin
--

ALTER DEFAULT PRIVILEGES FOR ROLE supabase_admin IN SCHEMA realtime GRANT ALL ON FUNCTIONS TO postgres;
ALTER DEFAULT PRIVILEGES FOR ROLE supabase_admin IN SCHEMA realtime GRANT ALL ON FUNCTIONS TO dashboard_user;


--
-- Name: DEFAULT PRIVILEGES FOR TABLES; Type: DEFAULT ACL; Schema: realtime; Owner: supabase_admin
--

ALTER DEFAULT PRIVILEGES FOR ROLE supabase_admin IN SCHEMA realtime GRANT REFERENCES,DELETE,TRIGGER,TRUNCATE,MAINTAIN,UPDATE ON TABLES TO postgres;
ALTER DEFAULT PRIVILEGES FOR ROLE supabase_admin IN SCHEMA realtime GRANT SELECT,INSERT ON TABLES TO postgres WITH GRANT OPTION;
ALTER DEFAULT PRIVILEGES FOR ROLE supabase_admin IN SCHEMA realtime GRANT ALL ON TABLES TO dashboard_user;


--
-- Name: DEFAULT PRIVILEGES FOR SEQUENCES; Type: DEFAULT ACL; Schema: storage; Owner: postgres
--

ALTER DEFAULT PRIVILEGES FOR ROLE postgres IN SCHEMA storage GRANT ALL ON SEQUENCES TO postgres;
ALTER DEFAULT PRIVILEGES FOR ROLE postgres IN SCHEMA storage GRANT ALL ON SEQUENCES TO anon;
ALTER DEFAULT PRIVILEGES FOR ROLE postgres IN SCHEMA storage GRANT ALL ON SEQUENCES TO authenticated;
ALTER DEFAULT PRIVILEGES FOR ROLE postgres IN SCHEMA storage GRANT ALL ON SEQUENCES TO service_role;


--
-- Name: DEFAULT PRIVILEGES FOR FUNCTIONS; Type: DEFAULT ACL; Schema: storage; Owner: postgres
--

ALTER DEFAULT PRIVILEGES FOR ROLE postgres IN SCHEMA storage GRANT ALL ON FUNCTIONS TO postgres;
ALTER DEFAULT PRIVILEGES FOR ROLE postgres IN SCHEMA storage GRANT ALL ON FUNCTIONS TO anon;
ALTER DEFAULT PRIVILEGES FOR ROLE postgres IN SCHEMA storage GRANT ALL ON FUNCTIONS TO authenticated;
ALTER DEFAULT PRIVILEGES FOR ROLE postgres IN SCHEMA storage GRANT ALL ON FUNCTIONS TO service_role;


--
-- Name: DEFAULT PRIVILEGES FOR TABLES; Type: DEFAULT ACL; Schema: storage; Owner: postgres
--

ALTER DEFAULT PRIVILEGES FOR ROLE postgres IN SCHEMA storage GRANT ALL ON TABLES TO postgres;
ALTER DEFAULT PRIVILEGES FOR ROLE postgres IN SCHEMA storage GRANT ALL ON TABLES TO anon;
ALTER DEFAULT PRIVILEGES FOR ROLE postgres IN SCHEMA storage GRANT ALL ON TABLES TO authenticated;
ALTER DEFAULT PRIVILEGES FOR ROLE postgres IN SCHEMA storage GRANT ALL ON TABLES TO service_role;


--
-- Name: issue_graphql_placeholder; Type: EVENT TRIGGER; Schema: -; Owner: supabase_admin
--

CREATE EVENT TRIGGER issue_graphql_placeholder ON sql_drop
         WHEN TAG IN ('DROP EXTENSION')
   EXECUTE FUNCTION extensions.set_graphql_placeholder();


ALTER EVENT TRIGGER issue_graphql_placeholder OWNER TO supabase_admin;

--
-- Name: issue_pg_cron_access; Type: EVENT TRIGGER; Schema: -; Owner: supabase_admin
--

CREATE EVENT TRIGGER issue_pg_cron_access ON ddl_command_end
         WHEN TAG IN ('CREATE EXTENSION')
   EXECUTE FUNCTION extensions.grant_pg_cron_access();


ALTER EVENT TRIGGER issue_pg_cron_access OWNER TO supabase_admin;

--
-- Name: issue_pg_graphql_access; Type: EVENT TRIGGER; Schema: -; Owner: supabase_admin
--

CREATE EVENT TRIGGER issue_pg_graphql_access ON ddl_command_end
         WHEN TAG IN ('CREATE EXTENSION')
   EXECUTE FUNCTION extensions.grant_pg_graphql_access();


ALTER EVENT TRIGGER issue_pg_graphql_access OWNER TO supabase_admin;

--
-- Name: issue_pg_net_access; Type: EVENT TRIGGER; Schema: -; Owner: supabase_admin
--

CREATE EVENT TRIGGER issue_pg_net_access ON ddl_command_end
         WHEN TAG IN ('CREATE EXTENSION')
   EXECUTE FUNCTION extensions.grant_pg_net_access();


ALTER EVENT TRIGGER issue_pg_net_access OWNER TO supabase_admin;

--
-- Name: pgrst_ddl_watch; Type: EVENT TRIGGER; Schema: -; Owner: supabase_admin
--

CREATE EVENT TRIGGER pgrst_ddl_watch ON ddl_command_end
   EXECUTE FUNCTION extensions.pgrst_ddl_watch();


ALTER EVENT TRIGGER pgrst_ddl_watch OWNER TO supabase_admin;

--
-- Name: pgrst_drop_watch; Type: EVENT TRIGGER; Schema: -; Owner: supabase_admin
--

CREATE EVENT TRIGGER pgrst_drop_watch ON sql_drop
   EXECUTE FUNCTION extensions.pgrst_drop_watch();


ALTER EVENT TRIGGER pgrst_drop_watch OWNER TO supabase_admin;

--
-- PostgreSQL database dump complete
--

\unrestrict V7SHf0M7SojOhosey7Acz3oOCmPARko4vPSSMPgqFgsfNvYVLKX5PDHqlGlPO3c

