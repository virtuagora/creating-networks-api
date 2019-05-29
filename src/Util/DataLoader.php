<?php

namespace App\Util;

use League\Csv\Reader;
use Grimzy\LaravelMysqlSpatial\Types\Point;

class DataLoader
{
    protected $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function createRegions()
    {
        $csv = Reader::createFromPath(__DIR__ . '/../../data/regions.csv', 'r');
        $csv->setHeaderOffset(0);
        $records = $csv->getRecords();
        foreach ($records as $offset => $record) {
            $this->db->createAndSave('App:Region', [
                'id' => $record['id'],
                'name' => $record['name'],
                'localization' => [
                    'es' => ['name' => $record['localization_es_name']]
                ],
            ]);
        }
    }

    public function createCountries()
    {
        $csv = Reader::createFromPath(__DIR__ . '/../../data/countries.csv', 'r');
        $csv->setHeaderOffset(0);
        $records = $csv->getRecords();
        foreach ($records as $o => $r) {
            $s = $this->db->createAndSave('App:Space', [
                'point' => new Point($r['longitude'], $r['latitude']),
            ]);
            $this->db->createAndSave('App:Country', [
                'id' => $r['id'],
                'name' => $r['name'],
                'code_2' => $r['code_2'],
                'code_3' => $r['code_3'],
                'region_id' => $r['region_id'],
                'space_id' => $s->id,
                'localization' => [
                    'es' => ['name' => $r['localization_es_name']],
                    'fr' => ['name' => $r['localization_fr_name']],
                ],
                'data' => [
                    'ldc' => (boolean) $r['data_ldc'],
                    'lldc' => (boolean) $r['data_lldc'],
                    'sids' => (boolean) $r['data_sids'],
                    'developed' => (boolean) $r['data_developed'],
                    'independent' => (boolean) $r['data_independent'],
                ],
            ]);
        }
    }

    public function createRegisteredCities()
    {
        $csv = Reader::createFromPath(__DIR__ . '/../../data/cities.csv', 'r');
        $csv->setHeaderOffset(0);
        $records = $csv->getRecords();
        $inserts = [];
        foreach ($records as $o => $r) {
            $inserts[] = [
                'name' => $r['name'],
                'trace' => Utils::traceStr($r['trace']),
                'point' => $this->db->getConnection()->raw('Point('.$r['lng'].','.$r['lat'].')'),
                'country_id' => $r['country_id'],
            ];
            if (count($inserts) > 99) {
                $this->db->table('registered_cities')->insert($inserts);
                $inserts = [];
            }
        }
        if (count($inserts) > 0) {
            $this->db->table('registered_cities')->insert($inserts);
        }
    }
}