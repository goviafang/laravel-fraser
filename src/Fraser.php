<?php
/**
 * Crawling school data from Fraser Institute.
 *
 * @author Govia Fang<goatnoble@gmail.com>
 * @version 1.0.0
 * @license MIT
 */
namespace Govia\Fraser;

use Illuminate\Config\Repository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\DomCrawler\Crawler;
use Weidner\Goutte\GoutteFacade;

/**
 * Crawling school data from Fraser Institute.
 * @package Govia\Fraser
 */
class Fraser
{
    /**
     * @var Repository Config Repository
     */
    protected $config;

    /**
     * @var object Uniform Resource Identifier
     */
    protected $uri;

    /**
     * @var string Province
     */
    protected $province;

    /**
     * @var string Grade
     */
    protected $grade;

    /**
     * Fraser constructor.
     * @param Repository $config
     */
    public function __construct(Repository $config)
    {
        $this->config = $config;
    }

    /**
     * Setting province.
     *
     * - ab
     * - bc
     * - on
     * - qc
     *
     * @param string $province
     * @return Fraser
     */
    public function setProvince(string $province): Fraser
    {
        $this->province = strtolower($province);
        return $this;
    }

    /**
     * Setting grade.
     *
     * - elementary
     * - high
     * - secondary
     *
     * @param string $grade
     * @return Fraser
     */
    public function setGrade(string $grade): Fraser
    {
        $this->grade = strtolower($grade);
        return $this;
    }

    /**
     * Parsing Url.
     *
     * @param string $url
     * @return Fraser
     */
    public function parserUri(string $url): Fraser
    {
        $this->uri = (object)parse_url($url);
        return $this;
    }


    /**
     * Getting the url of school listing page.
     *
     * #### Usage
     * ```
     * (new Fraser)->setProvince('on')
     *             ->setGrade('elementary')
     *             ->getListUri();
     * ```
     *
     * @throws InvalidUriException
     * @return string
     */
    public function getListUrl(): string
    {
        $uris = $this->config->get('fraser.uri');
        if ( ! isset($uris[$this->province][$this->grade])) {
            throw new InvalidUriException();
        }
        $url = $uris[$this->province][$this->grade];
        $this->parserUri($url);
        return $url;
    }

    /**
     * Getting the url of school detail page.
     *
     * @param string $url
     * @return string
     */
    public function getSchoolUrl(string $url): string
    {
        return sprintf('%s://%s%s',
            $this->uri->scheme,
            $this->uri->host,
            $url
        );
    }

    /**
     * Getting the school listing.
     *
     * #### Usage
     * ```
     * (new Fraser)->setProvince('on')
     *             ->setGrade('elementary')
     *             ->getList();
     * ```
     *
     * @return Collection
     */
    public function getList(): Collection
    {
        $url = $this->getListUrl();

        $html = Cache::remember($url, $this->config->get('fraser.cache'), function () use ($url) {
            return GoutteFacade::request('GET', $url)->html();
        });
        $crawler = new Crawler($html);
        $response = $crawler->filter('.rating table tr');

        $records = new Collection();
        $response->each(function ($node, $idx) use (&$records) {
            if ($idx > 0) {
                $td = $node->filter('td');
                $records->add((object)[
                    'rank' => $td->eq(0)->text(),
                    'name' => $td->eq(3)->text(),
                    'link' => $this->getSchoolUrl($td->eq(3)->filter('a')->extract('href')[0]),
                    'city' => $td->eq(4)->text(),
                    'rating' => $td->eq(5)->text(),
                ]);
            }
        });

        unset($response, $crawler, $html);
        return $records;
    }

    /**
     * Getting the school detail.
     *
     * @param string $url
     * @return \stdClass
     */
    public function getDetail(string $url): \stdClass
    {
        $html = Cache::remember($url, $this->config->get('fraser.cache'), function () use ($url) {
            return GoutteFacade::request('GET', $url)->html();
        });

        $crawler = new Crawler($html);
        $location = $this->getLocation($crawler->filter('head')->text());
        $information = $crawler->filter('#ctl00_ContentPlaceHolder1_SchoolInfoDisplay')->html();
        $website = (string)array_first($crawler->filter('#ctl00_ContentPlaceHolder1_hlSchoolWebsite')->extract('href'));
        unset($crawler, $html);

        $details = array_filter(explode('<br>', $information));

        foreach ($details as $key => $detail) {
            $details[$key] = trim(strip_tags($detail));
        }

        $area = $this->parserArea($details[3]);
        $phone = $this->parserPhone($details[4]);
        $district = $this->parserDistrict($details[6]);

        return (object)[
            'name' => isset($details[0]) ? $details[0] : '',
            'type' => isset($details[1]) ? $details[1] : '',
            'address' => isset($details[2]) ? $details[2] : '',
            'city' => $area->city,
            'province' => $area->province,
            'postcode' => $area->postcode,
            'phone' => $phone,
            'district' => $district,
            'website' => $website,
            'location' => $location,
        ];
    }

    /**
     * @param string $content
     * @return \stdClass
     */
    private function parserArea(string $content): \stdClass
    {
        list($city, $other) = explode(',', $content);
        $other = trim($other);
        if (mb_substr_count($other, ' ') > 0) {
            list($province, $postcode) = explode(' ', $other, 2);
            $postcode = preg_replace('/\s+/', '', $postcode);
        }
        else {
            $province = $other;
            $postcode = '';
        }
        return (object)[
            'city' => $city,
            'province' => $province,
            'postcode' => $postcode,
        ];
    }

    /**
     * @param string $content
     * @return string
     */
    private function parserPhone(string $content): string
    {
        return trim(strtr($content, ['Phone Number:' => '']));
    }

    /**
     * @param string $content
     * @return string
     */
    private function parserDistrict(string $content): string
    {
        return trim(strtr($content, ['School District:' => '']));
    }

    /**
     * @param string $content
     * @return \stdClass
     */
    private function getLocation(string $content): \stdClass
    {
        preg_match("/center\:\ new\ Microsoft\.Maps\.Location\((.+),\ (.+)\)/", $content, $matches);
        return (object)[
            'lat' => isset($matches[1]) ? $matches[1] : '',
            'lng' => isset($matches[2]) ? $matches[2] : '',
        ];
    }
}