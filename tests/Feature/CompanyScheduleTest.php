<?php

namespace Tests\Feature;

use App\Enums\Roles;
use App\Models\Company;
use App\Models\Region;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CompanyScheduleTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Company $company;

    private Region $city;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $country = Region::create([
            'name' => 'Беларусь',
            'name_en' => 'Belarus',
            'name_in' => 'Беларуси',
            'subdomain' => 'belarus',
            'timezone' => 'Europe/Minsk',
            'is_active' => true,
        ]);
        $area = Region::create([
            'name' => 'Минская область',
            'name_en' => 'Minsk region',
            'name_in' => 'Минской области',
            'subdomain' => 'minsk-region',
            'parent_id' => $country->id,
            'is_active' => true,
        ]);
        $this->city = Region::create([
            'name' => 'Минск',
            'name_en' => 'Minsk',
            'name_in' => 'Минске',
            'subdomain' => 'minsk',
            'parent_id' => $area->id,
            'is_active' => true,
        ]);

        $this->user = User::create([
            'name' => 'Иван',
            'email' => 'ivan@example.com',
            'password' => 'password',
            'role' => Roles::CUSTOMER,
            'email_verified_at' => now(),
        ]);

        $this->company = Company::create([
            'user_id' => $this->user->id,
            'region_id' => $this->city->id,
            'legal_name' => 'ООО Ромашка',
            'tax_id' => '100000001',
            'display_name' => 'Ромашка',
            'is_online_store' => false,
        ]);
    }

    private function url(string $path): string
    {
        return "http://minsk.furniture.test/account{$path}";
    }

    /**
     * @return array<string, mixed>
     */
    private function day(int $day, array $overrides = []): array
    {
        return [
            'day_of_week' => $day,
            'is_closed' => false,
            'open_at' => '09:00',
            'close_at' => '18:00',
            'has_break' => false,
            'break_start_at' => '',
            'break_end_at' => '',
            ...$overrides,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function week(array $overrides = []): array
    {
        return collect(range(1, 7))
            ->map(fn (int $day) => $this->day($day, $overrides[$day] ?? []))
            ->all();
    }

    public function test_schedule_page_shows_the_default_week(): void
    {
        $this->actingAs($this->user)
            ->get($this->url('/schedule'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Company/Schedule', false)
                ->has('schedule', 7)
                ->where('schedule.0.day_of_week', 1)
                ->where('schedule.0.open_at', '09:00')
                ->where('schedule.0.close_at', '18:00')
                ->where('schedule.0.is_closed', false)
                ->where('schedule.5.is_closed', true)
                ->where('schedule.6.is_closed', true)
                ->where('exceptions', [])
                ->where('timezone', 'Europe/Minsk'));
    }

    public function test_schedule_and_non_working_days_are_saved(): void
    {
        $payload = [
            'schedule' => $this->week([
                1 => ['has_break' => true, 'break_start_at' => '13:00', 'break_end_at' => '14:00'],
                2 => ['is_closed' => true],
                7 => ['is_closed' => true],
            ]),
            'exceptions' => [
                ['date' => '2026-12-25', 'name' => 'Рождество'],
                ['date' => '2027-01-01', 'name' => ''],
            ],
        ];

        $this->actingAs($this->user)
            ->put($this->url('/schedule'), $payload)
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertSame(7, $this->company->schedules()->count());

        $days = $this->company->schedules()->get()->keyBy('day_of_week');

        $this->assertTrue($days[1]->has_break);
        $this->assertSame('13:00', substr($days[1]->break_start_at, 0, 5));
        $this->assertSame('14:00', substr($days[1]->break_end_at, 0, 5));
        $this->assertTrue($days[2]->is_closed);
        $this->assertSame('00:00', substr($days[2]->open_at, 0, 5));

        $this->assertSame(2, $this->company->scheduleExceptions()->count());
        $this->assertDatabaseHas('company_schedule_exceptions', [
            'company_id' => $this->company->id,
            'date' => '2026-12-25',
            'name' => 'Рождество',
        ]);
        $this->assertDatabaseHas('company_schedule_exceptions', [
            'company_id' => $this->company->id,
            'date' => '2027-01-01',
            'name' => null,
        ]);
    }

    public function test_working_day_requires_open_and_close_times(): void
    {
        $payload = [
            'schedule' => $this->week([
                1 => ['open_at' => '', 'close_at' => ''],
            ]),
            'exceptions' => [],
        ];

        $this->actingAs($this->user)
            ->put($this->url('/schedule'), $payload)
            ->assertSessionHasErrors(['schedule.0.open_at', 'schedule.0.close_at']);

        $this->assertSame(0, $this->company->schedules()->count());
    }

    public function test_break_must_be_inside_working_hours(): void
    {
        $payload = [
            'schedule' => $this->week([
                1 => ['has_break' => true, 'break_start_at' => '08:00', 'break_end_at' => '09:30'],
            ]),
            'exceptions' => [],
        ];

        $this->actingAs($this->user)
            ->put($this->url('/schedule'), $payload)
            ->assertSessionHasErrors('schedule.0.break_start_at');
    }
}
