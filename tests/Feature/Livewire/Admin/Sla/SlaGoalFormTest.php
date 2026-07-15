<?php

namespace Tests\Feature\Livewire\Admin\Sla;

use App\Livewire\Admin\Sla\SlaGoalForm;
use App\Models\SlaGoal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SlaGoalFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_create_form()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(SlaGoalForm::class)
            ->assertSee('SLA');
    }

    public function test_renders_edit_form()
    {
        $this->actingAs(User::factory()->create());

        $goal = SlaGoal::factory()->create([
            'priority' => 'P1',
            'minutes' => 60,
        ]);

        Livewire::test(SlaGoalForm::class, ['id' => $goal->id])
            ->assertSet('priority', 'P1');
    }

    public function test_creates_goal()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(SlaGoalForm::class)
            ->set('priority', 'P2')
            ->set('minutes', 120)
            ->call('save')
            ->assertRedirect(route('admin.sla.goals.index'));

        $this->assertDatabaseHas('sla_goals', [
            'priority' => 'P2',
            'minutes' => 120,
        ]);
    }

    public function test_requires_priority_and_minutes()
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(SlaGoalForm::class)
            ->set('priority', '')
            ->set('minutes', '')
            ->call('save')
            ->assertHasErrors(['priority', 'minutes']);
    }

    public function test_updates_goal()
    {
        $this->actingAs(User::factory()->create());

        $goal = SlaGoal::factory()->create([
            'priority' => 'P3',
            'minutes' => 60,
        ]);

        Livewire::test(SlaGoalForm::class, ['id' => $goal->id])
            ->set('minutes', 90)
            ->call('save')
            ->assertRedirect(route('admin.sla.goals.index'));

        $this->assertDatabaseHas('sla_goals', [
            'id' => $goal->id,
            'minutes' => 90,
        ]);
    }
}
