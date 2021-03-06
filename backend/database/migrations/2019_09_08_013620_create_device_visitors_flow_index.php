<?php

use Illuminate\Database\Migrations\Migration;

class CreateDeviceVisitorsFlowIndex extends Migration
{
    private const INDEX_NAME = 'device-visitors-flow-index';
    private $client;

    public function __construct()
    {
        $this->client =  app('elasticsearch');
    }
    public function up()
    {
        $this->down();
        $params = [
            'index' => self::INDEX_NAME,
            'body' => [
                'mappings' => [
                    "properties" => [
                        "device" => [
                            "type" => "keyword"
                        ]
                    ]
                ]
            ]
        ];
        $this->client->indices()->create($params);
    }

    public function down()
    {
        $params = ['index' => self::INDEX_NAME];
        if ($this->client->indices()->exists($params)) {
            $this->client->indices()->delete($params);
        }
    }
}
