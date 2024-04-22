<?php

namespace Tests\Feature\IU\Course;

use App\Models\User;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\UserProfile;
use App\Models\IdentityVerification;

use App\DataObject\CertificateEntityData;
use App\DataObject\IdentityVerificationStatusData;

use Tests\TestCase;

use App\Traits\Tests\CertificateTestTrait;
use App\Traits\Tests\JSONResponseTestTrait;
use App\Traits\Tests\CourseTestTrait;

class CertificatesTest extends TestCase
{
    use CertificateTestTrait;
    use JSONResponseTestTrait;
    use CourseTestTrait;

    private $user, $data, $userIdentityVerified;

    public function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $this->user = User::factory()->verified()->create();
        UserProfile::factory()->withUser($this->user->id)->create();
        $this->userIdentityVerified = IdentityVerification::factory()->withUser($this->user->id)->withStatus(IdentityVerificationStatusData::COMPLETED)->create();
        $this->actingAs($this->user);
        $this->data = $this->CategoryCourseCourseModuleLessonSeeder($this->user);
    }

    public function testCertificatesAvailability()
    {

        $response = $this->json('GET',  '/api/iu/certificates/');

        $response->assertStatus(200);
    }

    public function testCourseCertificateAvailability()
    {
        $certificate = Certificate::factory()->withUserId($this->user->id)->withCourseId($this->data->course->id)->create();

        $response = $this->json('GET',  '/api/iu/certificates/' . $certificate->id);

        $response->assertStatus(200);
    }

    public function testLevelCertificateAvailability()
    {
        $certificate = Certificate::factory()->withUserId($this->user->id)->withCourseLevelId($this->data->courseLevel->id)->create();

        $response = $this->json('GET',  '/api/iu/certificates/' . $certificate->id);

        $response->assertStatus(200);
    }

    public function testModuleCertificateAvailability()
    {
        $certificate = Certificate::factory()->withUserId($this->user->id)->withCourseModuleId($this->data->courseModule->id)->create();

        $response = $this->json('GET',  '/api/iu/certificates/' . $certificate->id);

        $response->assertStatus(200);
    }

    public function testAllTypesCertificateAvailability()
    {
        $courseCertificate = Certificate::factory()->withUserId($this->user->id)->withCourseId($this->data->course->id)->create();
        $levelCertificate = Certificate::factory()->withUserId($this->user->id)->withCourseLevelId($this->data->courseLevel->id)->create();
        $moduleCertificate = Certificate::factory()->withUserId($this->user->id)->withCourseModuleId($this->data->courseModule->id)->create();

        $response = $this->json('GET',  '/api/iu/certificates/');
        $certificates = $response['data'];

        $this->assertEquals(count($certificates), 3);

        $courseCertificate = $this->findItemInArray($certificates, $courseCertificate);
        $levelCertificate = $this->findItemInArray($certificates, $levelCertificate);
        $moduleCertificate = $this->findItemInArray($certificates, $moduleCertificate);

        //check type of certificates
        $this->assertEquals($courseCertificate['type'], CertificateEntityData::ENTITY_COURSE);
        $this->assertEquals($levelCertificate['type'], CertificateEntityData::ENTITY_COURSE_LEVEL);
        $this->assertEquals($moduleCertificate['type'], CertificateEntityData::ENTITY_COURSE_MODULE);
    }

    public function testCertificateDownload()
    {
        $certificate = Certificate::factory()->withUserId($this->user->id)->withCourseId($this->data->course->id)->create();

        $response = $this->json('GET',  '/api/iu/certificates/' . $certificate->id . '/download');

        $response->assertStatus(200);
    }

    public function testUnverifiedIdentity()
    {
        $certificate = Certificate::factory()->withUserId($this->user->id)->withCourseId($this->data->course->id)->create();

        $this->userIdentityVerified->delete();

        $response = $this->json('GET',  '/api/iu/certificates/' . $certificate->id);
        
        $this->assertTrue(json_decode($response->content())->error->identityUnverified);
        $this->assertEquals(json_decode($response->content())->error->identityVerificationStatus, IdentityVerificationStatusData::PENDING);
    }

    public function testNextPageUrls()
    {
        $courses = Course::factory(20)->create();
        $this->GenerateCertificatesForMultipleCourses($courses, $this->user);

        $response = $this->json('GET',  '/api/iu/certificates/');

        $this->assertEquals($response['total'], 20);

        $nextPageUrl = $response['next_page_url'];
        $this->assertNotNull($nextPageUrl);
    }
}