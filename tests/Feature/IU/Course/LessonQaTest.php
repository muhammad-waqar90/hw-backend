<?php

namespace Tests\Feature\IU\Course;

use App\DataObject\Tickets\TicketCategoryData;
use App\DataObject\Tickets\TicketStatusData;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\User;
use App\Models\UserProfile;
use App\Traits\Tests\CourseTestTrait;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LessonQaTest extends TestCase
{
    use CourseTestTrait;

    private $user;

    private $data;

    private $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $this->user = User::factory()->verified()->create();
        UserProfile::factory()->withUser($this->user->id)->create();
        $this->admin = User::factory()->verified()->admin()->create();
        $this->actingAs($this->user);
        $this->data = $this->CategoryCourseCourseModuleLessonSeeder($this->user);
    }

    public function testLessonQaGetRouteDefault()
    {
        $response = $this->json('GET', '/api/iu/courses/'.$this->data->course->id.'/lessons/'.$this->data->lesson->id.'/qas/me');

        $response->assertStatus(200);

        $this->assertEmpty(json_decode($response->content())->data);
    }

    public function testLessonQaNoAnswerGetRoute()
    {
        $ticket = Ticket::factory()->withCategoryId(TicketCategoryData::LESSON_QA)->withTicketStatus(TicketStatusData::UNCLAIMED)->withUser($this->user)->create();
        TicketMessage::factory()->withUserId($this->user->id)->withTicketId($ticket->id)->create();

        DB::table('lesson_ticket')->insert(
            [
                'lesson_id' => $this->data->lesson->id,
                'ticket_id' => $ticket->id,
            ]
        );

        $response = $this->json('GET', '/api/iu/courses/'.$this->data->course->id.'/lessons/'.$this->data->lesson->id.'/qas/me/latest');

        $response->assertOk();
        $this->assertNull(json_decode($response->content())->answer);
        $this->assertNotNull(json_decode($response->content())->question);
    }

    public function testLessonQaGetRoute()
    {
        $ticket = Ticket::factory()->withCategoryId(TicketCategoryData::LESSON_QA)->withTicketStatus(TicketStatusData::RESOLVED)->withAdmin($this->admin)->withUser($this->user)->create();
        TicketMessage::factory()->withUserId($this->user->id)->withTicketId($ticket->id)->create();
        TicketMessage::factory()->withAdminId($this->admin->id)->withTicketId($ticket->id)->withMessage('admin message')->create();

        DB::table('lesson_ticket')->insert(
            [
                'lesson_id' => $this->data->lesson->id,
                'ticket_id' => $ticket->id,
            ]
        );

        $response = $this->json('GET', '/api/iu/courses/'.$this->data->course->id.'/lessons/'.$this->data->lesson->id.'/qas/me');

        $response->assertOk();
        $this->assertNotNull(json_decode($response->content())->data[0]->answer);
        $this->assertNotNull(json_decode($response->content())->data[0]->question);
    }

    public function testLessonQaPostRoute()
    {
        $response = $this->json('POST', '/api/iu/courses/'.$this->data->course->id.'/lessons/'.$this->data->lesson->id.'/qas', [
            'question' => 'dummy question',
        ]);

        $response->assertOk();

        $this->assertNull(json_decode($response->content())->data->answer);
        $this->assertNotNull(json_decode($response->content())->data->question);
    }

    public function testLessonQaSystemResponsePostRoute()
    {
        DB::table('lesson_faqs')->insert(
            [
                'lesson_id' => $this->data->lesson->id,
                'question' => 'dummy question',
                'answer' => 'dummy answer',
            ]
        );

        $response = $this->json('POST', '/api/iu/courses/'.$this->data->course->id.'/lessons/'.$this->data->lesson->id.'/qas', [
            'question' => 'dummy question',
        ]);

        $response->assertOk();
        $this->assertNotNull(json_decode($response->content())->data->answer);
        $this->assertNotNull(json_decode($response->content())->data->question);
    }
}
