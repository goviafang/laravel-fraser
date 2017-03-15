<?php

namespace Govia\Fraser;

use Illuminate\Config\Repository;
use PHPUnit\Framework\TestCase;

class FraserTest extends TestCase
{
    protected $fraser;

    public function __construct()
    {
        $this->fraser = new Fraser(new Repository(['fraser' => include(__DIR__ . '/../src/config/fraser.php')]));
    }

    public function testUriException()
    {
        $this->expectException(InvalidUriException::class);
        $this->fraser->setProvince('on')->getListUrl();
    }

    public function testGetUrl()
    {
        $uri = $this->fraser->setProvince('on')
            ->setGrade('elementary')
            ->getListUrl();
        $this->assertEquals('http://ontario.compareschoolrankings.org/elementary/SchoolsByRankLocationName.aspx', $uri);

        $uri = $this->fraser->setProvince('bc')
            ->setGrade('secondary')
            ->getListUrl();
        $this->assertEquals('http://britishcolumbia.compareschoolrankings.org/secondary/SchoolsByRankLocationName.aspx', $uri);
    }
}
