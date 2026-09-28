@extends('layouts.repository_layout')

@section('title', 'Repository')

@section('repository')
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
        list-route="repository"
        detail-route="research.show"
        :show-download="$showDownload"
        :is-public-repository="false"
    />
@endsection
