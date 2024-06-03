<?php

namespace App\Http\Controllers\AF;

use App\DataObject\Tickets\TicketCategoryData;
use App\DataObject\Tickets\TicketMessageTypeData;
use App\DataObject\Tickets\TicketStatusData;
use App\Events\Notifications\Tickets\AfTicketClaimed;
use App\Events\Notifications\Tickets\AfTicketReplied;
use App\Events\Notifications\Tickets\AfTicketResolved;
use App\Events\Notifications\Tickets\AfTicketUnclaimed;
use App\Http\Controllers\Controller;
use App\Http\Requests\AF\Tickets\AfReplyToTicketRequest;
use App\Http\Requests\AF\Tickets\AfTicketListRequest;
use App\Http\Requests\AF\Tickets\AfUpdateTicketCategoryRequest;
use App\Http\Requests\AF\TicketSubjects\AfCreateUpdateTicketSubjectRequest;
use App\Http\Requests\AF\TicketSubjects\AfTicketSubjectListRequest;
use App\Mail\ClosedGuestTicketEmail;
use App\Models\TicketCategory;
use App\Repositories\AF\AfLessonFaqRepository;
use App\Repositories\AF\AfTicketRepository;
use App\Repositories\HA\PermissionRepository;
use App\Repositories\IU\IuLessonQaRepository;
use App\Repositories\TicketRepository;
use App\Transformers\AF\AfSpecificTicketTransformer;
use App\Transformers\AF\AfTicketMessageTransformer;
use App\Transformers\AF\AfTicketTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AfTicketController extends Controller
{
    private TicketRepository $ticketRepository;

    private AfTicketRepository $afTicketRepository;

    private PermissionRepository $permissionRepository;

    private AfLessonFaqRepository $afLessonFaqRepository;

    private IuLessonQaRepository $iuLessonQaRepository;

    public function __construct(
        TicketRepository $ticketRepository,
        AfTicketRepository $afTicketRepository,
        PermissionRepository $permissionRepository,
        AfLessonFaqRepository $afLessonFaqRepository,
        IuLessonQaRepository $iuLessonQaRepository
    ) {
        $this->ticketRepository = $ticketRepository;
        $this->afTicketRepository = $afTicketRepository;
        $this->permissionRepository = $permissionRepository;
        $this->afLessonFaqRepository = $afLessonFaqRepository;
        $this->iuLessonQaRepository = $iuLessonQaRepository;
    }

    public function createTicketSubject(AfCreateUpdateTicketSubjectRequest $request)
    {
        $this->ticketRepository->createTicketSubject($request->categoryId, $request->name, $request->desc, $request->only_logged_in);

        return response()->json(['message' => 'Successfully created ticket subject'], 200);
    }

    public function getTicketCategories()
    {
        $data = $this->ticketRepository->getTicketCategories();

        return response()->json($data, 200);
    }

    public function getTicketSubjectList(AfTicketSubjectListRequest $request)
    {
        $data = $this->ticketRepository->getTicketSubjectPaginatedList($request->query('searchText'));

        return response()->json($data, 200);
    }

    public function getTicketSubject($id)
    {
        $data = $this->ticketRepository->getTicketSubject($id);
        if (! $data) {
            return response()->json(['errors' => Lang::get('general.notFound')], 404);
        }

        return response()->json($data, 200);
    }

    public function updateTicketSubject($id, AfCreateUpdateTicketSubjectRequest $request)
    {
        $this->ticketRepository->updateTicketSubject($id, $request->categoryId, $request->name, $request->desc, $request->only_logged_in);

        return response()->json(['message' => 'Successfully updated ticket subject'], 200);
    }

    public function deleteTicketSubject(int $id)
    {
        $ticketSubject = $this->ticketRepository->getTicketSubject($id);
        if (! $ticketSubject) {
            return response()->json(['errors' => Lang::get('general.notFound')], 404);
        }

        $this->ticketRepository->deleteTicketSubject($ticketSubject);

        return response()->json(['message' => 'Successfully deleted ticket subject'], 200);
    }

    public function getTicketList(AfTicketListRequest $request)
    {
        $userPermissions = $this->permissionRepository->getUserPermissionIds($request->user()->id)->toArray();

        $searchCategories = $this->afTicketRepository->parseSearchCategories($request->category, $userPermissions);
        $searchStatus = $this->afTicketRepository->parseSearchStatus($request->status);
        $searchSubject = $request->subject;

        if (! $searchCategories || empty($searchCategories) || ! $searchStatus) {
            return response()->json(['errors' => Lang::get('auth.forbidden')], 403);
        }

        $data = $this->afTicketRepository->getTicketQuery($searchCategories, $searchStatus, $searchSubject)
            ->oldest()
            ->paginate(20)
            ->appends([
                'category' => $request->category,
                'status' => $request->status,
                'subject' => $request->subject,
            ]);
        $fractal = fractal($data->getCollection(), new AfTicketTransformer());
        $data->setCollection(collect($fractal));

        return response()->json($data, 200);
    }

    public function claimTicket($id, Request $request)
    {
        $userId = $request->user()->id;
        $ticket = $this->afTicketRepository->getTicket($id);

        if (! $ticket) {
            return response()->json(['errors' => Lang::get('general.notFound')], 404);
        }
        if ($ticket->admin_id && $ticket->admin_id == $userId) {
            return response()->json(['errors' => 'You have already claimed the ticket'], 400);
        }
        if ($ticket->admin_id && $ticket->admin_id != $userId) {
            return response()->json(['errors' => 'Ticket already claimed by somebody else'], 400);
        }
        if ($ticket->ticket_status_id == TicketStatusData::RESOLVED) {
            return response()->json(['errors' => 'Cannot claim resolved ticket'], 400);
        }

        $userPermissions = $this->permissionRepository->getUserPermissionIds($request->user()->id)->toArray();
        $userCategoryAccess = $this->afTicketRepository->userTicketCategoriesFromPermissions($userPermissions);
        if (! in_array($ticket->ticket_category_id, $userCategoryAccess)) {
            return response()->json(['errors' => 'Permission missing to claim this ticket'], 400);
        }

        DB::beginTransaction();
        try {
            $ticket->admin_id = $userId;
            $ticket->ticket_status_id = TicketStatusData::IN_PROGRESS;
            $ticket->seen_by_user = false;
            $ticket->save();

            $userName = $request->user()->name;
            $message = $this->ticketRepository->createMessage(
                $userId,
                $ticket->id,
                'Admin "'.$userName.'" has claimed your ticket',
                TicketMessageTypeData::SYSTEM_MESSAGE
            );
            DB::commit();

            if (! $ticket->user_id) {
                return response()->json(['message' => 'Successfully claimed ticket', 'data' => $message], 200);
            }

            if ($ticket->ticket_category_id != TicketCategoryData::LESSON_QA) {
                AfTicketClaimed::dispatch($ticket->id, $ticket->user_id, $ticket->subject, $userName, $message->message);
            }

            return response()->json(['message' => 'Successfully claimed ticket', 'data' => $message], 200);
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Exception: AfTicketController@claimTicket', [$e->getMessage()]);

            return response()->json(['errors' => Lang::get('general.pleaseContactSupportWithCode', ['code' => 500])], 500);
        }
    }

    public function unclaimTicket($id, Request $request)
    {
        $userId = $request->user()->id;
        $ticket = $this->afTicketRepository->getTicket($id);

        if (! $ticket) {
            return response()->json(['errors' => Lang::get('general.notFound')], 404);
        }
        if (! $ticket->admin_id) {
            return response()->json(['errors' => 'You have not claimed the ticket yet'], 400);
        }
        if ($ticket->admin_id && $ticket->admin_id != $userId) {
            return response()->json(['errors' => 'Ticket claimed by somebody else'], 400);
        }

        DB::beginTransaction();
        try {
            $ticket->admin_id = null;
            $ticket->ticket_status_id = TicketStatusData::UNCLAIMED;
            $ticket->seen_by_user = false;
            $ticket->save();

            $userName = $request->user()->name;
            $message = $this->ticketRepository->createMessage(
                $userId,
                $ticket->id,
                'Admin "'.$userName.'" has unclaimed your ticket',
                TicketMessageTypeData::SYSTEM_MESSAGE
            );
            DB::commit();

            if ($ticket->user_id && $ticket->ticket_category_id != TicketCategoryData::LESSON_QA) {
                AfTicketUnclaimed::dispatch($ticket->id, $ticket->user_id, $ticket->subject, $userName, $message->message);
            }

            return response()->json(['message' => 'Successfully unclaimed ticket', 'data' => $message], 200);
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Exception: AfTicketController@unclaimTicket', [$e->getMessage()]);

            return response()->json(['errors' => Lang::get('general.pleaseContactSupportWithCode', ['code' => 500])], 500);
        }
    }

    public function onHoldTicket($id, Request $request)
    {
        $userId = $request->user()->id;
        $ticket = $this->afTicketRepository->getTicket($id);

        if (! $ticket) {
            return response()->json(['errors' => Lang::get('general.notFound')], 404);
        }
        if (! $ticket->admin_id) {
            return response()->json(['errors' => 'You have not claimed the ticket yet'], 400);
        }
        if ($ticket->admin_id && $ticket->admin_id != $userId) {
            return response()->json(['errors' => 'Ticket claimed by somebody else'], 400);
        }

        DB::beginTransaction();
        try {
            $ticket->ticket_status_id = TicketStatusData::ON_HOLD;
            $ticket->save();

            $userName = $request->user()->name;
            $message = $this->ticketRepository->createMessage(
                $userId,
                $ticket->id,
                'Admin "'.$userName.'" has put your ticket on hold',
                TicketMessageTypeData::SYSTEM_MESSAGE
            );
            DB::commit();

            return response()->json(['message' => 'Successfully put ticket on hold', 'data' => $message], 200);
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Exception: AfTicketController@onHoldTicket', [$e->getMessage()]);

            return response()->json(['errors' => Lang::get('general.pleaseContactSupportWithCode', ['code' => 500])], 500);
        }
    }

    public function getTicket($id, Request $request)
    {
        $ticket = $request->page == null ? $this->parseAfGetTicketData($id) : null;
        if ($request->page == null && ! $ticket) {
            return response()->json(['errors' => Lang::get('general.notFound')], 404);
        }

        $messages = $this->afTicketRepository->getTicketMessagesList($id);

        $fractal = fractal($messages->getCollection(), new AfTicketMessageTransformer());
        $messages->setCollection(collect($fractal));

        $data = (object) [
            'messages' => $messages,
        ];
        if ($request->page == null) {
            $data->ticket = fractal($ticket, new AfSpecificTicketTransformer($request->user()->id));
        }
        if ($ticket && $ticket->admin_id == $request->user()->id) {
            $this->ticketRepository->ticketSeenByAdmin($ticket->id, true);
        }

        return response()->json($data, 200);
    }

    private function parseAfGetTicketData($id)
    {
        $searchCategories = array_values(TicketCategoryData::getConstants());
        $searchStatus = array_values(TicketStatusData::getConstants());

        return $this->afTicketRepository->getTicketQuery($searchCategories, $searchStatus, null)
            ->where('tickets.id', $id)
            ->first();
    }

    public function updateTicketCategory($id, AfUpdateTicketCategoryRequest $request)
    {
        $userId = $request->user()->id;
        $ticket = $this->afTicketRepository->getTicket($id);
        if (! $ticket) {
            return response()->json(['errors' => Lang::get('general.notFound')], 404);
        }
        if ($ticket->ticket_category_id == $request->categoryId) {
            return response()->json(['errors' => 'Ticket already has the selected category'], 400);
        }
        if (! $ticket->user_id && $request->categoryId != TicketCategoryData::SYSTEM) {
            return response()->json(['errors' => Lang::get('tickets.guTicketCategoryNonChangeable')], 400);
        }
        if ($ticket->ticket_category_id == TicketCategoryData::LESSON_QA) {
            return response()->json(['errors' => Lang::get('tickets.lectureQaTicketCategoryNonChangeable')], 400);
        }
        if ($request->categoryId == TicketCategoryData::LESSON_QA) {
            return response()->json(['errors' => Lang::get('tickets.ticketCategoryNonChangeableToLectureQa')], 400);
        }

        $hasAdmin = (bool) $ticket->admin_id;
        if ($hasAdmin) {
            $this->unclaimTicket($id, $request);
        }

        DB::beginTransaction();
        try {
            $ticket->ticket_category_id = $request->categoryId;
            $ticket->ticket_status_id = TicketStatusData::UNCLAIMED;
            $ticket->timestamps = false;
            $ticket->save();

            $userName = $request->user()->name;
            $newTicketCategory = TicketCategory::find($request->categoryId);
            $message = $this->ticketRepository->createMessage(
                $userId,
                $ticket->id,
                'Admin "'.$userName.'" has updated ticket category to: "'.$newTicketCategory->name.'"',
                TicketMessageTypeData::ADMIN_ONLY_SYSTEM_MESSAGE
            );
            DB::commit();

            return response()->json(['message' => 'Successfully updated ticket category', 'data' => $message], 200);
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Exception: AfTicketController@updateTicketCategory', [$e->getMessage()]);

            return response()->json(['errors' => Lang::get('general.pleaseContactSupportWithCode', ['code' => 500])], 500);
        }
    }

    public function replyToTicket($id, AfReplyToTicketRequest $request)
    {
        $userId = $request->user()->id;
        $userName = $request->user()->name;
        $ticket = $this->afTicketRepository->getTicket($id);

        DB::beginTransaction();
        try {
            $message = $this->ticketRepository->createMessage(
                $userId,
                $id,
                $request->message,
                TicketMessageTypeData::ADMIN_MESSAGE
            );

            if (! $ticket->user_id) {
                return $this->handleReplyGuestTicket($userName, $ticket, $message);
            }

            if ($ticket->ticket_category_id === TicketCategoryData::LESSON_QA) {
                return $this->handleReplyLessonQATicket($ticket, $message);
            }

            return $this->handleReplyUserTicket($ticket, $message, $request->assets);
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Exception: AfTicketController@replyToTicket', [$e->getMessage()]);

            return response()->json(['errors' => Lang::get('general.pleaseContactSupportWithCode', ['code' => 500])], 500);
        }
    }

    private function handleReplyGuestTicket($userName, $ticket, $message)
    {
        $user_email = $ticket->user_email;

        $ticket->ticket_status_id = TicketStatusData::RESOLVED;
        $ticket->seen_by_user = false;
        $ticket->user_email = 'guest';
        $ticket->save();

        $data = (object) [
            'ticket' => $ticket,
            'message' => $message,
        ];

        DB::commit();
        Mail::to($user_email)->queue(new ClosedGuestTicketEmail(null, $ticket->subject, $userName, $message->message));

        return response()->json(['message' => 'Successfully replied to ticket', 'data' => $data], 200);
    }

    private function handleReplyLessonQATicket($ticket, $message)
    {
        // auto resolve lesson Q&A ticket
        $ticket->ticket_status_id = TicketStatusData::RESOLVED;
        $ticket->seen_by_user = false;
        $ticket->save();
        $data = (object) [
            'ticket' => $ticket,
            'message' => $message,
        ];
        DB::commit();

        if (empty($ticket->lesson->toArray())) {
            return;
        }

        $iuTicketLinkIds = [
            'lessonId' => $ticket->lesson[0]->id,
            'courseId' => $ticket->lesson[0]->course_id,
        ];

        AfTicketReplied::dispatch(
            $ticket->id,
            $ticket->user_id,
            $message->message,
            $ticket->subject,
            $iuTicketLinkIds
        );

        return response()->json(['message' => 'Successfully replied & marked ticket as resolved', 'data' => $data], 200);
    }

    private function handleReplyUserTicket($ticket, $message, $assets)
    {
        $messages = [$message];

        if ($assets) {
            $assets = $this->ticketRepository->handleTicketAssets($ticket->user_id, $assets, $ticket->id, TicketMessageTypeData::ADMIN_ASSET_MESSAGE);
            $messages = [...$messages, ...$assets];
        }

        foreach ($messages as $key => $msg) {
            $messages[$key] = fractal($msg, new AfTicketMessageTransformer());
        }

        $ticket->seen_by_user = false;
        $ticket->save();
        $data = (object) [
            'ticket' => $ticket,
            'message' => $messages,
        ];

        DB::commit();

        AfTicketReplied::dispatch($ticket->id, $ticket->user_id, $message->message, $ticket->subject);

        return response()->json(['message' => 'Successfully replied to ticket', 'data' => $data], 200);
    }

    public function resolveTicket($id, Request $request)
    {
        $userId = $request->user()->id;
        $userName = $request->user()->name;
        $ticket = $this->afTicketRepository->getTicket($id);

        DB::beginTransaction();
        try {
            $message = $this->ticketRepository->createMessage(
                $userId,
                $id,
                '"'.$userName.'" marked the ticket as resolved',
                TicketMessageTypeData::SYSTEM_MESSAGE
            );
            $ticket->ticket_status_id = TicketStatusData::RESOLVED;
            $ticket->seen_by_user = false;
            if (! $ticket->user_id) {
                $ticket->user_email = 'guest';
            }

            $ticket->save();

            DB::commit();

            AfTicketResolved::dispatch(
                $ticket->id,
                $ticket->user_id,
                $ticket->subject,
                $userName,
                $message->message,
            );

            return response()->json(['message' => 'Successfully marked ticket as resolved', 'data' => $message], 200);
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Exception: AfTicketController@resolveTicket', [$e->getMessage()]);

            return response()->json(['errors' => Lang::get('general.pleaseContactSupportWithCode', ['code' => 500])], 500);
        }
    }

    public function getMyTicketList(Request $request)
    {
        $userId = $request->user()->id;
        $searchStatus = $this->afTicketRepository->parseSearchStatus($request->status);
        if (! $searchStatus) {
            return response()->json(['errors' => Lang::get('auth.forbidden')], 403);
        }

        $data = $this->afTicketRepository->getMyTicketList($userId, $searchStatus, $request->subject)
            ->appends([
                'subject' => $request->subject,
                'status' => $request->status,
            ]);

        $fractal = fractal($data->getCollection(), new AfTicketTransformer());
        $data->setCollection(collect($fractal));

        return response()->json($data, 200);
    }

    public function saveTicketAsLessonFaq($id, Request $request)
    {
        $adminId = $request->user()->id;
        $ticket = $this->afTicketRepository->getTicket($id);
        if (! $ticket || empty($ticket?->lesson)) {
            return response()->json(['errors' => Lang::get('general.notFound')], 404);
        }

        if (
            empty($ticket->lesson->toArray()) ||
            $ticket->ticket_category_id != TicketCategoryData::LESSON_QA ||
            $ticket->ticket_status_id != TicketStatusData::RESOLVED ||
            $adminId != $ticket->admin_id
        ) {
            return response()->json(['errors' => 'Ticket can not be saved as lesson faq'], 400);
        }

        $question = $this->afTicketRepository->getTicketMessageByType($ticket->id, TicketMessageTypeData::USER_MESSAGE)->message;

        $lessonId = $ticket->lesson[0]->id;
        $lessonFaq = $this->afLessonFaqRepository->getLessonFaqByLessonId($lessonId, $question);
        if ($lessonFaq) {
            return response()->json(['errors' => 'Question already exist in lesson faq'], 400);
        }

        $answer = $this->afTicketRepository->getTicketMessageByType($ticket->id, TicketMessageTypeData::ADMIN_MESSAGE)->message;
        $this->afLessonFaqRepository->createLessonFaq($lessonId, $question, $answer);

        return response()->json(['message' => 'Successfully created lesson faq'], 200);
    }
}
