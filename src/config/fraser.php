<?php
return [

    /*
    |--------------------------------------------------------------------------
    | Cache Time
    |--------------------------------------------------------------------------
    */
    'cache' => 1440,

    /*
    |--------------------------------------------------------------------------
    | Uri From Fraser
    |--------------------------------------------------------------------------
    */
    'uri' => [
        'ab' => [
            'elementary' => 'http://alberta.compareschoolrankings.org/elementary/SchoolsByRankLocationName.aspx',
            'high' => 'http://alberta.compareschoolrankings.org/high/SchoolsByRankLocationName.aspx',
        ],
        'bc' => [
            'elementary' => 'http://britishcolumbia.compareschoolrankings.org/elementary/SchoolsByRankLocationName.aspx',
            'secondary' => 'http://britishcolumbia.compareschoolrankings.org/secondary/SchoolsByRankLocationName.aspx',
        ],
        'on' => [
            'elementary' => 'http://ontario.compareschoolrankings.org/elementary/SchoolsByRankLocationName.aspx',
            'secondary' => 'http://ontario.compareschoolrankings.org/secondary/SchoolsByRankLocationName.aspx',
        ],
        'qc' => [
            'secondary' => 'http://quebec.compareschoolrankings.org/secondary/SchoolsByRankLocationName.aspx',
        ],
    ],
];