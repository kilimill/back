<?php

namespace App\Http\Adapters;

use App\Models\City;
use App\Models\Country;
use App\Models\Region;

class AddressAdapter
{
    private int $countryId;
    private int $regionId;
    private int $cityId;
    private string $address;

    public static function transform(array $address): self
    {
        $adapter = new static();
        $adapter->countryId = Country::findRussia()->getKey();
        $adapter->regionId = $adapter->findRegionId($address['province']);
        $adapter->cityId = $adapter->findCityId($address['locality']);
        $adapter->address = implode(', ', $address);

        return $adapter;
    }

    private function findRegionId($name): int
    {
        $region = Region::query()->where('name', $name)->first();
        if (!$region) {
            $region = new Region();
            $region->name = $name;
            $region->country_id = $this->countryId;
            $region->save();
        }

        return $region->getKey();
    }

    private function findCityId($name): int
    {
        $city = City::query()->where('name', $name)->first();
        if (!$city) {
            $city = new City();
            $city->name = $name;
            $city->country_id = $this->countryId;
            $city->region_id = $this->regionId;
            $city->save();
        }

        return $city->getKey();
    }

    public function getCountryId(): int
    {
        return $this->countryId;
    }

    public function getRegionId(): int
    {
        return $this->regionId;
    }

    public function getCityId(): int
    {
        return $this->cityId;
    }

    public function getAddress(): string
    {
        return $this->address;
    }
}
