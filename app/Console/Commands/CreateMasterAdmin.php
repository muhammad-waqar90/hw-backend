<?php

namespace App\Console\Commands;

use App\DataObject\RoleData;
use App\Mail\AdminAccountCreatedEmail;
use App\Models\User;
use App\Repositories\AuthenticationRepository;
use App\Repositories\HA\AdminManipulationRepository;
use App\Repositories\IU\IuUserRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class CreateMasterAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:masterAdmin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create master admin for the system';
    /**
     * @var User
     */
    private $user;
    /**
     * @var AuthenticationRepository
     */
    private $authenticationRepository;
    /**
     * @var IuUserRepository
     */
    private $iuUserRepository;

    private $email;
    private $firstName;
    private $lastName;
    /**
     * @var AdminManipulationRepository
     */
    private $adminManipulationRepository;

    /**
     * Create a new command instance.
     *
     * @param User $user
     * @param AuthenticationRepository $authenticationRepository
     * @param IuUserRepository $iuUserRepository
     * @param AdminManipulationRepository $adminManipulationRepository
     */

    public function __construct(User $user, AuthenticationRepository $authenticationRepository, IuUserRepository $iuUserRepository,
        AdminManipulationRepository $adminManipulationRepository)
    {
        parent::__construct();
        $this->user = $user;
        $this->authenticationRepository = $authenticationRepository;
        $this->iuUserRepository = $iuUserRepository;
        $this->adminManipulationRepository = $adminManipulationRepository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if($this->checkIfMasterAdminExists())
            return $this->error('Master admin already exists in the system');

        $this->email = $this->ask('Input email');
        $this->firstName = $this->ask('Input first name');
        $this->lastName = $this->ask('Input last name');

        $this->displaySelectedValues();

        if (!$this->confirm('Do you wish to proceed?'))
            return;

        if(!$this->isValidInput())
            return $this->error('Invalid input!');

        $this->createMasterAdmin();
        $this->info('Successfully created master admin');
    }

    private function checkIfMasterAdminExists()
    {
        return $this->user->where('role_id', RoleData::MASTER_ADMIN)->exists();
    }

    private function displaySelectedValues()
    {
        $this->info('Selected email:');
        $this->line($this->email);
        $this->info('Selected first name:');
        $this->line($this->firstName);
        $this->info('Selected last name:');
        $this->line($this->lastName);
    }

    private function createMasterAdmin()
    {
        DB::beginTransaction();
        try {
            $userName = $this->authenticationRepository->generateUsername($this->firstName, $this->lastName);
            $user = $this->adminManipulationRepository->createAdmin($userName, $this->firstName, $this->lastName, RoleData::MASTER_ADMIN);
            $this->adminManipulationRepository->createAdminProfile($user->id, $this->email);
            $passwordReset = $this->authenticationRepository->createPasswordResetToken($userName);

            Mail::to($this->email)->queue(new AdminAccountCreatedEmail($user, $passwordReset->token, $userName));
            DB::commit();
        } catch(\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    private function isValidInput()
    {
        $values = [
            'email'     => $this->email,
            'firstName' => $this-> firstName,
            'lastName'  => $this->lastName
        ];

        $validator = Validator::make($values, [
            'firstName' => 'required|min:2|max:20',
            'lastName' => 'required|min:2|max:20',
            'email' => 'required|email|max:255',
        ]);

        if($validator->fails())
            return false;

        return true;
    }
}
