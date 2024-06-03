<?php

namespace App\Providers;

use App\Models\Customer;
use App\Models\CourseModule;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // forcing pagination urls and path to https
        if ($this->app->environment('production')) {
            $this->app['request']->server->set('HTTPS', 'on');
        }

        // TODO: requried to check stripe func flow after removing this line
        Cashier::useCustomerModel(Customer::class);

        Validator::extend('string_or_array', function ($attribute, $value, $parameters, $validator) {
            return is_string($value) || is_array($value) || is_null($value);
        });
        Validator::extend('max_mb', function ($attribute, $value, $parameters, $validator) {
            if ($value instanceof UploadedFile && ! $value->isValid()) {
                return false;
            }
            // SplFileInfo::getSize returns filesize in bytes
            $size = $value->getSize() / 1024 / 1024;

            return $size <= $parameters[0];
        });
        Validator::replacer('max_mb', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':'.$rule, $parameters[0], $message);
        });
        Validator::extend('exactly_one_required', function ($attribute, $value, $parameters, $validator) {
            $totalFilled = (bool) $value ? 1 : 0;
            $rowNum = explode('.', $attribute)[0];
            foreach ($parameters as $parameter) {
                $isFilled = (bool) Arr::get($validator->getData(), "$rowNum.$parameter", null);
                if ($isFilled) {
                    $totalFilled++;
                }
                if ($totalFilled > 1) {
                    break;
                }
            }

            return $totalFilled == 1;
        });
        Validator::extend('max_diff_in_months', function ($attribute, $value, $parameters, $validator) {
            $dateBeginning = Carbon::createFromFormat('Y-m-d', Arr::get($validator->getData(), $parameters[0])); // do confirm the date format.
            $dateEnd = Carbon::createFromFormat('Y-m-d', $value);

            return $dateBeginning->diffInMonths($dateEnd) <= $parameters[1];
        });
        Validator::replacer('max_diff_in_months', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':'.'value', $parameters[1], $message);
        });
        Validator::extend('is_divisible_by', function ($attribute, $value, $parameters, $validator) {
            return $value % $parameters[0] === 0;
        });
        Validator::replacer('is_divisible_by', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':value', $parameters[0], $message);
        });

        // TODO: we required to store properly model(class) name as entity_type while creating the entity else morph relation will not work and required to map here.
        Relation::morphMap([
            'course_module' => CourseModule::class,
        ]);
    }
}
