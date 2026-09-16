<?php

/*
|--------------------------------------------------------------------------
| UN Sustainable Development Goals
|--------------------------------------------------------------------------
|
| Official names for SDG numbers 1-17, keyed by the `sdgs.number` column
| (NOT `sdgs.id` — the two are only incidentally equal on a freshly seeded
| table; see SdgSeeder and ResearchAnalysisController).
|
| Single source of truth: read via Sdg::$name / Sdg::$label. This list was
| previously inlined inside the SDG picker component, which meant the
| submission form was the only place that could name a goal — cards, the
| detail page, and both reviewer surfaces had no way to label one.
|
*/

return [
    1 => 'No Poverty',
    2 => 'Zero Hunger',
    3 => 'Good Health and Well-being',
    4 => 'Quality Education',
    5 => 'Gender Equality',
    6 => 'Clean Water and Sanitation',
    7 => 'Affordable and Clean Energy',
    8 => 'Decent Work and Economic Growth',
    9 => 'Industry, Innovation and Infrastructure',
    10 => 'Reduced Inequalities',
    11 => 'Sustainable Cities and Communities',
    12 => 'Responsible Consumption and Production',
    13 => 'Climate Action',
    14 => 'Life Below Water',
    15 => 'Life on Land',
    16 => 'Peace, Justice and Strong Institutions',
    17 => 'Partnerships for the Goals',
];
