<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Space;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SpaceControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_admin_only_sees_spaces_of_their_company(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        Space::factory()->for($companyA)->create(['name' => 'Sala A1']);
        Space::factory()->for($companyA)->create(['name' => 'Sala A2']);
        Space::factory()->for($companyB)->create(['name' => 'Sala B1']);

        $adminA = User::factory()->admin()->for($companyA)->create();

        $this->actingAs($adminA)
            ->get('/spaces')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Spaces/Index')
                ->has('spaces', 2)
                ->where('spaces.0.name', 'Sala A1')
                ->where('spaces.1.name', 'Sala A2')
            );
    }

    public function test_admin_can_create_a_space(): void
    {
        $company = Company::factory()->create();
        $admin = User::factory()->admin()->for($company)->create();

        $this->actingAs($admin)
            ->post('/spaces', [
                'name' => 'Sala Reuniones',
                'duration_minutes' => 60,
                'price' => 15.50,
                'fixed_duration' => true,
                'show_price' => true,
                'show_duration' => true,
            ])
            ->assertRedirect('/spaces');

        $this->assertDatabaseHas('spaces', [
            'name' => 'Sala Reuniones',
            'company_id' => $company->id,
            'duration_minutes' => 60,
        ]);
    }

    public function test_creating_a_space_requires_a_name(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post('/spaces', ['duration_minutes' => 60])
            ->assertSessionHasErrors('name');

        $this->assertDatabaseCount('spaces', 0);
    }

    public function test_non_admin_user_cannot_create_a_space(): void
    {
        $regular = User::factory()->create();

        $this->actingAs($regular)
            ->post('/spaces', ['name' => 'Sala intrusa'])
            ->assertForbidden();

        $this->assertDatabaseCount('spaces', 0);
    }

    public function test_admin_cannot_access_space_from_another_company(): void
    {
        $companyA = Company::factory()->create();
        $companyB = Company::factory()->create();

        $spaceOfB = Space::factory()->for($companyB)->create();

        $adminA = User::factory()->admin()->for($companyA)->create();

        $this->actingAs($adminA)
            ->get("/spaces/{$spaceOfB->id}/edit")
            ->assertNotFound();

        $this->actingAs($adminA)
            ->delete("/spaces/{$spaceOfB->id}")
            ->assertNotFound();

        $this->assertDatabaseHas('spaces', ['id' => $spaceOfB->id]);
    }
}
