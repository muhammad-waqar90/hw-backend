<?php

namespace Tests\Feature\HA;

use App\Models\PermGroup;
use App\Models\User;
use App\Traits\Tests\PermGroupUserTestTrait;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HeadAdminPermissionsTest extends TestCase
{
    use PermGroupUserTestTrait;

    private $wrongUser;

    private $data;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $user = User::factory()->verified()->hAdmin()->create();
        $this->wrongUser = User::factory()->verified()->create();
        $this->actingAs($user);
        $this->data = $this->permissionsSeeder();
    }

    //CreateHeadAdmin CLI tests

    public function testCommandCreateHeadAdminValid()
    {
        $this->artisan('create:headAdmin')
            ->expectsQuestion('Input email', 'testCLI@test.com')
            ->expectsQuestion('Input first name', 'Head')
            ->expectsQuestion('Input last name', 'Admin')
            ->expectsOutput('Selected email:')
            ->expectsOutput('testCLI@test.com')
            ->expectsOutput('Selected first name:')
            ->expectsOutput('Head')
            ->expectsOutput('Selected last name:')
            ->expectsOutput('Admin')
            ->expectsConfirmation('Do you wish to proceed?', 'yes')
            ->expectsOutput('Successfully created head admin')
            ->assertExitCode(0);

        $createdAdmin = DB::table('admin_profiles')->where('email', 'testCLI@test.com')->first();
        $this->assertNotEmpty($createdAdmin->user_id);
    }

    public function testCommandCreateHeadAdminInvalidEmail()
    {
        $this->artisan('create:headAdmin')
            ->expectsQuestion('Input email', 'testCLI')
            ->expectsQuestion('Input first name', 'Head')
            ->expectsQuestion('Input last name', 'Admin')
            ->expectsOutput('Selected email:')
            ->expectsOutput('testCLI')
            ->expectsOutput('Selected first name:')
            ->expectsOutput('Head')
            ->expectsOutput('Selected last name:')
            ->expectsOutput('Admin')
            ->expectsConfirmation('Do you wish to proceed?', 'yes')
            ->expectsOutput('Invalid input!')
            ->assertExitCode(0);

        $createdAdmin = DB::table('admin_profiles')->where('email', 'testCLI')->first();
        $this->assertEquals(null, $createdAdmin);
    }

    public function testCommandCreateHeadAdminInvalidName()
    {
        $this->artisan('create:headAdmin')
            ->expectsQuestion('Input email', 'testCLI@test.com')
            ->expectsQuestion('Input first name', '')
            ->expectsQuestion('Input last name', 'Admin')
            ->expectsOutput('Selected email:')
            ->expectsOutput('testCLI@test.com')
            ->expectsOutput('Selected first name:')
            ->expectsOutput('Selected last name:')
            ->expectsOutput('Admin')
            ->expectsConfirmation('Do you wish to proceed?', 'yes')
            ->expectsOutput('Invalid input!')
            ->assertExitCode(0);

        $createdAdmin = DB::table('admin_profiles')->where('email', 'testCLI@test.com')->first();
        $this->assertEquals(null, $createdAdmin);
    }

    public function testCommandCreateHeadAdminValidNotCreated()
    {
        $this->artisan('create:headAdmin')
            ->expectsQuestion('Input email', 'testCLI@test.com')
            ->expectsQuestion('Input first name', 'Head')
            ->expectsQuestion('Input last name', 'Admin')
            ->expectsOutput('Selected email:')
            ->expectsOutput('testCLI@test.com')
            ->expectsOutput('Selected first name:')
            ->expectsOutput('Head')
            ->expectsOutput('Selected last name:')
            ->expectsOutput('Admin')
            ->expectsConfirmation('Do you wish to proceed?', 'no')
            ->assertExitCode(0);

        $createdAdmin = DB::table('admin_profiles')->where('email', 'testCLI@test.com')->first();
        $this->assertEquals(null, $createdAdmin);
    }

    //CreateMasterAdmin CLI tests

    public function testCommandCreateMasterAdminValid()
    {
        $this->artisan('create:masterAdmin')
            ->expectsQuestion('Input email', 'testCLI@test.com')
            ->expectsQuestion('Input first name', 'Master')
            ->expectsQuestion('Input last name', 'Admin')
            ->expectsOutput('Selected email:')
            ->expectsOutput('testCLI@test.com')
            ->expectsOutput('Selected first name:')
            ->expectsOutput('Master')
            ->expectsOutput('Selected last name:')
            ->expectsOutput('Admin')
            ->expectsConfirmation('Do you wish to proceed?', 'yes')
            ->expectsOutput('Successfully created master admin')
            ->assertExitCode(0);

        $createdAdmin = DB::table('admin_profiles')->where('email', 'testCLI@test.com')->first();
        $this->assertNotEmpty($createdAdmin->user_id);
    }

    public function testCommandCreateMasterAdminInvalidEmail()
    {
        $this->artisan('create:masterAdmin')
            ->expectsQuestion('Input email', 'testCLI')
            ->expectsQuestion('Input first name', 'Master')
            ->expectsQuestion('Input last name', 'Admin')
            ->expectsOutput('Selected email:')
            ->expectsOutput('testCLI')
            ->expectsOutput('Selected first name:')
            ->expectsOutput('Master')
            ->expectsOutput('Selected last name:')
            ->expectsOutput('Admin')
            ->expectsConfirmation('Do you wish to proceed?', 'yes')
            ->expectsOutput('Invalid input!')
            ->assertExitCode(0);

        $createdAdmin = DB::table('admin_profiles')->where('email', 'testCLI')->first();
        $this->assertEquals(null, $createdAdmin);
    }

    public function testCommandCreateMasterAdminInvalidName()
    {
        $this->artisan('create:masterAdmin')
            ->expectsQuestion('Input email', 'testCLI@test.com')
            ->expectsQuestion('Input first name', '')
            ->expectsQuestion('Input last name', 'Admin')
            ->expectsOutput('Selected email:')
            ->expectsOutput('testCLI@test.com')
            ->expectsOutput('Selected first name:')
            ->expectsOutput('Selected last name:')
            ->expectsOutput('Admin')
            ->expectsConfirmation('Do you wish to proceed?', 'yes')
            ->expectsOutput('Invalid input!')
            ->assertExitCode(0);

        $createdAdmin = DB::table('admin_profiles')->where('email', 'testCLI@test.com')->first();
        $this->assertEquals(null, $createdAdmin);
    }

    public function testCommandCreateMasterAdminValidNotCreated()
    {
        $this->artisan('create:masterAdmin')
            ->expectsQuestion('Input email', 'testCLI@test.com')
            ->expectsQuestion('Input first name', 'Master')
            ->expectsQuestion('Input last name', 'Admin')
            ->expectsOutput('Selected email:')
            ->expectsOutput('testCLI@test.com')
            ->expectsOutput('Selected first name:')
            ->expectsOutput('Master')
            ->expectsOutput('Selected last name:')
            ->expectsOutput('Admin')
            ->expectsConfirmation('Do you wish to proceed?', 'no')
            ->assertExitCode(0);

        $createdAdmin = DB::table('admin_profiles')->where('email', 'testCLI@test.com')->first();
        $this->assertEquals(null, $createdAdmin);
    }

    //////////
    // ROUTE TESTING
    //////////

    public function testPermissionsGetRouteValid()
    {
        $response = $this->json('GET', '/api/ha/permissions/');

        $response->assertStatus(200);

        $this->assertTrue(count(json_decode($response->content())) > 0);
    }

    public function testPermissionsGetRouteValidSearch()
    {
        $response = $this->json('GET', '/api/ha/permissions?searchText='.$this->data->searchPermissions[0]->display_name);

        $response->assertStatus(200);

        $this->assertEquals(count($this->data->searchPermissions), count(json_decode($response->content())));
    }

    public function testPermissionsGetRouteValidSearchNoResults()
    {
        $response = $this->json('GET', '/api/ha/permissions?searchText=PermissionWrongDoesntExists');

        $response->assertStatus(200);

        $this->assertEquals(0, count(json_decode($response->content())));
    }

    public function testPermissionsGetRouteInvalidUser()
    {
        $response = $this->actingAs($this->wrongUser)->json('GET', '/api/ha/permissions/');

        $response->assertStatus(403);
    }

    public function testPermissionsGroupsGetRouteValid()
    {
        $response = $this->json('GET', '/api/ha/permissions/groups');

        $response->assertStatus(200);

        $this->assertEquals(15, count(json_decode($response->content())->data->data));
    }

    public function testPermissionsGroupsGetRouteInvalidUser()
    {
        $response = $this->actingAs($this->wrongUser)->json('GET', '/api/ha/permissions/groups');

        $response->assertStatus(403);
    }

    public function testPermissionsGroupsGetRouteValidSearch()
    {
        $response = $this->json('GET', '/api/ha/permissions/groups?searchText='.$this->data->permGroups[0]->name);

        $response->assertStatus(200);

        $this->assertEquals(1, count(json_decode($response->content())->data->data));
    }

    public function testPermissionsGroupsGetRouteValidSearch2()
    {
        PermGroup::factory()->withName('PermGroup1')->create();
        PermGroup::factory()->withName('PermGroup2')->create();

        $response = $this->json('GET', '/api/ha/permissions/groups?searchText=PermGroup');

        $response->assertStatus(200);

        $this->assertEquals(2, count(json_decode($response->content())->data->data));
    }

    public function testPermissionsGroupsGetRouteValidSearchNoResults()
    {
        $response = $this->json('GET', '/api/ha/permissions/groups?searchText=PermGroupWrongDoesntExists');

        $response->assertStatus(200);

        $this->assertEquals(0, count(json_decode($response->content())->data->data));
    }

    public function testPermissionsGroupsGetRouteByIdValid()
    {
        $permissionGroup = PermGroup::factory()->withName('PermGroup1')->create();

        $response = $this->json('GET', '/api/ha/permissions/groups/'.$permissionGroup->id);

        $response->assertStatus(200);
    }

    public function testPermissionsGroupsPostRouteValid()
    {
        $response = $this->json('POST', '/api/ha/permissions/groups/', ['name' => 'GroupName', 'users' => [], 'permissions' => [$this->data->permissions[0], $this->data->permissions[1]], 'description' => 'desc']);

        $response->assertStatus(201);
    }

    public function testPermissionsGroupsPostRouteInvalidUserAlreadyExists()
    {
        $response = $this->json('POST', '/api/ha/permissions/groups/', ['name' => $this->data->permGroups[0]->name, 'users' => [], 'permissions' => [$this->data->permissions[0], $this->data->permissions[1]], 'description' => 'desc']);

        $response->assertStatus(422);
    }

    public function testPermissionsGroupsDelRouteValid()
    {
        $response = $this->json('DELETE', '/api/ha/permissions/groups/'.$this->data->users[0]->id);

        $response->assertStatus(200);
    }

    public function testPermissionsGroupsDelNGetRouteValidUpdatedInGetRoute()
    {
        $response = $this->json('DELETE', '/api/ha/permissions/groups/'.$this->data->permGroups[0]->id);

        $response = $this->json('GET', '/api/ha/permissions/groups/');
        $this->assertEquals(14, count(json_decode($response->content())->data->data));
    }

    public function testPermissionsGroupsPutRouteValid()
    {
        $response = $this->json('PUT', '/api/ha/permissions/groups/'.$this->data->permGroups[0]->id, ['name' => 'GroupName', 'users' => [], 'permissions' => [$this->data->permissions[0], $this->data->permissions[1]], 'description' => 'desc']);

        $response->assertStatus(200);
    }
}
