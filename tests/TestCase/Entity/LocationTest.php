<?php
declare(strict_types=1);

/**
 * OpenAgenda API client.
 * Copyright (c) Erwane BRETON
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright   Copyright (c) Erwane BRETON
 * @see         https://github.com/Erwane/openagenda-api
 * @license     https://opensource.org/licenses/mit-license.php MIT License
 */
namespace OpenAgenda\Test\TestCase\Entity;

use Cake\Chronos\Chronos;
use OpenAgenda\Entity\Location;
use OpenAgenda\Test\Utility\FileResource;
use PHPUnit\Framework\TestCase;

/**
 * Entity\Location tests
 *
 * @uses \OpenAgenda\Entity\Location
 * @covers \OpenAgenda\Entity\Location
 */
class LocationTest extends TestCase
{
    public function testAliasesIn()
    {
        $json = FileResource::instance($this)->getContent('Response/locations/location.json');
        $payload = json_decode($json, true);
        $ent = new Location($payload['location']);
        $result = $ent->toArray();
        $this->assertEquals([
            'id' => 35867424,
            'name' => 'Centres sociaux de Wattrelos 59150',
            'address' => '4 rue Edouard Herriot 59150 Wattrelos',
            'access' => [],
            'description' => [],
            // 'image' => null,
            'image_credits' => null,
            'slug' => 'centres-sociaux-de-wattrelos-59150_6977111',
            'set_id' => null,
            'city' => 'Wattrelos',
            'department' => 'Nord',
            'region' => 'Hauts-de-France',
            'postal_code' => '59150',
            'insee' => '59650',
            'country' => 'FR',
            'district' => null,
            'latitude' => 50.70428,
            'longitude' => 3.235638,
            'created_at' => Chronos::parse('2024-12-27T15:41:32.000Z'),
            'updated_at' => Chronos::parse('2024-12-27T15:42:32.000Z'),
            // 'website' => null,
            'email' => null,
            'phone' => null,
            'links' => [],
            'timezone' => 'Europe/Paris',
            'ext_id' => null,
            'state' => 0,
        ], $result);
    }

    public function testAliasesOut()
    {
        $ent = new Location([
            'id' => 35867424,
            'name' => 'Centres sociaux de Wattrelos 59150',
            'address' => '4 rue Edouard Herriot 59150 Wattrelos',
            'access' => [],
            'description' => [],
            // 'image' => null,
            'image_credits' => null,
            'slug' => 'centres-sociaux-de-wattrelos-59150_6977111',
            'set_id' => null,
            'city' => 'Wattrelos',
            'department' => 'Nord',
            'region' => 'Hauts-de-France',
            'postal_code' => '59150',
            'insee' => '59650',
            'country' => 'FR',
            'district' => null,
            'latitude' => 50.70428,
            'longitude' => 3.235638,
            'created_at' => Chronos::parse('2024-12-27T15:41:32.000Z'),
            'updated_at' => Chronos::parse('2024-12-27T15:42:32.000Z'),
            // 'website' => null,
            'email' => null,
            'phone' => null,
            'links' => [],
            'timezone' => 'Europe/Paris',
            'ext_id' => null,
            'state' => 0,
        ]);

        $this->assertEquals([
            'uid' => 35867424,
            'name' => 'Centres sociaux de Wattrelos 59150',
            'address' => '4 rue Edouard Herriot 59150 Wattrelos',
            'access' => [],
            'description' => [],
            'imageCredits' => null,
            'slug' => 'centres-sociaux-de-wattrelos-59150_6977111',
            'setUid' => null,
            'city' => 'Wattrelos',
            'department' => 'Nord',
            'region' => 'Hauts-de-France',
            'postalCode' => '59150',
            'insee' => '59650',
            'countryCode' => 'FR',
            'district' => null,
            'latitude' => 50.70428,
            'longitude' => 3.235638,
            'createdAt' => '2024-12-27T15:41:32',
            'updatedAt' => '2024-12-27T15:42:32',
            'email' => null,
            'phone' => null,
            'links' => [],
            'timezone' => 'Europe/Paris',
            'extId' => null,
            'state' => 0,
        ], $ent->toOpenAgenda());
    }

    public function testExists()
    {
        $this->markTestIncomplete();
    }

    public function testGet()
    {
        $this->markTestIncomplete();
    }

    public function testCreate()
    {
        $this->markTestIncomplete();
    }

    public function testUpdate()
    {
        $this->markTestIncomplete();
    }

    public function testDelete()
    {
        $this->markTestIncomplete();
    }
}
