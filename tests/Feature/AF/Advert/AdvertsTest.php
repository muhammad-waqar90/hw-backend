<?php

namespace Tests\Feature\AF\Advert;

use App\DataObject\AdvertData;
use App\Models\Advert;
use App\Models\User;
use App\Traits\Tests\PermGroupUserTestTrait;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Lang;
use Tests\TestCase;

class AdvertsTest extends TestCase
{
    use PermGroupUserTestTrait;

    private $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $this->admin = User::factory()->verified()->admin()->create();
        $this->actingAs($this->admin);
        $this->assignAllPermissionToUser($this->admin);
    }

    public function testAdvertDefaultGetRoute()
    {
        $response = $this->json('GET', '/api/af/adverts?status='.AdvertData::STATUS_ACTIVE);

        $response->assertStatus(200);
    }

    public function testAdvertActiveGetRoute()
    {
        Advert::factory(5)->create();

        $response = $this->json('GET', '/api/af/adverts?status='.AdvertData::STATUS_ACTIVE);

        $response->assertStatus(200);
        $this->assertEquals(5, count(json_decode($response->content())->data));
    }

    public function testAdvertInactiveGetRoute()
    {
        Advert::factory(5)->inactive()->create();

        $response = $this->json('GET', '/api/af/adverts?status='.AdvertData::STATUS_INACTIVE);

        $response->assertStatus(200);
        $this->assertEquals(5, count(json_decode($response->content())->data));
    }

    public function testAdvertGetByIdRoute()
    {
        $advert = Advert::factory()->create();

        $response = $this->json('GET', '/api/af/adverts/'.$advert->id);

        $response->assertStatus(200);
    }

    public function testAdvertPostRoute()
    {
        $response = $this->json('POST', '/api/af/adverts', [
            'name' => 'advert1',
            'url' => 'https://google.com',
            'img' => UploadedFile::fake()->image('avatar.jpg'),
            'status' => AdvertData::STATUS_INACTIVE,
            'expires_at' => date('Y-m-d', strtotime('+1 years')),
        ]);

        $response->assertStatus(200);
        $this->assertEquals(Lang::get('advert.success.created'), json_decode($response->content())->message);
    }

    public function testAdvertUpdatePostRoute()
    {
        $advert = Advert::factory()->create();

        $response = $this->json('POST', '/api/af/adverts/'.$advert->id, [
            'name' => 'advert1',
            'url' => 'https://google.com',
            'status' => AdvertData::STATUS_INACTIVE,
            'expires_at' => date('Y-m-d', strtotime('+1 years')),
        ]);

        $response->assertStatus(200);
        $this->assertEquals(Lang::get('advert.success.updated'), json_decode($response->content())->message);
    }

    public function testAdvertDeleteRoute()
    {
        $advert = Advert::factory()->create();

        $response = $this->json('DELETE', '/api/af/adverts/'.$advert->id);

        $response->assertStatus(200);
        $this->assertEquals(Lang::get('advert.success.deleted'), json_decode($response->content())->message);
    }

    public function testAdvertSortPostRoute()
    {
        $advert1 = Advert::factory()->create();
        $advert2 = Advert::factory()->create();

        $response = $this->json('POST', '/api/af/adverts/sort', [
            'data' => [
                ['id' => $advert2->id, 'priority' => '1'],
                ['id' => $advert1->id, 'priority' => '2'],
            ],
        ]);

        $response->assertStatus(200);
    }
}
