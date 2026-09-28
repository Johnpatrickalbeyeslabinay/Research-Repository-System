@extends('layouts.public_layout')

@section('title', 'Public Research')

@section('public')
    <x-research.browser
        :stud-files="$studFiles"
        :years="$years"
        :types="$types"
        :sdgs="$sdgs"
        :search="$search"
        :year="$year"
        :type="$type"
        :sdg="$sdg"
        :sort="$sort"
        list-route="public"
        detail-route="public.research.show"
        :show-download="false"
        :is-public-repository="true"
    />
@endsection
