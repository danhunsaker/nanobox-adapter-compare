<?php

namespace App\Http\Controllers;

use GuzzleHttp\Exception\RequestException;

class LookupController extends Controller
{
    protected $providerList;

    public function __construct() {
        $this->providerList = [
            'do' => [
                'disp' => 'Digital Ocean',
                'base' => 'https://adapter-digital-ocean.nanoapp.io/',
            ],
            'linode' => [
                'disp' => 'Linode',
                'base' => 'https://adapter-linode.nanoapp.io/',
            ],
        ];

        $libcloudHome = 'https://adapters-adapter-libcloud.nanoapp.io';
        $libcloudResponse = \Guzzle::get("{$libcloudHome}/");
        $body = $libcloudResponse->getBody();
        $parsed = new \Htmldom($body);
        foreach ($parsed->find('ul li a') as $el) {
            $this->providerList[trim($el->href, '/')] = [
                'disp' => $el->plaintext,
                'base' => "{$libcloudHome}{$el->href}/",
            ];
        }
    }

    public function providers() {
        // List all supported providers
        $output = [];

        foreach ($this->providerList as $key => $info) {
            $output[$key] = $info['disp'];
        }

        return response()->json($output);
    }

    public function sizes(string $provider = null) {
        // List supported sizes for all providers, or just for $provider
        $output = [];

        if (!empty($provider)) {
            $output = $this->getSizeInfo($provider);
        } else {
            foreach ($this->providerList as $provider => $info) {
                $output += $this->getSizeInfo($provider);
            }
        }

        return response()->json($output);
    }

    protected function getSizeInfo(string $provider): array {
        $output = [];

        try {
            $catalogResponse = \Guzzle::get("{$this->providerList[$provider]['base']}catalog");
        } catch (RequestException $e) {
            return [];
        }

        $catalog = json_decode($catalogResponse->getBody());

        foreach ($catalog as $region) {
            foreach ($region->plans as $plan) {
                foreach ($plan->specs as $size) {
                    if (property_exists($size, 'name')) {
                        $name = "{$size->name} ({$size->id})";
                    } else {
                        $name = $size->id;
                    }
                    $output[] = [
                        'provider' => "{$this->providerList[$provider]['disp']} ($provider)",
                        'region' => "{$region->name} ({$region->id})",
                        'plan' => "{$plan->name} ({$plan->id})",
                        'name' => $name,
                        'cpu' => $size->cpu,
                        'ram' => $size->ram,
                        'disk' => $size->disk,
                        'transfer' => $size->transfer,
                        'dollars_per_hr' => $size->dollars_per_hr,
                        'dollars_per_mo' => $size->dollars_per_mo,
                    ];
                }
            }
        }

        return $output;
    }
}
