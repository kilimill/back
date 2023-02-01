<?php

namespace App\Console\Commands;

use App\Models\City;
use App\Models\Contact;
use App\Models\Hotel;
use App\Models\Region;
use App\Models\User;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class YandexParser extends Command
{
    protected $signature = 'yandex-parser:start';

    protected $description = 'Load hotels from Yandex.';

    public function handle()
    {
        $this->info('Started');
        $userParser = User::query()->where('name', 'YaParser')->first();
        if (empty($userParser)) {
            $userParser = new User();
            $userParser->name = 'YaParser';
            $userParser->role_id = User::ROLE_ID_OWNER;
            $userParser->phone = '77894561212';
            $userParser->save();
        }
        $noNameRegion = Region::query()->where('name', 'noName')->first();
        if (empty($noNameRegion)) {
            $noNameRegion = new Region();
            $noNameRegion->name = 'noName';
            $noNameRegion->country_id = 1;
            $noNameRegion->save();
        }
        $regionsAll = Region::query()->where('id', '!=', 1)->get();
        $skips = [0, 500, 1000];
        $client = new Client();
        $counter = 0;
        try {
            foreach ($regionsAll as $item) {
                $countEntries = 0;
                foreach ($skips as $skip) {
                    $response = $client->request(
                        'GET',
                        'https://search-maps.yandex.ru/v1',
                        [
                            'query' => [
                                'text' => $item->name . ' база отдыха',
                                'type' => 'biz',
                                'lang' => 'ru_RU',
                                'results' => 500,
                                'skip' => $skip,
                                'apikey' => 'ee0815c6-9e8b-470a-a7ac-682fe36421c7',
//                            'apikey' => '4e89d2a2-9ee4-441f-bca3-32a510c02948',
//                            'apikey' => '4360ad0a-ef48-4e12-9bbf-b13ba897c8ea',
//                            'apikey' => '902b52b5-d3f8-4670-b535-7d90a68c9301',
                            ]
                        ]
                    );

                    $response = json_decode($response->getBody());

                    foreach ($response->features as $hotel) {
                        $this->line($counter);
                        $address = $hotel->properties->CompanyMetaData->address ?? $hotel->properties->description;
                        $addressArray = explode(',', $address);
                        $counter++;
                        $coordinates = $hotel->geometry->coordinates[1] . ',' . $hotel->geometry->coordinates[0];
                        $regionName = trim($addressArray[0]);

                        /** @var Region $region */
                        $region = Region::query()->where('name', $regionName)->first();
                        $findCity = $addressArray[1] ?? 'None';

                        if (!$region) {
                            $city = City::query()->where('name', $regionName)
                                ->first();
                            $region = !empty($city->region_id) ? Region::query()->find($city->region_id) : $noNameRegion;
                            $findCity = $addressArray[0] ?? 'None';
                        }

                        $filterCity = (str_ends_with($findCity, 'округ') || str_ends_with($findCity, 'район'))
                            ? ($addressArray[2] ?? "None")
                            : ($addressArray[1] ?? "None");
                        $cityFullName = str_contains($filterCity, 'улица') ? 'None' : trim($filterCity);
                        if ($cityFullName == 'None') {
                            continue;
                        }
                        $cityArray = explode(' ', trim($cityFullName));

                        $cityName = match ($cityArray[0]) {
                            'село', 'поселок', 'посёлок', 'деревня', 'станица', 'город' => str_replace($cityArray[0], '', $cityFullName),
                            default => $cityFullName,
                        };
                        /** @var City $city */
                        $city = City::query()->where('name', trim($cityName))
                            ->where('region_id', $region->id)
                            ->first();

                        if (empty($city)) {
                            $city = City::query()
                                ->where('name', $cityFullName)
                                ->where('region_id', $region->id)
                                ->first();
                            if (empty($city)) {
                                $city = new City();
                                $city->name = $cityFullName;
                                $city->region_id = $region->id;
                                $city->country_id = 1;
                                $city->save();
                            }
                        }

                        if (!empty($region) && !empty($city)
                        ) {
                            /** @var Hotel $entry */
                            $entry = Hotel::query()
                                ->where('name', $hotel->properties->name)
                                ->where('city_id', $city->id)
                                ->first();
                            if (empty($entry)) {
                                $entry = new Hotel();
                                $entry->name = $hotel->properties->name;
                                $entry->city_id = $city->id;
                                $entry->status_id = Hotel::STATUS_ID_DRAFT;
                                $entry->country_id = 1;
                                $entry->region_id = $region->id;
                                $entry->coordinates = $coordinates;
                                $entry->address = $address;
                                $entry->user_id = $userParser->getKey();
                                $entry->save();
                            }
                            $countEntries++;

                            if (!empty($hotel->properties->CompanyMetaData->Phones)) {
                                foreach ($hotel->properties->CompanyMetaData->Phones as $phone) {
                                    $entry->contacts()->upsert(
                                        ['value' => $phone->formatted, 'type_id' => Contact::TYPE_ID_PHONE, 'hotel_id' => $entry->getKey()],
                                        ['value' => $phone->formatted]);
                                }
                            }

                            if (!empty($hotel->properties->CompanyMetaData->url)) {
                                $entry->contacts()->upsert(
                                    ['value' => $hotel->properties->CompanyMetaData->url, 'type_id' => Contact::TYPE_ID_SITE, 'hotel_id' => $entry->getKey()],
                                    ['value' => $hotel->properties->CompanyMetaData->url]);
                            }
                        }
                    }
                }
                $logMessage = $item->name . ': ' . $countEntries . ' записей';
                $this->info($logMessage);
                Log::info($logMessage);
            }
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
        $this->info('Finished');
    }
}
